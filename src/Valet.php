<?php

namespace WP_CLI_Valet;

/**
 * Class Valet
 * @package WP_CLI_Valet
 *
 * @method static domain
 * @method static secure(string $full_path)
 * @method static unsecure(string $full_path)
 */
class Valet extends Facade
{
    public static function getContainerKey()
    {
        return 'valet';
    }
}
