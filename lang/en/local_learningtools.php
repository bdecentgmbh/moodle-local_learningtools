<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Local plugin "Learning Tools" - string file.
 *
 * @package   local_learningtools
 * @copyright bdecent GmbH 2021
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined("MOODLE_INTERNAL") || die();

$string['pluginname'] = "Learning Tools";
$string['learningtoolproducts'] = "Learning Tools Products";
$string['products'] = "Products";
$string['learningtools'] = "Learning Tools";
$string['general'] = 'General';
$string['topic'] = 'Topic {$a}';
$string['learningtoolsltool'] = "Manage Learning Tools";
$string['notificationdisappertitle'] = "Notification disapper";
$string['notificationdisapperdesc'] = "Use this option to set the notification disapper time between each Learning Tools save the notification. Value is in milliseconds (i.e 1 second = 1000 milliseconds)";
$string['popout'] = "Pop Out";
$string['viewreports'] = "View reports";
$string['learningtoolssettings'] = "General settings";
$string['ltoolsusermenu'] = "Display Learning Tools in usermenu bar";
$string['ltoolusermenu_help'] = "List of menus available to display in the user menu. copy and paste the  below given menu tools path in user menu items. Go to the Appearence->Themes->Theme settings in user menu items. ";
$string['privacy:metadata'] = 'Learning tools parent plugin don\'t store any user-related data, The learning tool sub plugins are stores the users data individually.';
$string['coursenotes'] = "Course Notes";
$string['addbookmark'] = "Add bookmark";
$string['createnote'] = "Create note";
$string['fabiconbackcolor'] = "Learning Tools icon background color";
$string['fabiconcolor'] = "Learning Tools icon color";
$string['iconbackcolor'] = '{$a} icon background color';
$string['iconcolor'] = '{$a} icon color';
$string['ltoolsettings'] = '{$a} settings';
$string['everywhere'] = "Everywhere";
$string['allcourses'] = "All Courses";
$string['specificcate'] = "Specific Categories";
$string['visiblelearningtools'] = "Visibility of Learning Tools";
$string['visiblecategories'] = "Select Visible Categories";
$string['disablemodules'] = "Select Disable activities to hide Learning Tools";
$string['enabledisablemodules'] = "Enable Learning Tools to hide activities";
$string['fabbuttonvisible_desc'] = 'You can decide where the Learning Tools shall be available.';
$string['sticky'] = "Sticky";
$string['alwaysactive'] = "Always show active tools";
$string['learningtools:studentcontroller'] = "Learning Tools student controller.";

$string['bookmarksusermenu'] = "Display Bookmarks tool in user menu";
$string['bookmarksusermenu_help'] = "bookmarks,local_learningtools|/local/learningtools/ltool/bookmarks/list.php";
$string['notesusermenu'] = "Display Notes tool in user menu";
$string['notesusermenu_help'] = "notes,local_learningtools|/local/learningtools/ltool/note/list.php";


// Bookmarks strings.
$string['bookmarks'] = "Bookmarks";
$string['managebookmarks'] = "Manage bookmarks access the users";

$string['eventltbookmarkscreated'] = "Learning Tools bookmarks created";
$string['eventltbookmarksviewed'] = "Learning Tools bookmarks viewed";
$string['eventltbookmarksdeleted'] = "Learning Tools bookmarks deleted";

// Note event strings.
$string['eventltnotecreated'] = "Learning Tools notes created";
$string['eventltnotedeleted'] = "Learning Tools notes deleted";
$string['eventltnoteviewed'] = "Learning Tools notes viewed";
$string['eventltnoteedited'] = "Learning Tools notes edited";


$string['bookicon'] = "Icon";
$string['bookmarkinfo'] = "Bookmarks Info";
$string['time'] = "Time";
$string['delete'] = "Delete";
$string['view'] = "View Bookmarks";
$string['allcourses'] = "All courses";
$string['sortbydate'] = "Sort by date";
$string['sortbycourse'] = "Sort by course";
$string['viewcourse'] = "View course";
$string['viewactivity'] = "View Activity";
$string['deletemessage'] = 'Delete Message';
$string['deletemsgcheckfull'] = 'Are you absolutely sure you want to completely delete the bookmarks, including their bookmarks and other bookmarks data?';
$string['deletednotmessage'] = 'Could not delete bookmarks!';
$string['successdeletemessage'] = "Successfully deleted";
$string['ltbookmarks:manageltbookmarks'] = "User access the ltbookmarks tool.";
$string['bookmarkstoolcategory'] = "Tool Bookmarks";
$string['coursebookmarks'] = "Course Bookmarks";
$string['successbookmarkmessage'] = "This page bookmarked successfully and you can view the bookmarks under profile / learning tools / bookmarks.";
$string['removebookmarkmessage'] = "This page bookmark removed and you can view the bookmarks under profile / learning tools / bookmarks.";

// Notes strings.

$string['note'] = "Notes";
$string['notes'] = "Notes";
$string['managenote'] = "Manage note access the users";
$string['ltnote:manageltnote'] = "User access the ltnote tool";
$string['newnote'] = "Take notes";
$string['allcourses'] = "All Courses";
$string['sortbydate'] = "Sort by date";
$string['sortbycourse'] = "Sort by course";
$string['sortbyactivity'] = "Sort by Activity";
$string['coursenotes'] = "Course Notes";
$string['deletemessage'] = 'Delete Message';
$string['editnote'] = 'Edit Note';

$string['deletemsgcheckfullbookmarks'] = "Are you absolutely sure you want to completely delete the Bookmarks, including their Bookmarks and data?";

