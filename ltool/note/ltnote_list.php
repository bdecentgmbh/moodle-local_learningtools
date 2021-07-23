<?php

require_once(dirname(__FILE__).'/../../../../config.php');
require_login();

$context = context_system::instance();

$title = get_string('note', 'local_learningtools');
$PAGE->set_context($context);
$PAGE->set_url('/local/learningtools/ltool/note/ltnote_list.php');
$PAGE->set_title($title);
$PAGE->set_heading($SITE->fullname);
$selectcourse = optional_param('selectcourse', 0, PARAM_INT);
$activity = optional_param('activity', 0, PARAM_INT);
$sort = optional_param('sort', 'date', PARAM_TEXT);
$delete = optional_param('delete', 0, PARAM_INT);
$confirm = optional_param('confirm', '', PARAM_ALPHANUM);
$courseid = optional_param('courseid', 0, PARAM_INT);
$childid = optional_param('userid', 0, PARAM_INT);

$urlparams = [];

if ($courseid && !$childid) {
    $selectcourse = $courseid;
    $coursecontext = context_course::instance($courseid);
    $urlparams['courseid'] = $courseid;
    require_capability('ltool/note:viewnote', $coursecontext);
} else if ($childid) {
    $usercontext = context_user::instance($childid);
    $urlparams['userid'] = $childid;  
    require_capability('ltool/note:viewnote', $usercontext, $USER->id);  
} else {
    require_capability('ltool/note:viewownnote', $context);
}

$pageurl = new moodle_url('/local/learningtools/ltool/note/ltnote_list.php', $urlparams);
// delete action in note

if ($delete && confirm_sesskey()) {

	if ($confirm != md5($delete)) {
       
        echo $OUTPUT->header();
        echo $OUTPUT->heading(get_string('deletemessage', 'local_learningtools'));

        $optionsyes = array('delete'=>$delete, 'confirm'=>md5($delete), 'sesskey'=>sesskey());
        if ($courseid) {
            $optionsyes['courseid'] =  $courseid;
        }
        if ($childid) {
            $optionsyes['userid'] = $childid;
        }

        $deleteurl = new moodle_url($pageurl, $optionsyes);
        $deletebutton = new single_button($deleteurl, get_string('delete'), 'post');

        echo $OUTPUT->confirm(get_string('deletemsgcheckfull','local_learningtools'), $deletebutton, $pageurl);
        echo $OUTPUT->footer();
        die;

    } else if (data_submitted()) {

        if ($DB->delete_records('learningtools_note',['id' => $delete])) {

            // add event to user delete the bookmark
            $event = \ltool_note\event\ltnote_deleted::create([
                'context' => $context,
            ]);
            $event->trigger();

            \core\session\manager::gc(); // Remove stale sessions.
            redirect($pageurl, get_string('successdeletemessage', 'local_learningtools'), null, \core\output\notification::NOTIFY_SUCCESS);
        } else {
            \core\session\manager::gc(); // Remove stale sessions.
            redirect($pageurl, get_string('deletednotmessage', 'local_learningtools'), null, \core\output\notification::NOTIFY_ERROR);
        }
    }
}

// If user is logged in, then use profile navigation in breadcrumbs.
if ($profilenode = $PAGE->settingsnav->find('myprofile', null)) {
    $profilenode->make_active();
}
$PAGE->navbar->add($title);

$heading = $title;
if ($sort == 'course') {
    $heading = get_string('coursenotes', 'local_learningtools');
}

echo $OUTPUT->header();
echo $OUTPUT->heading($heading);

if (file_exists($CFG->dirroot.'/local/learningtools/lib.php')) {

    $blockinstance = new \ltool_note\notetool_filter($USER->id);
    //echo $blockinstance->get_course_selector();
    echo $blockinstance->get_main_body($selectcourse, $sort, $activity, $courseid, $childid);
}

echo $OUTPUT->footer();

// add event to user view the note
$event = \ltool_note\event\ltnote_viewed::create([
    'context' => $context,
]);

$event->trigger();

