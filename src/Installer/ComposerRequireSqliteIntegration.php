<?php

namespace WP_CLI_Valet\Installer;

use WP_CLI_Valet\Composer;
use WP_CLI_Valet\Props;
use WP_CLI_Valet\ValetCommand as Command;

trait ComposerRequireSqliteIntegration
{
    /**
     * Install the sqlite plugin.
     *
     * @param string|null $version
     */
    public function installSqliteIntegration($version = null)
    {
        Command::debug('Requiring sqlite-integration with Composer.');

        Composer::_require('wpackagist-plugin/sqlite-integration', [
            'working-dir'    => Command::resolve(Props::class)->projectRoot(),
            'no-interaction' => true,
        ]);
    }
}
