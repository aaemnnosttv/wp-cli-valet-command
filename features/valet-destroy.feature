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

    When I try `wp valet destroy {PROJECT} --debug`
    Then STDOUT should contain:
      """
      This will delete all files and drop the database for the install. Are you sure?
      """
    Then the {PROJECT} directory should exist

    When I run `wp valet destroy {PROJECT} --yes --debug`
    Then STDOUT should contain:
      """
      Success: {PROJECT} was destroyed.
      """

    And the {PROJECT} directory should not exist
    And the wp_{PROJECT} database should not exist
