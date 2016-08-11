<?php

namespace WP_CLI_Valet;

use WP_CLI;
use Symfony\Component\Process\Process;

/**
 * Zonda is golden.
 */
class Valet_Command
{
    /**
     * Associative arguments passed to the command
     * @var array
     */
    protected $args;
    /**
     * The new site name
     * @var string
     */
    protected $site_name;
    /**
     * The full domain of the site
     * @var string
     */
    protected $domain;
    /**
     * If the site should be setup with a secure protocol
     * @var string
     */
    protected $is_secure;
    /**
     * The full site url
     * @var string
     */
    protected $full_url;
    /**
     * The absolute path to the project directory
     * @var string
     */
    protected $full_path;
    /**
     * Whether or not to fancify the output
     * @var bool
     */
    protected $show_progress_bar;

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
     * default:
     * ---
     *
     * [--locale=<locale>]
     * : Select which language you want to install
     * ---
     * default:
     * ---
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
     * [--unsecure]
     * : Provisions the site for http rather than https.
     *
     * [--skip-progress]
     * : Disable the progress bar, gain .1sec
     *
     * @subcommand new
     *
     * @when before_wp_load
     */
    public function _new($args, $assoc_args)
    {
        $this->setup_props($args, $assoc_args);

        if (! is_dir($this->full_path) && ! mkdir($this->full_path, 0755, true)) {
            WP_CLI::error('failed creating directory');
        }

        /**
         * Here we are going to emulate a progress bar, so we don't use WP_CLI::line
         * as that would add a new line at the end, which would ruin the effect.
         **/
        echo 'Don\'t go anywhere, this will only take a second! ';

        // we can spare .3 sec for a touch of zonda here...
        $this->progressBar(3);

        $this->download_wp();
        $this->progressBar(1);

        $this->configure_wp();
        $this->progressBar(1);

        $this->create_db();
        $this->progressBar(1);

        $this->install_wp();
        $this->progressBar(1);

        if ($this->is_secure) {
            $this->valet("secure $this->site_name");
        }

        // big finale
        $this->progressBar(10, 50);
        echo "\n";

        WP_CLI::success("$this->site_name ready! $this->full_url");
    }

    /**
     * Download WordPress core
     */
    protected function download_wp()
    {
        WP_CLI::debug('Downloading WordPress');

        $args = array_filter([
            'version' => $this->args['version'],
            'locale'  => $this->args['locale'],
        ]);

        $this->wp('core download', [], $args);
    }

    /**
     * Generate the configuration file
     */
    protected function configure_wp()
    {
        WP_CLI::debug('Configuring WP');

        $this->wp('core config', [], [
            'dbname'   => $this->args['dbname'] ?: "wp_{$this->site_name}",
            'dbuser'   => $this->args['dbuser'],
            'dbprefix' => $this->args['dbprefix'],
        ]);
    }

    /**
     * Create the database
     */
    protected function create_db()
    {
        if ('sqlite' == $this->args['db']) {
            $this->create_sqlite_db();
        } else {
            $this->create_mysql_db();
        }
    }

    /**
     * Create MySQL database
     */
    protected function create_mysql_db()
    {
        WP_CLI::debug('Creating MySQL DB');

        $this->wp('db create');
    }

    /**
     * Download and install sqlite-integration
     */
    protected function create_sqlite_db()
    {
        WP_CLI::debug('Installing SQLite DB');

        $this->install_sqlite_integration("$this->full_path/wp-content/plugins/");

        copy(
            "$this->full_path/wp-content/plugins/sqlite-integration/db.php",
            "$this->full_path/wp-content/db.php"
        );

        if (! file_exists("$this->full_path/wp-content/db.php")) {
            WP_CLI::error('sqlite-integration install failed');
        }
    }

