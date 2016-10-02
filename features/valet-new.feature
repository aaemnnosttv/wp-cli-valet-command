Feature: Create a new install.

  Scenario: It can create a new vanilla WordPress install, secure with https.
    Given an empty directory
    And a random project name as {PROJECT}
    When I run `wp valet new {PROJECT}`
    Then the {PROJECT}/wp-config.php file should exist
    And STDOUT should contain:
      """
      Success: {PROJECT} ready! https://{PROJECT}.dev
      """

  Scenario: It can create a new vanilla WordPress install using regular http.
    Given an empty directory
    And a random project name as {PROJECT}
    When I run `wp valet new {PROJECT} --unsecure`
    Then the {PROJECT}/wp-config.php file should exist
    And STDOUT should contain:
      """
      Success: {PROJECT} ready! http://{PROJECT}.dev
      """

  @issue-10
  Scenario: It accepts options for configuring the new install.
    Given an empty directory
    And a random project name as {PROJECT}
    And a random string as {ADMIN}
    And a random string as {PATH}

    When I run `wp valet new {PROJECT} --in={PATH} --admin_user={ADMIN} --admin_email=hello@{PROJECT}.dev --version=4.5 --dbname=wp_cli_test --dbprefix={ADMIN}_ --dbuser=wp_cli_test --dbpass=password1`
    Then the {PATH}/{PROJECT}/wp-config.php file should exist
    Then the wp_cli_test database should exist

    When I run `wp db tables --path={PATH}/{PROJECT}`
    Then STDOUT should contain:
      """
      {ADMIN}_users
      """

    When I run `wp core version --path={PATH}/{PROJECT}`
    Then STDOUT should be:
      """
      4.5
      """

    When I run `wp user list --fields=ID,user_login,user_email --path={PATH}/{PROJECT}`
    Then STDOUT should be a table containing rows:
      | ID | user_login   | user_email          |
      | 1  | {ADMIN}      | hello@{PROJECT}.dev |

  Scenario: It can create a new WordPress install using sqlite for the database.
    Given an empty directory
    And a random project name as {PROJECT}
    When I run `wp valet new {PROJECT} --db=sqlite`
    Then the {PROJECT}/wp-config.php file should exist
    And the {PROJECT}/wp-content/db.php file should exist
    And the wp_{PROJECT} database should not exist
    And STDOUT should contain:
      """
      Success: {PROJECT} ready! https://{PROJECT}.dev
      """

  Scenario: It can create a new portable WordPress install.
    Given an empty directory
    And a random project name as {PROJECT}
    When I run `wp valet new {PROJECT} --portable`
    Then the {PROJECT}/wp-config.php file should exist
    And the {PROJECT}/wp-content/db.php file should exist
    And the wp_{PROJECT} database should not exist
    And STDOUT should contain:
      """
      Success: {PROJECT} ready! http://{PROJECT}.dev
      """
