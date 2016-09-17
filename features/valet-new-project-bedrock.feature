Feature: It can create new installs for Valet-supported WordPress projects.

  Scenario: Create a new Bedrock install.
    Given an empty directory
    And a random project name as {PROJECT}
    When I run `wp valet new {PROJECT} --project=bedrock --debug`
    Then the {PROJECT}/web/wp-config.php file should exist
    And the {PROJECT}/.env file should exist
    And STDOUT should contain:
      """
      Success: {PROJECT} ready! https://{PROJECT}.dev
      """

    When I run `cd {PROJECT} && wp user list --fields=ID,user_login,user_email`
    Then STDOUT should be a table containing rows:
      | ID | user_login | user_email          |
      | 1  | admin      | admin@{PROJECT}.dev |

  Scenario: It can create a new Bedrock install using sqlite instead of MySql.
    Given an empty directory
    And a random project name as {PROJECT}
    When I run `wp valet new {PROJECT} --project=bedrock --db=sqlite --debug`
    And STDOUT should contain:
      """
      Success: {PROJECT} ready! https://{PROJECT}.dev
      """

    When I run `cd {PROJECT} && wp user list --fields=ID,user_login,user_email`
    Then STDOUT should be a table containing rows:
      | ID | user_login | user_email          |
      | 1  | admin      | admin@{PROJECT}.dev |

    When I run `wp valet destroy {PROJECT} --yes`
    Then the {PROJECT} directory should not exist
