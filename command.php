<?php

if (defined('WP_CLI') && WP_CLI) {
    WP_CLI::add_command('valet', WP_CLI_Valet\Valet_Command::class);
}
