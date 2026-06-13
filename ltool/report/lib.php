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
 * ltool plugin "Learning Tools Report" - library file.
 *
 * @package   ltool_report
 * @copyright 2026, bdecent gmbh bdecent.de
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/local/learningtools/lib.php');

/**
 * The issue types the report tool supports, in display order.
 *
 * @return string[]
 */
function ltool_report_get_issue_types() {
    return ['technical', 'question', 'accessibility', 'content'];
}

/**
 * The issue types the admin has enabled, in display order.
 *
 * @return string[]
 */
function ltool_report_get_enabled_issue_types() {
    $enabled = [];
    foreach (ltool_report_get_issue_types() as $type) {
        // Default to enabled when the setting has never been saved.
        $config = get_config('ltool_report', 'enable' . $type);
        if ($config === false || $config) {
            $enabled[] = $type;
        }
    }
    return $enabled;
}

/**
 * Resolve the recipients of a report of a given issue type submitted from a context.
 *
 * Recipients = holders of the admin-configured role(s) for the issue type in the page context
 * (including parent contexts). When that resolves nobody (no role configured, or no holder
 * present) it falls back to the support user. The admin may additionally route every report to
 * the support user and/or a specific user.
 *
 * @param \context $context the page context the report was submitted from
 * @param string $issuetype the issue type key
 * @return array recipient user records indexed by user id
 */
function ltool_report_get_recipients(\context $context, $issuetype) {
    $recipients = [];

    // Holders of the configured role(s) for this issue type.
    $rolesconfig = get_config('ltool_report', 'roles' . $issuetype);
    $roleids = ($rolesconfig !== false && $rolesconfig !== '') ? explode(',', $rolesconfig) : [];
    foreach ($roleids as $roleid) {
        $roleid = (int) $roleid;
        if (!$roleid) {
            continue;
        }
        // Fetch full user records ('u.*') so message_send() has every property it needs.
        foreach (get_role_users($roleid, $context, true, 'u.*') as $user) {
            $recipients[$user->id] = $user;
        }
    }

    // Fallback to the support user when the issue type routed to nobody.
    if (empty($recipients)) {
        $support = \core_user::get_support_user();
        $recipients[$support->id] = $support;
    }

    // Optional: also send every report to the support user.
    if (get_config('ltool_report', 'alsosupport')) {
        $support = \core_user::get_support_user();
        $recipients[$support->id] = $support;
    }

    // Optional: also send every report to a specific user (or all holders of the receive capability).
    $specific = get_config('ltool_report', 'specificuser');
    if (!empty($specific) && $specific !== '$@NONE@$') {
        if ($specific === '$@ALL@$') {
            $capusers = get_users_by_capability(\context_system::instance(), 'ltool/report:receivereport');
            foreach ($capusers as $user) {
                $recipients[$user->id] = $user;
            }
        } else {
            foreach (explode(',', $specific) as $userid) {
                $userid = (int) $userid;
                if (!$userid) {
                    continue;
                }
                $user = \core_user::get_user($userid);
                if ($user && empty($user->deleted)) {
                    $recipients[$user->id] = $user;
                }
            }
        }
    }

    return $recipients;
}

/**
 * Store an issue report, fire the event and notify the resolved recipients.
 *
 * @param int $contextid page context id
 * @param array $data page identity data including issuetype and description
 * @return array result with success flag, message and notification type
 */
function ltool_report_user_submit_report($contextid, $data) {
    global $DB, $PAGE, $USER;
    $context = context::instance_by_id($contextid, MUST_EXIST);
    $PAGE->set_context($context);
    if (!PHPUNIT_TEST) {
        if (!confirm_sesskey()) {
            return '';
        }
    }

    $issuetype = clean_param(isset($data['issuetype']) ? $data['issuetype'] : '', PARAM_ALPHA);
    if (!in_array($issuetype, ltool_report_get_issue_types(), true)) {
        throw new moodle_exception('invalidissuetype', 'ltool_report');
    }
    if (!in_array($issuetype, ltool_report_get_enabled_issue_types(), true)) {
        throw new moodle_exception('issuetypedisabled', 'ltool_report');
    }

    $record = new stdClass();
    $record->userid = $USER->id;
    $record->course = $data['course'];
    $record->contextlevel = $data['contextlevel'];
    $record->contextid = $contextid;
    if ($record->contextlevel == CONTEXT_MODULE) {
        $record->coursemodule = local_learningtools_get_coursemodule_id($record);
    } else {
        $record->coursemodule = 0;
    }
    $record->pagetype = $data['pagetype'];
    $record->pagetitle = $data['pagetitle'];
    $record->pageurl = $data['pageurl'];
    $record->issuetype = $issuetype;
    $record->description = clean_param(isset($data['description']) ? $data['description'] : '', PARAM_TEXT);
    $record->timecreated = time();
    $record->id = $DB->insert_record('ltool_report_data', $record);

    \ltool_report\event\ltreport_submitted::create([
        'objectid' => $record->id,
        'courseid' => local_learningtools_get_eventlevel_courseid($context, $data['course']),
        'context' => $context,
        'userid' => $USER->id,
        'other' => ['issuetype' => $issuetype],
    ])->trigger();

    $recipients = ltool_report_get_recipients($context, $issuetype);
    ltool_report_send_report_notifications($recipients, $record, $USER, $context);

    return [
        'success' => true,
        'message' => get_string('reportsubmittedmessage', 'ltool_report'),
        'notificationtype' => 'success',
    ];
}

