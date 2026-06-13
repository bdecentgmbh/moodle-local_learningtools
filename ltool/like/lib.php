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
 * ltool plugin "Learning Tools Like" - library file.
 *
 * @package   ltool_like
 * @copyright 2026, bdecent gmbh bdecent.de
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/local/learningtools/lib.php');

/**
 * The reaction types supported by the like tool, in display order.
 *
 * @return string[]
 */
function ltool_like_get_reaction_types() {
    return ['dislike', 'like', 'superlike'];
}

/**
 * The reaction types the admin has enabled, in display order.
 *
 * @return string[]
 */
function ltool_like_get_enabled_reactions() {
    $enabled = [];
    foreach (ltool_like_get_reaction_types() as $type) {
        // Default to enabled when the setting has never been saved.
        $config = get_config('ltool_like', 'enable' . $type);
        if ($config === false || $config) {
            $enabled[] = $type;
        }
    }
    return $enabled;
}

/**
 * The current user's reaction for a page, or null.
 *
 * @param int $contextid page context id
 * @param string $pageurl page url
 * @param int $userid user id
 * @return string|null dislike|like|superlike or null
 */
function ltool_like_get_user_reaction($contextid, $pageurl, $userid) {
    global $DB;
    $sql = "SELECT reaction
              FROM {ltool_like_data}
             WHERE " . $DB->sql_compare_text('pageurl', 255) . " = " . $DB->sql_compare_text('?', 255) . "
               AND contextid = ?
               AND userid = ?";
    $record = $DB->get_record_sql($sql, [$pageurl, $contextid, $userid]);
    return $record ? $record->reaction : null;
}

/**
 * Aggregate reaction counts for a page.
 *
 * @param int $contextid page context id
 * @param string $pageurl page url
 * @return array reaction type => count
 */
function ltool_like_get_counts($contextid, $pageurl) {
    global $DB;
    $counts = ['dislike' => 0, 'like' => 0, 'superlike' => 0];
    $sql = "SELECT reaction, COUNT(id) AS num
              FROM {ltool_like_data}
             WHERE " . $DB->sql_compare_text('pageurl', 255) . " = " . $DB->sql_compare_text('?', 255) . "
               AND contextid = ?
          GROUP BY reaction";
    $records = $DB->get_records_sql($sql, [$pageurl, $contextid]);
    foreach ($records as $record) {
        if (array_key_exists($record->reaction, $counts)) {
            $counts[$record->reaction] = (int) $record->num;
        }
    }
    return $counts;
}

/**
 * The users who gave a particular reaction on a page.
 *
 * @param int $contextid page context id
 * @param string $pageurl page url
 * @param string $reaction reaction type
 * @return array list of user records
 */
function ltool_like_get_like_users($contextid, $pageurl, $reaction) {
    global $DB;
    $userfields = \core_user\fields::for_userpic()->get_sql('u', false, '', '', false)->selects;
    $sql = "SELECT $userfields
              FROM {ltool_like_data} d
              JOIN {user} u ON u.id = d.userid
             WHERE " . $DB->sql_compare_text('d.pageurl', 255) . " = " . $DB->sql_compare_text('?', 255) . "
               AND d.contextid = ?
               AND d.reaction = ?
          ORDER BY u.firstname ASC, u.lastname ASC";
    return $DB->get_records_sql($sql, [$pageurl, $contextid, $reaction]);
}

/**
 * The success notification message for a reaction.
 *
 * @param string $reaction reaction type
 * @return string
 */
function ltool_like_reaction_message($reaction) {
    return get_string('reaction' . $reaction . 'message', 'ltool_like');
}

/**
 * Build a new like data record from the page identity data.
 *
 * @param int $contextid page context id
 * @param array $data page identity data
 * @param string $reaction reaction type
 * @return stdClass
 */
function ltool_like_build_record($contextid, $data, $reaction) {
    global $USER;
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
    $record->reaction = $reaction;
    return $record;
}

