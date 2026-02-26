<?php

namespace WP_CLI_Valet;

use Illuminate\Container\Container;
use Symfony\Component\Filesystem\Filesystem;
use WP_CLI;
use WP_CLI_Valet\Installer\BedrockInstaller;
use WP_CLI_Valet\Installer\InstallerInterface;
use WP_CLI_Valet\Installer\WordPressInstaller;
use WP_CLI_Valet\Process\FakeValet;
use WP_CLI_Valet\Process\SystemComposer;
use WP_CLI_Valet\Process\SystemValet;
use WP_CLI_Valet\Process\SystemWp;

/**
 * White-glove turn-key install services.
 */
class ValetCommand
{
    /**
     * @var Props
     */
    protected $props;

    /**
     * @var Container
     */
    protected static $container;

    /**
     * Register the command with WP-CLI.
     */
    public static function register()
    {
        WP_CLI::add_command('valet', static::class, [
            'before_invoke' => [static::class, 'boot']
        ]);
    }

    /**
     * Boot the command.
     */
    public static function boot()
    {
        static::$container = $container = new Container;

        $container->singleton('valet', getenv('BEHAT_RUN') ? FakeValet::class : SystemValet::class);
        $container->singleton('wp', SystemWp::class);
        $container->singleton('composer', SystemComposer::class);
        $container->singleton('config', function () {
            return getenv('BEHAT_RUN')
                ? new ValetConfig(['tld' => 'dev'])
                : ValetConfig::loadSystem();
        });

        $container->bind('wp-installer', WordPressInstaller::class);
        $container->bind('bedrock-installer', BedrockInstaller::class);
    }

    /**
     * Create a new WordPress install -- fast
     *
     * This command will spin up a new WordPress installation -- complete with database and https
     * _ready-to-use in your browser_ faster than you can put your pants on.
     * 
     * **NB** If you have not used `valet park` for the directory or parent directory you are 
     * running the installation in you need to do a `valet link` to make sure the site will run
     * without running into 404s.
     *
     * ## OPTIONS
     *
     * <name>
     * : Site domain name without TLD. This will become the directory name of the project root.
     * Eg: To create an install for example.dev, `wp valet new example`
     *
     * [--project=<project>]
     * : The WordPress project to install. Choose from any project supported by Laravel Valet.
     * ---
     * default: wp
     * options:
     *   - wp
     *   - bedrock
     * ---
     *
     * [--in=<dir>]
     * : Specify the path to the parent directory to create the install in.
     * Defaults to the current working directory.
     *
     * [--version=<version>]
     * : WordPress version to install.
     * ---
     * default: latest
     * ---
     *
     * [--locale=<locale>]
     * : Select which language you want to install.
     *
     * [--db=<db>]
     * : Database driver to provision the site with.
     * ---
     * default: mysql
     * options:
     *   - mysql
     *   - sqlite
     * ---
     *
     * [--dbname=<dbname>]
     * : Database name (MySQL only).
     * Defaults to 'wp_<name>'.
     *
     * [--dbuser=<dbuser>]
     * : Database User (MySQL only).
     * ---
     * default: root
     * ---
     *
     * [--dbpass=<dbpass>]
     * : Set the database user password (MySQL only).
     * ---
     * Default: ''
     * ---
     *
     * [--dbhost=<dbhost>]
     * : Set the database host.
     * ---
     * default: localhost
     * ---
     *
     * [--dbprefix=<dbprefix>]
     * : Set the database table prefix.
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
     *
     * [--unsecure]
     * : Provision the site for http rather than https.
     *
     * [--portable]
     * : Provision the site to be portable. Implies --unsecure and --db=sqlite.
     *
     * [--network]
     * : Install WordPress as a multisite network.
     *
     * [--subdomains]
     * : Use subdomains for multisite. Implies --network.
     *
     * @subcommand new
     *
     * @when before_wp_load
     *
     * @param $args
     * @param $assoc_args
     */
    public function _new($args, $assoc_args)
    {
        $this->setup_props($args, $assoc_args);

        $project = $this->props->option('project');

        if (! $installer = $this->getInstaller($project)) {
            WP_CLI::error("No installer found for project: '$project'");
        }

        static::debug(sprintf('Installing using %s', get_class($installer)));

        WP_CLI::line("Don't go anywhere, this should only take a second...");

        try {
            $installer->download();
            $installer->configure();
            $installer->createDatabase();
            $installer->runInstall();
        } catch (\Exception $e) {
            $this->exceptionHandler($e);
        }

        if ($this->props->isSecure()) {
            Valet::secure($this->props->site_name);
        }

        WP_CLI::success($this->props->site_name . ' ready! ' . $this->props->fullUrl());
    }

