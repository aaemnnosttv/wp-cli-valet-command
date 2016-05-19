<?php

if (! class_exists('WP_CLI')) {
    return;
}

WP_CLI::add_command('valet', WP_CLI_Valet\Valet_Command::class);
