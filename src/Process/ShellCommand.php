<?php

namespace WP_CLI_Valet\Process;

use WP_CLI\Process;

class ShellCommand
{
    protected $command = 'echo';

    /**
     * Catch-all method
     *
     * @param $method
     * @param $arguments
     *
     * @return \WP_CLI\ProcessRun
     */
    public function __call($method, $arguments)
    {
        $command = str_replace('_', ' ', $method);

        $positional = $assoc = [];

        foreach ($arguments as $key => $arg) {
            if (is_array($arg)) {
                $assoc = array_merge($assoc, $arg);
            } else {
                $positional[] = $arg;
            }
        }

        return $this->run($command, $positional, $assoc);
    }

    /**
     * Run the process and return the result.
     *
     * @param       $command
     * @param array $positional
     * @param array $assoc
     *
     * @return \WP_CLI\ProcessRun
     */
    protected function run($command, $positional = [], $assoc = [])
    {
        $positional = \WP_CLI\Utils\args_to_str($positional);
        $assoc      = \WP_CLI\Utils\assoc_args_to_str($assoc);

        return Process::create(
            $this->rootCommand() . " $command $positional $assoc",
            $this->getCwd(),
            $this->getEnv()
        )->run();
    }

    protected function rootCommand()
    {
        return $this->command;
    }

    /**
     * @return null
     */
    protected function getCwd()
    {
        return null;
    }

    /**
     * @return array
     */
    protected function getEnv()
    {
        return [
            'HOME' => getenv('HOME'),
            'PATH' => getenv('PATH'),
        ];
    }
}
