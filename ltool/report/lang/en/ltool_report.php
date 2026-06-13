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
 * Language strings for the report learning tool.
 *
 * @package   ltool_report
 * @copyright 2026, bdecent gmbh bdecent.de
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['alsosupport'] = 'Also send to the support user';
$string['alsosupport_desc'] = 'Send a copy of every report to the site support user, in addition to the recipient roles.';
$string['description'] = 'Description';
$string['descriptionplaceholder'] = 'Describe the issue you encountered on this page.';
$string['eventltreportsubmitted'] = 'Learning tools report submitted';
$string['invalidissuetype'] = 'Invalid issue type.';
$string['issuetype'] = 'Issue type';
$string['issuetype_accessibility'] = 'Accessibility problem';
$string['issuetype_content'] = 'Content error';
$string['issuetype_question'] = 'General question';
$string['issuetype_technical'] = 'Technical issue';
$string['issuetypedisabled'] = 'This issue type is not currently accepted.';
$string['issuetypesheading'] = 'Issue types and recipients';
$string['issuetypesheading_desc'] = 'Enable each issue type and choose which role holders in the page’s context receive it.';
$string['messagebody'] = '{$a->user} reported an issue on the platform.

Issue type: {$a->issuetype}
Page: {$a->pagetitle}
Link: {$a->pageurl}

Description:
{$a->description}';
$string['messagebodyhtml'] = '<p>{$a->user} reported an issue on the platform.</p>
<ul>
<li><strong>Issue type:</strong> {$a->issuetype}</li>
<li><strong>Page:</strong> <a href="{$a->pageurl}">{$a->pagetitle}</a></li>
</ul>
<p><strong>Description:</strong></p>
<p>{$a->description}</p>';
$string['messageprovider:report'] = 'Issue reports';
$string['messagesmall'] = '{$a->user} reported a {$a->issuetype} on “{$a->pagetitle}”.';
$string['messagesubject'] = 'New report ({$a->issuetype}): {$a->pagetitle}';
$string['nodescription'] = 'Please describe the issue.';
$string['pluginname'] = 'Learning Tools Report';
$string['privacy:metadata:report:contextid'] = 'The context id of the page.';
$string['privacy:metadata:report:contextlevel'] = 'The context level of the page.';
$string['privacy:metadata:report:course'] = 'The course the page belonged to.';
$string['privacy:metadata:report:coursemodule'] = 'The course module the page belonged to.';
$string['privacy:metadata:report:description'] = 'The description of the reported issue.';
$string['privacy:metadata:report:issuetype'] = 'The reported issue type.';
$string['privacy:metadata:report:pagetitle'] = 'The title of the page.';
$string['privacy:metadata:report:pagetype'] = 'The type of the page.';
$string['privacy:metadata:report:pageurl'] = 'The URL of the page.';
$string['privacy:metadata:report:timecreated'] = 'The time the report was submitted.';
$string['privacy:metadata:report:userid'] = 'The id of the user who submitted the report.';
$string['privacy:metadata:reportmetadata'] = 'Information about issue reports submitted by users.';
$string['privacyreport'] = 'Learning Tools Report';
$string['report'] = 'Report';
$string['report:createreport'] = 'Submit an issue report';
$string['report:receivereport'] = 'Receive issue reports';
$string['reporthovername'] = 'Report an issue';
$string['reportsubmittedmessage'] = 'Thank you! Your report has been sent.';
$string['selectissuetype'] = 'Choose an issue type…';
$string['settingenable'] = 'Enable “{$a}”';
$string['settingenable_desc'] = 'Allow users to submit “{$a}” reports.';
$string['settingroles'] = 'Recipient roles for “{$a}”';
$string['settingroles_desc'] = 'Members of these roles in the page’s context receive “{$a}” reports. If no role is selected, the report is sent to the support user instead.';
$string['specificuser'] = 'Also send to a specific user';
$string['specificuser_desc'] = 'Send a copy of every report to the chosen user(s), in addition to the recipient roles.';
$string['submit'] = 'Submit report';
$string['submitreport'] = 'Report an issue';
$string['viewpage'] = 'View the page';
