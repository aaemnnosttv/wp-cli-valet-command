<?php

use WP_CLI_Valet\Valet_Command;

if (defined('WP_CLI') && WP_CLI) {
    Valet_Command::register();
}
