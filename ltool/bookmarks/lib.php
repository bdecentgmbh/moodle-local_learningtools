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
 * ltool plugin "Learning Tools Bookmarks" - library file
 *
 * @package    ltool_bookmarks
 * @copyright  2021 lmsace
 */

use core_user\output\myprofile\tree;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot. '/local/learningtools/lib.php');

function ltool_bookmarks_myprofile_navigation(tree $tree, $user, $iscurrentuser, $course) {
	 global $PAGE, $USER;
    //  exit;
    $context = context_system::instance();
    if (is_bookmarks_status()) {
        if ($iscurrentuser) {
            if (!empty($course)) {

                $coursecontext = context_course::instance($course->id);
                if (has_capability('ltool/bookmarks:viewbookmarks', $coursecontext)) {
                    $bookmarksurl = new moodle_url('/local/learningtools/ltool/bookmarks/bookmarksstudents.php', array('courseid' =>  $course->id)); 
                    $bookmarksnode = new core_user\output\myprofile\node('learningtools', 'bookmarks',
                    get_string('bookmarks', 'local_learningtools'), null, $bookmarksurl);
                    $tree->add_node($bookmarksnode);
                }

            } else {
                if (has_capability('ltool/bookmarks:viewownbookmarks', $context)) {
                    $bookmarksurl = new moodle_url('/local/learningtools/ltool/bookmarks/ltbookmarks_list.php');
                    $bookmarksnode = new core_user\output\myprofile\node('learningtools', 'bookmarks',
                        get_string('bookmarks', 'local_learningtools'), null, $bookmarksurl);
                    $tree->add_node($bookmarksnode);
                }
            }
        } else {
            if (is_parentforchild($user->id, 'ltool/bookmarks:viewbookmarks')) {
                $params = ['userid' => $user->id];
                if (!empty($course)) {
                    $params['selectcourse'] = $course->id;
                }

                $bookmarksurl = new moodle_url('/local/learningtools/ltool/bookmarks/ltbookmarks_list.php', $params); 
                $bookmarksnode = new core_user\output\myprofile\node('learningtools', 'bookmarks',
                get_string('bookmarks', 'local_learningtools'), null, $bookmarksurl);
                $tree->add_node($bookmarksnode);
                // exit;
                return true;
            }
        }
    }
    return true;
}




