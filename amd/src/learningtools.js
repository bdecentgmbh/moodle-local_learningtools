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
 * @category  Classes - autoloading
 * @copyright 2021, bdecent gmbh bdecent.de
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define([], function() {

    /* global fabbuttonhtml */

    /**
     * Controls Learning Tools action.
     * @param {bool} loggedin login status
     */
    function learningToolsAction(loggedin) {
        fabbuttonhtml = JSON.parse(fabbuttonhtml);
        // Add fab button.
        if (loggedin) {
            var pagewrapper = document.querySelector("footer");
            pagewrapper.insertAdjacentHTML("beforebegin", fabbuttonhtml);
        }

        // Add body class
        var body = document.querySelector('body');
        if (body) {
            if (!body.classList.contains('local-learningtools')) {
                body.classList.add('local-learningtools');
            }
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

        var bodyid = document.querySelector("body").id;
        if (bodyid == 'page-admin-setting-local_learningtools' || bodyid == 'page-admin-setting-local_learningtools_settings') {
            document.querySelectorAll("#admin-visiblecategories")[0].style.display = 'none';
            document.querySelectorAll("#admin-fabbuttonvisible select")[0].addEventListener("change", function() {
                var val = this.value;
                if (val == 'specificcate') {
                    document.querySelectorAll("#admin-visiblecategories")[0].style.display = 'flex';
                } else {
                    document.querySelectorAll("#admin-visiblecategories")[0].style.display = 'none';
                }
            });

            var select = document.querySelectorAll("#admin-fabbuttonvisible select")[0];
            var option = select.options[select.selectedIndex];
            var optionval = option.value;
            if (optionval == 'specificcate') {
                document.querySelectorAll("#admin-visiblecategories")[0].style.display = 'flex';
            }
        }
    }
    return {
        init: function(loggedin) {
            learningToolsAction(loggedin);
        }
    };

});
