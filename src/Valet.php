<?php

namespace WP_CLI_Valet;

/**
 * Class Valet
 * @package WP_CLI_Valet
 *
 * @method static domain
 * @method static secure(string $name)
 * @method static unsecure(string $name)
 */
class Valet extends Facade
{
    public static function getContainerKey()
    {
        return 'valet';
    }
}
