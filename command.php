<?php

if (! class_exists('WP_CLI')) {
    return;
}

/**
 * Zonda is golden.
 */
class WP_CLI_Valet_Command
{
    protected $args;
    protected $site_name;
    protected $is_secure;
    protected $proto;
    protected $tld;
    protected $domain;
    protected $full_url;
    protected $full_path;

    /**
     * Create a new WordPress install -- fast
     *
     * ## OPTIONS
     * <domain>
     * : Site domain name without TLD.  Eg:  example.com = example
     *
     * [--version=<version>]
     * : WordPress version to install
     * ---
     * default: latest
     * ---
     *
     * [--locale=<locale>]
     * : Select which language you want to install
     *
     * [--db=<db>]
     * : Database driver
     * ---
     * default: mysql
     * options:
     *   - mysql
     *   - sqlite
     * ---
     *
     * [--dbname=<dbname>]
     * : Database name (MySQL only). Default: 'wp_{domain}'
     * ---
     * default:
     * ---
     *
     * [--dbuser=<dbuser>]
     * : Database User (MySQL only)
     * ---
     * default: root
     * ---
     *
     * [--dbpass=<dbpass>]
     * : Set the database user password (MySQL only).  Default: ''
     *
     * [--dbprefix=<dbprefix>]
     * : Set the database table prefix. Default: 'wp_'
     * ---
     * default: 'wp_'
     * ---
     *
     * [--admin_user=<username>]
     * : The username to create for the WordPress admin user.
     * ---
     * default: admin
     * ---
     *
     * [--admin_password=<password>]
     * : The password to create for the WordPress admin user.
     * ---
     * default: admin
     * ---
     *
     * [--admin_email=<email>]
     * : The email to use for the WordPress admin user.
     * ---
     * default:
     * ---
     *
     * [--secure]
     * : Valet will setup the site using HTTPS (default). Use --no-secure for http
     *
     * @when before_wp_load
     */
    public function new($args, $assoc_args)
    {
        $this->setup_props($args, $assoc_args);

        if (! is_dir($this->full_path) && ! mkdir($this->full_path, 0755, true)) {
            WP_CLI::error('failed creating directory');
        }

        WP_CLI::line('Don\'t go anywhere, this will only take a second...');

        $this->download_wp();

        $this->configure_wp();

        $this->create_db();

        $this->install_wp();

        if ($this->is_secure) {
            $this->valet("secure $this->site_name");
        }

        WP_CLI::success("$this->site_name ready! $this->full_url");
    }

    protected function download_wp()
    {
        WP_CLI::debug('Downloading WP');

        WP_CLI::launch_self('core download', [], [
            'path'    => $this->full_path,
            'version' => $this->args['version'],
        ], true, true);
    }

    protected function configure_wp()
    {
        WP_CLI::debug('Configuring WP');

        WP_CLI::launch_self('core config', [], [
            'dbname'   => $this->args['dbname'] ?: "wp_{$this->site_name}",
            'dbuser'   => $this->args['dbuser'],
            'dbprefix' => $this->args['dbprefix'],
        ], false, true, [
            'path' => $this->full_path,
        ]);
    }


    protected function create_db()
    {
        if ('sqlite' == $this->args['db']) {
            return $this->create_sqlite_db();
        }

        return $this->create_mysql_db();
    }

    protected function create_mysql_db()
    {
        WP_CLI::debug('Creating MySQL DB');

        WP_CLI::launch_self('db create', [], [], true, true, [
            'path' => $this->full_path,
        ]);
    }

    protected function create_sqlite_db()
    {
        WP_CLI::debug('Installing SQLite DB');

        if (! class_exists('ZipArchive')) {
            WP_CLI::error('PHP Zip extension seems to be missing.  Can\'t install SQLite integration automatically.');
        }

        WP_CLI::debug('Downloading sqlite-integration');
        $local_file = "$this->full_path/sqlite.zip";

        file_put_contents($local_file, fopen('https://downloads.wordpress.org/plugin/sqlite-integration.1.8.1.zip', 'r'));

		WP_CLI::debug('Extracting sqlite-integration');
        $zip = new ZipArchive;
        $zip->open($local_file);
        $zip->extractTo("$this->full_path/wp-content/plugins/");
        $zip->close();

        unlink($local_file);

        if (! file_exists("$this->full_path/wp-content/plugins/sqlite-integration/db.php")) {
            WP_CLI::error('sqlite-integration install failed');
        }

        copy(
            "$this->full_path/wp-content/plugins/sqlite-integration/db.php",
            "$this->full_path/wp-content/db.php"
        );
    }

    protected function install_wp()
    {
        WP_CLI::debug('Installing WordPress');

        WP_CLI::launch_self('core install', [], [
            'url'            => $this->full_url,
            'title'          => $this->site_name,
            'admin_user'     => $this->args['admin_user'],
            'admin_password' => $this->args['admin_password'],
            'admin_email'    => $this->args['admin_email'] ?: "admin@{$this->domain}",
            'skip-email'     => true
        ], true, true, [
            'path' => $this->full_path,
        ]);
    }

    /**
     * [setup_props description]
     * @param  [type] $_          [description]
     * @param  [type] $assoc_args [description]
     * @return [type]             [description]
     */
    protected function setup_props($args, $assoc_args)
    {
        $this->args       = $assoc_args;

        $this->site_name  = preg_replace('/^a-zA-Z/', '-', $args[0]);
        $this->is_secure  = \WP_CLI\Utils\get_flag_value($assoc_args, 'secure', true);
        $this->proto      = $this->is_secure ? 'https' : 'http';
        $this->tld        = $this->valet('domain');
        $this->domain     = "{$this->site_name}.{$this->tld}";

        $this->full_url   = "{$this->proto}://{$this->domain}";
        $this->full_path  = getcwd() . '/' . $this->site_name;
    }

    /**
     * Execute a command to the system's valet executable
     *
     * @param  [type] $command [description]
     * @return [type]          [description]
     */
    private function valet($command)
    {
        WP_CLI::debug("Running `valet $command`");

        $exit_code = null;

        ob_start();
        system("valet $command", $exit_code);
        $stdout = ob_get_clean();

        if ($exit_code > 0) {
            WP_CLI::error("There was a problem running 'valet $command'.\nError: $stdout");
        }

        return trim($stdout);
    }
}
WP_CLI::add_command('valet', WP_CLI_Valet_Command::class);
