<?php

namespace WP_CLI_Valet;

class ValetConfig
{
    protected $data;

    /**
     * ValetConfig constructor.
     *
     * @param $data
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * @throws \Exception
     */
    public static function loadSystem()
    {
        $config_path = file_exists("{$_SERVER['HOME']}/.config/valet/config.json")
            ? "{$_SERVER['HOME']}/.config/valet/config.json"
            : "{$_SERVER['HOME']}/.valet/config.json";

        if (! file_exists($config_path)) {
            throw new \Exception('Valet configuration file not found.');
        }

        return new static(json_decode(file_get_contents($config_path), true));
    }

    public function get($key)
    {
        return isset($this->data[$key]) ? $this->data[$key] : null;
    }
}
