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
 * Schedule ltool define js.
 * @module   ltool_schedule
 * @copyright 2021, bdecent gmbh bdecent.de
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 define(['core/fragment', 'core/modal_factory', 'core/str', 'core/modal_events', 'core/notification'],
 function(Fragment, ModalFactory, String, ModalEvents, Notification) {

    /**
     * Controls Schedule tool action.
     * @param {object} params
     */
    var LearningToolSchedule = function(params) {
        var self = this;
        var scheduleInfo = document.querySelector(".ltoolschedule-info #ltoolschedule-action");
        if (scheduleInfo) {
            // Hover color.
            var schedulehovercolor = scheduleInfo.getAttribute("data-hovercolor");
            var schedulefontcolor = scheduleInfo.getAttribute("data-fontcolor");
            if (schedulehovercolor && schedulefontcolor) {
                scheduleInfo.addEventListener("mouseover", function() {
                    document.querySelector('#ltoolschedule-action p').style.background = schedulehovercolor;
                    document.querySelector('#ltoolschedule-action p').style.color = schedulefontcolor;
                });
            }
            scheduleInfo.addEventListener('click', function() {
                self.displaySchedulebox(params);
            });
        }

    };

    LearningToolSchedule.prototype.displaySchedulebox = function(params) {
        var self = this;
        var strschedule = String.get_string('schedule', 'local_learningtools');
        ModalFactory.create({
            title: strschedule,
            type: ModalFactory.types.SAVE_CANCEL,
            body: self.getScheduleAction(params),
            large: true
        }).then(function(modal) {
            modal.show();
            modal.getRoot().on(ModalEvents.hidden, function() {
                modal.destroy();
            });

            modal.getRoot().on(ModalEvents.save, function(e) {
                e.preventDefault();
                var schedulenameinfo = document.querySelectorAll("#ltoolschedule-editorbox input[name='schedulename']")[0];
                if (schedulenameinfo.value) {
                    self.submitFormData(params.contextid);
                    modal.getRoot().submit();
                    modal.hide();
                    window.location.reload();
                }
            });
            return modal;
        }).fail(Notification.exception);

    };

    LearningToolSchedule.prototype.submitFormData = function(contextid) {
        var modalform = document.querySelectorAll('#ltoolschedule-editorbox form')[0];
        var formData = new URLSearchParams(new FormData(modalform)).toString();
        Fragment.loadFragment('ltool_schedule', 'set_calendar_event', contextid, {'formdata': formData});
        modalform.submit();
        return true;
    };

    LearningToolSchedule.prototype.getScheduleAction = function(params) {
        return Fragment.loadFragment('ltool_schedule', 'get_schedule_form', params.contextid, params);
    };

    return {
        init: function(params) {
            return new LearningToolSchedule(params);
        }
    };
 });