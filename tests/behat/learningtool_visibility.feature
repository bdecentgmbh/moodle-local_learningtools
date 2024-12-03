@local @local_learningtools

Feature: Check the learning tools features and manage sub plugins.
  In order to check ltools features works
  As a admin
  I should manage subplugins order and enable/disable plugins.

  Background: Create users to check the visbility.
    Given the following "users" exist:
      | username | firstname | lastname | email              |
      | student1 | Student   | User 1   | student1@test.com  |
      | teacher1 | Teacher   | User 1   | teacher1@test.com  |
    And the following "courses" exist:
      | fullname | shortname | category | enablecompletion | showcompletionconditions |
      | Course 1 | C1        | 0        | 1                | 1                        |
    And the following "course enrolments" exist:
      | user | course | role           |
      | student1 | C1 | student        |
      | teacher1 | C1 | editingteacher |

  @javascript
  Scenario: Check the subplugins visibility.
    # Admin view
    Given I log in as "admin"
    And I follow "Dashboard"
    Then "#tool-action-button" "css_element" should be visible
    And I click on "#tool-action-button" "css_element"
    Then "#ltnoteinfo" "css_element" should be visible
    And "#ltbookmarksinfo" "css_element" should be visible
    And I log out
    # Teacher view
    And I log in as "teacher1"
    And I am on "Course 1" course homepage
    Then "#tool-action-button" "css_element" should be visible
    And I click on "#tool-action-button" "css_element"
    Then "#ltnoteinfo" "css_element" should be visible
    And "#ltbookmarksinfo" "css_element" should be visible
    And I log out
    # Student view
    And I log in as "student1"
    And I am on "Course 1" course homepage
    Then "#tool-action-button" "css_element" should exist
    And I click on "#tool-action-button" "css_element"
    Then "#ltnoteinfo" "css_element" should be visible
    And "#ltbookmarksinfo" "css_element" should be visible

  @javascript
  Scenario: Test the subplugins order and enable disable methods.
    Given I log in as "admin"
    And "#tool-action-button" "css_element" should exist
    And I navigate to "Plugins > Local plugins > Manage Learning Tools" in site administration
    Then "table#learningtool-products" "css_element" should exist
    And I should see "Learning Tools Bookmarks" in the "#learningtool_products_info_r0" "css_element"
    # Disable the learning tools bookmarks.
    And I click on ".fa-eye" "css_element" in the "Learning Tools Bookmarks" "table_row"
    And "#ltbookmarksinfo" "css_element" should not exist
    And I click on "#tool-action-button" "css_element"
    And "#ltbookmarksinfo" "css_element" should not be visible
    # Enable the learning tools bookmarks.
    And I click on ".fa-eye-slash" "css_element" in the "Learning Tools Bookmarks" "table_row"
    And "#ltbookmarksinfo" "css_element" should exist
    And I click on "#tool-action-button" "css_element"
    And "#ltbookmarksinfo" "css_element" should be visible
    # Note Learning tools - Enable.
    And I click on ".fa-eye" "css_element" in the "Learning Tools Note" "table_row"
    And "#ltnoteinfo" "css_element" should not exist
    And I click on "#tool-action-button" "css_element"
    And "#ltnoteinfo" "css_element" should not be visible
    # Note learning tools - Disable.
    And I click on ".fa-eye-slash" "css_element" in the "Learning Tools Note" "table_row"
    And "#ltnoteinfo" "css_element" should exist
    And I click on "#tool-action-button" "css_element"
    And "#ltnoteinfo" "css_element" should be visible

  @javascript
  Scenario: Test the subplugins order the plugin views.
    Given I log in as "admin"
    And I navigate to "Plugins > Local plugins > Manage Learning Tools" in site administration
    Then "table#learningtool-products" "css_element" should exist
    # Bookmark order changed.
    Then I click on ".fa-arrow-down" "css_element" in the "Learning Tools Bookmarks" "table_row"
    And "Learning Tools Focus mode" "table_row" should appear before "Learning Tools Bookmarks" "table_row"
    And I am on "Course 1" course homepage with editing mode on
    And I click on "#tool-action-button" "css_element"
    And "#ltbookmarksinfo" "css_element" should appear after "#ltoolfocus-info" "css_element"
    # Email order down.
    And I navigate to "Plugins > Local plugins > Manage Learning Tools" in site administration
    Then I click on ".fa-arrow-down" "css_element" in the "Learning Tools Focus mode" "table_row"
    And "Learning Tools Bookmarks" "table_row" should appear before "Learning Tools Focus mode" "table_row"
    And I am on "Course 1" course homepage with editing mode on
    And I click on "#tool-action-button" "css_element"
    And "#ltbookmarksinfo" "css_element" should appear before "#ltoolfocus-info" "css_element"
