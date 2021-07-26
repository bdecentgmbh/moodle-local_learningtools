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
 * Local plugin "Learning Tools" - lib file
 *
 * @package    local_learningtools
 * @copyright  2021 lmsace
 */

use core_user\output\myprofile\tree;

defined('MOODLE_INTERNAL') || die();

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

function local_learningtools_show_tools() {
    global $DB;
    $learningtools = $DB->get_records('learningtools_products', null, 'sort');
}

function local_learningtools_extend_settings_navigation($settingnav, $context) {
    global $PAGE, $CFG, $USER, $COURSE;
    $context = context_system::instance();
    $PAGE->requires->jquery();
    $fabbuttonhtml = get_learningtools_info();
    $PAGE->requires->data_for_js('fabbuttonhtml', $fabbuttonhtml);
    $disappertimenotify = get_config('local_learningtools', 'notificationdisapper');
    $PAGE->requires->data_for_js('disappertimenotify', $disappertimenotify);
    //$PAGE->requires->js(new moodle_url('/local/learningtools/javascript/learningtools.js'));
    $viewbookmarks = false;
    if (has_capability('ltool/bookmarks:viewownbookmarks', $context) && is_bookmarks_status()) {
        $viewbookmarks = true;
    }

    $viewnote = false;
    if (has_capability('ltool/note:viewownnote', $context) && is_note_status()) {
        $viewnote = true;
    }

    $viewcapability = array('viewbookmarks' => $viewbookmarks, 'viewnote' => $viewnote);
    /* print_object($viewcapability);
     exit;*/
     
    $PAGE->requires->js_call_amd('local_learningtools/learningtools', 'init', $viewcapability);

    if (file_exists($CFG->dirroot.'/local/learningtools/ltool/note/lib.php')) {
        require_once($CFG->dirroot.'/local/learningtools/ltool/note/lib.php');

        $params['course'] = $COURSE->id;
        $params['contextlevel'] = $PAGE->context->contextlevel;
        $params['pagetype'] = $PAGE->pagetype;
        $params['pageurl'] = $PAGE->url->out(false);
        $params['user'] = $USER->id;
        $params['contextid'] = $PAGE->context->id;
        $PAGE->requires->js_call_amd('ltool_note/learningnote', 'init', array($PAGE->context->id, $params));
    }    
}

function get_coursemodule($params) {

    $coursemodule = 0;
    if ($params['contextlevel'] == 70) {
        $record = new stdclass;
        $record->contextid = $params['contextid'];
        $record->contextlevel = $params['contextlevel'];
        $coursemodule = get_coursemodule_id();
    }

    return $coursemodule;
}


function check_instanceof_block($record) {

        $data = new stdClass;
        if ($record->contextlevel == 10) { // system level
            $data->instance = 'system';
        } else if($record->contextlevel == 30) { // user level
            $data->instance = 'user';
        } else if($record->contextlevel == 50) {  // course level
            $data->instance = 'course';
            $data->courseid = $record->course;
            $data->contextid = $record->contextid;

        } else if($record->contextlevel == 70) { // mod level
            $data->instance = 'mod';
            $data->courseid = $record->course;
            $data->contextid = $record->contextid;
            $data->coursemodule = get_coursemodule_id($record);

        } else if($record->contextlevel == 80) { // context blocklevel
            $data->instance = 'block';
        }
        return $data;
}

function get_coursemodule_id($record) {
    global $DB;

    $contextinfo = $DB->get_record('context', array('id' => $record->contextid, 'contextlevel' => $record->contextlevel));
    return $contextinfo->instanceid;
}

