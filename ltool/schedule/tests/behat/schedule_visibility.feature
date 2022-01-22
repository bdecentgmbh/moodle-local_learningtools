@local @local_learningtools @ltool @ltool_schedule

Feature: Check the schedule ltool workflow.
  In order to check schedule
   ltool features workflow.
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
    And I log in as "admin"
    And I am on "Course 1" course homepage with editing mode on
    And I add the "Calendar" block
    And I log out

  @javascript
  Scenario: Check the student able to schedule activity.
    Given I log in as "student1"
    And I click on FAB button
    And "#ltoolschedule-info" "css_element" should not exist
    And I am on "Course 1" course homepage
    And I click on FAB button
    And "#ltoolschedule-info" "css_element" should exist
    And I click on "#ltoolschedule-info" "css_element"
    Then I should see "Schedule"
    And "#ltoolschedule-editorbox" "css_element" should exist
    Then I log out

  @javascript
  Scenario: Create schedule in user calendar.
    Given I log in as "student1"
    And I am on "Course 1" course homepage
    And I click on FAB button
    And I click on "#ltoolschedule-info" "css_element"
    And I set the following fields to these values:
    | Event title | Test schedule event |
    | Description | Wait, this event isn't that great. |
    And I press "Save changes"
    And I am on "Course 1" course homepage
    And I check schedule event
    Then I should see "Test schedule event"
    And I log out
    Then I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I check schedule event
    Then I should not see "Test schedule event"
