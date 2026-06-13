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
 * Like ltool: inline reaction buttons + manager user-list modal.
 *
 * @module     ltool_like/learninglike
 * @copyright  2026, bdecent gmbh bdecent.de
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define([
    'core/ajax', 'core/notification', 'core/modal', 'core/fragment', 'core/str'
], function(Ajax, Notification, Modal, Fragment, Str) {

    'use strict';

    /* global ltools */

    var SELECTORS = {
        root: '.ltlikeinfo',
        button: '.ltlike-btn',
        launcher: '.ltlike-launcher',
        countClickable: '.ltlike-count-clickable'
    };

    var OPEN_CLASS = 'ltlike-open';

    /**
     * Open or close the floating-button reaction flyout.
     *
     * @param {HTMLElement} info The .ltlikeinfo element.
     * @param {Boolean} open Whether the flyout should be open.
     */
    var setFlyout = function(info, open) {
        info.classList.toggle(OPEN_CLASS, open);
        var launcher = info.querySelector(SELECTORS.launcher);
        if (launcher) {
            launcher.setAttribute('aria-expanded', open ? 'true' : 'false');
        }
    };

    /**
     * Close every open reaction flyout on the page.
     */
    var closeAllFlyouts = function() {
        document.querySelectorAll(SELECTORS.root + '.' + OPEN_CLASS).forEach(function(info) {
            setFlyout(info, false);
        });
    };

    /**
     * Reflect the server response: the active button and (if allowed) the counts.
     *
     * @param {HTMLElement} root The #ltlikeinfo element.
     * @param {Object} response The save_userlike response.
     */
    var applyState = function(root, response) {
        root.querySelectorAll(SELECTORS.button).forEach(function(btn) {
            var isactive = btn.getAttribute('data-reaction') === response.reaction;
            btn.classList.toggle('active', isactive);
            btn.setAttribute('aria-pressed', isactive ? 'true' : 'false');
        });
        // Mirror the chosen reaction on the floating-button launcher.
        var launcher = root.querySelector(SELECTORS.launcher);
        if (launcher) {
            launcher.classList.toggle('active', !!response.reaction);
            var icon = launcher.querySelector('i');
            if (icon) {
                icon.className = 'fa ' + (response.reaction === 'dislike' ? 'fa-thumbs-down' : 'fa-thumbs-up');
            }
        }
        if (response.cancount && response.counts) {
            Object.keys(response.counts).forEach(function(type) {
                var el = root.querySelector('#ltlike-count-' + type);
                if (!el) {
                    return;
                }
                var num = el.querySelector('[aria-hidden="true"]');
                if (num) {
                    num.textContent = response.counts[type];
                }
                var sr = el.querySelector('.sr-only, .visually-hidden');
                if (sr) {
                    var parts = sr.textContent.trim().split(' ');
                    parts[0] = String(response.counts[type]);
                    sr.textContent = parts.join(' ');
                }
            });
        }
    };

    /**
     * Save, switch or remove the reaction.
     *
     * @param {HTMLElement} root The #ltlikeinfo element.
     * @param {Number} contextid The page context id.
     * @param {Object} params The page identity data.
     * @param {String} reaction The chosen reaction, or '' to remove.
     */
    var saveLike = function(root, contextid, params, reaction) {
        var formdata = Object.assign({}, params, {reaction: reaction});
        var buttons = root.querySelectorAll(SELECTORS.button);
        // Briefly lock the group so a second click cannot race the in-flight request.
        // core/ajax only honours done/fail (not always), so each must release the lock.
        buttons.forEach(function(b) {
            b.disabled = true;
        });
        var release = function() {
            buttons.forEach(function(b) {
                b.disabled = false;
            });
        };
        Ajax.call([{
            methodname: 'ltool_like_save_userlike',
            args: {contextid: contextid, formdata: JSON.stringify(formdata)},
            done: function(response) {
                applyState(root, response);
                Notification.addNotification({message: response.message, type: response.notificationtype});
                if (typeof ltools !== 'undefined' && ltools.disappertimenotify != 0) {
                    setTimeout(function() {
                        var notifications = document.querySelector('span.notifications');
                        if (notifications) {
                            notifications.innerHTML = '';
                        }
                    }, ltools.disappertimenotify);
                }
                release();
            },
            fail: function(ex) {
                release();
                Notification.exception(ex);
            }
        }]);
    };

    /**
     * Open the manager modal listing the users who reacted.
     *
     * @param {HTMLElement} root The #ltlikeinfo element.
     */
    var openUsersModal = function(root) {
        var contextid = parseInt(root.getAttribute('data-contextid'), 10);
        var pageurl = root.getAttribute('data-pageurl');
        Str.get_string('reactionusers', 'ltool_like').then(function(title) {
            return Modal.create({
                title: title,
                body: Fragment.loadFragment('ltool_like', 'get_like_users', contextid,
                    {contextid: contextid, pageurl: pageurl}),
                large: true,
                removeOnClose: true
            });
        }).then(function(modal) {
            modal.show();
            return modal;
        }).catch(Notification.exception);
    };

    return {
        init: function(contextid, params) {
            contextid = parseInt(contextid, 10);
            params = params || {};
            // Delegated handlers, so they work whether the buttons are server-rendered
            // (drawer) or injected into the page later by the floating button.
            document.addEventListener('click', function(e) {
                // The launcher (floating button only) just opens/closes the reaction flyout.
                var launcher = e.target.closest(SELECTORS.root + ' ' + SELECTORS.launcher);
                if (launcher) {
                    e.preventDefault();
                    var info = launcher.closest(SELECTORS.root);
                    setFlyout(info, !info.classList.contains(OPEN_CLASS));
                    return;
                }
                var btn = e.target.closest(SELECTORS.root + ' ' + SELECTORS.button);
                if (btn) {
                    // Toggle off if the active reaction is clicked again.
                    var chosen = btn.classList.contains('active') ? '' : btn.getAttribute('data-reaction');
                    saveLike(btn.closest(SELECTORS.root), contextid, params, chosen);
                    return;
                }
                var count = e.target.closest(SELECTORS.root + ' ' + SELECTORS.countClickable);
                if (count) {
                    openUsersModal(count.closest(SELECTORS.root));
                    return;
                }
                // A click anywhere outside a reaction widget closes any open flyout.
                if (!e.target.closest(SELECTORS.root)) {
                    closeAllFlyouts();
                }
            });
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeAllFlyouts();
                    return;
                }
                if (e.key !== 'Enter' && e.key !== ' ') {
                    return;
                }
                var count = e.target.closest(SELECTORS.root + ' ' + SELECTORS.countClickable);
                if (count) {
                    e.preventDefault();
                    openUsersModal(count.closest(SELECTORS.root));
                }
            });
        }
    };
});
