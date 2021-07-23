<?php 

require_once(dirname(__FILE__) . '/../../../../config.php');
require_login();
require_sesskey();
$context = context_system::instance();
$user = optional_param('user', 0, PARAM_INT);
$course = optional_param('course', 0, PARAM_INT);
$contextlevel = optional_param('contextlevel', 0, PARAM_TEXT);
$contextid = optional_param('contextid', 0, PARAM_TEXT);
$pagetype = optional_param('pagetype', '', PARAM_TEXT);
$bookmarkurl = optional_param('pageurl', '', PARAM_TEXT);

if ($user && $course && $contextlevel && $contextid && $bookmarkurl) {
    
    $record = new stdClass;
    $record->user = $user;
    $record->course = $course;
    $record->contextlevel = $contextlevel;
    $record->contextid = $contextid;
    $record->pagetype = $pagetype;
    $record->pageurl = $bookmarkurl;
    $record->timecreated = time();
    $DB->insert_record('learningtools_bookmarks', $record);

    // add event to user create the bookmark
    $event = \ltool_bookmarks\event\ltbookmarks_created::create([
        'context' => $context,
        'other' => [
            'courseid' => $course,
            'pagetype' => $pagetype,
        ]
    ]);
    
    $event->trigger();

    redirect($bookmarkurl, get_string('successbookmarkmessage', 'local_learningtools'), 100, \core\output\notification::NOTIFY_SUCCESS);
}

