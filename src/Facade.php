<?php

namespace WP_CLI_Valet;

use BadMethodCallException;
use Exception;
use WP_CLI_Valet\ValetCommand as Command;

abstract class Facade
{
    /**
     * @throws Exception
     * @return string
     */
    public static function getContainerKey()
    {
        throw new Exception(static::class . ' does not implement the getContainerKey method.');
    }

    /**
     * Proxy method calls on to the appropriate instance.
     *
     * @param $method
     * @param $arguments
     *
     * @return mixed
     */
    public static function __callStatic($method, $arguments)
    {
        $instance = Command::resolve(static::getContainerKey());
        $method = trim($method, '_');

        if (! method_exists($instance, $method) && ! method_exists($instance, '__call')) {
            throw new BadMethodCallException("Method '$method' does not exist.'");
        }

        return call_user_func_array([$instance, $method], $arguments);
    }
}
