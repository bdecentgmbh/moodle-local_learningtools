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
 * @param object $settingnav navigation node
 * @param object $context context
 * @return navigation_node Returns the question branch that was added
 */
function local_learningtools_extend_settings_navigation($settingnav, $context) {

    global $PAGE, $USER, $COURSE;

    $context = context_system::instance();
    $ltoolsjs = array();
    // Content of fab button html.
    $fabbuttonhtml = get_learningtools_info();
    $ltoolsjs['disappertimenotify'] = get_config('local_learningtools', 'notificationdisapper');
    $PAGE->requires->data_for_js('ltools', $ltoolsjs);
    $loggedin = false;
    if (isloggedin() && !isguestuser()) {
        $loggedin = true;
    }
    $viewcapability = array('loggedin' => $loggedin, 'fabbuttonhtml' => $fabbuttonhtml);
    $PAGE->requires->js_call_amd('local_learningtools/learningtools', 'init', $viewcapability);
    // List of subplugins.
    // Load available subplugins javascript.
    $subplugins = local_learningtools_get_subplugins();
    foreach ($subplugins as $shortname => $plugin) {
        if (method_exists($plugin, 'load_js')) {
            $plugin->load_js();
        }
    }
}

/**
 * Get the type of instance.
 * @param object $record list of the page info.
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
 * @param int $contextid context id
 * @param int $contextlevel context level
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
 * @param array $record list of the page info.
 * @return int course module id.
 */
function get_coursemodule_id($record) {
    global $DB;

    $contextinfo = $DB->get_record('context', array('id' => $record->contextid, 'contextlevel' => $record->contextlevel));
    return $contextinfo->instanceid;
}
/**
 * Get the courses name.
 * @param array $courses courseids
 * @param string $url page url
 * @param int $selectcourse selected course id
 * @param int $userid user id.
 * @param int $usercourseid  course id.
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
 * @param int $courseid course id
 * @return string course name
 */
function get_course_name($courseid) {

    $course = get_course($courseid);
    $course = new core_course_list_element($course);
    return $course->get_formatted_name();
}

/**
 * Get the course category name.
 * @param int $courseid course id.
 * @return string category name.
 */
function get_course_categoryname($courseid) {

    $course = get_course($courseid);
    $category = \core_course_category::get($course->category);
    return $category->get_formatted_name();
}

/**
 * Get the course module name
 * @param object $data instance data
 * @param bool $mod return which type of name
 * @return string modulename | instance name
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
 * Get the course module current section.
 * @param int $courseid course id
 * @param int $modid coursemodule id
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
 * Get list of available sub plugins.
 *
 * @return array $plugins List of available subplugins.
 */
function local_learningtools_get_subplugins() {
    global $DB;
    $context = context_system::instance();
    $learningtools = $DB->get_records('local_learningtools_products', array('status' => 1), 'sort');
    if (!empty($learningtools)) {
        foreach ($learningtools as $tool) {
            $capability = 'ltool/'.$tool->shortname.':create'. $tool->shortname;
            if (has_capability($capability, $context)) {
                $plugin = 'ltool_'.$tool->shortname;
                $classname = "\\$plugin\\$tool->shortname";
                if (class_exists($classname)) {
                    $plugins[$tool->shortname] = new $classname();
                }
            }
        }
        return isset($plugins) ? $plugins : [];
    }
    return [];
}


/**
 * Display fab button html.
 * @return string fab button html content.
 */
function get_learningtools_info() {
    $content = '';

    $content .= html_writer::start_tag('div', array('class' => 'floating-button'));
    $content .= html_writer::start_tag('div', array('class' => 'list-learningtools'));

    // Get list of ltool sub plugins.
    $subplugins = local_learningtools_get_subplugins();
    if (!empty($subplugins)) {
        foreach ($subplugins as $shortname => $toolobj) {
            $content .= $toolobj->render_template();
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
 * Get the students in course.
 * @param int $courseid course id
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
 * @param int $childuserid userid
 * @param string $capability
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
 * @param array $assignedroles list of the roles.
 * @param string $capability acces capability.
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
 * Get the tool instance view url.
 * @param object $row list of the tool record
 * @return string view html
 */
function get_instance_tool_view_url($row) {
    global $OUTPUT;
    $data = check_instanceof_block($row);

    if ($data->instance == 'course') {
        $courseurl = new moodle_url('/course/view.php', array('id' => $data->courseid));
        $viewurl = $OUTPUT->single_button($courseurl, get_string('viewcourse', 'local_learningtools'), 'get');
    } else if ($data->instance == 'mod') {
        $viewurl = $OUTPUT->single_button($row->pageurl, get_string('viewactivity', 'local_learningtools'), 'get');
    } else {
        $viewurl = $OUTPUT->single_button($row->pageurl, get_string('viewpage', 'local_learningtools'), 'get');
    }
    return $viewurl;
}

/**
 * Get the event level course id.
 * @param object $context context object
 * @param int $courseid related course id
 * @return string view html
 */
function get_eventlevel_courseid($context, $courseid) {
    $course = 0;
    if ($context->contextlevel == 50 || $context->contextlevel == 70) {
        return $courseid;
    } else {
        return $course;
    }
}
