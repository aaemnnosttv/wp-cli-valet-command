<?php

namespace WP_CLI_Valet\Installer;

use WP_CLI\Process;
use WP_CLI_Valet\ValetCommand as Command;

class BedrockInstaller extends WordPressInstaller
{
    protected $contentDir = 'web/app';

    /**
     * Download project files.
     */
    public function download()
    {
        Command::debug('Installing Bedrock via Composer');

        $process = Process::create("composer create-project --no-interaction roots/bedrock {$this->props->site_name}",
            dirname($this->props->fullPath()),
            [
                'HOME' => getenv('HOME')
            ]
        )->run();

        Command::debug((string) $process);
    }

    /**
     * Configure the .env
     */
    public function configure()
    {
        Command::debug('Configuring .env');

        $env = file_get_contents($this->props->fullPath('.env.example'));

        $env = str_replace(
            [
                'database_name',
                'database_user',
                'database_password',
                'database_host',
                'http://example.com',
            ],
            [
                $this->props->databaseName(),
                $this->props->option('dbuser'),
                $this->props->databasePassword(),
                $this->props->option('dbhost', 'localhost'),
                $this->props->fullUrl(),
            ],
            $env
        );

        file_put_contents($this->props->fullPath('.env'), $env);
    }

    /**
     * Install the sqlite plugin.
     *
     * @param string|null $version
     */
    public function installSqliteIntegration($version = null)
    {
        Command::debug('Requiring sqlite-integration with Composer.');

        $process = Process::create('composer require --no-interaction wpackagist-plugin/sqlite-integration',
            $this->props->fullPath(),
            [
                'HOME' => getenv('HOME')
            ]
        )->run();

        Command::debug((string) $process);
    }
}
