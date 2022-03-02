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
 * Focus ltool define js.
 * @module   ltool_focus
 * @copyright 2021, bdecent gmbh bdecent.de
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 define(['jquery', 'core/ajax'],
 function($, Ajax) {

    /**
     * Controls focus tool action.
     * @param {int} focusMode
     */
    var LearningToolFocus = function(focusMode) {
        var self = this;
        var focusBlock = document.querySelector('.ltoolfocus-info');
        var focusInfo = document.querySelector(".ltoolfocus-info #ltoolfocus-action");
        if (focusMode) {
            self.addFocusMode(focusBlock);
            if (self.disableButton) {
                if (self.disableButton.classList.contains('d-none')) {
                    self.disableButton.classList.remove('d-none');
                }
            }
        }

        if (focusInfo) {
            focusInfo.addEventListener("click", function() {
                if (focusBlock) {
                    if (focusBlock.classList.contains('enable')) {
                        focusBlock.classList.remove('enable');
                        self.focusModeAction(0);
                        self.removeFocusMode();
                    } else {
                        focusBlock.classList.add('enable');
                        self.focusModeAction(1);
                        self.addFocusMode(focusBlock);
                    }
                }
            });

            // Hover color.
            var focushovercolor = focusInfo.getAttribute("data-hovercolor");
            var focusfontcolor = focusInfo.getAttribute("data-fontcolor");
            if (focushovercolor && focusfontcolor) {
                focusInfo.addEventListener("mouseover", function() {
                    document.querySelector('#ltoolfocus-info p').style.background = focushovercolor;
                    document.querySelector('#ltoolfocus-info p').style.color = focusfontcolor;
                });
            }
        }

        if (self.disableButton) {
            self.disableButton.addEventListener("click", function() {
                self.focusModeAction(0);
                self.removeFocusMode();
            });
        }
    };

    LearningToolFocus.prototype.disableButton = document.querySelector('#page-wrapper #disable-focusmode');

    LearningToolFocus.prototype.addFocusMode = function(element) {
        var self = this;
        var focusCssUrl = element.getAttribute('data-focuscssurl');
        var focusCssLink = document.querySelectorAll("#page-header link#ltool-focuscss")[0];
        if (focusCssLink) {
            focusCssLink.setAttribute('href', focusCssUrl);
        }
        self.loadEnableFocusmode();
    };

    LearningToolFocus.prototype.removeFocusMode = function() {
        var self = this;
        var focusCssLink = document.querySelectorAll("#page-header link#ltool-focuscss")[0];
        if (focusCssLink) {
            focusCssLink.setAttribute('href', "");
        }

        var focusBlock = document.querySelector('.ltoolfocus-info');
        if (focusBlock.classList.contains('enable')) {
            focusBlock.classList.remove('enable');
        }
        self.loadDisableFocusmode();
    };

    LearningToolFocus.prototype.loadDisableFocusmode = function() {
        var self = this;
        if (self.disableButton) {
            if (!self.disableButton.classList.contains('d-none')) {
                self.disableButton.classList.add('d-none');
            }
        }
    };

    LearningToolFocus.prototype.loadEnableFocusmode = function() {
        var self = this;
        // Implemented the focus disable button.
        var body = document.querySelector("body");
        if (body) {
            if (!body.classList.contains('drawer-open-left')) {
                body.classList.remove('drawer-open-left');
            }
        }
        if (self.disableButton) {
            self.disableButton.classList.remove('d-none');
        }
    };

    LearningToolFocus.prototype.focusModeAction = function(status) {
        Ajax.call([{
            methodname: 'ltool_focus_save_userfocusmode',
            args: {status: status},
            done: function() {
                return true;
            }
        }]);
    };

    return {
        init: function(focusMode) {
            return new LearningToolFocus(focusMode);
        }
    };

 });