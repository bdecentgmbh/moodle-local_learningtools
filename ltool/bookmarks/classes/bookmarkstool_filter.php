<?php
namespace ltool_bookmarks;
use moodle_url;
use context_user;

require_once($CFG->dirroot.'/local/learningtools/lib.php');
class bookmarkstool_filter {

	public function __construct($userid) {
		$this->userid = $userid;
	}

	public function get_course_selector($selectcourse, $usercondition, $userparams, $childuserid=0) {
		global $DB, $OUTPUT;

		$template = [];
		$courses = [];
		$urlparams = [];

		//$records = $DB->get_records('learningtools_bookmarks', array('user' => $this->userid));
		$records = $DB->get_records_sql("SELECT * FROM {learningtools_bookmarks} WHERE $usercondition", $userparams);

		if (!empty($records)) {
			foreach($records as $record) {
				$instanceblock = check_instanceof_block($record);
				if (isset($instanceblock->instance) && $instanceblock->instance == 'course' || $instanceblock->instance == 'mod') {
					$courses[] = $instanceblock->courseid;
				}
			}
		}

		$courses = get_courses_name(array_unique($courses), '/local/learningtools/ltool/bookmarks/ltbookmarks_list.php', $selectcourse, $childuserid);
		$template['courses'] = $courses;
		$template['coursefilter'] = true;		
		if ($childuserid) {
			$urlparams = ['userid' => $childuserid];
		}
		
		$pageurl = new moodle_url('/local/learningtools/ltool/bookmarks/ltbookmarks_list.php', $urlparams);
		$template['pageurl'] = $pageurl->out(false);

		return $OUTPUT->render_from_template('ltool_bookmarks/ltbookmarks', $template);
	}

	public function get_parent_child_selector($child) {
		global $OUTPUT, $USER;
		$usercontext = context_user::instance($USER->id);
		$childusers = get_childuser_info();

		if (!empty($childusers) && has_capability('ltool/bookmarks:viewchildbookmarks', $usercontext)) {
			$template['parentfilter'] = true;
			$pageurl = new moodle_url('/local/learningtools/ltool/bookmarks/ltbookmarks_list.php');
			$template['pageurl'] = $pageurl->out(false);
			$childurl = new moodle_url('/local/learningtools/ltool/bookmarks/ltbookmarks_list.php', array('child' => true));
			$template['childurl'] = $childurl->out(false);
			$template['childselect'] = !empty($child) ? "selected" : '';
			return $OUTPUT->render_from_template('ltool_bookmarks/ltbookmarks', $template);
		}
	}

	public function get_sort_instance() {

		global $OUTPUT;
		$template = [];

		$coursesort = new moodle_url('/local/learningtools/ltool/bookmarks/ltbookmarks_list.php', array('sort' => 'course'));
		$datesort = new moodle_url('/local/learningtools/ltool/bookmarks/ltbookmarks_list.php', array('sort' => 'date'));
		$template['coursesort'] = $coursesort->out(false);
		$template['datesort'] = $datesort->out(false);
		$template['sortfilter'] = true;
		return $OUTPUT->render_from_template('ltool_bookmarks/ltbookmarks', $template);
	}
}