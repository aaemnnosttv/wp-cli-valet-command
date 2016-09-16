Feature: It can create new WordPlate installs.

  Scenario: Create a new WordPlate install.
    Given an empty directory
    And a random project name as {PROJECT}
    When I run `wp valet new {PROJECT} --project=wordplate --debug`
    Then the {PROJECT}/vendor/wordplate/framework directory should exist
    And the {PROJECT}/.env file should exist
    And STDOUT should contain:
      """
      Success: {PROJECT} ready! https://{PROJECT}.dev
      """

    When I try `cd {PROJECT} && wp user list --fields=ID,user_login,user_email --url=https://{PROJECT}.dev`
    Then STDOUT should be a table containing rows:
      | ID | user_login | user_email          |
      | 1  | admin      | admin@{PROJECT}.dev |

  Scenario: It can create a new WordPlate install using sqlite instead of MySql.
    Given an empty directory
    And a random project name as {PROJECT}
    When I run `wp valet new {PROJECT} --project=wordplate --db=sqlite --debug`
    And STDOUT should contain:
      """
      Success: {PROJECT} ready! https://{PROJECT}.dev
      """

    When I run `cd {PROJECT} && wp user list --fields=ID,user_login,user_email --url=https://{PROJECT}.dev`
    Then STDOUT should be a table containing rows:
      | ID | user_login | user_email          |
      | 1  | admin      | admin@{PROJECT}.dev |
