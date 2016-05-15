Feature: Test that Valet new command works

  Scenario: WP-CLI loads for your tests
    Given an empty directory

    When I run `wp valet new testsite`

    Then the "testsite/wp-config.php" file should exist

    And STDOUT should contain:
      """
      Success: testsite ready!
      """
