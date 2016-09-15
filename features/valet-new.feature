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

  Scenario: It can create a new WordPress install using sqlite for the database.
    Given an empty directory
    And a random project name as {PROJECT}
    When I run `wp valet new {PROJECT} --db=sqlite`
    Then the {PROJECT}/wp-config.php file should exist
    And the {PROJECT}/wp-content/db.php file should exist
    And STDOUT should contain:
      """
      Success: {PROJECT} ready! https://{PROJECT}.dev
      """
