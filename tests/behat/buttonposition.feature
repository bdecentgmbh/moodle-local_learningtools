@local @local_learningtools @ltool @ltool_note
Feature: Configure the Learning Tools button position
  In order to place the Learning Tools launcher to suit the site
  As an admin
  I need to choose between a bottom-right button, a bottom-left button, or a navbar drawer.

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email             |
      | student1 | Student   | User 1   | student1@test.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "course enrolments" exist:
      | user     | course | role    |
      | student1 | C1     | student |

  @javascript
  Scenario: The default position is the bottom-right floating button
    Given I log in as "student1"
    Then the FAB button should exist
    And ".learningtools-action-info .floating-button" "css_element" should exist
    And ".learningtools-action-info.floating-button-left" "css_element" should not exist
    And "#learningtools-drawer-toggle" "css_element" should not exist

  @javascript
  Scenario: The bottom-left position moves the floating button to the left
    Given the following config values are set as admin:
      | buttonposition | bottomleft | local_learningtools |
    And I log in as "student1"
    Then the FAB button should exist
    And ".learningtools-action-info.floating-button-left .floating-button" "css_element" should exist

  @javascript
  Scenario: The drawer position replaces the floating button with a navbar icon
    Given the following config values are set as admin:
      | buttonposition | drawer | local_learningtools |
    And I log in as "student1"
    Then "#tool-action-button" "css_element" should not exist
    And "#learningtools-drawer-toggle" "css_element" should exist
    And "#learningtools-drawer" "css_element" should not be visible

  @javascript
  Scenario: The drawer shows the notes editor and the other tools as buttons
    Given the following config values are set as admin:
      | buttonposition | drawer | local_learningtools |
    And I log in as "student1"
    When I open the learning tools drawer
    And I wait until ".ltoolusernotes" "css_element" exists
    Then ".ltoolusernotes" "css_element" should be visible
    And "#learningtools-drawer #ltbookmarksinfo" "css_element" should exist
    And "#learningtools-drawer #ltoolfocus-info" "css_element" should exist
    And "#learningtools-drawer #ltnoteinfo" "css_element" should not exist

  @javascript
  Scenario: A note can be saved from the drawer
    Given the following config values are set as admin:
      | buttonposition | drawer | local_learningtools |
    And I log in as "student1"
    When I open the learning tools drawer
    And I set the field "ltnoteeditor" to "Drawer note one"
    And I press "Save changes"
    Then I should see "Notes added successfully"

  @javascript
  Scenario: Focus mode works from the drawer
    Given the following config values are set as admin:
      | buttonposition | drawer | local_learningtools |
    And I log in as "student1"
    When I open the learning tools drawer
    And I click on "#ltoolfocus-info" "css_element"
    And I wait "3" seconds
    Then I check focus mode enable

  @javascript
  Scenario: Bookmarking from the drawer marks the bookmark button
    Given the following config values are set as admin:
      | buttonposition | drawer | local_learningtools |
    And I log in as "student1"
    When I open the learning tools drawer
    And I click on "#ltbookmarks-action" "css_element"
    Then I wait until "#learningtools-drawer #bookmarks-marked.marked" "css_element" exists

  @javascript
  Scenario: Clicking the backdrop closes the drawer
    Given the following config values are set as admin:
      | buttonposition | drawer | local_learningtools |
    And I log in as "student1"
    When I open the learning tools drawer
    And I click on ".modal-backdrop" "css_element"
    Then "#learningtools-drawer" "css_element" should not be visible

  @javascript
  Scenario: Notes auto-save when the drawer is closed
    Given the following config values are set as admin:
      | buttonposition | drawer | local_learningtools |
      | autosavenotes  | 1      | local_learningtools |
    And I log in as "student1"
    When I open the learning tools drawer
    And "[data-action=\"lt-drawer-save-note\"]" "css_element" should not exist
    And I set the field "ltnoteeditor" to "Auto note one"
    And I click on ".modal-backdrop" "css_element"
    Then I should see "Notes added successfully"
