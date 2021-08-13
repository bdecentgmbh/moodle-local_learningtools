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
 * Local plugin "Learning Tools" - lib file.
 *
 * @package   local_learningtools
 * @copyright bdecent GmbH 2021
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core_user\output\myprofile\tree;

defined('MOODLE_INTERNAL') || die();

/**
 * Defines learningtools nodes for my profile navigation tree.
 *
 * @param \core_user\output\myprofile\tree $tree Tree object
 * @param stdClass $user user object
 * @param bool $iscurrentuser is the user viewing profile, current user ?
 * @param stdClass $course course object
 *
 * @return bool
 */
function local_learningtools_myprofile_navigation(tree $tree, $user, $iscurrentuser, $course) {
    // Get the learningtools category.
    if (!array_key_exists('learningtools', $tree->__get('categories'))) {
        // Create the category.
        $categoryname = get_string('learningtools', 'local_learningtools');
        $category = new core_user\output\myprofile\category('learningtools', $categoryname, 'privacyandpolicies');
        $tree->add_category($category);
    } else {
        // Get the existing category.
        $category = $tree->__get('categories')['learningtools'];
    }
    return true;
}
/**
 * Adds ltools action in each page to the given navigation node if caps are met.
 *
 * @param navigation_node $navigationnode The navigation node to add the question branch to
 * @param object $context
 * @return navigation_node Returns the question branch that was added
 */
function local_learningtools_extend_settings_navigation($settingnav, $context) {

    global $PAGE, $CFG, $USER, $COURSE;
    $context = context_system::instance();
    $ltoolsjs = array();
    $fabbuttonhtml = get_learningtools_info();
    $ltoolsjs['disappertimenotify'] = get_config('local_learningtools', 'notificationdisapper');
    $ltoolsjs['pagebookmarks'] = check_page_bookmarks_exist($PAGE->context->id, $PAGE->pagetype, $USER->id);
    $ltoolsjs['notestrigger'] = optional_param('notes', '', PARAM_TEXT);
    $PAGE->requires->data_for_js('fabbuttonhtml', $fabbuttonhtml);
    $PAGE->requires->data_for_js('ltools', $ltoolsjs);
    $viewbookmarks = false;
    $viewnote = false;
    if (has_capability('ltool/bookmarks:viewownbookmarks', $context) && is_bookmarks_status()) {
        $viewbookmarks = true;
    }
    if (has_capability('ltool/note:viewownnote', $context) && is_note_status()) {
        $viewnote = true;
    }

    $loggedin = false;
    if (isloggedin() && !isguestuser()) {
        $loggedin = true;
    }
    $viewcapability = array('viewbookmarks' => $viewbookmarks, 'viewnote' => $viewnote, 'loggedin' => $loggedin);
    $PAGE->requires->js_call_amd('local_learningtools/learningtools', 'init', $viewcapability);

    $params['course'] = $COURSE->id;
    $params['contextlevel'] = $PAGE->context->contextlevel;
    $params['pagetype'] = $PAGE->pagetype;
    $params['pageurl'] = $PAGE->url->out(false);
    $params['user'] = $USER->id;
    $params['contextid'] = $PAGE->context->id;
    $params['title'] = $PAGE->title;
    $params['heading'] = $PAGE->heading;

    if (file_exists($CFG->dirroot.'/local/learningtools/ltool/note/lib.php')) {
        $PAGE->requires->js_call_amd('ltool_note/learningnote', 'init', array($PAGE->context->id, $params));
    }
    if (file_exists($CFG->dirroot.'/local/learningtools/ltool/bookmarks/lib.php')) {
        $PAGE->requires->js_call_amd('ltool_bookmarks/learningbookmarks', 'init', array($PAGE->context->id));
    }

}

/**
 * Get the type of instance.
 * @param object list of the page info.
 * @return object instance object.
 */

function check_instanceof_block($record) {

    $data = new stdClass;
    if ($record->contextlevel == 10) { // System level.
        $data->instance = 'system';
    } else if ($record->contextlevel == 30) { // User level.
        $data->instance = 'user';
    } else if ($record->contextlevel == 50) {  // Course level.
        $data->instance = 'course';
        $data->courseid = $record->course;
        $data->contextid = $record->contextid;

    } else if ($record->contextlevel == 70) { // Mod level.
        $data->instance = 'mod';
        $data->courseid = $record->course;
        $data->contextid = $record->contextid;
        $data->coursemodule = get_coursemodule_id($record);

    } else if ($record->contextlevel == 80) { // Context blocklevel.
        $data->instance = 'block';
    }
    return $data;
}
/**
 * Get the course module id.
 * @param int context id
 * @parma int context level
 * @return int course module id
 */
