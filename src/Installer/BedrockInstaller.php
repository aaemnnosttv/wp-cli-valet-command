<?php

namespace WP_CLI_Valet\Installer;

use WP_CLI_Valet\Composer;
use WP_CLI_Valet\ValetCommand as Command;

class BedrockInstaller extends WordPressInstaller
{
    use ComposerRequireSqliteIntegration;

    protected $contentDir = 'web/app';

    /**
     * Download project files.
     */
    public function download()
    {
        Command::debug('Installing Bedrock via Composer');

        $process = Composer::createProject('roots/bedrock', $this->props->site_name, [
            'working-dir'    => dirname($this->props->fullPath()),
            'no-interaction' => true,
        ]);

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
}
