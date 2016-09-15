<?php

namespace WP_CLI_Valet\Installer;

interface InstallerInterface
{
    /**
     * Download the project files.
     *
     * @return void
     */
    public function download();

    /**
     * Configure the installation.
     *
     * @return void
     */
    public function configure();

    /**
     * @return void
     */
    public function createDatabase();

    /**
     * Run the WordPress install.
     *
     * @return void
     */
    public function runInstall();
}
