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
        $this->site_name = preg_replace('/[^a-zA-Z0-9\.]/', '-', $this->positional[0]);
        $this->domain    = sprintf('%s.%s', $this->site_name, Config::get('tld') ?: Config::get('domain'));
    }

    /**
     * @return bool
     */
    public function usingSqlite()
    {
        return 'sqlite' == $this->option('db')
            || $this->option('portable');
    }

    /**
     * Get the database name as specified by the user, or fallback to a sensible default.
     *
     * @return string
     */
    public function databaseName()
    {
        return preg_replace( '/[^a-zA-Z0-9_-]/', '_',
            $this->option('dbname', "wp_{$this->site_name}")
        );
    }

    /**
     * Get the database name as specified by the user, or fallback to an empty string.
     *
     * @return string
     */
    public function databasePassword()
    {
        return $this->option('dbpass', '');
    }

    /**
     * Get the absolute path to the project's root directory.
     *
     * @return string
     */
    public function projectRoot()
    {
        return $this->fullPath();
    }

    /**
     * Get the absolute path to the root install's parent directory.
     *
     * @return string
     */
    public function parentDirectory()
    {
        return dirname($this->fullPath());
    }

    /**
     * Get the absolute file path to the root directory of the install.
     *
     * @param string $relative  A path relative to the project root.
     *
     * @return string
     */
    public function fullPath($relative = '')
    {
        $parts = array_filter([
            $this->option('in', getcwd()),
            $this->site_name,
            $relative
        ]);

        return implode('/', $parts);
    }

    /**
     * Get the full URL to the install.
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
        return ! ($this->option('unsecure') || $this->option('portable'));
    }

    /**
     * Whether or not the install will be a multisite network.
     *
     * @return bool
     */
    public function isMultisite()
    {
        return (bool) $this->option('network') || $this->option('subdomains');
    }

    /**
     * Whether or not the install will use subdomains for multisite.
     *
     * @return bool
     */
    public function isSubdomains()
    {
        return (bool) $this->option('subdomains');
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
