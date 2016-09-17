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

        Composer::createProject('roots/bedrock', $this->props->site_name, [
            'working-dir'    => $this->props->parentDirectory(),
            'no-interaction' => true,
        ]);
    }

    /**
     * Configure the .env
     */
    public function configure()
    {
        Command::debug('Configuring .env');

        $env_file_path = $this->props->fullPath('.env.example');

        $env_contents = str_replace(
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
            file_get_contents($env_file_path)
        );

        file_put_contents($env_file_path, $env_contents);
    }
}
