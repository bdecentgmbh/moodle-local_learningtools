@local @local_learningtools @ltool @ltool_focus

Feature: Check the focus ltool workflow.
  In order to check ltools focus features workflow.
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
  Scenario: Check the focus mode enable/disable.
    Given I log in as "student1"
    And I click on FAB button
    And I check focus mode disable
    And "#ltoolfocus-info" "css_element" should exist
    And I click on "#ltoolfocus-info" "css_element"
    Then I wait "5" seconds
    And I check focus mode enable
    And I am on "Course 1" course homepage
    And I check focus mode enable
    Then I click on "#disable-focusmode" "css_element"
    And I check focus mode disable

  @javascript
  Scenario: Check the session based focus mode.
    Given I log in as "student1"
    And I click on FAB button
    And "#ltoolfocus-info" "css_element" should exist
    And I click on "#ltoolfocus-info" "css_element"
    Then I wait "5" seconds
    And I check focus mode enable
    Then I log out
    And I log in as "student1"
    And I check focus mode disable
