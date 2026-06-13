@local @local_learningtools @ltool @ltool_report
Feature: Report learning tool lets users report issues from any page
  In order to flag problems on the platform
  As a user
  I can submit an issue report with a type and description, after reviewing it

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email             |
      | student1 | Student   | One      | student1@test.com |
      | teacher1 | Teacher   | One      | teacher1@test.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | student1 | C1     | student        |
      | teacher1 | C1     | editingteacher |

  @javascript
  Scenario: A student reviews and submits an issue report
    Given I am on the "Course 1" course page logged in as student1
    And I click on FAB button
    And I click on "#ltreport-launcher" "css_element"
    And I set the field "issuetype" to "Technical issue"
    And I should see "Technical issues are sent to the site support team."
    And I set the field "description" to "The video on this page will not play."
    And I press "Continue"
    And I should see "The video on this page will not play."
    When I press "Confirm and send"
    Then I should see "Thank you! Your report has been sent."

  @javascript
  Scenario: The student can go back from the review to edit the report
    Given I am on the "Course 1" course page logged in as student1
    And I click on FAB button
    And I click on "#ltreport-launcher" "css_element"
    And I set the field "issuetype" to "Content error"
    And I set the field "description" to "Typo in the second paragraph."
    And I press "Continue"
    And I should see "Typo in the second paragraph."
    When I press "Back"
    Then "#ltreport-description" "css_element" should be visible

  @javascript
  Scenario: Description is required before continuing
    Given I am on the "Course 1" course page logged in as student1
    And I click on FAB button
    And I click on "#ltreport-launcher" "css_element"
    And I set the field "issuetype" to "Technical issue"
    And I press "Continue"
    Then I should see "Please describe the issue."
    And "#ltreport-description" "css_element" should be visible

  @javascript
  Scenario: The report form only offers the issue types
    Given I am on the "Course 1" course page logged in as student1
    And I click on FAB button
    And I click on "#ltreport-launcher" "css_element"
    Then the "issuetype" select box should contain "Technical issue"
    And the "issuetype" select box should contain "General question"
    And the "issuetype" select box should contain "Accessibility problem"
    And the "issuetype" select box should contain "Content error"
