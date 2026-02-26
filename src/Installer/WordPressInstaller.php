<?php

namespace WP_CLI_Valet\Installer;

use WP_CLI;
use WP_CLI_Valet\Props;
use WP_CLI_Valet\ValetCommand as Command;
use WP_CLI_Valet\WP;

/**
 * Class WordPressInstaller
 * @package WP_CLI_Valet\Installer
 */
class WordPressInstaller implements InstallerInterface
{
    /**
     * @var Props
     */
    protected $props;

    /**
     * @var string
     */
    protected $contentDir = 'wp-content';

    /**
     * BaseInstaller constructor.
     *
     * @param Props $props
     */
    public function __construct(Props $props)
    {
        $this->props = $props;
    }

    /**
     * Download the project files.
     *
     * @return void
     */
    public function download()
    {
        Command::debug('Downloading WordPress');

        $args = array_filter([
            'version' => $this->props->option('version'),
            'locale'  => $this->props->option('locale'),
        ]);

        if (! is_dir($full_path = $this->props->projectRoot())) {
            mkdir($full_path, 0755, true);
        }

        WP::core('download', $args);
    }

    /**
     * Configure the installation.
     *
     * @return void
     */
    public function configure()
    {
        WP::config('create', [
            'dbname'   => $this->props->databaseName(),
            'dbuser'   => $this->props->option('dbuser'),
            'dbpass'   => $this->props->databasePassword(),
            'dbhost'   => $this->props->option('dbhost'),
            'dbprefix' => $this->props->option('dbprefix'),
        ]);
    }

    /**
     * Create the database for the install.
     */
    public function createDatabase()
    {
        if ($this->props->usingSqlite()) {
            $this->createSqlite();
        } else {
            $this->createMySql();
        }
    }

    /**
     * Create a MySql database for the install.
     */
    protected function createMySql()
    {
        Command::debug('Creating MySQL DB');

        WP::db('create');
    }

    /**
     * Install the sqlite plugin and database drop-in.
     */
    public function createSqlite()
    {
        Command::debug('Installing SQLite DB drop-in');

        $this->installSqliteIntegration();
    }

    /**
     * Run the WordPress install.
     *
     * @return void
     */
    public function runInstall()
    {
        Command::debug('Installing WordPress');

        if ($this->props->isMultisite()) {
            $this->runMultisiteInstall();
        } else {
            $this->runSingleInstall();
        }
    }

    /**
     * Run single site WordPress install.
     */
    protected function runSingleInstall()
    {
        WP::core('install', [
            'url'            => $this->props->fullUrl(),
            'title'          => $this->props->site_name,
            'admin_user'     => $this->props->option('admin_user'),
            'admin_password' => $this->props->option('admin_password'),
            'admin_email'    => $this->props->option('admin_email', "admin@{$this->props->domain}"),
            'skip-email'     => true
        ]);
    }

    /**
     * Run multisite network WordPress install.
     */
    protected function runMultisiteInstall()
    {
        $args = [
            'url'            => $this->props->fullUrl(),
            'title'          => $this->props->site_name,
            'admin_user'     => $this->props->option('admin_user'),
            'admin_password' => $this->props->option('admin_password'),
            'admin_email'    => $this->props->option('admin_email', "admin@{$this->props->domain}"),
            'skip-email'     => true
        ];

        if ($this->props->isSubdomains()) {
            $args['subdomains'] = true;
        }

        WP::core('multisite-install', $args);
    }

    /**
     * Install the sqlite database drop-in.
     */
    protected function installSqliteIntegration()
    {
        $cache = WP_CLI::get_cache();
        $version = $this->getWpSqliteDbVersion();
        Command::debug("Installing wp-sqlite-db: $version");
        $cache_key = "aaemnnosttv/wp-sqlite-db/raw/$version/src/db.php";

        if ($cache->has($cache_key)) {
            Command::debug("Using cached file: $cache_key");
        } else {
            Command::debug("Downloading: https://github.com/$cache_key");
            $http_request = \WP_CLI\Utils\http_request('GET', "https://github.com/$cache_key");
            Command::debug($http_request->status_code, $http_request->headers->getAll());

            if ($http_request->success) {
                $cache->write($cache_key, $http_request->body);
            } else {
                WP_CLI::error("Failed to download $cache_key");
            }
        }

        // Ensure wp-content directory exists as it will not
        // if skip-content is passed.
        if (! is_dir($this->contentPath())) {
            mkdir($this->contentPath(), 0755, true);
        }

        $cache->export($cache_key, $this->contentPath('db.php'));
    }

    /**
     * Get the latest version to install.
     *
     * Attempts to fetch the latest commit hash from the GitHub API, with a fallback to 'master'.
     * Technically it points to the same file, but a hash is better for caching.
     *
     * @return string
     */
    protected function getWpSqliteDbVersion()
    {
        $cache = WP_CLI::get_cache();
        $cache_key = 'aaemnnosttv/wp-cli-valet-command/wp-sqlite-db/sha';
        // A token isn't necessary but helps rule out rate limiting as a point of failure in CI
        $token = getenv('WP_CLI_VALET_GITHUB_TOKEN');
        $master_branch = \WP_CLI\Utils\http_request('GET',
            'https://api.github.com/repos/aaemnnosttv/wp-sqlite-db/branches/master',
            null,
            $token ? ['Authorization' => "token $token"] : []
        );

        // Always try to get the latest commit hash, if possible.
        if ($master_branch->success && ($master = json_decode($master_branch->body, true)) && isset($master['commit']['sha'])) {
            $cache->write($cache_key, $master['commit']['sha']);

            return $master['commit']['sha'];
        } elseif ($cache->has($cache_key)) {
            // If we got here, the API request failed for some reason, so use a stale version if it exists
            $version = $cache->read($cache_key);

            WP_CLI::warning("Unable to get latest wp-sqlite-db commit, falling back to $version (cached).");

            return $version;
        }

        WP_CLI::warning('Unable to get latest wp-sqlite-db commit, falling back to master.');

        return 'master';
    }

    /**
     * Get the absolute path to the content directory, optionally combined with the given relative path.
     *
     * @param string $relative
     *
     * @return string
     */
    protected function contentPath($relative = '')
    {
        $path = array_filter([
            $this->props->fullPath(),
            $this->contentDir,
            $relative
        ]);

        return implode('/', $path);
    }
}
