Feature: Take a screenshot of an application and compare it with a previous taken screenshot

  Scenario: Compare correct page with screenshot
    Given I configured the screenshot compare extension
    And a feature file that opens the url "/" and compares it to "test.png"
    When I run behat
    Then it should pass


  Scenario: Compare modified page with screenshot
    Given I configured the screenshot compare extension
    And a feature file that opens the url "/?borderwidth=10" and compares it to "test.png"
    When I run behat
    Then it should fail
