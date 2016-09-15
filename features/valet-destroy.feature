Feature: It can completely erase an installation.

  Scenario: It erases a regular WordPress install w/ MySql and https.
    Given an empty directory
    And a random project name as {PROJECT}
    When I run `wp valet new {PROJECT}`
    Then the {PROJECT}/wp-config.php file should exist
    And STDOUT should contain:
      """
      Success: {PROJECT} ready! https://{PROJECT}.dev
      """

    When I run `wp valet destroy {PROJECT} --debug`
    Then the {PROJECT} directory should not exist
    And the wp_{PROJECT} database should not exist
