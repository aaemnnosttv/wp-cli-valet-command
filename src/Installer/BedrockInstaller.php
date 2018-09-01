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

        if (! is_dir($full_path = $this->props->projectRoot())) {
            mkdir($full_path, 0755, true);
        }

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

        $env_contents = str_replace(
            [
                'database_name',
                'database_user',
                'database_password',
                'database_host',
                'http://example.com',
                '# DB_PREFIX=wp_'
            ],
            [
                $this->props->databaseName(),
                $this->props->option('dbuser'),
                $this->props->databasePassword(),
                $this->props->option('dbhost', 'localhost'),
                $this->props->fullUrl(),
                sprintf('DB_PREFIX=%s', $this->props->option('dbprefix'))
            ],
            file_get_contents($this->props->fullPath('.env.example'))
        );

        file_put_contents($this->props->fullPath('.env'), $env_contents);
    }
}
