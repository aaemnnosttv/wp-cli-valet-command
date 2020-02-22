Feature: It can create new installs for Valet-supported WordPress projects.

  @issue-62
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

  @issue-10
  Scenario: It can create a new Bedrock install using the given path to the parent dir.
    Given an empty directory
    And a random project name as {PROJECT}
    And a random string as {PATH}

    When I run `wp valet new {PROJECT} --project=bedrock --in={PATH} --debug`
    Then the {PATH}/{PROJECT}/.env file should exist

  @issue-32
  Scenario: The --dbprefix option is respected.
    Given an empty directory
    And a random project name as {PROJECT}
    And a random string as {PATH}

    When I run `wp valet new {PROJECT} --project=bedrock --in={PATH} --dbprefix=foo`
    Then the {PATH}/{PROJECT}/.env file should contain:
      """
      DB_PREFIX=foo
      """
    And I run `wp eval 'echo getenv("DB_PREFIX");' --path={PATH}/{PROJECT}/web/wp/`
    Then STDOUT should be:
      """
      foo
      """
