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
 * Report ltool: open the issue-report modal and submit it.
 *
 * @module     ltool_report/learningreport
 * @copyright  2026, bdecent gmbh bdecent.de
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define([
    'jquery', 'core/modal_save_cancel', 'core/modal_events', 'core/fragment',
    'core/ajax', 'core/notification', 'core/str'
], function($, ModalSaveCancel, ModalEvents, Fragment, Ajax, Notification, Str) {

    'use strict';

    var SELECTORS = {
        root: '.ltreportinfo',
        launcher: '.ltreport-btn'
    };

    /**
     * Flash a validation message to the user.
     *
     * @param {String} key The string key in ltool_report.
     */
    var warn = function(key) {
        Str.get_string(key, 'ltool_report').then(function(message) {
            Notification.addNotification({message: message, type: 'error'});
            return message;
        }).catch(Notification.exception);
    };

    /**
     * Validate and submit the report form via the external service.
     *
     * @param {Modal} modal The report modal.
     * @param {Number} contextid The page context id.
     * @param {Object} params The page identity data.
     */
    var submitReport = function(modal, contextid, params) {
        var root = modal.getRoot();
        var issuetype = root.find('[name="issuetype"]').val();
        var description = root.find('[name="description"]').val();
        if (!issuetype) {
            warn('selectissuetype');
            return;
        }
        if (!description || !description.trim()) {
            warn('nodescription');
            return;
        }
        var formdata = Object.assign({}, params, {issuetype: issuetype, description: description});
        Ajax.call([{
            methodname: 'ltool_report_submit_report',
            args: {contextid: contextid, formdata: JSON.stringify(formdata)},
            done: function(response) {
                Notification.addNotification({message: response.message, type: response.notificationtype});
                modal.hide();
            },
            fail: Notification.exception
        }]);
    };

    /**
     * Open the report modal for the current page.
     *
     * @param {Number} contextid The page context id.
     * @param {Object} params The page identity data.
     */
    var openModal = function(contextid, params) {
        Str.get_strings([
            {key: 'submitreport', component: 'ltool_report'},
            {key: 'submit', component: 'ltool_report'}
        ]).then(function(strings) {
            return ModalSaveCancel.create({
                title: strings[0],
                body: Fragment.loadFragment('ltool_report', 'get_report_form', contextid, {contextid: contextid}),
                large: false
            }).then(function(modal) {
                modal.setSaveButtonText(strings[1]);
                modal.show();
                modal.getRoot().on(ModalEvents.hidden, function() {
                    modal.destroy();
                });
                modal.getRoot().on(ModalEvents.save, function(e) {
                    e.preventDefault();
                    submitReport(modal, contextid, params);
                });
                return modal;
            });
        }).catch(Notification.exception);
    };

    return {
        init: function(contextid, params) {
            contextid = parseInt(contextid, 10);
            params = params || {};
            // Delegated handler, so it works whether the launcher is server-rendered (drawer)
            // or injected later by the floating button.
            document.addEventListener('click', function(e) {
                var launcher = e.target.closest(SELECTORS.root + ' ' + SELECTORS.launcher);
                if (launcher) {
                    e.preventDefault();
                    openModal(contextid, params);
                }
            });
        }
    };
});
