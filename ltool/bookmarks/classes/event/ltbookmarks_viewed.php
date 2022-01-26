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
 * Viewing the ltool_bookmarks creates the event.
 *
 * @package   ltool_bookmarks
 * @copyright bdecent GmbH 2021
 * @category  event
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace ltool_bookmarks\event;

/**
 * Bookmarks tool call to view event.
 */
class ltbookmarks_viewed extends \core\event\base {

    /**
     * Init method.
     */
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    /**
     * Returns localised general event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('eventltbookmarksviewed', 'local_learningtools');
    }

    /**
     * Returns description of what happened.
     *
     * @return string
     */
    public function get_description() {

        if ($this->relateduserid) {
            return "The related user with id '$this->relateduserid' has viewed the user with id '$this->userid' bookmarks.";
        }
        return "The user with id '$this->userid' has viewed the bookmarks.";
    }
}
