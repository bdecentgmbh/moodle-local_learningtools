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
    global $PAGE, $CFG;
    $context = context_system::instance();
    $ltoolsjs = array();
    // Content of fab button html.
    $fabbuttonhtml = json_encode(local_learningtools_get_learningtools_info());
    $ltoolsjs['disappertimenotify'] = get_config('local_learningtools', 'notificationdisapper');
    $PAGE->requires->data_for_js('ltools', $ltoolsjs);
    $PAGE->requires->data_for_js('fabbuttonhtml', $fabbuttonhtml, true);
    $loggedin = false;
    if (isloggedin() && !isguestuser()) {
        $loggedin = true;
    }
    $viewcapability = array('loggedin' => $loggedin);
    $PAGE->requires->js_call_amd('local_learningtools/learningtoolsinfo', 'init', $viewcapability);
    // List of subplugins.
    // Load available subplugins javascript.
    $subplugins = local_learningtools_get_subplugins();
    foreach ($subplugins as $shortname => $plugin) {
        if (method_exists($plugin, 'load_js')) {
            $plugin->load_js();
        }
        // Required load tools function.
        if (method_exists($plugin, 'required_load_data')) {
            $plugin->required_load_data();
        }
    }
}

/**
 * Get the type of instance.
 * @param object $record list of the page info.
 * @return object instance object.
 */
function local_learningtools_check_instanceof_block($record) {

    $data = new stdClass;
    if ($record->contextlevel == CONTEXT_SYSTEM) { // System level.
        $data->instance = 'system';
    } else if ($record->contextlevel == CONTEXT_USER) { // User level.
        $data->instance = 'user';
    } else if ($record->contextlevel == CONTEXT_COURSE) {  // Course level.
        $data->instance = 'course';
        $data->courseid = $record->course;
        $data->contextid = $record->contextid;

    } else if ($record->contextlevel == CONTEXT_MODULE) { // Mod level.
        $data->instance = 'mod';
        $data->courseid = $record->course;
        $data->contextid = $record->contextid;
        $data->coursemodule = local_learningtools_get_coursemodule_id($record);

    } else if ($record->contextlevel == CONTEXT_BLOCK) { // Context blocklevel.
        $data->instance = 'block';
    } else {
        $data->instance = '';
    }
    return $data;
}

/**
 * Get the course module id.
 * @param int $contextid context id
 * @param int $contextlevel context level
 * @return int course module id
 */
function local_learningtools_get_moduleid($contextid, $contextlevel) {
    $coursemodule = 0;
    if ($contextlevel == CONTEXT_MODULE) {
        $record = new stdClass;
        $record->contextid = $contextid;
        $record->contextlevel = $contextlevel;
        $coursemodule = local_learningtools_get_coursemodule_id($record);
    }
    return $coursemodule;
}

/**
 * Get the course module Id.
 * @param stdclass $record list of the page info.
 * @return int course module id.
 */