function get_moduleid($contextid, $contextlevel) {
    $coursemodule = 0;
    if ($contextlevel == 70) {
        $record = new stdClass;
        $record->contextid = $contextid;
        $record->contextlevel = $contextlevel;
        $coursemodule = get_coursemodule_id($record);
    }
    return $coursemodule;
}

/**
 * Get the course module Id.
 * @param array list of the page info.
 * @return int course module id.
 */
function get_coursemodule_id($record) {
    global $DB;

    $contextinfo = $DB->get_record('context', array('id' => $record->contextid, 'contextlevel' => $record->contextlevel));
    return $contextinfo->instanceid;
}
/**
 * Get the courses name.
 * @param array course ids
 * @param string pageurl
 * @param int course selected
 * @param int userid
 * @param int courseid
 * @return array list of the course info.
 */
function get_courses_name($courses, $url = '', $selectcourse = 0, $userid= 0, $usercourseid = 0) {
    $courseids = [];
    $courseinfo = [];
    $courseids = $courses;
    if (!empty($courseids)) {
        foreach ($courseids as $courseid) {
            $course = get_course($courseid);
            $course = new core_course_list_element($course);
            if ($url) {
                $list = [];
                $list['id'] = $course->id;
                $list['name'] = $course->get_formatted_name();
                $urlparams = array('selectcourse' => $course->id);
                if ($userid) {
                    $urlparams['userid'] = $userid;
                }
                if ($usercourseid) {
                    $urlparams['courseid'] = $usercourseid;
                }
                $url = new moodle_url($url, $urlparams);
                $list['url'] = $url->out(false);
                if ($course->id == $selectcourse) {
                    $list['selected'] = "selected";
                } else {
                    $list['selected'] = "";
                }
                $courseinfo[] = $list;
            } else {
                $courseinfo[$course->id] = $course->get_formatted_name();
            }
        }
    }
    return $courseinfo;

}

/**
 * Get the course name.
 * @param int course id.
 * @param string course name.
 */
function get_course_name($courseid) {

    $course = get_course($courseid);
    $course = new core_course_list_element($course);
    return $course->get_formatted_name();
}

/**
 * Get the course category name.
 * @param int course id.
 * @param string category name.
 */
function get_course_categoryname($courseid) {

    $course = get_course($courseid);
    $category = \core_course_category::get($course->category);
    return $category->get_formatted_name();
}

/**
 * Get the course module name
 * @param object instance of the page.
 * @param bool return which type of name.
 * @param string modulename | instance name
 */
function get_module_name($data, $mod = false) {
    global $DB;
    $coursemoduleinfo = $DB->get_record('course_modules', array('id' => $data->coursemodule));
    $moduleinfo = $DB->get_record('modules', array('id' => $coursemoduleinfo->module));
    if ($mod) {
        return $moduleinfo->name;
    }
    // Get module instance name.
    $report = get_coursemodule_from_instance($moduleinfo->name, $coursemoduleinfo->instance, $data->courseid);
    return $report->name;
}

/**
 * Get the course module include with section.
 * @param object instance of the page.
 * @param string ltool type.
 * @return string instance of coursemodule name.
 */
function get_module_coursesection($data, $type = 'bookmark') {
    $coursename = get_course_name($data->courseid);
    $section = get_mod_section($data->courseid, $data->coursemodule);
    if ($type == 'note') {
        $modulename = get_module_name($data);
        return $coursename.' / '. $section. ' / '. $modulename;
    }
    return $coursename.' / '. $section;
}

/**
 * Get the course module current section.
 * @param int course id.
 * @param int coursemodule id.
 * @return string|bool section name.
 */
function get_mod_section($courseid, $modid) {
    global $DB;

    $sections = $DB->get_records('course_sections', array('course' => $courseid));
    $sectionname = [];
    $sectionmod = [];
    if (!empty($sections)) {
        foreach ($sections as $key => $value) {

            $sequence = '';
            if (!empty($value->name)) {
                $sectionname[$value->id] = $value->name;
            } else {
                if ($value->section == 0) {
                    $sectionname[$value->id] = get_string('general', 'local_learningtools');
                } else {
                    $sectionname[$value->id] = get_string('topic', 'local_learningtools', $value->section);
                }
            }
            if ($value->sequence) {
                $sequence = explode(',', $value->sequence);
            }
            $sectionmod[$value->id] = isset($sequence) ? $sequence : '';

        }
    }
    if ($sectionname && $sectionmod) {
        foreach ($sectionmod as $key => $value) {
            if (!empty($value)) {
                if ( is_numeric(array_search($modid, $value)) ) {
                    return $sectionname[$key];
                }
            }
        }
    }
    return '';
}

