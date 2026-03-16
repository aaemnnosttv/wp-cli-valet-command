<?php

namespace WP_CLI_Valet\Tests\Context;

use Exception;
use WP_CLI\Process;

class FeatureContext extends \WP_CLI\Tests\Context\FeatureContext
{
    /**
     * Get environment variable from $_SERVER or fallback to getenv().
     * Supports both traditional $_SERVER globals and environment variables.
     *
     * @param string $var Environment variable name.
     * @return string|false The environment variable value or false if not found.
     */
    private function getEnvVar($var)
    {
        // Check $_SERVER first (direct access, faster)
        if (isset($_SERVER[$var])) {
            return $_SERVER[$var];
        }
        // Fallback to getenv() for container/cloud environments
        return getenv($var);
    }

    /**
     * @Given /^a random string as \{(\w+)\}$/
     */
    public function aRandomStringAs($name)
    {
        $this->variables[$name] = substr(uniqid('v'), 0, 8); // ensure the string starts with a letter
    }

    /**
     * @Given /^a random project name as \{(\w+)\}$/
     */
    public function aRandomProjectNameAs($name)
    {
        $this->variables[$name] = uniqid('valet-test-');
    }

    /**
     * @Then /^the ([^\s]+) database should( not)? exist$/
     */
    public function theGivenDatabaseShouldNotExist($database_name, $should_not_exist = false) {
        $database_name = $this->replace_variables($database_name);

        $process = Process::create(
            "mysql -e 'SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = \"$database_name\";' -uroot",
            null,
            ['PATH' => $this->getEnvVar('PATH'), 'HOME' => $this->getEnvVar('HOME')]
        )->run();

        $exists = strlen(trim($process->stdout)) > 0;
        $should_exist = ! $should_not_exist;

        if ($exists && $should_not_exist) {
            throw new Exception("Failed to assert that no database exists with the name '$database_name'");
        } elseif (! $exists && $should_exist) {
            throw new Exception("Failed to assert that a database exists with the name '$database_name'");
        }
    }
}
