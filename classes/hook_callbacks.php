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

namespace local_learningtools;

/**
 * Hook callbacks for local_learningtools.
 *
 * @package   local_learningtools
 * @copyright 2026, bdecent gmbh bdecent.de
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class hook_callbacks {
    /**
     * @var string|null Drawer HTML rendered during settings navigation.
     *
     * Some tools register page-header actions as a side effect of rendering (the focus tool
     * adds its <link> stylesheet via $PAGE->add_header_action()), which must happen before the
     * page header is output. So the drawer is rendered early, in
     * local_learningtools_extend_settings_navigation(), and the rendered HTML is stashed here
     * for this hook to output.
     */
    public static $drawerhtml = null;

    /**
     * Render the Learning Tools drawer into the page when the drawer launcher is enabled.
     *
     * Mirrors the way core messaging adds its drawer.
     *
     * @param \core\hook\output\after_standard_main_region_html_generation $hook The hook.
     */
    public static function add_learningtools_drawer(
        \core\hook\output\after_standard_main_region_html_generation $hook
    ): void {
        if (!\local_learningtools_drawer_should_show()) {
            return;
        }
        // Use the early-rendered drawer if available; otherwise build it now.
        $hook->add_html(self::$drawerhtml ?? helper::render_drawer());
    }
}
