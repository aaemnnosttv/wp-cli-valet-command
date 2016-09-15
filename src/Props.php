<?php

namespace WP_CLI_Valet;

class Props
{
    /* @var array */
    public $positional;

    /* @var array */
    public $options;

    /* @var string */
    public $site_name;

    /* @var string */
    public $domain;

    /**
     * Props constructor.
     *
     * @param array $positional
     * @param array $options
     */
    public function __construct(array $positional, array $options)
    {
        $this->positional = $positional;
        $this->options    = $options;
    }

    /**
     * Populate a few computed properties.
     */
    public function populate()
    {
        $this->site_name = preg_replace('/^a-zA-Z/', '-', $this->positional[0]);
        $this->domain    = sprintf('%s.%s', $this->site_name, Valet::domain());
    }

    /**
     * Get the absolute file path to the root directory of the install.
     *
     * @return string
     */
    public function fullPath()
    {
        return getcwd() . '/' . $this->site_name;
    }

    /**
     * Get the full URL to the new website.
     *
     * @return string
     */
    public function fullUrl()
    {
        return sprintf('%s://%s',
            $this->isSecure() ? 'https' : 'http',
            $this->domain
        );
    }

    /**
     * Whether or not the install will be secured with https.
     *
     * @return bool
     */
    public function isSecure()
    {
        return ! $this->option('unsecure');
    }

    /**
     * Whether or not to show the progress bar while installing.
     *
     * @return bool
     */
    public function showProgress()
    {
        return ! $this->option('skip-progress');
    }

    /**
     * Get an option value by name.
     *
     * @param      $name
     * @param null $default
     *
     * @return mixed|null
     */
    public function option($name, $default = null)
    {
        return isset($this->options[$name]) ? $this->options[$name] : $default;
    }
}
