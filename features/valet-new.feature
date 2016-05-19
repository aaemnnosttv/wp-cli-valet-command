Feature: Test that Valet new command works

  Scenario: The valet command exists
    Given an empty directory

    When I run `wp valet new`

    STDOUT should contain:
    """
    wp valet new <domain>
    """
