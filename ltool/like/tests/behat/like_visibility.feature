@local @local_learningtools @ltool @ltool_like
Feature: Like learning tool lets users rate a page with capability-gated results
  In order to react to a page
  As a user
  I can like, dislike or super like it, and staff can see the results.

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email             |
      | student1 | Student   | One      | student1@test.com |
      | teacher1 | Teacher   | One      | teacher1@test.com |
      | manager1 | Manager   | One      | manager1@test.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | student1 | C1     | student        |
      | teacher1 | C1     | editingteacher |
    And the following "role assigns" exist:
      | user     | role    | contextlevel | reference |
      | manager1 | manager | Course       | C1        |

  @javascript
  Scenario: A student reacts, switches and removes, seeing only their own choice
    Given I am on the "Course 1" course page logged in as student1
    And I click on FAB button
    And I click on "#ltlike-launcher" "css_element"
    When I click on "#ltlike-like" "css_element"
    Then "#ltlike-like.active" "css_element" should exist
    And "#ltlike-like[aria-pressed='true']" "css_element" should exist
    And I should see "You liked this page"
    And "#ltlike-count-like" "css_element" should not exist
    When I click on "#ltlike-superlike" "css_element"
    Then "#ltlike-superlike.active" "css_element" should exist
    And "#ltlike-like.active" "css_element" should not exist
    When I click on "#ltlike-superlike" "css_element"
    Then "#ltlike-superlike.active" "css_element" should not exist
    And "#ltlike-superlike[aria-pressed='false']" "css_element" should exist
    And I should see "Your reaction was removed"

  @javascript
  Scenario: A teacher sees the aggregate counts but cannot open the user list
    Given I am on the "Course 1" course page logged in as student1
    And I click on FAB button
    And I click on "#ltlike-launcher" "css_element"
    And I click on "#ltlike-like" "css_element"
    And I wait until "#ltlike-like.active" "css_element" exists
    And I log out
    When I am on the "Course 1" course page logged in as teacher1
    And I click on FAB button
    And I click on "#ltlike-launcher" "css_element"
    Then "#ltlike-count-like" "css_element" should exist
    And I should see "1" in the "#ltlike-count-like" "css_element"
    And ".ltlike-count-clickable" "css_element" should not exist

  @javascript
  Scenario: A manager opens the user-list modal from a reaction count
    Given I am on the "Course 1" course page logged in as student1
    And I click on FAB button
    And I click on "#ltlike-launcher" "css_element"
    And I click on "#ltlike-like" "css_element"
    And I wait until "#ltlike-like.active" "css_element" exists
    And I log out
    When I am on the "Course 1" course page logged in as manager1
    And I click on FAB button
    And I click on "#ltlike-launcher" "css_element"
    And I click on "#ltlike-count-like" "css_element"
    Then ".ltlike-users" "css_element" should exist
    And I should see "Liked this page" in the ".ltlike-users" "css_element"
    And I should see "Student One" in the ".ltlike-users" "css_element"
