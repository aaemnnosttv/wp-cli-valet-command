<?php

namespace WP_CLI_Valet\Process;

interface ValetInterface
{
    /**
     * Secure the installation with a self-signed TLS certificate.
     *
     * @param $domain
     *
     * @return mixed
     */
    public function secure($domain);

    /**
     * Remove any Valet self-signed TLS certificate for this installation.
     *
     * @param $domain
     *
     * @return mixed
     */
    public function unsecure($domain);
}
