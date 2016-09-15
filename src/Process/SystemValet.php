<?php

namespace WP_CLI_Valet\Process;

use WP_CLI;
use WP_CLI\Process;

class SystemValet implements ValetInterface
{

    /**
     * Get the local domain served by Valet.
     *
     * @return string
     */
    public function domain()
    {
        return $this->run('domain');
    }

    /**
     * Secure the installation with a self-signed TLS certificate.
     *
     * @param string $domain
     *
     * @return mixed
     */
    public function secure($domain)
    {
        return $this->run("secure $domain");
    }

    /**
     * Remove any Valet self-signed TLS certificate for this installation.
     *
     * @param string $domain
     *
     * @return mixed
     */
    public function unsecure($domain)
    {
        return $this->run("unsecure $domain");
    }

    /**
     * Run a valet command
     *
     * @param string $command the sub-command to execute
     *
     * @return string
     */
    protected function run($command)
    {
        $process = Process::create("valet $command", null, [
            'PATH' => getenv('PATH'),
            'HOME' => getenv('HOME'),
        ])->run();

        if ($process->return_code > 0) {
            WP_CLI::debug("valet $command [STDOUT]: $process->stdout", __CLASS__);
            WP_CLI::debug("valet $command [STDERR]: $process->stderr", __CLASS__);
            WP_CLI::error("There was a problem running \"valet $command\"");
        }

        return trim($process->stdout);
    }
}
