<?php

namespace WP_CLI_Valet;

/**
 * @method static get(string $key)
 */
class Config extends Facade
{
    public static function getContainerKey()
    {
        return 'config';
    }
}
