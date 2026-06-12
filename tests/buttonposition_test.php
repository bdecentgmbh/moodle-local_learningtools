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
 * Unit tests for the Learning Tools button position setting.
 *
 * @package   local_learningtools
 * @copyright 2026, bdecent gmbh bdecent.de
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_learningtools;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/local/learningtools/lib.php');

/**
 * Tests for the configurable Learning Tools button position (FAB / drawer).
 */
final class buttonposition_test extends \advanced_testcase {
    /**
     * Reset the database before each test.
     */
    public function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
    }

    /**
     * With no configuration set, the position defaults to the bottom-right FAB.
     *
     * @covers ::local_learningtools_get_button_position
     */
    public function test_get_button_position_defaults_to_bottomright(): void {
        $this->assertEquals('bottomright', local_learningtools_get_button_position());
    }

    /**
     * A stored, valid setting is returned verbatim.
     *
     * @covers ::local_learningtools_get_button_position
     */
    public function test_get_button_position_reads_setting(): void {
        set_config('buttonposition', 'bottomleft', 'local_learningtools');
        $this->assertEquals('bottomleft', local_learningtools_get_button_position());

        set_config('buttonposition', 'drawer', 'local_learningtools');
        $this->assertEquals('drawer', local_learningtools_get_button_position());
    }

    /**
     * An unknown/garbage value falls back to the safe default.
     *
     * @covers ::local_learningtools_get_button_position
     */
    public function test_get_button_position_invalid_falls_back(): void {
        set_config('buttonposition', 'garbage', 'local_learningtools');
        $this->assertEquals('bottomright', local_learningtools_get_button_position());
    }

    /**
     * Data provider mapping each position to its expected container class.
     *
     * @return array<string, array{0: string, 1: string}>
     */
    public static function fab_container_class_provider(): array {
        return [
            'bottomright' => ['bottomright', ''],
            'bottomleft' => ['bottomleft', 'floating-button-left'],
            'drawer' => ['drawer', ''],
        ];
    }

    /**
     * The container class reflects the position (left modifier only for bottomleft).
     *
     * @dataProvider fab_container_class_provider
     * @covers ::local_learningtools_get_fab_container_class
     * @param string $position the configured position
     * @param string $expected the expected container class
     */
    public function test_get_fab_container_class_mapping(string $position, string $expected): void {
        $this->assertEquals($expected, local_learningtools_get_fab_container_class($position));
    }

    /**
     * Drawer mode is true only for the drawer position; the argument overrides the setting.
     *
     * @covers ::local_learningtools_is_drawer_mode
     */
    public function test_is_drawer_mode(): void {
        $this->assertFalse(local_learningtools_is_drawer_mode('bottomright'));
        $this->assertFalse(local_learningtools_is_drawer_mode('bottomleft'));
        $this->assertTrue(local_learningtools_is_drawer_mode('drawer'));

        // When no argument is passed it reads the stored setting.
        set_config('buttonposition', 'drawer', 'local_learningtools');
        $this->assertTrue(local_learningtools_is_drawer_mode());
        set_config('buttonposition', 'bottomright', 'local_learningtools');
        $this->assertFalse(local_learningtools_is_drawer_mode());
    }

    /**
     * No navbar icon is rendered when the launcher is a floating button.
     *
     * @covers ::local_learningtools_render_navbar_output
     */
    public function test_render_navbar_output_empty_when_not_drawer(): void {
        global $PAGE;
        $this->setAdminUser();
        $renderer = $PAGE->get_renderer('core');
        $this->assertSame('', local_learningtools_render_navbar_output($renderer));
    }

    /**
     * Guests never get the drawer navbar icon, even in drawer mode.
     *
     * @covers ::local_learningtools_render_navbar_output
     */
    public function test_render_navbar_output_empty_for_guest(): void {
        global $PAGE;
        set_config('buttonposition', 'drawer', 'local_learningtools');
        $this->setGuestUser();
        $renderer = $PAGE->get_renderer('core');
        $this->assertSame('', local_learningtools_render_navbar_output($renderer));
    }

    /**
     * In drawer mode a logged-in user with capability gets the navbar toggle.
     *
     * @covers ::local_learningtools_render_navbar_output
     */
    public function test_render_navbar_output_has_toggle_in_drawer_mode(): void {
        global $PAGE;
        set_config('buttonposition', 'drawer', 'local_learningtools');
        $this->setAdminUser();
        $PAGE->set_context(\context_system::instance());
        $PAGE->set_url('/');
        $output = local_learningtools_render_navbar_output($PAGE->get_renderer('core'));
        $this->assertStringContainsString('learningtools-drawer-toggle', $output);
    }

    /**
     * The drawer shows the notes region and the other tool buttons, but no notes FAB button.
     *
     * @covers \local_learningtools\helper::render_drawer
     */
    public function test_render_drawer_has_notes_region_and_no_note_button(): void {
        global $PAGE;
        set_config('buttonposition', 'drawer', 'local_learningtools');
        $this->setAdminUser();
        $PAGE->set_context(\context_system::instance());
        $PAGE->set_url('/');
        $html = helper::render_drawer();
        $this->assertStringContainsString('data-region="learningtools-drawer"', $html);
        $this->assertStringContainsString('data-region="lt-drawer-notes"', $html);
        // Notes are shown expanded, never as a floating-button entry.
        $this->assertStringNotContainsString('id="ltnoteinfo"', $html);
    }

    /**
     * The drawer save button is present by default and hidden when auto-save is enabled.
     *
     * @covers \local_learningtools\helper::render_drawer
     */
    public function test_render_drawer_save_button_follows_autosave_setting(): void {
        global $PAGE;
        set_config('buttonposition', 'drawer', 'local_learningtools');
        $this->setAdminUser();
        $PAGE->set_context(\context_system::instance());
        $PAGE->set_url('/');

        // Default: explicit save button is shown.
        $this->assertStringContainsString('lt-drawer-save-note', helper::render_drawer());

        // Auto-save on: no save button.
        set_config('autosavenotes', 1, 'local_learningtools');
        $this->assertStringNotContainsString('lt-drawer-save-note', helper::render_drawer());
    }
}