/**
 * Save, switch or remove the current user's reaction for a page (Netflix-style toggle).
 *
 * @param int $contextid page context id
 * @param array $data page identity data including the chosen reaction
 * @return array result with the user's reaction, message, notification type and (when permitted) counts
 */
function ltool_like_user_save_like($contextid, $data) {
    global $DB, $PAGE;
    $context = context::instance_by_id($contextid, MUST_EXIST);
    $PAGE->set_context($context);
    if (!PHPUNIT_TEST) {
        if (!confirm_sesskey()) {
            return '';
        }
    }

    $new = clean_param(isset($data['reaction']) ? $data['reaction'] : '', PARAM_ALPHA);
    if ($new !== '' && !in_array($new, ltool_like_get_enabled_reactions(), true)) {
        throw new moodle_exception('invalidreaction', 'ltool_like');
    }
    $pageurl = $data['pageurl'];

    $sql = "SELECT *
              FROM {ltool_like_data}
             WHERE " . $DB->sql_compare_text('pageurl', 255) . " = " . $DB->sql_compare_text('?', 255) . "
               AND contextid = ?
               AND userid = ?";
    $existing = $DB->get_record_sql($sql, [$pageurl, $contextid, $data['user']]);

    $eventcourseid = local_learningtools_get_eventlevel_courseid($context, $data['course']);
    $eventparams = [
        'courseid' => $eventcourseid,
        'context' => $context,
        'userid' => $data['user'],
        'other' => ['pagetype' => $data['pagetype'], 'reaction' => $new],
    ];

    if (empty($existing) && $new !== '') {
        // Add a new reaction.
        $record = ltool_like_build_record($contextid, $data, $new);
        $record->timecreated = time();
        $record->timemodified = time();
        $record->id = $DB->insert_record('ltool_like_data', $record);
        \ltool_like\event\ltlike_created::create(['objectid' => $record->id] + $eventparams)->trigger();
        $own = $new;
        $message = ltool_like_reaction_message($new);
        $notificationtype = 'success';
    } else if (!empty($existing) && ($new === '' || $new === $existing->reaction)) {
        // Remove the existing reaction (clicked the active one, or cleared it).
        $DB->delete_records('ltool_like_data', ['id' => $existing->id]);
        $eventparams['other']['reaction'] = $existing->reaction;
        \ltool_like\event\ltlike_deleted::create(['objectid' => $existing->id] + $eventparams)->trigger();
        $own = '';
        $message = get_string('reactionremovedmessage', 'ltool_like');
        $notificationtype = 'info';
    } else if (!empty($existing)) {
        // Switch to a different reaction.
        $existing->reaction = $new;
        $existing->timemodified = time();
        $DB->update_record('ltool_like_data', $existing);
        \ltool_like\event\ltlike_updated::create(['objectid' => $existing->id] + $eventparams)->trigger();
        $own = $new;
        $message = ltool_like_reaction_message($new);
        $notificationtype = 'success';
    } else {
        // Nothing existed and nothing chosen.
        $own = '';
        $message = get_string('reactionremovedmessage', 'ltool_like');
        $notificationtype = 'info';
    }

    $cancount = has_capability('ltool/like:viewcount', $PAGE->context);
    $result = [
        'reaction' => $own,
        'message' => $message,
        'notificationtype' => $notificationtype,
        'cancount' => $cancount,
        'canviewusers' => has_capability('ltool/like:viewlikes', $PAGE->context),
    ];
    if ($cancount) {
        $result['counts'] = ltool_like_get_counts($contextid, $pageurl);
    }
    return $result;
}

/**
 * Build the template context for the inline reaction button group.
 *
 * @param array $data tool records (page identity, colours, capability flags)
 * @return array template context
 */