function get_courses_name($courses, $url = '', $selectcourse = 0, $userid=0, $usercourseid = 0) {
    
    $courseids = [];
    $courseinfo = [];
    $courseids = $courses;
    if (!empty($courseids)) {
        foreach($courseids as $courseid) {
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

function get_course_name($courseid) {

    $course = get_course($courseid);
    $course = new core_course_list_element($course);
    return $course->get_formatted_name();
}   

function get_course_categoryname($courseid) {

    $course = get_course($courseid);
    $category = \core_course_category::get($course->category);
    return $category->get_formatted_name();
}

function get_module_name($data, $mod = false) {
    global $DB;
    $coursemoduleinfo = $DB->get_record('course_modules', array('id' => $data->coursemodule));
    $moduleinfo = $DB->get_record('modules', array('id' => $coursemoduleinfo->module));
    if ($mod) {
        return $moduleinfo->name;
    }
    $report = get_coursemodule_from_instance($moduleinfo->name, $coursemoduleinfo->instance, $data->courseid);
    return $report->name;
}

function get_module_coursesection($data, $type = 'bookmark') {
    $coursename = get_course_name($data->courseid);
    $section = get_mod_section($data->courseid, $data->coursemodule);
    if ($type == 'note') {
        $modulename = get_module_name($data);
        return $coursename.' / '. $section. ' / '. $modulename;
    }
    return $coursename.' / '. $section;
}

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
        foreach($sectionmod as $key => $value) {
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
 * display fab button info 
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
                if (file_exists("$location/$tool->shortname.php")) {
                    include_once("$location/$tool->shortname.php");
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
    $content .= html_writer::start_tag('i', array('class' => 'fa fa-gear'));
    $content .= html_writer::end_tag('i');
    $content .= html_writer::end_tag("button");
    $content .= html_writer::end_tag('div');
    return $content;
}  

/**
 * get bookmarks content
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
    $data['contextlevel'] = $PAGE->context->contextlevel;
    $data['contextid'] = $PAGE->context->id;
    $data['sesskey'] = sesskey();
    $data['ltbookmark'] = true;
    return learningtools_render_template($data);
}

/**
 * get note content
 * @param object tool info
 * @param object note plugin info
 * @return string display tool note plugin html 
 */
function get_note_info($toolobj, $tool) {
    $data = $toolobj->get_tool_info();
    $data['ltnote'] = true;
    return learningtools_render_template($data);
}

/**
 * learning tools template function 
 * @param array template content
 * @param string display html content 
 */
function learningtools_render_template($data) {
    global $OUTPUT;
    return $OUTPUT->render_from_template('local_learningtools/learningtools', $data);
}
/**
 * get students in course
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
            foreach($coursestudents as $coursestudent) {
                $users[] = $coursestudent->id;
            }
        }
    }
    return $users;
}


function get_childuser_info() {
    global $DB, $USER;
    $userids = [];
    $userfieldsapi = \core_user\fields::for_name();
    $allusernames = $userfieldsapi->get_sql('u', false, '', '', false)->selects;
    $usercontexts = $DB->get_records_sql("SELECT c.instanceid, c.instanceid, $allusernames
                                                    FROM {role_assignments} ra, {context} c, {user} u
                                                   WHERE ra.userid = ?
                                                         AND ra.contextid = c.id
                                                         AND c.instanceid = u.id
                                                         AND c.contextlevel = ".CONTEXT_USER, array($USER->id));
    if (!empty($usercontexts)) {
        foreach($usercontexts as $usercontext) {
            $userids[] = $usercontext->instanceid;
        }
    }
    return $userids;
}


/**
 * Find the logged in user is assigned into any relative roles to the shared user.
 *
 * @param  mixed $childuserid
 * @return object|bool 
 */
function is_parentforchild(int $childuserid, string $capability='') {
    global $USER;
    $usercontext = \context_user::instance($childuserid); // USER - child id
    $usercontextroles = get_user_roles($usercontext, $USER->id); // Loggedin - parent.
    if (!empty($capability)) {
        return has_viewtool_capability_role($usercontextroles, $capability);
    }
    return (!empty($usercontextroles)) ? $usercontextroles  : false;
}

function has_viewtool_capability_role($assignedroles, string $capability) {
    $roles = [];
    if (empty($assignedroles)) {
        return false;
    }
    foreach ($assignedroles as $assignid => $role) {
        $roles[] = $role->roleid;
    }
    $roleshascaps =  get_roles_with_capability($capability);
    $result = array_intersect($roles, array_keys($roleshascaps));
    return !empty($result) ? true : false;
}


/**
 * Check the bookmarks status
 * @return bool 
 */
function is_bookmarks_status() {
    global $DB;
    $bookmarksrecord = $DB->get_record('learningtools_products', array('shortname' => 'bookmarks'));
    if ($bookmarksrecord->status) {
        return true;
    }
    return false;
}

function require_bookmarks_status() {
    if (!is_bookmarks_status()) {
        $url = new moodle_url('/my');
        redirect($url);       
    }   
    return true;
}

/**
 * Check the note status
 * @return bool 
 */
function is_note_status() {
    global $DB;
    $noterecord = $DB->get_record('learningtools_products', array('shortname' => 'note'));
    if ($noterecord->status) {
        return true;
    }
    return false;
}


function require_note_status() {
    if (!is_note_status()) {
        $url = new moodle_url('/my');
        redirect($url);       
    }   
    return true;
}