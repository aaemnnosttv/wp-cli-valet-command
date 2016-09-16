<?php

namespace WP_CLI_Valet;

/**
 * Class Composer
 * @package WP_CLI_Valet
 *
 * @method static config(...$args)
 * @method static createProject(...$args)
 * @method static install(...$args)
 * @method static _require(...$args)
 */
class Composer extends Facade
{
    public static function getContainerKey()
    {
        return 'composer';
    }
}