    /**
     * Install the sqlite-integration plugin, and database drop-in.
     *
     * We can't just run `plugin install ...' because it requires the database to be setup.
     *
     * @param  string $path    The full path to install the plugin to
     * @param  string|null $version The specific plugin version to install
     */
    protected function install_sqlite_integration($path, $version = null)
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
            WP_CLI::debug("Using cached file: $cache_key");
            $cache->export($cache_key, $local_file);
        } else {
            file_put_contents($local_file, file_get_contents("https://downloads.wordpress.org/plugin/sqlite-integration.{$version}.zip"));

            WP_CLI::get_cache()->import($cache_key, $local_file);
        }

        WP_CLI::debug('Extracting sqlite-integration');

        $zip = new \ZipArchive;
        $zip->open($local_file);
        $zip->extractTo("$this->full_path/wp-content/plugins/");
        $zip->close();

        unlink($local_file);
    }

    /**
     * Install WordPress
     */
    protected function install_wp()
    {
        WP_CLI::debug('Installing WordPress');

        $this->wp('core install', [], [
            'url'            => $this->full_url,
            'title'          => $this->site_name,
            'admin_user'     => $this->args['admin_user'],
            'admin_password' => $this->args['admin_password'],
            'admin_email'    => $this->args['admin_email'] ?: "admin@{$this->domain}",
            'skip-email'     => true
        ]);
    }

    /**
     * Setup properties based on command arguments
     * @param  array $args          positional arguments
     * @param  array $assoc_args    associative arguments
     */
    protected function setup_props($args, $assoc_args)
    {
        $this->args       = $assoc_args;
        $this->site_name  = preg_replace('/^a-zA-Z/', '-', $args[0]);
        $this->is_secure  = ! \WP_CLI\Utils\get_flag_value($assoc_args, 'unsecure');
        $tld              = $this->valet('domain');
        $this->domain     = "{$this->site_name}.{$tld}";
        $this->full_path  = getcwd() . '/' . $this->site_name;
        $this->full_url   = sprintf('%s://%s',
            $this->is_secure ? 'https' : 'http',
            $this->domain
        );
        $this->show_progress_bar = ! \WP_CLI\Utils\get_flag_value($assoc_args, 'skip-progress');
    }

    /**
     * Spawn a new WP-CLI process
     * @param  string $command     command to run
     * @param  array  $positional  positional arguments
     * @param  array  $assoc_args  associative arguments
     */
    protected function wp($command, $positional = [], $assoc_args = [])
    {
        WP_CLI::debug("Running 'wp $command' ...");

        $assoc_args['path'] = $this->full_path;

        $php_bin          = WP_CLI::get_php_binary();
        $script_path      = $GLOBALS[ 'argv' ][ 0 ];
        $positional       = implode(' ', array_map('escapeshellarg', $positional));
        $other_assoc_args = \WP_CLI\Utils\assoc_args_to_str($assoc_args);
        $full_command     = "{$php_bin} {$script_path} {$command} {$positional} {$other_assoc_args}";

        $process = \WP_CLI\Process::create($full_command, null, [
            'HOME'                => getenv('HOME'),
            'WP_CLI_PACKAGES_DIR' => getenv('WP_CLI_PACKAGES_DIR'),
            'WP_CLI_CONFIG_PATH'  => getenv('WP_CLI_CONFIG_PATH'),
        ]);
        $result  = $process->run();

        WP_CLI::debug("Completed {$result->command}");

        if ($result->return_code > 0) {
            WP_CLI::error($result->stderr);
        }

        WP_CLI::debug($result->stdout);
    }

    /**
     * Execute a command to the system's valet executable
     *
     * @param  string $command valet command to run
     *
     * @return string
     */
    private function valet($command)
    {
        WP_CLI::debug("Running `valet $command`");

        $process = new Process("valet $command");
        $process->run();
        $output = trim($process->getOutput());

        if (! $process->isSuccessful()) {
            WP_CLI::error(
                sprintf("There was a problem running \"valet %s\"\nError: %s", $command, $output)
            );
        }

        return $output;
    }

    /**
     * Generate a very basic progress bar.
     *
     * @param     $num
     * @param int $fractionOfSec
     */
    protected function progressBar($num, $fractionOfSec = 10)
    {
        if (! $this->show_progress_bar) {
            return;
        }
        foreach (range(1,$num) as $iteration) {
            echo '.';
            usleep(1000000 / $fractionOfSec);
        }
    }
}
