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
            'no-install'     => true,
            'no-interaction' => true,
            'no-dev'         => true,
        ]);
        // Install dependencies with updates (required for older PHP)
        Composer::update([
            'working-dir'    => $this->props->parentDirectory(),
            'no-interaction' => true,
            'no-dev'         => true,
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
            ],
            [
                $this->props->databaseName(),
                $this->props->option('dbuser'),
                $this->props->databasePassword(),
                $this->props->option('dbhost', 'localhost'),
                $this->props->fullUrl(),
            ],
            file_get_contents($this->props->fullPath('.env.example'))
        );
        // DB_PREFIX value is quoted in newer versions, not in older.
        $env_contents = preg_replace(
            '/# DB_PREFIX=.*/',
            sprintf('DB_PREFIX=\'%s\'', $this->props->option('dbprefix')),
            $env_contents
        );

        file_put_contents($this->props->fullPath('.env'), $env_contents);
    }
}
