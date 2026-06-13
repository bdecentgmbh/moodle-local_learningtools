@local @local_learningtools @ltool @ltool_report
Feature: Report learning tool lets users report issues from any page
  In order to flag problems on the platform
  As a user
  I can submit an issue report with a type and description

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
  Scenario: A student opens the report tool and submits an issue
    Given I am on the "Course 1" course page logged in as student1
    And I click on FAB button
    And I click on "#ltreport-launcher" "css_element"
    And I set the field "issuetype" to "Technical issue"
    And I set the field "description" to "The video on this page will not play."
    And I press "Submit report"
    Then I should see "Thank you! Your report has been sent."

  @javascript
  Scenario: The report form only offers the issue types
    Given I am on the "Course 1" course page logged in as student1
    And I click on FAB button
    And I click on "#ltreport-launcher" "css_element"
    Then the "issuetype" select box should contain "Technical issue"
    And the "issuetype" select box should contain "General question"
    And the "issuetype" select box should contain "Accessibility problem"
    And the "issuetype" select box should contain "Content error"