function ltool_like_get_template_context($data) {
    $icons = [
        'dislike' => 'fa-thumbs-down',
        'like' => 'fa-thumbs-up',
        'superlike' => 'fa-thumbs-up',
    ];
    $reactions = [];
    foreach (ltool_like_get_enabled_reactions() as $type) {
        $isactive = ($data['currentreaction'] === $type);
        $count = isset($data['counts'][$type]) ? (int) $data['counts'][$type] : 0;
        $reactions[] = [
            'type' => $type,
            'icon' => $icons[$type],
            'label' => get_string('reaction' . $type . 'label', 'ltool_like'),
            'isactive' => $isactive,
            'superlike' => ($type === 'superlike'),
            'count' => $count,
            'countlabel' => get_string(
                'reactioncountlabel',
                'ltool_like',
                (object) ['count' => $count, 'reaction' => get_string('reaction' . $type, 'ltool_like')]
            ),
        ];
    }
    $data['reactions'] = $reactions;
    $data['ltoollike'] = true;
    // The floating-button launcher mirrors the user's current reaction (neutral thumb when none).
    $current = (string) $data['currentreaction'];
    $data['hasreaction'] = ($current !== '');
    $data['launchericon'] = ($current === 'dislike') ? 'fa-thumbs-down' : 'fa-thumbs-up';
    return $data;
}

/**
 * Learning tools like template function.
 *
 * @param array $templatecontent template content
 * @return string display html content.
 */
function ltool_like_render_template($templatecontent) {
    global $OUTPUT;
    return $OUTPUT->render_from_template('ltool_like/like', ltool_like_get_template_context($templatecontent));
}

/**
 * Queue the like tool javascript.
 *
 * @param array $data tool records
 * @return void
 */
function ltool_like_load_like_js_config($data) {
    global $PAGE;
    $PAGE->requires->js_call_amd('ltool_like/learninglike', 'init', [$PAGE->context->id, $data]);
}

/**
 * Whether the like tool is enabled.
 *
 * @return bool
 */
function ltool_like_is_like_status() {
    global $DB;
    $record = $DB->get_record('local_learningtools_products', ['shortname' => 'like']);
    return isset($record->status) && !empty($record->status);
}

/**
 * Delete a course's reaction data.
 *
 * @param int $courseid course id
 * @return void
 */
function ltool_like_delete_course_likes($courseid) {
    global $DB;
    if ($DB->record_exists('ltool_like_data', ['course' => $courseid])) {
        $DB->delete_records('ltool_like_data', ['course' => $courseid]);
    }
}

/**
 * Delete a module's reaction data.
 *
 * @param int $module course module id
 * @return void
 */
function ltool_like_delete_module_likes($module) {
    global $DB;
    if ($DB->record_exists('ltool_like_data', ['coursemodule' => $module])) {
        $DB->delete_records('ltool_like_data', ['coursemodule' => $module]);
    }
}

/**
 * Fragment: render the capability-gated list of users grouped by reaction (manager modal).
 *
 * @param array $args contextid + pageurl
 * @return string rendered html
 */
function ltool_like_output_fragment_get_like_users($args) {
    global $OUTPUT;
    $contextid = clean_param($args['contextid'], PARAM_INT);
    $pageurl = isset($args['pageurl']) ? $args['pageurl'] : '';
    $context = context::instance_by_id($contextid, MUST_EXIST);
    require_capability('ltool/like:viewlikes', $context);

    $groups = [];
    foreach (ltool_like_get_enabled_reactions() as $type) {
        $users = ltool_like_get_like_users($contextid, $pageurl, $type);
        $userlist = [];
        foreach ($users as $user) {
            $userlist[] = [
                'fullname' => fullname($user),
                'profileurl' => (new moodle_url('/user/profile.php', ['id' => $user->id]))->out(false),
                'userpic' => $OUTPUT->user_picture($user, ['size' => 35, 'link' => false]),
            ];
        }
        $groups[] = [
            'type' => $type,
            'heading' => get_string('reaction' . $type . 'heading', 'ltool_like'),
            'count' => count($userlist),
            'hasusers' => !empty($userlist),
            'users' => $userlist,
        ];
    }
    return $OUTPUT->render_from_template('ltool_like/like_users', ['groups' => $groups]);
}
