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
 * External functions for the like learning tool.
 *
 * @package   ltool_like
 * @copyright 2026, bdecent gmbh bdecent.de
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace ltool_like;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');

/**
 * External API for saving a user reaction.
 */
class external extends \external_api {
    /**
     * Parameters for save_userlike.
     *
     * @return \external_function_parameters
     */
    public static function save_userlike_parameters() {
        return new \external_function_parameters(
            [
                'contextid' => new \external_value(PARAM_INT, 'The page context id'),
                'formdata' => new \external_value(PARAM_RAW, 'JSON encoded page identity and chosen reaction'),
            ]
        );
    }

    /**
     * Save, switch or remove the current user's reaction.
     *
     * @param int $contextid page context id
     * @param string $formdata JSON page identity and reaction
     * @return array|string result, or '' when the request is rejected
     */
    public static function save_userlike($contextid, $formdata) {
        global $CFG, $USER;
        require_login();
        require_once($CFG->dirroot . '/local/learningtools/ltool/like/lib.php');
        $context = \context_system::instance();
        require_capability('ltool/like:createlike', $context);
        $params = self::validate_parameters(
            self::save_userlike_parameters(),
            ['contextid' => $contextid, 'formdata' => $formdata]
        );
        $data = (array) json_decode($params['formdata']);
        if ($USER->id == $data['user']) {
            return ltool_like_user_save_like($params['contextid'], $data);
        }
        return '';
    }

    /**
     * Return structure for save_userlike.
     *
     * @return \external_single_structure
     */
    public static function save_userlike_returns() {
        return new \external_single_structure(
            [
                'reaction' => new \external_value(PARAM_ALPHA, 'The user\'s reaction, or empty when removed'),
                'message' => new \external_value(PARAM_TEXT, 'Notification message'),
                'notificationtype' => new \external_value(PARAM_TEXT, 'Notification type'),
                'cancount' => new \external_value(PARAM_BOOL, 'Whether the user may see the counts'),
                'canviewusers' => new \external_value(PARAM_BOOL, 'Whether the user may see who reacted'),
                'counts' => new \external_single_structure(
                    [
                        'dislike' => new \external_value(PARAM_INT, 'Dislike count'),
                        'like' => new \external_value(PARAM_INT, 'Like count'),
                        'superlike' => new \external_value(PARAM_INT, 'Super like count'),
                    ],
                    'Aggregate counts (only present when cancount is true)',
                    VALUE_OPTIONAL
                ),
            ]
        );
    }
}
