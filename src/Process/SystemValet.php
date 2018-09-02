<?php

namespace WP_CLI_Valet\Process;

class SystemValet extends ShellCommand implements ValetInterface
{
    protected $command = 'valet';

    /**
     * Secure the installation with a self-signed TLS certificate.
     *
     * @param string $domain
     *
     * @return mixed
     */
    public function secure($domain)
    {
        return $this->run('secure', [$domain]);
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
        return $this->run('unsecure', [$domain]);
    }
}
