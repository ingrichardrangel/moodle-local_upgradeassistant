@local @local_upgradeassistant
Feature: Upgrade Assistant sensitive diagnostics permissions
  In order to keep server paths available only to technical administrators
  As a site administrator
  I need the assistant UI to respect sensitive diagnostics capabilities

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email |
      | reviewer | Report | Reviewer | reviewer@example.com |
    And the following "system role assigns" exist:
      | user | role |
      | reviewer | manager |

  Scenario: Manager without sensitive diagnostics does not see full diagnostics notice
    Given I log in as "reviewer"
    When I navigate to "Server > Upgrade Assistant" in site administration
    Then I should see "Upgrade Assistant"
    And I should not see "This section can display sensitive server paths"
