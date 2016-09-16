Feature: It can completely erase an installation.

  Scenario: It erases a regular WordPress install w/ MySql and https.
    Given a random string as {INSTALL}
    And a WP install in '{INSTALL}'

    Then the {INSTALL}/wp-config.php file should exist
    And the wp_cli_test database should exist

    When I try `wp valet destroy {INSTALL}`
    Then STDOUT should contain:
      """
      This will delete all files and drop the database for the install. Are you sure?
      """
    Then the {INSTALL} directory should exist

    When I run `wp valet destroy {INSTALL} --yes`
    Then STDOUT should contain:
      """
      Success: {INSTALL} was destroyed.
      """
    And the {INSTALL} directory should not exist
    And the wp_cli_test database should not exist
