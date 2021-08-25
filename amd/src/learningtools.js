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
 * Learningtools define js.
 * @package   local_learnigtools
 * @category  Classes - autoloading
 * @copyright 2021, bdecent gmbh bdecent.de
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define([], function() {

    function learning_tools_action(loggedin, fabbuttonhtml) {
        // Add fab button.
        if (loggedin) {
            var pagewrapper = document.getElementById("page-footer");
            pagewrapper.insertAdjacentHTML("beforebegin", fabbuttonhtml);
        }

        var toolaction = document.getElementById("tool-action-button");
        if (toolaction !== null) {
            toolaction.addEventListener("click", function() {
                var list = document.getElementsByClassName("list-learningtools")[0];
                if (list.classList.contains('show')) {
                    list.classList.remove('show');
                } else {
                    list.classList.add('show');
                }
            });
        }
    }
    return {
        init: function(loggedin, fabbuttonhtml) {
            learning_tools_action(loggedin, fabbuttonhtml);
        }
    };
});