$string['deletemsgcheckfull'] = 'Are you absolutely sure you want to completely delete the Note, including their Note and data?';

$string['deletednotmessage'] = 'Could not delete Note!';
$string['successdeletemessage'] = "Successfully deleted";
$string['successeditnote'] = "Successfully edited";
$string['allactiviies'] = "All Activities";
$string['successnotemessage'] = "Notes added successfully and you can view the note under profile / learning tools / note.";
$string['viewownbookmarks'] = "View Own Bookmarks";
$string['viewchildbookmarks'] = "View Child Bookmarks";
$string['pagenotes'] = "Page notes";
$string['courseparticipants'] = "Course Participants";
$string['viewbookmarks'] = "View Bookmarks";
$string['viewpage'] = "View Page";

// Invite ltool strings.
$string['invite'] = "Invite";
$string['inviteusers'] = "Invite Users";
$string['usersemail'] = "Users Email";
$string['invitenow'] = "Invite Now";
$string['inviteuserslist'] = "Invite users reports";
$string['successinviteusers'] = 'Invite users successfully.';
$string['donotcreateusers'] = "Do not create users";
$string['enrolled'] = "Enrolled successfully";
$string['alredyenrolled'] = "User was already enrolled";
$string['suspended'] = "User was suspended";
$string['timeaccess'] = "Time access";
$string['registerandenrolled'] = "User was registered and enrolled successfully";
$string['invaildemail'] = "User email not exists";
$string['invaildemailadderss'] = "Email adderss should be wrong";
$string['invite_maunalenrolerr'] = "Manual enrolments are not available in this course.";

// Resume course strings.
$string['resumecourse'] = "Resume course";
$string['donotresumecourse'] = "You don't have any pages to resume";

// Email tool strings.
$string['email'] = "Email";
$string['sentemailparticipants'] = "Send the email to course participants";
$string['subject'] = "Subject";
$string['message'] = "Message";
$string['subjecterr'] = "Missing Subject";
$string['messageerr'] = "Missing Message";
$string['recipients'] = "Recipients";
$string['recipientserr'] = "Missing Recipient";
$string['attachments'] = "Attachments";
$string['msgemailsent'] = "Successfully sent the mail to users";
$string['sentemailuserslist'] = "Sent the email to users list";
$string['receivedusers'] = "Received Users";
$string['listemailreports'] = "Email send reports";
$string['inviteusers_help'] = "Please add one e-mail per line.";
// Force activity.

$string['forceactivity'] = "Force activity";
$string['courseactivity'] = "Course activity";
$string['noactivity'] = "No force activiy";
$string['successforceactivityusers'] = "Successfully added the force activity in the course";

// Information tool.
$string['information'] = "Information";
$string['courseinfo'] = "Course Information";

// Focus tool.
$string['focus'] = "Focus";
$string['focusmodecss'] = "Customize Focus Mode";
$string['focusmode'] = "Focus mode";

// Css tool.
$string['css'] = "Css";
$string['coursecss'] = "Implement Course Css";
$string['customstyles'] = "Custom styles";
$string['coursecustomstyle'] = 'Custom styles for {$a->name} {$a->help}';
$string['parsecustomstyles_help'] = "Use this field to provide CSS code which will be injected at
the end of the style sheet for all pages in the current course.";
$string['parsecustomstyles'] = "Use this field to provide CSS code which will be injected at
the end of the style sheet for all pages in the current course.";

// Schedule tool.
$string['schedule'] = "Schedule";
$string['visitpage'] = "Visit page";
$string['successtoolschedule'] = "Schedule added the user calendar";

// Time management.

$string['timemanagement'] = 'Time management';
$string['timemanagementheader'] = 'Time management for {$a->name}';
$string['print'] = "Print";
$string['sendmessage'] = "Send message";
$string['viewprofile'] = "View profile";
$string['managedates'] = "Manage dates";
$string['activities'] = "activities";
$string['today'] = "Today";
$string['expectstarton'] = "should be started";
$string['expectdueon'] = "should be completed";
$string['open'] = "Open";
$string['resume'] = "Resume";
$string['review'] = "Review";
$string['receivegrade'] = "Receive grade";
$string['completeactivity'] = "Complete activity";
$string['exuserenrollment'] = "example user enrollment";
$string['courseduedate'] = "Course due date";
$string['none'] = "None";
$string['after'] = "After";
$string['date'] = "Date";
$string['hours'] = "Hours";
$string['days'] = "Days";
$string['months'] = "Months";
$string['years'] = "Years";
$string['startdate'] = "Start date";
$string['duedate'] = "Due date";
$string['uponenroll'] = "Upon enrollment";
$string['open'] = "Open";
$string['saveandgen'] = "Save and generate calendar entries";
$string['cancel'] = "Cancel";
$string['enablecompletiontrackmsg'] = "Turn on completion tracking to use Time Management.";
$string['enrolluserscourse'] = "Please enroll at least one user to use Time Management.";
$string['enrolldurationstr'] = "Enrollment duration";
$string['courseprogress'] = "Course progress";
$string['coursecompdate'] = "Course completion date";
$string['progress'] = "Progress";
$string['cmpdate'] = "Completion date";
$string['managetimemanagment'] = "Manage dates reports";
$string['gotocourse'] = "Go to course";
$string['baseformat'] = '%B %d, %Y, %I:%M %p';
$string['strftimemonthdateyear'] = '%B, %dth %Y';
$string['strftimeyearmonth'] = '%Y/%m/%d';
$string['strftimemonthnamedate'] = '%B %d, %Y';
$string['subplugintype_ltool'] = "Learning Tools";
$string['subplugintype_ltool_plural'] = "ltools";