/**
 * Send the report notification to each recipient.
 *
 * @param array $recipients recipient user records
 * @param stdClass $record the stored report record
 * @param stdClass $reporter the user who submitted the report
 * @param \context $context the page context
 * @return void
 */
function ltool_report_send_report_notifications($recipients, $record, $reporter, \context $context) {
    $a = (object) [
        'user' => fullname($reporter),
        'issuetype' => get_string('issuetype_' . $record->issuetype, 'ltool_report'),
        'pagetitle' => $record->pagetitle,
        'pageurl' => $record->pageurl,
        'description' => $record->description,
    ];
    $subject = get_string('messagesubject', 'ltool_report', $a);
    $smallmessage = get_string('messagesmall', 'ltool_report', $a);
    $fullmessage = get_string('messagebody', 'ltool_report', $a);
    $fullmessagehtml = get_string('messagebodyhtml', 'ltool_report', $a);

    foreach ($recipients as $recipient) {
        $message = new \core\message\message();
        $message->component = 'ltool_report';
        $message->name = 'report';
        $message->userfrom = \core_user::get_noreply_user();
        $message->userto = $recipient;
        $message->subject = $subject;
        $message->fullmessage = $fullmessage;
        $message->fullmessageformat = FORMAT_PLAIN;
        $message->fullmessagehtml = $fullmessagehtml;
        $message->smallmessage = $smallmessage;
        $message->notification = 1;
        $message->courseid = (!empty($record->course)) ? $record->course : SITEID;
        $message->contexturl = $record->pageurl;
        $message->contexturlname = get_string('viewpage', 'ltool_report');
        message_send($message);
    }
}

/**
 * Build the template context for the report tool launcher.
 *
 * @param array $data tool records
 * @return array template context
 */
function ltool_report_get_template_context($data) {
    $data['ltoolreport'] = true;
    return $data;
}

/**
 * Render the report tool launcher button.
 *
 * @param array $templatecontent template content
 * @return string display html content.
 */
function ltool_report_render_template($templatecontent) {
    global $OUTPUT;
    return $OUTPUT->render_from_template('ltool_report/report', ltool_report_get_template_context($templatecontent));
}

/**
 * Queue the report tool javascript.
 *
 * @param array $data tool records
 * @return void
 */
function ltool_report_load_report_js_config($data) {
    global $PAGE;
    $PAGE->requires->js_call_amd('ltool_report/learningreport', 'init', [$PAGE->context->id, $data]);
}

/**
 * Fragment: render the report submission form with only the enabled issue types.
 *
 * @param array $args contextid
 * @return string rendered html
 */
function ltool_report_output_fragment_get_report_form($args) {
    global $OUTPUT;
    $contextid = clean_param($args['contextid'], PARAM_INT);
    $context = context::instance_by_id($contextid, MUST_EXIST);
    require_capability('ltool/report:createreport', $context);

    $issuetypes = [];
    foreach (ltool_report_get_enabled_issue_types() as $type) {
        $issuetypes[] = [
            'type' => $type,
            'label' => get_string('issuetype_' . $type, 'ltool_report'),
        ];
    }
    return $OUTPUT->render_from_template('ltool_report/report_form', ['issuetypes' => $issuetypes]);
}

/**
 * Whether the report tool is enabled.
 *
 * @return bool
 */
function ltool_report_is_report_status() {
    global $DB;
    $record = $DB->get_record('local_learningtools_products', ['shortname' => 'report']);
    return isset($record->status) && !empty($record->status);
}

/**
 * Delete a course's report data.
 *
 * @param int $courseid course id
 * @return void
 */
function ltool_report_delete_course_reports($courseid) {
    global $DB;
    if ($DB->record_exists('ltool_report_data', ['course' => $courseid])) {
        $DB->delete_records('ltool_report_data', ['course' => $courseid]);
    }
}

/**
 * Delete a module's report data.
 *
 * @param int $module course module id
 * @return void
 */
function ltool_report_delete_module_reports($module) {
    global $DB;
    if ($DB->record_exists('ltool_report_data', ['coursemodule' => $module])) {
        $DB->delete_records('ltool_report_data', ['coursemodule' => $module]);
    }
}
