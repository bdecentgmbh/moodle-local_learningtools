<?php

require_once('../../config.php');
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
$PAGE->set_url(new moodle_url('/local/learningtools/test.php'));

$usercontext = context_user::instance($USER->id);
echo has_capability('ltool/bookmarks:viewchildbookmarks', $usercontext);
 