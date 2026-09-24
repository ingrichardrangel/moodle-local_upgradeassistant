@local @local_upgradeassistant
Feature: Upgrade Assistant redacted diagnostics
  In order to avoid accidental disclosure of server paths
  As a non-technical reviewer
  I need sensitive diagnostics to be redacted unless I have explicit permission

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email |
      | reviewer | Report | Reviewer | reviewer@example.com |
    And the following "system role assigns" exist:
      | user | role |
      | reviewer | manager |

  Scenario: Manager without sensitive diagnostics sees no full diagnostics notice
    Given I log in as "reviewer"
    When I navigate to "Server > Upgrade Assistant" in site administration
    Then I should see "Upgrade Assistant"
    And I should not see "This page is showing sensitive technical diagnostics"
