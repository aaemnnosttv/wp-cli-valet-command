Feature: It can completely erase an installation.

  Scenario: It erases a regular WordPress install w/ MySql and https.
    Given a random string as {INSTALL}
    And a WP install in '{INSTALL}'
    And a session file:
      """
      n
      """

    Then the {INSTALL}/wp-config.php file should exist
    And the wp_cli_test database should exist

    When I try `wp valet destroy {INSTALL} < session`
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

  Scenario: It can erase multiple installs in a single command.
    Given a random string as {INSTALL_A}
    And a WP install in '{INSTALL_A}'
    And a random string as {INSTALL_B}
    And a WP install in '{INSTALL_B}'

    When I run `wp valet destroy {INSTALL_A} {INSTALL_B} --yes`
    Then STDOUT should contain:
      """
      Success: {INSTALL_A} was destroyed.
      Success: {INSTALL_B} was destroyed.
      """
    And the {INSTALL_A} directory should not exist
    And the {INSTALL_B} directory should not exist
    And the wp_cli_test database should not exist

  Scenario: It can erase multiple installs in a single command using a glob pattern.
    Given a WP install in 'install_a'
    And a WP install in 'install_b'

    When I run `wp valet destroy install_* --yes`
    Then STDOUT should contain:
      """
      Success: install_a was destroyed.
      Success: install_b was destroyed.
      """
    And the install_a directory should not exist
    And the install_b directory should not exist
    And the wp_cli_test database should not exist

