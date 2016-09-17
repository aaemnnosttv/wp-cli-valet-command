<?php

namespace WP_CLI_Valet\Process;

use WP_CLI_Valet\Props;

class SystemWp extends ShellCommand
{
    protected $command = 'wp';

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

    protected function rootCommand()
    {
        return \WP_CLI::get_php_binary() . ' ' . $GLOBALS['argv'][0];
    }

    protected function getCwd()
    {
        return $this->props->projectRoot();
    }

    protected function getEnv()
    {
        return array_merge(parent::getEnv(), [
            'WP_CLI_PACKAGES_DIR' => getenv('WP_CLI_PACKAGES_DIR'),
            'WP_CLI_CONFIG_PATH'  => getenv('WP_CLI_CONFIG_PATH'),
        ]);
    }
}