/**
 * Display fab button html.
 * @return string fab button html content.
 */
function get_learningtools_info() {

    global $DB, $CFG;
    $context = context_system::instance();
    $content = '';

    $content .= html_writer::start_tag('div', array('class' => 'floating-button'));
    $content .= html_writer::start_tag('div', array('class' => 'list-learningtools'));
    $learningtools = $DB->get_records('learningtools_products', array('status' => 1), 'sort');

    if (!empty($learningtools)) {
        foreach ($learningtools as $tool) {
            $capability = 'ltool/'.$tool->shortname.':create'. $tool->shortname;
            if (has_capability($capability, $context)) {
                $location = "$CFG->dirroot/local/learningtools/ltool/$tool->shortname";
                $class = "$tool->shortname";
                if (file_exists("$location/index.php")) {
                    include_once("$location/index.php");
                    if (class_exists($class)) {
                        $function  = "get_{$tool->shortname}_info";
                        $toolobj = new $class();
                        $content .= $function($toolobj, $tool);
                    }
                }
            }
        }
    }
    $content .= html_writer::end_tag('div');
            $content .= html_writer::start_tag('button', array("class" => "btn btn-primary", 'id' => 'tool-action-button') );
    $content .= html_writer::start_tag('i', array('class' => 'fa fa-magic'));
    $content .= html_writer::end_tag('i');
    $content .= html_writer::end_tag("button");
    $content .= html_writer::end_tag('div');
    return $content;
}

/**
 * Get the bookmarks content.
 * @param object tool info
 * @param object bookmarks plugin info
 * @return string display tool bookmarks plugin html.
 */
function get_bookmarks_info($toolobj, $tool) {
    global $CFG, $USER, $COURSE, $PAGE;
    $data = $toolobj->get_tool_info();
    $data['toolurl'] = "$CFG->wwwroot/local/learningtools/ltool/$tool->shortname/$tool->shortname"."_info.php";
    $data['id'] = $tool->shortname;
    $data['user'] = $USER->id;
    $data['course'] = $COURSE->id;
    $data['pageurl'] = $PAGE->url->out(false);
    $data['pagetype'] = $PAGE->pagetype;
    $data['coursemodule'] = get_moduleid($PAGE->context->id, $PAGE->context->contextlevel);
    $data['contextlevel'] = $PAGE->context->contextlevel;
    $data['contextid'] = $PAGE->context->id;
    $data['sesskey'] = sesskey();
    $data['ltbookmark'] = true;
    $data['bookmarkhovername'] = get_string('addbookmark', 'local_learningtools');
    $data['pagebookmarks'] = check_page_bookmarks_exist($PAGE->context->id, $PAGE->pagetype, $USER->id);
    return learningtools_render_template($data);
}
/**
 * Check the page bookmarks exists or not.
 * @param int page context id
 * @param string page type
 * @param int userid
 * @return bool page bookmarks status
 */
function check_page_bookmarks_exist($contextid, $pagetype, $userid) {
    global $DB;

    $pagebookmarks = false;
    if ($DB->record_exists('learningtools_bookmarks', array('contextid' => $contextid,
        'pagetype' => $pagetype, 'user' => $userid))) {
        $pagebookmarks = true;
    }
    return $pagebookmarks;
}

/**
 * Get the notes content.
 * @param object tool info
 * @param object note plugin info
 * @return string display tool note plugin html
 */
function get_note_info($toolobj, $tool) {
    global $DB, $PAGE, $USER;

    $args = [];
    $args['contextid'] = $PAGE->context->id;
    $args['pagetype'] = $PAGE->pagetype;
    $args['user'] = $USER->id;
    $data = $toolobj->get_tool_info();
    $data['ltnote'] = true;
    $data['pagenotes'] = get_userpage_countnotes($args);
    $data['notehovername'] = get_string('createnote', 'local_learningtools');
    return learningtools_render_template($data);
}
/**
 * Get the user pagenotes
 * @param array page info
 * @return int page user notes.
 */
