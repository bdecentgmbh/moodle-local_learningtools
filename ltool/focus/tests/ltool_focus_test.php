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
 * focus ltool lib test cases defined.
 *
 * @package   ltool_focus
 * @copyright bdecent GmbH 2021
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace ltool_focus;

/**
 * focus subplugin for learningtools phpunit test cases defined.
 */
class ltool_focus_test extends \advanced_testcase {
    /**
     * Create custom page instance and set admin user as loggedin user.
     *
     * @return void
     */
    public function setup(): void {
        global $CFG;
        require_once($CFG->dirroot."/local/learningtools/ltool/focus/lib.php");
        $this->resetAfterTest();
        $this->setAdminUser();
    }

    /**
     * Create css file in temp directory.
     * @covers ::ltool_focus_create_focus_temp_cssfile
     */
    public function test_ltool_focus_create_focus_temp_cssfile() {
        $configdata = get_config('ltool_focus', 'focusmodecss');
        $fileinfo = ltool_focus_create_focus_temp_cssfile();
        $this->assertEquals($configdata, $fileinfo->get_content());
    }
}
