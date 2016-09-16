<?php

namespace WP_CLI_Valet;

/**
 * Class Composer
 * @package WP_CLI_Valet
 *
 * @method static createProject(...$args)
 * @method static _require(...$args)
 */
class Composer extends Facade
{
    public static function getContainerKey()
    {
        return 'composer';
    }
}
