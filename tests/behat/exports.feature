@local @local_upgradeassistant
Feature: Smart Upgrade Assistant export permissions
  In order to protect sensitive technical diagnostics
  As a site administrator
  I need report export buttons to respect export capabilities

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email |
      | reviewer | Report | Reviewer | reviewer@example.com |
    And the following "system role assigns" exist:
      | user | role |
      | reviewer | manager |

  Scenario: Manager without sensitive diagnostics cannot see complete export actions
    Given I log in as "reviewer"
    When I navigate to "Server > Smart Upgrade Assistant" in site administration
    Then I should see "Smart Upgrade Assistant"
    And I should not see "Complete PDF"
    And I should not see "Complete HTML"