    /**
     * Completely remove an installation.
     *
     * This will drop the database, and delete all of the files as well as
     * remove any self-signed TLS certificate that was generated for serving
     * this install over https.
     *
     * ## OPTIONS
     *
     * <name>
     * : Site domain name without TLD. It should match the directory name of the project root.
     *
     * [--yes]
     * : Pre-approve the confirmation to delete all files and drop the database.
     *
     * @when before_wp_load
     *
     * @param $args
     * @param $assoc_args
     */
    public function destroy($args, $assoc_args)
    {
        $this->setup_props($args, $assoc_args);
        $project_abspath = $this->props->projectRoot();

        if (! is_dir($project_abspath)) {
            WP_CLI::error("No install exists at $project_abspath");
        }

        static::debug("Preparing to destroy {$this->props->site_name}.");

        WP_CLI::confirm('This will delete all files and drop the database for the install. Are you sure?', $assoc_args);

        try {
            static::debug('Dropping database...');
            WP::db('drop', ['yes' => true]);
        } catch (\Exception $e) {
            WP_CLI::warning('The database was unable to be dropped. Disregard this warning if using sqlite for this site.');
        }

        try {
            static::debug('Removing any TLS certificate for this install...');
            Valet::unsecure($this->props->site_name);
        } catch (\Exception $e) {
            $this->exceptionHandler($e);
        }

        static::debug('Removing all files...');
        if ($this->rm_rf($project_abspath)) {
            WP_CLI::success("{$this->props->site_name} was destroyed.");
        } else {
            WP_CLI::warning('Some files were unable to be deleted.');
        }
    }

    /**
     * Recursively delete all files and directories within (and including) the given path.
     *
     * @param $abspath
     *
     * @return bool
     */
    protected function rm_rf($abspath)
    {
        (new Filesystem())->remove($abspath);

        return ! file_exists($abspath);
    }

    /**
     * @param $project
     *
     * @return InstallerInterface
     */
    protected function getInstaller($project)
    {
        return $this->resolve("$project-installer");
    }

    /**
     * Setup properties based on command arguments.
     *
     * @param  array $args          positional arguments
     * @param  array $assoc_args    associative arguments
     */
    protected function setup_props($args, $assoc_args)
    {
        $this->props = $props = new Props($args, $assoc_args);
        $props->populate();
        $this->container()->instance(Props::class, $props);
    }

    /**
     * @param mixed ..$message
     */
    public static function debug()
    {
        foreach (func_get_args() as $arg) {
            $message = is_scalar($arg) ? (string) $arg : print_r($arg, true);
            WP_CLI::debug($message, 'aaemnnosttv/wp-cli-valet-command');
        }
    }

    /**
     * Get the IoC container instance for the command.
     *
     * @return Container
     */
    public static function container()
    {
        return static::$container;
    }

    /**
     * Resolve an instance from the container.
     *
     * @param $abstract
     *
     * @return mixed
     */
    public static function resolve($abstract)
    {
        return static::container()->make($abstract);
    }

    /**
     * @param $e
     */
    protected function exceptionHandler($e)
    {
        WP_CLI::error(preg_replace('/^Error: /', '', $e->getMessage()));
    }
}
