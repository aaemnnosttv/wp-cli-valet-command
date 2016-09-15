<?php

namespace WP_CLI_Valet;

use Illuminate\Container\Container;
use WP_CLI;
use WP_CLI_Valet\Installer\BedrockInstaller;
use WP_CLI_Valet\Installer\InstallerInterface;
use WP_CLI_Valet\Installer\WordPressInstaller;
use WP_CLI_Valet\Process\FakeValet;
use WP_CLI_Valet\Process\SystemValet;
use WP_CLI_Valet\Process\SystemWp;

/**
 * Zonda is golden.
 */
class ValetCommand
{
    /**
     * @var Props
     */
    protected $props;

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
        Container::setInstance($container = new Container);

        $container->singleton('valet', getenv('BEHAT_RUN') ? FakeValet::class : SystemValet::class);
        $container->singleton('wp', SystemWp::class);

        $container->bind('wp-installer', WordPressInstaller::class);
        $container->bind('bedrock-installer', BedrockInstaller::class);
    }

    /**
     * Create a new WordPress install -- fast
     *
     * ## OPTIONS
     * <domain>
     * : Site domain name without TLD.  Eg:  example.com = example
     *
     * [--project=<project>]
     * : Composer project to use instead of vanilla WordPress.
     * ---
     * default: wp
     * options:
     *   - wp
     *   - bedrock
     * ---
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
     *
     * [--dbuser=<dbuser>]
     * : Database User (MySQL only)
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
     *
     * [--unsecure]
     * : Provisions the site for http rather than https.
     *
     * [--skip-progress]
     * : Disable the progress bar, gain .1sec
     *
     * @subcommand new
     *
     * @when       before_wp_load
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

        /**
         * Here we are going to emulate a progress bar, so we don't use WP_CLI::line
         * as that would add a new line at the end, which would ruin the effect.
         **/
        echo 'Don\'t go anywhere, this will only take a second! ';
        // we can spare .3 sec for a touch of zonda here...
        $this->progressBar(3);

        $installer->download();
        $this->progressBar(1);

        $installer->configure();
        $this->progressBar(1);

        $installer->createDatabase();
        $this->progressBar(1);

        $installer->runInstall();
        $this->progressBar(1);

        if ($this->props->isSecure()) {
            Valet::secure($this->props->site_name);
        }

        // big finale
        $this->progressBar(10, 50);
        echo "\n";

        WP_CLI::success("{$this->props->site_name} ready! " . $this->props->fullUrl());
    }

    /**
     * @param $project
     *
     * @return InstallerInterface
     */
    protected function getInstaller($project)
    {
        return Container::getInstance()->make("$project-installer");
    }

    /**
     * Setup properties based on command arguments
     * @param  array $args          positional arguments
     * @param  array $assoc_args    associative arguments
     */
    protected function setup_props($args, $assoc_args)
    {
        $this->props = $props = new Props($args, $assoc_args);
        $props->populate();
        Container::getInstance()->instance(Props::class, $props);
    }

    /**
     * Generate a very basic progress bar.
     *
     * @param     $num
     * @param int $fractionOfSec
     */
    protected function progressBar($num, $fractionOfSec = 10)
    {
        if (! $this->props->showProgress()) {
            return;
        }
        foreach (range(1,$num) as $iteration) {
            echo '.';
            usleep(1000000 / $fractionOfSec);
        }
    }

    /**
     * @param $message
     */
    public static function debug($message)
    {
        WP_CLI::debug($message, 'aaemnnosttv/wp-cli-valet-command');
    }
}
