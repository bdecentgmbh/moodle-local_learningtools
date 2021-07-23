<?php 

require_once(dirname(__FILE__).'/../../../../config.php');
require_login();

//require_once($CFG->dirroot.'/admin/tool/ltnote/lib.php');
require_once(dirname(__FILE__).'/lib.php');

$context = context_system::instance();
$title = get_string('note', 'local_learningtools');
$PAGE->set_context($context);
$PAGE->set_url('/local/learningtools/ltool/note/ltnote_editlist.php');
$PAGE->set_title($title);
$PAGE->set_heading($SITE->fullname);
$edit = optional_param('edit', 0, PARAM_INT);
$courseid = optional_param('courseid', 0, PARAM_INT);

$urlparams = [];
if ($courseid) {
    $urlparams['courseid'] = $courseid;
    $coursecontext = context_course::instance($courseid);
    require_capability('ltool/note:managenote', $coursecontext);
} else {
    require_capability('ltool/note:manageownnote', $context);
}
$returnurl = new moodle_url('/local/learningtools/ltool/note/ltnote_list.php', $urlparams);

// If user is logged in, then use profile navigation in breadcrumbs.
if ($profilenode = $PAGE->settingsnav->find('myprofile', null)) {
    $profilenode->make_active();
}
$PAGE->navbar->add($title);

// edit action in note
if($edit && confirm_sesskey()) {

    $params['id'] = $edit;
    $params['courseid'] = $courseid;
    $editorform = new edit_noteinfo(null, $params);
    if ($editorform->is_cancelled()) {
        redirect($returnurl);
    } else if($fromdata = $editorform->get_data())  {
        $usernote = $fromdata->noteeditor['text'];
        $exitnote = $DB->get_record('learningtools_note', array('id' => $edit));
        if ($usernote != $exitnote->note) {
                $DB->set_field('learningtools_note', 'note', $usernote, array('id' => $edit));
                $DB->set_field('learningtools_note', 'timemodified', time(), array('id' => $edit));
                // add event to user edit the note
                $event = \ltool_note\event\ltnote_edited::create([
                    'context' => $context,
                ]);
                $event->trigger();
                redirect($returnurl, get_string('successeditnote', 'local_learningtools'), null, \core\output\notification::NOTIFY_SUCCESS);
        }
        redirect($returnurl);
    }  else {

        echo $OUTPUT->header();
        echo $OUTPUT->heading(get_string('editnote', 'local_learningtools'));
        $editorform->display();
    }
}

echo $OUTPUT->footer();