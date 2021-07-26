<?php

require_once(dirname(__FILE__).'/../../../../config.php');
require_once($CFG->dirroot. '/local/learningtools/lib.php');
require_login();
require_note_status();

$context = context_system::instance();
$title = get_string('note', 'local_learningtools');
$courseid = required_param('courseid', PARAM_INT);
$PAGE->set_context($context);
$PAGE->set_url('/local/learningtools/ltool/note/notestudents.php');
$PAGE->set_title($title);
$PAGE->set_heading($SITE->fullname);

// Participants table filterset.
$filterset = new \core_user\table\participants_filterset;
$filterset->add_filter(
    new \core_table\local\filter\integer_filter('courseid', \core_table\local\filter\filter::JOINTYPE_DEFAULT, [(int) $courseid])
);
// Approver user table - pariticipants table wrapper.
$participanttable = new \local_learningtools\table\courseparticipants("user-index-participants-note");
$participanttable->define_baseurl($CFG->wwwroot.'/local/learningtools/ltool/note/notestudents.php');
$participanttable->set_filterset($filterset);

echo $OUTPUT->header();
if (isset($participanttable)) {
    echo $participanttable->out(10, true);
}
echo $OUTPUT->footer();


