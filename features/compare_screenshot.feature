Feature: Take a screenshot of an application and compare it with a previous taken screenshot

  Scenario: Compare with equal screenshot
    Given I am on "http://upload.wikimedia.org/wikipedia/meta/0/08/Wikipedia-logo-v2_1x.png"
    Then the screenshot should be equal to "Screen1.png"
