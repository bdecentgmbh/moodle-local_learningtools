<?php
require_once(dirname(__FILE__).'/../../../../config.php');
require_once($CFG->dirroot. '/local/learningtools/ltool/note/lib.php');

require_login();
require_note_status();
$contextid = optional_param('contextid', 0, PARAM_INT);
$course = optional_param('course', 0, PARAM_INT);
$user = optional_param('user', 0, PARAM_INT);
$contextlevel = optional_param('contextlevel', 0, PARAM_INT);
$pagetype = optional_param('pagetype', '', PARAM_TEXT);
$pageurl = optional_param('pageurl', '', PARAM_RAW);
$context = context_system::instance();
$PAGE->set_context($context);
$params = [];
$params['contextid'] = $contextid;
$params['course'] = $course;
$params['user'] = $user;
$params['contextlevel'] = $contextlevel;
$params['pagetype'] = $pagetype;
$params['pageurl'] = $pageurl;
$url = new moodle_url('/local/learningtools/ltool/note/popoutaction.php');
$url->params($params);
$PAGE->set_url($url);
$PAGE->set_title(get_string('newnote', 'local_learningtools'));
$PAGE->set_heading($SITE->fullname);

sesskey();

if ($contextid && $course && $user && $contextlevel 
	&& $pagetype && $pageurl) {
	$params['popoutaction'] = true;
	$actionurl = $url->out(false);
	$mform = new editorform($actionurl, $params);
	if($mform->is_cancelled()) {
		redirect($pageurl);
	} else if($formdata = (array)$mform->get_data())  {
		user_save_notes($contextid, $formdata);
		redirect($pageurl, get_string('successnotemessage', 'local_learningtools'), null, \core\output\notification::NOTIFY_SUCCESS);
	} else {
		echo $OUTPUT->header();
		echo $OUTPUT->heading(get_string('newnote', 'local_learningtools'));
		$mform->display();
		echo $OUTPUT->footer();
	}

	//user_save_notes($contextid, $data);
}

