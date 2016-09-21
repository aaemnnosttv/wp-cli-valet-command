<?php

namespace WP_CLI_Valet\Installer;

use WP_CLI_Valet\Composer;
use WP_CLI_Valet\ValetCommand as Command;

class ThemosisInstaller extends WordPressInstaller
{
    use ComposerRequireSqliteIntegration;

    protected $contentDir = 'htdocs/content';

    /**
     * Download project files.
     */
    public function download()
    {
        Command::debug('Installing Themosis via Composer');

        Composer::createProject('themosis/themosis', $this->props->site_name, [
            'working-dir'    => $this->props->parentDirectory(),
            'no-interaction' => true,
        ]);
    }

    /**
     * Configure the environment file.
     */
    public function configure()
    {
        Command::debug('Configuring .env.local.php');

        $env = str_replace(
            [
                'database-name',
                'database-user',
                'database-password',
                'localhost',
                'http://domain.tld'
            ],
            [
                $this->props->databaseName(),
                $this->props->option('dbuser'),
                $this->props->databasePassword(),
                $this->props->option('dbhost', 'localhost'),
                $this->props->fullUrl()
            ],
            file_get_contents($this->props->fullPath('.env.local.php'))
        );

        file_put_contents($this->props->fullPath('.env.local.php'), $env);

        /**
         * Themosis determines the environment by matching against the system's hostname.
         */
        $config = file_get_contents($this->props->fullPath('config/environment.php'));
        $config = str_replace('your-local-hostname', gethostname(), $config);
        file_put_contents($this->props->fullPath('config/environment.php'), $config);
    }
}

