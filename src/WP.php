<?php

namespace WP_CLI_Valet;

/**
 * Class WP
 * @package WP_CLI_Valet
 *
 * @method static void core_download(array $args = null)
 * @method static void core_config(array $args = null)
 * @method static void core_install(array $args = null)
 * @method static void db_create(array $args = null)
 */
class WP extends Facade
{
    public static function getContainerKey()
    {
        return 'wp';
    }
}
