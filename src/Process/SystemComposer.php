<?php

namespace WP_CLI_Valet\Process;

class SystemComposer extends ShellCommand
{
    protected $command = 'composer';

    /**
     * @return \WP_CLI\ProcessRun
     */
    public function createProject()
    {
        return $this->__call('create-project', func_get_args());
    }
}
