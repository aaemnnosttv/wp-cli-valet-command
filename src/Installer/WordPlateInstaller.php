<?php

namespace WP_CLI_Valet\Installer;

use WP_CLI_Valet\Composer;
use WP_CLI_Valet\ValetCommand as Command;

class WordPlateInstaller extends WordPressInstaller
{
    use ComposerRequireSqliteIntegration;

    protected $contentDir = 'public';

    /**
     * Download project files.
     */
    public function download()
    {
        Command::debug('Installing WordPlate via Composer');

        $process = Composer::createProject('wordplate/wordplate', $this->props->site_name, [
            'working-dir'    => dirname($this->props->fullPath()),
            'no-interaction' => true,
        ]);

        if (! file_exists($this->props->fullPath('wp-cli.yml'))) {
            file_put_contents($this->props->fullPath('wp-cli.yml'), "path: public/wordpress\n");
        }

        Command::debug((string) $process);
    }

    /**
     * Configure the .env
     */
    public function configure()
    {
        Command::debug('Configuring .env');

        $env = file_get_contents($this->props->fullPath('.env'));

        $env = str_replace(
            [
                'DB_NAME=homestead',
                'DB_USER=homestead',
                'DB_PASSWORD=secret',
                'DB_HOST=localhost',
            ],
            [
                'DB_NAME=' . $this->props->databaseName(),
                'DB_USER=' . $this->props->option('dbuser'),
                'DB_PASSWORD=' . $this->props->databasePassword(),
                'DB_HOST=' . $this->props->option('dbhost', 'localhost'),
            ],
            $env
        );

        file_put_contents($this->props->fullPath('.env'), $env);
    }
}
