<?php

namespace WP_CLI_Valet;

/**
 * Class WP
 * @package WP_CLI_Valet
 *
 * @method static void config(...$args)
 * @method static void core(...$args)
 * @method static void db(...$args)
 */
class WP extends Facade
{
    public static function getContainerKey()
    {
        return 'wp';
    }
}
