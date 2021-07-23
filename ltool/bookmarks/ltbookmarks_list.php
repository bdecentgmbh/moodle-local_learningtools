<?php

require_once(dirname(__FILE__).'/../../../../config.php');
require_login();
$context = context_system::instance();

$title = get_string('bookmarks', 'local_learningtools');
$PAGE->set_context($context);
$PAGE->set_url('/local/learningtools/ltool/bookmarks/ltbookmarks_list.php');
$PAGE->set_title($title);
$PAGE->set_heading($SITE->fullname);
$selectcourse = optional_param('selectcourse', 0, PARAM_INT);
$sort = optional_param('sort', '', PARAM_TEXT);
$delete = optional_param('delete', 0, PARAM_INT);
$confirm = optional_param('confirm', '', PARAM_ALPHANUM); 
$page = optional_param('page', 0, PARAM_INT);
$perpage = optional_param('perpage', 10, PARAM_INT);
$courseid = optional_param('courseid', 0, PARAM_INT);
$childid = optional_param('userid', 0, PARAM_INT);

// If user is logged in, then use profile navigation in breadcrumbs.
if ($profilenode = $PAGE->settingsnav->find('myprofile', null)) {
    $profilenode->make_active();
}

$PAGE->navbar->add($title);
$urlparams = [];

if ($courseid) {
    $selectcourse = $courseid;
}

if ($courseid && !$childid) {
    $urlparams['courseid'] = $courseid;
    $coursecontext = context_course::instance($courseid);
    require_capability('ltool/bookmarks:viewbookmarks', $coursecontext);  
} else if ($childid) {
    $usercontext = context_user::instance($childid);
    require_capability('ltool/bookmarks:viewbookmarks', $usercontext, $USER->id);  
}else {
    require_capability('ltool/bookmarks:viewownbookmarks', $context);
}

if ($selectcourse) {
    $urlparams['selectcourse'] = $selectcourse;
}

$baseurl = new moodle_url('/local/learningtools/ltool/bookmarks/ltbookmarks_list.php', $urlparams);

if ($delete && confirm_sesskey()) {

	if ($confirm != md5($delete)) {
       
        echo $OUTPUT->header();
        echo $OUTPUT->heading(get_string('deletemessage', 'local_learningtools'));
        $optionsyes = array('delete'=>$delete, 'confirm'=>md5($delete), 'sesskey'=>sesskey());

        if ($selectcourse) {
            $optionsyes['selectcourse'] = $selectcourse;
        }
        if ($courseid) {
            $optionsyes['courseid'] = $courseid;
        }

        $deleteurl = new moodle_url('/local/learningtools/ltool/bookmarks/ltbookmarks_list.php', $optionsyes);
        $deletebutton = new single_button($deleteurl, get_string('delete'), 'post');

        echo $OUTPUT->confirm(get_string('deletemsgcheckfullbookmarks','local_learningtools'), $deletebutton, $baseurl);
        echo $OUTPUT->footer();
        die;
    } else if (data_submitted()) {

        if ($DB->delete_records('learningtools_bookmarks',['id' => $delete])) {
            // add event to user delete the bookmark
            $event = \ltool_bookmarks\event\ltbookmarks_deleted::create([
                'context' => $context,
            ]);
            $event->trigger();
            \core\session\manager::gc(); // Remove stale sessions.
            redirect($baseurl, get_string('successdeletemessage', 'local_learningtools'), null, \core\output\notification::NOTIFY_SUCCESS);
        } else {
            \core\session\manager::gc(); // Remove stale sessions.
            redirect($baseurl, get_string('deletednotmessage', 'local_learningtools'), null, \core\output\notification::NOTIFY_ERROR);
        }
    }
}
$coursetitle = get_string('coursebookmarks', 'local_learningtools');
$heading = empty($selectcourse) ? $title : $coursetitle;
echo $OUTPUT->header();
echo $OUTPUT->heading($heading);



if (file_exists($CFG->dirroot.'/local/learningtools/lib.php')) {
    
    $blockinstance = new \ltool_bookmarks\bookmarkstool_filter($USER->id);

    $sqlconditions = '';
    $sqlparams = [];

    if (!empty($courseid) && !$childid)  {
        $students = get_students_incourse($courseid);
        if (!empty($students)) {
            list($studentcondition, $sqlparams) = $DB->get_in_or_equal($students, SQL_PARAMS_NAMED);
            $sqlconditions .= 'user '. $studentcondition;
        }
    } elseif ($childid) {
        $sqlconditions .= 'user = :childid';
        $sqlparams['childid'] = $childid;
        /* // $childusers = get_childuser_info();
        if (!empty($childusers)) {
            // list($childuserscondition, $sqlparams) = $DB->get_in_or_equal($childusers);
            // $sqlconditions .= 'user '. $childuserscondition;
        } */
    } else {
        $sqlconditions .= 'user = :userid';
        $sqlparams['userid'] = $USER->id;
    }
    if (!$courseid) {
         echo $blockinstance->get_course_selector($selectcourse, $sqlconditions, $sqlparams, $childid);
        // echo $blockinstance->get_parent_child_selector($child);
     }

    if ($selectcourse) {
    	$courseconditions = ' AND course = :courseid';
    	$courseparams['courseid'] = $selectcourse;
    	$sqlconditions .= $courseconditions;
    	$sqlparams = array_merge($sqlparams, $courseparams);
    }


    $table = new \ltool_bookmarks\bookmarkstool_table('datatable-bookmarktool', $courseid, $childid);
    $dbfields = 'id, user, course, contextlevel, contextid,pagetype, pageurl, timecreated';
    $table->set_sql($dbfields,'{learningtools_bookmarks}', $sqlconditions, $sqlparams);
    $table->define_baseurl($baseurl);
    $table->out(10, true);
}

echo $OUTPUT->footer();

// add event to user view the bookmark
$event = \ltool_bookmarks\event\ltbookmarks_viewed::create([
    'context' => $context,
]);

$event->trigger();