function local_learningtools_get_coursemodule_id($record) {
    global $DB;

    $contextinfo = $DB->get_record('context', array('id' => $record->contextid, 'contextlevel' => $record->contextlevel));
    return isset($contextinfo->instanceid) ? $contextinfo->instanceid : 0;
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
function local_learningtools_get_courses_name($courses, $url = '', $selectcourse = 0, $userid= 0, $usercourseid = 0) {
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
function local_learningtools_get_course_name($courseid) {

    $course = get_course($courseid);
    $course = new core_course_list_element($course);
    return $course->get_formatted_name();
}

/**
 * Get the course category name.
 * @param int $courseid course id.
 * @return string category name.
 */
function local_learningtools_get_course_categoryname($courseid) {

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
function local_learningtools_get_module_name($data, $mod = false) {
    global $DB;
    $coursemoduleinfo = $DB->get_record('course_modules', array('id' => $data->coursemodule));
    if (empty($coursemoduleinfo)) {
        return "";
    }
    $moduleinfo = $DB->get_record('modules', array('id' => $coursemoduleinfo->module));
    if ($moduleinfo) {
        if ($mod) {
            return $moduleinfo->name;
        }
        // Get module instance name.
        $report = get_coursemodule_from_instance($moduleinfo->name, $coursemoduleinfo->instance, $data->courseid);
        return $report->name;
    }
    return "";
}


/**
 * Get the course module current section.
 * @param int $courseid course id
 * @param int $modid coursemodule id
 * @return string|bool section name.
 */
function local_learningtools_get_mod_section($courseid, $modid) {
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
    global $DB, $PAGE, $SITE;
    $learningtools = $DB->get_records('local_learningtools_products', array('status' => 1), 'sort');
    if (!empty($learningtools)) {
        foreach ($learningtools as $tool) {
            $plugin = 'ltool_'.$tool->shortname;
            $classname = "\\$plugin\\$tool->shortname";
            if (class_exists($classname)) {
                $plugins[$tool->shortname] = new $classname();
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
function local_learningtools_get_learningtools_info() {
    global $PAGE, $SITE, $USER;
    $content = '';
    // Visiblity of learningtools.
    $fabvisiablestatus = get_config('local_learningtools', 'fabbuttonvisible');
    if ($fabvisiablestatus == 'allcourses') {
        if (empty($PAGE->course->id) || ($PAGE->course->id == $SITE->id)) {
            return '';
        }
    } else if ($fabvisiablestatus == 'specificcate') {
        if (isset($PAGE->course->category) && !empty($PAGE->course->category)) {
            $visiblecategories = explode(",", get_config('local_learningtools', 'visiblecategories'));
            if (!in_array($PAGE->course->category, $visiblecategories)) {
                return '';
            }
        } else {
            return '';
        }
    }

    // Disable of activities.
    $disablemodstatus = get_config('local_learningtools', 'disablemod');
    if ($disablemodstatus != 0) {
        if (isset($PAGE->cm->module) && !empty($PAGE->cm->module)) {
            $visiblemods = explode(",", get_config('local_learningtools', 'disablemod'));
            if (in_array($PAGE->cm->module, $visiblemods)) {
                return '';
            }
        }
    }
    $contentinner = '';
    // Get list of ltool sub plugins.
    $subplugins = local_learningtools_get_subplugins();
    $context = context_system::instance();
    $stickytools = '';
    if (!empty($subplugins)) {
        foreach ($subplugins as $shortname => $toolobj) {
            $capability = 'ltool/'.$toolobj->shortname.':create'. $toolobj->shortname;
            if ($toolobj->contextlevel == 'system') {
                if (has_capability($capability, $context)) {
                    if (get_config('ltool_' . $toolobj->shortname, 'sticky')) {
                        $stickytools .= $toolobj->render_template();
                    } else if (get_config('local_learningtools', 'showactive')) {
                        if (method_exists($toolobj, 'tool_active_condition')) {
                            if ($toolobj->tool_active_condition()) {
                                $stickytools .= $toolobj->tool_active_condition();
                            } else {
                                $contentinner .= $toolobj->render_template();
                            }
                        }
                    } else {
                        $contentinner .= $toolobj->render_template();
                    }
                }
            } else {
                if (get_config('ltool_' . $toolobj->shortname, 'sticky')) {
                    $stickytools .= $toolobj->render_template();
                } else if (get_config('local_learningtools', 'showactive')) {
                    $activetool = $toolobj->render_template();
                    if (method_exists($toolobj, 'tool_active_condition')) {
                        if ($toolobj->tool_active_condition()) {
                            $stickytools .= $toolobj->tool_active_condition();
                            $activetool = '';
                        }
                    }
                    $contentinner .= $activetool;
                } else {
                    $contentinner .= $toolobj->render_template();
                }
            }
        }
    }
    $stickytoolstatus = local_learningtools_get_stickytool_status();
    $stickyclass = '';
    $fabiconclass = "fa fa-magic";
    if ($stickytoolstatus && !empty($stickytools)) {
        $fabiconclass = "fa fa-angle-double-up";
        $stickyclass = 'sticky-tool';
    }
    $fabbackiconcolor = get_config('local_learningtools', 'fabiconbackcolor');
    $fabiconcolor = get_config('local_learningtools', 'fabiconcolor');
    $content .= html_writer::start_tag('div', array('class' => 'learningtools-action-info'));
    $content .= html_writer::start_tag('div', array('class' => "floating-button $stickyclass"));
    $content .= html_writer::start_tag('div', array('class' => 'list-learningtools'));
    $content .= $contentinner;
    $content .= html_writer::end_tag('div');
            $content .= html_writer::start_tag('button', array("class" => "btn btn-primary",
            'id' => 'tool-action-button', 'style' => "background:$fabbackiconcolor;") );
    $content .= html_writer::start_tag('i', array('class' => $fabiconclass, 'style' => "color:$fabiconcolor;"));
    $content .= html_writer::end_tag('i');
    $content .= html_writer::end_tag("button");
        $content .= html_writer::start_tag('div', array('class' => 'sticky-tools-list'));
        $content .= $stickytools;
        $content .= html_writer::end_tag('div');
    $content .= html_writer::end_tag('div');
    $content .= html_writer::end_tag('div');
    return $content;
}

/**
 * Get sticky tools.
 * @return void
 */
function local_learningtools_get_stickytool_status() {
    $subplugins = local_learningtools_get_subplugins();
    $stickystatus = false;
    if (!empty($subplugins)) {
        foreach ($subplugins as $shortname => $toolobj) {
            $capability = 'ltool/'.$toolobj->shortname.':create'. $toolobj->shortname;
            if ($toolobj->contextlevel == 'system') {
                if (has_capability($capability, context_system::instance())) {
                    if (get_config('ltool_' . $toolobj->shortname, 'sticky') ||
                        get_config('local_learningtools', 'showactive')) {
                        $stickystatus = true;
                    }
                }
            } else {
                if (get_config('ltool_' . $toolobj->shortname, 'sticky') ||
                    get_config('local_learningtools', 'showactive')) {
                    $stickystatus = true;
                }
            }
        }
    }
    return $stickystatus;
}


/**
 * Get the students in course.
 * @param int $courseid course id
 * @return array students user ids.
 */
function local_learningtools_get_students_incourse($courseid) {
    global $DB;
    $coursecontext = context_course::instance($courseid);
    $studentids = array_keys(get_enrolled_users($coursecontext, 'local/learningtools:studentcontroller'));
    return $studentids;
}

/**
 * Find the logged in user is assigned into any relative roles to the shared user.
 * @param int $childuserid userid
 * @param string $capability
 * @return object|bool
 */
function local_learningtools_is_parentforchild(int $childuserid, string $capability='') {
    global $USER;
    $usercontext = \context_user::instance($childuserid); // USER - child id.
    $usercontextroles = get_user_roles($usercontext, $USER->id); // Loggedin - parent.
    if (!empty($capability)) {
        return local_learningtools_has_viewtool_capability_role($usercontextroles, $capability);
    }
    return (!empty($usercontextroles)) ? $usercontextroles : false;
}

/**
 * Check the tool capability for parents.
 * @param array $assignedroles list of the roles.
 * @param string $capability acces capability.
 * @return bool stauts
 */
function local_learningtools_has_viewtool_capability_role($assignedroles, string $capability) {
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
function local_learningtools_get_instance_tool_view_url($row) {
    global $OUTPUT;
    $data = local_learningtools_check_instanceof_block($row);
    if (!isset($data->instance)) {
        return '';
    }
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
function local_learningtools_get_eventlevel_courseid($context, $courseid) {
    $course = 0;
    if ($context->contextlevel == CONTEXT_COURSE || $context->contextlevel == CONTEXT_MODULE) {
        return $courseid;
    } else {
        return $course;
    }
}

/**
 * Add the learningtools in db.
 * @param string $plugin plugin name
 * @return void
 */
function local_learningtools_add_learningtools_plugin($plugin) {
    global $DB;
    $strpluginname = get_string('pluginname', 'ltool_' . $plugin);
    if (!$DB->record_exists('local_learningtools_products', array('shortname' => $plugin)) ) {
        $existrecords = $DB->get_records('local_learningtools_products', null);
        $maxrecord = $DB->get_record_sql('SELECT MAX(sort) AS value FROM {local_learningtools_products}', null);
        $sortval = !empty($existrecords) ? $maxrecord->value + 1 : 1;
        $record = new stdClass;
        $record->shortname = $plugin;
        $record->name = $strpluginname;
        $record->status = 1;
        $record->sort = $sortval;
        $record->timecreated = time();
        $DB->insert_record('local_learningtools_products', $record);
    }
}

/**
 * Remove the learningtools in db.
 * @param string $plugin ltool plugin shortname
 * @return void
 */
function local_learningtools_delete_ltool_table($plugin) {
    global $DB;
    if ($DB->record_exists('local_learningtools_products', array('shortname' => $plugin)) ) {
        $DB->delete_records('local_learningtools_products', array('shortname' => $plugin));
    }
}

/**
 * Clean the assignment page userlist id.
 * @param string $pageurl pageurl
 * @param object $cm course module id.
 * @return string pageurl
 */
function local_learningtools_clean_mod_assign_userlistid($pageurl, $cm) {
    if (!empty($cm->id)) {
        $data = new stdClass;
        $data->coursemodule = $cm->id;
        $modname = local_learningtools_get_module_name($data, true);
        if ($modname == 'assign') {
            $parsed = parse_url($pageurl);
            if (isset($parsed['query'])) {
                $query = $parsed['query'];
                parse_str($query, $params);
                unset($params['useridlistid']);
            };
            $url = $parsed['scheme'] . "://" . $parsed['host'] . $parsed['path'];
            $urlparams = isset($parsed['query']) ? '?' . http_build_query($params, '', '&') : '';
            $url = $url . $urlparams;
            return $url;
        } else {
            return $pageurl;
        }
    } else {
        return $pageurl;
    }
}

/**
 * Is the event visible?
 *
 * This is used to determine global visibility of an event in all places throughout Moodle.
 *
 * @param calendar_event $event
 * @param int $requestinguserid User id to use for all capability checks, etc. Set to 0 for current user (default).
 * @return bool Returns true if the event is visible to the current user, false otherwise.
 */
function local_learningtools_core_calendar_is_event_visible($event, $requestinguserid) {
    global $DB;
    $userid = $DB->get_field('event', 'userid', ['id' => $event->id]);
    return ($userid == $requestinguserid) ? true : false;
}

/**
 * Can access the Course tool for current page.
 *
 * @return void
 */
function local_learningtools_can_visible_tool_incourse() {
    global $SITE, $PAGE, $USER;
    $access = false;
    if (!empty($PAGE->course->id) && $PAGE->course->id != $SITE->id) {
        if (can_access_course($PAGE->course, $USER, '', true)) {
            $access = true;
        }
    }
    return $access;
}
