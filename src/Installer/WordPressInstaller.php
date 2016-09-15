<?php

namespace WP_CLI_Valet\Installer;

use WP_CLI;
use WP_CLI_Valet\Props;
use WP_CLI_Valet\ValetCommand as Command;
use WP_CLI_Valet\WP;

class WordPressInstaller implements InstallerInterface
{
    /**
     * @var Props
     */
    protected $props;

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

        WP::core_download($args);
    }

    /**
     * Configure the installation.
     *
     * @return void
     */
    public function configure()
    {
        $args = [
            'dbname'   => $this->props->option('dbname', "wp_{$this->props->site_name}"),
            'dbuser'   => $this->props->option('dbuser'),
            'dbpass'   => $this->props->option('dbpass', ''),
            'dbprefix' => $this->props->option('dbprefix'),
        ];

        WP::core_config($args);
    }

    public function createDatabase()
    {
        if ('sqlite' == $this->props->option('db')) {
            $this->createSqlite();
        } else {
            $this->createMySql();
        }
    }

    protected function createMySql()
    {
        Command::debug('Creating MySQL DB');

        WP::db_create();
    }

    public function createSqlite()
    {
        Command::debug('Installing SQLite DB');

        $this->installSqliteIntegration();

        copy(
            $this->contentPath('plugins/sqlite-integration/db.php'),
            $this->contentPath('db.php')
        );

        if (! file_exists($this->contentPath('db.php'))) {
            WP_CLI::error('sqlite-integration install failed');
        }
    }

    /**
     * Run the WordPress install.
     *
     * @return void
     */
    public function runInstall()
    {
        Command::debug('Installing WordPress');

        WP::core_install([
            'url'            => $this->props->fullUrl(),
            'title'          => $this->props->site_name,
            'admin_user'     => $this->props->option('admin_user'),
            'admin_password' => $this->props->option('admin_password'),
            'admin_email'    => $this->props->option('admin_email', "admin@{$this->props->domain}"),
            'skip-email'     => true
        ]);
    }

    /**
     * Install the sqlite-integration plugin, and database drop-in.
     *
     * We can't just run `plugin install ...' because it requires the database to be setup.
     *
     * @param  string|null $version The specific plugin version to install
     */
    protected function installSqliteIntegration($version = null)
    {
        /**
         * If no version is requested, fetch the latest from the api
         */
        if (! $version) {
            $response = json_decode(file_get_contents("https://api.wordpress.org/plugins/info/1.0/sqlite-integration.json"));

            if (! $response) {
                WP_CLI::error('There was a problem parsing the response from the wordpress.org api. Try again!');
            }

            $version = $response->version;
        }

        $cache = WP_CLI::get_cache();
        $cache_key = "aaemnnosttv/wp-cli-valet-command/sqlite-integration.{$version}.zip";
        $local_file = "/tmp/sqlite-integration.{$version}.zip";

        if ($cache->has($cache_key)) {
            Command::debug("Using cached file: $cache_key");
            $cache->export($cache_key, $local_file);
        } else {
            file_put_contents($local_file, file_get_contents("https://downloads.wordpress.org/plugin/sqlite-integration.{$version}.zip"));

            WP_CLI::get_cache()->import($cache_key, $local_file);
        }

        Command::debug('Extracting sqlite-integration');

        $zip = new \ZipArchive;
        $zip->open($local_file);
        $zip->extractTo($this->contentPath('plugins/'));
        $zip->close();

        unlink($local_file);
    }

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