function get_userpage_countnotes($args) {
    global $DB;
    return $DB->count_records('learningtools_note', array('contextid' => $args['contextid'],
        'pagetype' => $args['pagetype'], 'user' => $args['user']));

}

/**
 * Learning tools template function.
 * @param array template content
 * @param string display html content.
 */
function learningtools_render_template($data) {
    global $OUTPUT;
    return $OUTPUT->render_from_template('local_learningtools/learningtools', $data);
}
/**
 * Get the students in course.
 * @param int course id
 * @return array students user ids.
 */
function get_students_incourse($courseid) {
    global $DB;
    $users = [];
    $student = $DB->get_record('role', array('shortname' => 'student'));
    if ($student) {
        $coursecontext = context_course::instance($courseid);
        $coursestudents = get_role_users($student->id, $coursecontext);
        if (!empty($coursestudents)) {
            foreach ($coursestudents as $coursestudent) {
                $users[] = $coursestudent->id;
            }
        }
    }
    return $users;
}

/**
 * Find the logged in user is assigned into any relative roles to the shared user.
 *
 * @param  mixed $childuserid
 * @return object|bool
 */
function is_parentforchild(int $childuserid, string $capability='') {
    global $USER;
    $usercontext = \context_user::instance($childuserid); // USER - child id.
    $usercontextroles = get_user_roles($usercontext, $USER->id); // Loggedin - parent.
    if (!empty($capability)) {
        return has_viewtool_capability_role($usercontextroles, $capability);
    }
    return (!empty($usercontextroles)) ? $usercontextroles : false;
}

/**
 * Check the tool capability for parents.
 * @param array assign roles.
 * @param string capability
 * @return bool stauts
 */
function has_viewtool_capability_role($assignedroles, string $capability) {
    $roles = [];
    if (empty($assignedroles)) {
        return false;
    }
    foreach ($assignedroles as $assignid => $role) {
        $roles[] = $role->roleid;
    }
    $roleshascaps = get_roles_with_capability($capability);
    $result = array_intersect($roles, array_keys($roleshascaps));
    return !empty($result) ? true : false;
}


/**
 * Check the bookmarks status.
 * @return bool
 */
function is_bookmarks_status() {
    global $DB;
    $bookmarksrecord = $DB->get_record('learningtools_products', array('shortname' => 'bookmarks'));
    if (isset($bookmarksrecord->status) && !empty($bookmarksrecord->status)) {
        return true;
    }
    return false;
}

/**
 * Check the bookmarks view capability
 * @return bool|redirect status
 */
function require_bookmarks_status() {
    if (!is_bookmarks_status()) {
        $url = new moodle_url('/my');
        redirect($url);
    }
    return true;
}

/**
 * Check the note status.
 * @return bool
 */
function is_note_status() {
    global $DB;
    $noterecord = $DB->get_record('learningtools_products', array('shortname' => 'note'));
    if (isset($noterecord->status) && !empty($noterecord->status)) {
        return true;
    }
    return false;
}
/**
 * Check the note view capability.
 * @return bool|redirect status
 */
function require_note_status() {
    if (!is_note_status()) {
        $url = new moodle_url('/my');
        redirect($url);
    }
    return true;
}

/**
 * Delete the course bookmarks
 * @param int courseid
 */
function delete_course_bookmarks($courseid) {
    global $DB;
    if ($DB->record_exists('learningtools_bookmarks', array('course' => $courseid))) {
        $DB->delete_records('learningtools_bookmarks', array('course' => $courseid));
    }
}

/**
 * Delete the course notes.
 * @param int courseid
 */
function delete_course_notes($courseid) {
    global $DB;
    if ($DB->record_exists('learningtools_note', array('course' => $courseid))) {
        $DB->delete_records('learningtools_note', array('course' => $courseid));
    }
}


/**
 * Delete the course bookmarks.
 * @param int courseid
 */
function delete_module_bookmarks($module) {
    global $DB;

    if ($DB->record_exists('learningtools_bookmarks', array('coursemodule' => $module))) {
        $DB->delete_records('learningtools_bookmarks', array('coursemodule' => $module));
    }
}

/**
 * Delete the course notes.
 * @param int courseid
 */
function delete_module_notes($module) {
    global $DB;

    if ($DB->record_exists('learningtools_note', array('coursemodule' => $module))) {
        $DB->delete_records('learningtools_note', array('coursemodule' => $module));
    }
}


