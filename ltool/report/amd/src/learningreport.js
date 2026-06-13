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
 * Report ltool: open the issue-report modal, review, then submit it.
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
     * Enable or disable the modal's action buttons (used to lock during submit).
     *
     * @param {jQuery} root The modal root.
     * @param {Boolean} locked Whether the buttons should be disabled.
     */
    var lockButtons = function(root, locked) {
        root.find('[data-action="save"], .ltreport-back').prop('disabled', locked);
    };

    /**
     * Send the report via the external service, locking the buttons first.
     *
     * @param {Modal} modal The report modal.
     * @param {jQuery} root The modal root.
     * @param {Number} contextid The page context id.
     * @param {Object} params The page identity data.
     */
    var sendReport = function(modal, root, contextid, params) {
        lockButtons(root, true);
        var formdata = Object.assign({}, params, {
            issuetype: root.find('[name="issuetype"]').val(),
            description: root.find('[name="description"]').val()
        });
        Ajax.call([{
            methodname: 'ltool_report_submit_report',
            args: {contextid: contextid, formdata: JSON.stringify(formdata)},
            done: function(response) {
                Notification.addNotification({message: response.message, type: response.notificationtype});
                modal.hide();
            },
            fail: function(ex) {
                lockButtons(root, false);
                Notification.exception(ex);
            }
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
            {key: 'continuereport', component: 'ltool_report'},
            {key: 'confirmsend', component: 'ltool_report'}
        ]).then(function(strings) {
            return ModalSaveCancel.create({
                title: strings[0],
                body: Fragment.loadFragment('ltool_report', 'get_report_form', contextid, {contextid: contextid}),
                large: false
            }).then(function(modal) {
                var root = modal.getRoot();
                var step = 'form';
                var submitting = false;
                modal.setSaveButtonText(strings[1]);
                modal.show();

                root.on(ModalEvents.hidden, function() {
                    modal.destroy();
                });

                // Show the selected issue type's description on the form.
                root.on('change', '[name="issuetype"]', function() {
                    var option = this.options[this.selectedIndex];
                    root.find('.ltreport-issuedesc').text(option ? (option.getAttribute('data-description') || '') : '');
                });

                // Return from the review step to the form.
                root.on('click', '.ltreport-back', function() {
                    step = 'form';
                    root.find('.ltreport-step-review').attr('hidden', 'hidden');
                    root.find('.ltreport-step-form').removeAttr('hidden');
                    modal.setSaveButtonText(strings[1]);
                });

                root.on(ModalEvents.save, function(e) {
                    e.preventDefault();
                    if (step === 'form') {
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
                        // Populate and show the read-only review step.
                        root.find('.ltreport-review-type').text(root.find('[name="issuetype"] option:selected').text());
                        root.find('.ltreport-review-description').text(description);
                        root.find('.ltreport-review-link').text(params.pageurl).attr('href', params.pageurl);
                        root.find('.ltreport-step-form').attr('hidden', 'hidden');
                        root.find('.ltreport-step-review').removeAttr('hidden');
                        step = 'review';
                        modal.setSaveButtonText(strings[2]);
                        return;
                    }
                    // Review step: confirm and send, locking immediately against a double submit.
                    if (submitting) {
                        return;
                    }
                    submitting = true;
                    sendReport(modal, root, contextid, params);
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
