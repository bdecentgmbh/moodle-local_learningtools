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
 * Note ltool lib test cases defined.
 *
 * @package   ltool_note
 * @copyright bdecent GmbH 2021
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace ltool_note;
/**
 * Note subplugin for learningtools phpunit test cases defined.
 */
class ltool_note_test extends \advanced_testcase {

    /**
     * Create custom page instance and set admin user as loggedin user.
     *
     * @return void
     */
    public function setup(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $this->context = \context_course::instance($course->id);
        $page = new \moodle_page();
        $page->set_context($this->context);
        $page->set_course($course);
        $page->set_title('Course: Course 1');
        $page->set_pagelayout('standard');
        $page->set_pagetype('course-view');
        $page->set_url(new \moodle_url('/course/view.php', ['id' => $course->id]));
        $this->page = $page;
    }

    /**
     * Generate data to create note.
     *
     * @return array
     */
    public function create_note(): array {
        global $CFG, $DB;
        $toolobj = new \ltool_note\note();
        $tool = $DB->get_record('local_learningtools_products', ['shortname' => 'note']);
        $data = $this->get_note_info($toolobj, $tool);
        $data['ltnoteeditor'] = 'Test note';
        return $data;
    }

    /**
     * Test the note save method and the triggered event have correct context.
     * @covers ::ltool_note_user_save_notes
     * @return void
     */
    public function test_save_note(): void {

        $data = $this->create_note();
        $sink = $this->redirectEvents();
        ltool_note_user_save_notes($this->context->id, $data);
        $events = $sink->get_events();
        $event = reset($events);
        $this->assertInstanceOf('\ltool_note\event\ltnote_created', $event);
        $this->assertEquals($this->context, $event->get_context());
    }

    /**
     * Test created notes count.
     * @covers ::ltool_note_get_userpage_countnotes
     * @return void
     */
    public function test_note_count(): void {

        $data = $this->create_note();
        ltool_note_user_save_notes($this->context->id, $data);
        $data1 = $this->create_note();
        ltool_note_user_save_notes($this->context->id, $data1);
        $args = [
            'contextid' => $data['contextid'],
            'pagetype' => $data['pagetype'],
            'user' => $data['user'],
            'pageurl' => $data['pageurl']
        ];
        $count = ltool_note_get_userpage_countnotes($args);
        $this->assertEquals(2, $count);
        $notes = local_learningtools_check_instanceof_block((object) $data);
        $this->assertEquals('course', $notes->instance);
    }

    /**
     * Case to test the external method to create/delete notes.
     * @covers \ltool_note\external::save_usernote
     * @return void
     */
    public function test_external_test(): void {
        global $CFG, $DB, $USER;
        $data = $this->create_note();
        $data = str_replace('amp;', '', http_build_query($data));
        // Redirect all events. Created event must trigger when the note saved.
        $sink = $this->redirectEvents();
        $notecount = \ltool_note\external::save_usernote($this->context->id, $data);
        $events = $sink->get_events();
        $event = reset($events);
        $this->assertInstanceOf('\ltool_note\event\ltnote_created', $event);
        $this->assertEquals($this->context, $event->get_context());
        $notecount = \ltool_note\external::save_usernote($this->context->id, $data);
        $this->assertEquals(2, $notecount);
    }


    /**
     * Generate and fetch data for create new note instnace.
     *
     * @param  mixed $toolobj
     * @param  mixed $tool
     * @return void
     */
    public function get_note_info($toolobj, $tool) {
        global $DB, $USER;
        $data = $toolobj->get_tool_info();
        $data['course'] = $this->page->course->id;
        $data['pageurl'] = $this->page->url->out(false);
        $data['pagetype'] = $this->page->pagetype;
        $data['contextlevel'] = $this->page->context->contextlevel;
        $data['contextid'] = $this->page->context->id;
        $data['pagetitle'] = $this->page->title;
        $data['user'] = $USER->id;
        $data['ltnote'] = true;
        return $data;
    }
}
