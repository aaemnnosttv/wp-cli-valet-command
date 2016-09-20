<?php

use WP_CLI_Valet\ValetCommand;

if (defined('WP_CLI') && WP_CLI) {
    ValetCommand::register();
}
