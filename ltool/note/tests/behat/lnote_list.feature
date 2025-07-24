@local @local_learningtools @ltool @ltool_note @ltool_note_list

Feature: Check the Note ltool listing and notes add/edit delete and list viewes.
  In order to check ltools notes features works
  As a admin
  I should able to add/delete and view notes.

  Background: Create users to check the visbility of the notes.
    Given the following "users" exist:
      | username | firstname | lastname | email               |
      | student1 | Student   | User 1   | student1@email.com  |
      | teacher1 | Teacher   | User 1   | teacher1@email.com  |
    And the following "courses" exist:
      | fullname | shortname | category | enablecompletion | showcompletionconditions |
      | Course 1 | C1        | 0        | 1                | 1                        |
    And the following "course enrolments" exist:
      | user | course | role           |
      | student1 | C1 | student        |
      | teacher1 | C1 | editingteacher |
    And the following "activities" exist:
      | activity | name       | course | idnumber  |  intro           | section |completion|
      | page     | PageName1  | C1     | page1     | Page description | 1       | 1 |
      | page     | Test page2 | C1     | page1     | Page description | 2       | 1 |
      | page     | Test page3 | C1     | page1     | Page description | 3       | 1 |
      | quiz     | Quiz1      | C1     | quiz1     | Page description | 1       | 1 |
      | page     | Test page4 | C1     | page1     | Page description | 1       | 1 |
      | assign   | Assign1    | C1     | assign1   | Page description | 1       | 1 |
    And I log in as "student1"
    And I click on FAB button
    And I click on "#ltnoteinfo" "css_element"
    And ".modal-title" "css_element" should be visible
    And I should see "Take notes" in the ".modal-title" "css_element"
    And I set the field "ltnoteeditor" to "Test note 1"
    And I press "Save changes"
    Then I should see "Notes added successfully"
    And I am on the "Course 1" course page
    And I click on FAB button
    And I click on "#ltnoteinfo" "css_element"
    And ".modal-title" "css_element" should be visible
    And I should see "Take notes" in the ".modal-title" "css_element"
    And I set the field "ltnoteeditor" to "Course Test note 1"
    And I press "Save changes"
    Then I should see "Notes added successfully"
    And I am on the "Course 1 > Section 1" "course > section" page
    And I click on FAB button
    And I click on "#ltnoteinfo" "css_element"
    And ".modal-title" "css_element" should be visible
    And I should see "Take notes" in the ".modal-title" "css_element"
    And I set the field "ltnoteeditor" to "Section Test note 1"
    And I press "Save changes"
    Then I should see "Notes added successfully"
    And I am on the "Course 1 > Section 2" "course > section" page
    And I click on FAB button
    And I click on "#ltnoteinfo" "css_element"
    And ".modal-title" "css_element" should be visible
    And I should see "Take notes" in the ".modal-title" "css_element"
    And I set the field "ltnoteeditor" to "Section Test note 2"
    And I press "Save changes"
    Then I should see "Notes added successfully"
    And I am on the "PageName1" "page activity" page
    And I click on FAB button
    And I click on "#ltnoteinfo" "css_element"
    And ".modal-title" "css_element" should be visible
    And I should see "Take notes" in the ".modal-title" "css_element"
    And I set the field "ltnoteeditor" to "Module Test note 1"
    And I press "Save changes"
    Then I should see "Notes added successfully"

  @javascript
  Scenario: Access Notes Listing from Learning Tools Navigation.
    Given I am on the "Course 1" course page logged in as student1
    And I should see "Learning Tools" in the ".secondary-navigation" "css_element"
    And I click on "Learning Tools" "link" in the ".secondary-navigation" "css_element"
    And I should see "Module Test note 1" in the ".note-card:nth-child(1) .note-content p" "css_element"
    And I should see "Module: Page" in the ".note-card:nth-child(1) .note-context span" "css_element"
    And I should see "PageName1" in the ".note-card:nth-child(1) .note-title span" "css_element"
    And I should see "Section Test note 1" in the ".note-card:nth-child(3) .note-content p" "css_element"
    And I should see "Section Test note 2" in the ".note-card:nth-child(2) .note-content p" "css_element"
    And I should see "Course Test note 1" in the ".note-card:nth-child(4) .note-content p" "css_element"

  @javascript
  Scenario: Notes Listing page actions.
    # Edit the notes on the listing page.
    Given I am on the "Course 1" course page logged in as student1
    And I should see "Learning Tools" in the ".secondary-navigation" "css_element"
    And I click on "Learning Tools" "link" in the ".secondary-navigation" "css_element"
    And I should see "Module Test note 1" in the ".note-card:nth-child(1) .note-content p" "css_element"
    And I should see "Module: Page" in the ".note-card:nth-child(1) .note-context span" "css_element"
    And I should see "PageName1" in the ".note-card:nth-child(1) .note-title span" "css_element"
    And I click on " .note-card:nth-child(1) .note-actions button" "css_element"
    And I click on "Edit" "link" in the ".note-card:nth-child(1) .note-actions .dropdown-menu" "css_element"
    And I set the field "noteeditor[text]" to "Module Test note 1 is edited"
    And I press "Save changes"
    And I should see "Module Test note 1 is edited" in the " .note-card:nth-child(1) .note-content p" "css_element"
    # Delete the notes on the listing page.
    And I click on " .note-card:nth-child(1) .note-actions button" "css_element"
    And I click on "Delete" "link" in the " .note-card:nth-child(1) .note-actions .dropdown-menu" "css_element"
    And "Confirm" "dialogue" should be visible
    Then I should see "Are you absolutely sure you want to completely delete the Note, including their Note and data?"
    And "Delete" "button" should exist in the "Confirm" "dialogue"
    When I click on "Delete" "button" in the "Confirm" "dialogue"
    And I wait until the page is ready
    Then I should see "Successfully deleted"
    And I should not see "Module Test note 1 is edited"
    # View the notes on the listing page.
    And I click on ".note-card:nth-child(1) .note-actions button" "css_element"
    And I click on "View" "link" in the ".note-card:nth-child(1) .note-actions .dropdown-menu" "css_element"
    And I should see "New section"
    And I click on FAB button
    And I click on "#ltnoteinfo" "css_element"
    And ".modal-title" "css_element" should be visible
    And I should see "Take notes" in the ".modal-title" "css_element"
    And I should see "Section Test note 2" in the ".modal-body .card-body:nth-of-type(1)" "css_element"

  @javascript
  Scenario: Notes course section filter.
    Given I am on the "Course 1" course page logged in as student1
    And I am on the "Course 1 > Section 1" "course > section" page
    And I click on FAB button
    And I click on "#ltnoteinfo" "css_element"
    And ".modal-title" "css_element" should be visible
    And I should see "Take notes" in the ".modal-title" "css_element"
    And I set the field "ltnoteeditor" to "Section Test note 3"
    And I press "Save changes"
    And I am on the "Course 1 > Section 3" "course > section" page
    And I click on FAB button
    And I click on "#ltnoteinfo" "css_element"
    And ".modal-title" "css_element" should be visible
    And I should see "Take notes" in the ".modal-title" "css_element"
    And I set the field "ltnoteeditor" to "Section Test note 4"
    And I press "Save changes"
    And I am on the "Course 1" course page
    And I should see "Learning Tools" in the ".secondary-navigation" "css_element"
    And I click on "Learning Tools" "link" in the ".secondary-navigation" "css_element"
    And I should see "Section" in the ".ltnote-sectionfilter label" "css_element"
    When I select "Section 3" from the "section-filter" singleselect
    And I should see "Section Test note 4" in the ".note-card:nth-child(1) .note-content p" "css_element"
    And I should not see "Section Test note 3"
    And I should not see "Module Test note 1"
    When I select "All sections" from the "section-filter" singleselect
    And I should see "Section Test note 4" in the ".note-card:nth-child(1) .note-content p" "css_element"
    And I should see "Section Test note 3" in the ".note-card:nth-child(2) .note-content p" "css_element"
    And I should see "Module Test note 1" in the ".note-card:nth-child(3) .note-content p" "css_element"

  @javascript
  Scenario: Notes course activity filter.
    Given I am on the "Course 1" course page logged in as student1
    And I am on the "Quiz1" "quiz activity" page
    And I click on FAB button
    And I click on "#ltnoteinfo" "css_element"
    And ".modal-title" "css_element" should be visible
    And I should see "Take notes" in the ".modal-title" "css_element"
    And I set the field "ltnoteeditor" to "Quiz module Test note 1"
    And I press "Save changes"
    Then I should see "Notes added successfully"
    When I am on the "Assign1" "assign activity" page
    And I click on FAB button
    And I click on "#ltnoteinfo" "css_element"
    And ".modal-title" "css_element" should be visible
    And I should see "Take notes" in the ".modal-title" "css_element"
    And I set the field "ltnoteeditor" to "Assignment module Test note 1"
    And I press "Save changes"
    Then I should see "Notes added successfully"
    And I am on the "Course 1" course page
    And I should see "Learning Tools" in the ".secondary-navigation" "css_element"
    And I click on "Learning Tools" "link" in the ".secondary-navigation" "css_element"
    And I should see "Activity" in the ".ltnote-activityfilter label" "css_element"
    When I select "PageName1" from the "activity-filter" singleselect
    And I should see "Module Test note 1" in the ".note-card:nth-child(1) .note-content p" "css_element"
    When I select "Quiz1" from the "activity-filter" singleselect
    And I should see "Quiz module Test note 1" in the ".note-card:nth-child(1) .note-content p" "css_element"
    And I should not see "Module Test note 1"
    When I select "Assign1" from the "activity-filter" singleselect
    And I should see "Assignment module Test note 1" in the ".note-card:nth-child(1) .note-content p" "css_element"
    And I should not see "Quiz module Test note 1"
    When I select "All Activities" from the "activity-filter" singleselect
    And I should see "Assignment module Test note 1" in the ".note-card:nth-child(1) .note-content p" "css_element"
    And I should see "Quiz module Test note 1" in the ".note-card:nth-child(2) .note-content p" "css_element"
    And I should see "Module Test note 1" in the ".note-card:nth-child(3) .note-content p" "css_element"
    And I log out

  @javascript
  Scenario: Notes search check.
    Given I am on the "Course 1" course page logged in as student1
    And I should see "Learning Tools" in the ".secondary-navigation" "css_element"
    And I click on "Learning Tools" "link" in the ".secondary-navigation" "css_element"
    # Activity type based search.
    When I set the field "Search my notes" to "page"
    And I should see "Module Test note 1" in the ".note-card:nth-child(1) .note-content p" "css_element"
    And I should not see "Section Test note 1"
    And I should not see "Course Test note 1"
    And I am on the "Quiz1" "quiz activity" page
    And I click on FAB button
    And I click on "#ltnoteinfo" "css_element"
    And ".modal-title" "css_element" should be visible
    And I should see "Take notes" in the ".modal-title" "css_element"
    And I set the field "ltnoteeditor" to "Quiz module Test note 1"
    And I press "Save changes"
    And I am on the "Course 1" course page
    And I click on "Learning Tools" "link" in the ".secondary-navigation" "css_element"
    # Activity name based search.
    When I set the field "Search my notes" to "Quiz1"
    And I should see "Quiz module Test note 1" in the ".note-card:nth-child(1) .note-content p" "css_element"
    And I should not see "Module Test note 1"
    And I should not see "Section Test note 1"
    And I should not see "Course Test note 1"
    # Notes content based search.
    When I set the field "Search my notes" to "Course Test"
    And I should see "Course Test note 1" in the ".note-card:nth-child(1) .note-content p" "css_element"
    And I should not see "Module Test note 1"
    And I should not see "Section Test note 1"
    # Notes created mon based search.
    When I set the field "Search my notes" to "June"
    And I should see "Course Test note 1" in the ".note-card:nth-child(1) .note-content p" "css_element"
