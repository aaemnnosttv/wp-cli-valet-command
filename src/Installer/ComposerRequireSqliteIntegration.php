<?php

namespace WP_CLI_Valet\Installer;

use WP_CLI_Valet\Composer;
use WP_CLI_Valet\Props;
use WP_CLI_Valet\ValetCommand as Command;

trait ComposerRequireSqliteIntegration
{
    /**
     * Install the sqlite database drop-in.
     */
    public function installSqliteIntegration()
    {
        Command::debug('Requiring wp-sqlite-db driver with Composer');

        $workingDir = Command::resolve(Props::class)->projectRoot();
        $flags      = [
            'working-dir'    => $workingDir,
            'no-interaction' => true,
        ];

        Composer::_require('koodimonni/composer-dropin-installer', $flags);

        Composer::config('extra.dropin-paths.web/app/', 'package:aaemnnosttv/wp-sqlite-db:db.php', $flags);

        Composer::_require('aaemnnosttv/wp-sqlite-db:dev-master', $flags);
    }
}
