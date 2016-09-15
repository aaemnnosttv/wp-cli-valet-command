<?php

namespace WP_CLI_Valet\Process;

use WP_CLI;
use WP_CLI\Process;
use WP_CLI_Valet\Props;
use WP_CLI_Valet\ValetCommand as Command;

class SystemWp
{
    /**
     * @var Props
     */
    protected $props;

    /**
     * SystemWp constructor.
     *
     * @param Props $props
     */
    public function __construct(Props $props)
    {
        $this->props = $props;
    }

    /**
     * Catch-all method
     *
     * @param $method
     * @param $arguments
     */
    public function __call($method, $arguments)
    {
        $command = str_replace('_', ' ', $method);

        $this->run($command, [], $arguments ? $arguments[0] : []);
    }

    /**
     * @param string $command
     * @param array  $positional
     * @param array  $assoc_args
     */
    protected function run($command, array $positional = [], array $assoc_args = [])
    {
        $php_bin     = WP_CLI::get_php_binary();
        $script_path = $GLOBALS['argv'][0];
        $positional  = implode(' ', array_map('escapeshellarg', $positional));
        $assoc_args  = \WP_CLI\Utils\assoc_args_to_str($assoc_args);

        $process = Process::create("$php_bin $script_path $command $positional $assoc_args",
            $this->props->fullPath(),
            [
                'HOME'                => getenv('HOME'),
                'WP_CLI_PACKAGES_DIR' => getenv('WP_CLI_PACKAGES_DIR'),
                'WP_CLI_CONFIG_PATH'  => getenv('WP_CLI_CONFIG_PATH'),
            ]
        )->run();

        Command::debug("Completed $process->command");

        if ($process->return_code > 0) {
            WP_CLI::error($process->stderr);
        }

        Command::debug($process->stdout);
    }
}
