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
 * Bookmarks ltool lib test cases defined.
 *
 * @package   ltool_bookmarks
 * @copyright bdecent GmbH 2021
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace ltool_bookmarks;

/**
 * Bookmarks subplugin for learningtools phpunit test cases defined.
 * @runTestsInSeparateProcesses
 */
class ltool_bookmarks_test extends \advanced_testcase {


    /**
     * Summary of context
     * @var object
     */
    public $page;

    /**
     * Summary of context
     * @var object
     */
    public $context;

    /**
     * Create custom page instance and set admin user as loggedin user.
     *
     * @return void
     */
    public function setup(): void {
        global $PAGE;
        $this->resetAfterTest(true);
        $this->setAdminUser();

        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $this->context = \context_course::instance($course->id);
        $page = new \moodle_page();
        $page->set_context($this->context);
        $page->set_course($course);
        $page->set_pagelayout('standard');
        $page->set_pagetype('course-view');
        $page->set_title('Course: Course 1');
        $page->set_url(new \moodle_url('/course/view.php', ['id' => $course->id]));
        $this->page = $page;
    }

    /**
     * Case to test the external method to create/delete bookmarks.
     * @covers \ltool_bookmarks\external::save_userbookmarks
     * @runInSeparateProcess
     * @return void
     */
    public function test_external_test(): void {
        global $CFG, $DB, $USER;

        $toolobj = new \ltool_bookmarks\bookmarks();
        $tool = $DB->get_record('local_learningtools_products', ['shortname' => 'bookmarks']);
        $data = $this->get_bookmarks_info($toolobj, $tool);
        $data = json_encode($data);
        // Redirect all events. Created event must trigger when the note saved.
        $sink = $this->redirectEvents();
        $bookmarks = \ltool_bookmarks\external::save_userbookmarks($this->context->id, $data);
        $events = $sink->get_events();
        $event = reset($events);
        $exist = ltool_bookmarks_check_page_bookmarks_exist($this->context->id, $this->page->url->out(), $USER->id);
        $bookmarksmsg = get_string('successbookmarkmessage', 'local_learningtools');
        $this->assertEquals($bookmarks['bookmarksmsg'], $bookmarksmsg);
        $this->assertTrue($exist);
        $this->assertInstanceOf('\ltool_bookmarks\event\ltbookmarks_created', $event);
        $this->assertEquals($this->context, $event->get_context());
    }

    /**
     * Case to test the save function to create/delete bookmarks.
     * @covers ::ltool_bookmarks_user_save_bookmarks
     * @return void
     */
    public function test_bookmark_save(): void {
        global $CFG, $DB, $USER;

        $toolobj = new \ltool_bookmarks\bookmarks();
        $tool = $DB->get_record('local_learningtools_products', ['shortname' => 'bookmarks']);
        $data = $this->get_bookmarks_info($toolobj, $tool);

        $sink = $this->redirectEvents();
        ltool_bookmarks_user_save_bookmarks($this->context->id, $data);
        $events = $sink->get_events();
        $event = reset($events);

        $exist = ltool_bookmarks_check_page_bookmarks_exist($this->context->id, $this->page->url->out(), $USER->id);
        $this->assertTrue($exist);
        $this->assertInstanceOf('\ltool_bookmarks\event\ltbookmarks_created', $event);
        $this->assertEquals($this->context, $event->get_context());
        // Test the toggle of bookmarks. Delete the bookmark if already stored.
        $sink = $this->redirectEvents();
        ltool_bookmarks_user_save_bookmarks($this->context->id, $data);
        $events = $sink->get_events();
        $event = reset($events);

        $exist = ltool_bookmarks_check_page_bookmarks_exist($this->context->id, $this->page->url->out(), $USER->id);
        $this->assertFalse($exist);
        $this->assertInstanceOf('\ltool_bookmarks\event\ltbookmarks_deleted', $event);
        $this->assertEquals($this->context, $event->get_context());
    }

    /**
     * Generate and fetch bookmarks info.
     * @param  mixed $toolobj
     * @param  mixed $tool
     * @return void
     */
    public function get_bookmarks_info($toolobj, $tool) {
        global $CFG, $USER, $COURSE;
        $data = $toolobj->get_tool_info();
        $data['toolurl'] = "$CFG->wwwroot/local/learningtools/ltool/$tool->shortname/$tool->shortname"."_info.php";
        $data['id'] = $tool->shortname;
        $data['user'] = $USER->id;
        $data['course'] = $this->context->instanceid;
        $data['pageurl'] = $this->page->url->out(false);
        $data['pagetype'] = $this->page->pagetype;
        $data['coursemodule'] = local_learningtools_get_moduleid($this->page->context->id, $this->page->context->contextlevel);
        $data['contextlevel'] = $this->page->context->contextlevel;
        $data['contextid'] = $this->page->context->id;
        $data['sesskey'] = sesskey();
        $data['ltbookmark'] = true;
        $data['pagetitle'] = $this->page->title;
        $data['bookmarkhovername'] = get_string('addbookmark', 'local_learningtools');
        $data['pagebookmarks'] = ltool_bookmarks_check_page_bookmarks_exist($this->page->context->id, $this->page->pagetype,
            $USER->id);
        return $data;
    }
}
