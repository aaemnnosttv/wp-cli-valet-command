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
        WP::core('config', [
            'dbname'   => $this->props->databaseName(),
            'dbuser'   => $this->props->option('dbuser'),
            'dbpass'   => $this->props->databasePassword(),
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
     * Install the sqlite integration database drop-in.
     */
    protected function installSqliteIntegration()
    {
        $cache = WP_CLI::get_cache();
        $version = 'a7ee20a021f9df42cd1880ca926fa0ea45c39dc8';
        $cache_key = "aaemnnosttv/wp-cli-valet-command/wp-sqlite-db/$version/db.php";
        $local_file = $this->contentPath('db.php');

        if ($cache->has($cache_key)) {
            Command::debug("Using cached file: $cache_key");
            $cache->export($cache_key, $local_file);
        } else {
            file_put_contents($local_file, file_get_contents("https://github.com/aaemnnosttv/wp-sqlite-db/raw/$version/db.php"));

            WP_CLI::get_cache()->import($cache_key, $local_file);
        }
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
