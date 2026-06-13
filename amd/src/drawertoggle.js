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
 * Learning Tools drawer launcher: wires the navbar toggle to the core drawer, shows a
 * backdrop while open, and lazy-loads the Notes editor when the drawer is first opened.
 *
 * @module     local_learningtools/drawertoggle
 * @copyright  2026, bdecent gmbh bdecent.de
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define([
    'jquery',
    'core/drawer',
    'core/drawer_events',
    'core/pubsub',
    'core/fragment',
    'core/templates',
    'core/modal_backdrop',
    'core/ajax',
    'core/notification',
    'core/str'
], function($, Drawer, DrawerEvents, PubSub, Fragment, Templates, ModalBackdrop, Ajax, Notification, Str) {

    'use strict';

    var SELECTORS = {
        drawer: '#learningtools-drawer',
        toggle: '#learningtools-drawer-toggle',
        notesRegion: '[data-region="lt-drawer-notes"]',
        closeButton: '[data-action="closedrawer"]',
        saveButton: '[data-action="lt-drawer-save-note"]',
        noteForm: '.ltoolusernotes form',
        noteBadge: '.ltnoteinfo span'
    };

    // The note editor is loaded on the first drawer open only.
    var notesLoaded = false;
    var backdropPromise = null;
    // Auto-save state.
    var autosave = false;
    var lastSavedContent = '';

    /**
     * Get the TinyMCE editor instance for the drawer note editor, if available.
     *
     * @return {Object|null} The editor or null.
     */
    var getNoteEditor = function() {
        var textarea = document.querySelector(SELECTORS.noteForm + ' textarea[name="ltnoteeditor"]');
        var tiny = window.tinymce || window.tinyMCE;
        if (textarea && tiny && tiny.get) {
            return tiny.get(textarea.id);
        }
        return null;
    };

    /**
     * Whether some editor HTML is effectively empty (no text once tags are stripped).
     *
     * @param {String} html The editor HTML.
     * @return {Boolean} True when empty.
     */
    var isEmptyContent = function(html) {
        return html.replace(/<[^>]*>/g, '').replace(/&nbsp;/g, ' ').trim() === '';
    };

    /**
     * Auto-save the current note (on editor blur or drawer close) when enabled.
     *
     * @param {Number} contextid The page context id.
     */
    var autoSaveNote = function(contextid) {
        if (!autosave) {
            return;
        }
        // Make sure TinyMCE has flushed its content into the textarea before we read it.
        var editor = getNoteEditor();
        if (editor) {
            editor.save();
        }
        var textarea = document.querySelector(SELECTORS.noteForm + ' textarea[name="ltnoteeditor"]');
        var content = textarea ? textarea.value : '';
        if (isEmptyContent(content) || content === lastSavedContent) {
            return;
        }
        lastSavedContent = content;
        saveNote(contextid);
    };

    /**
     * Lazily create the backdrop shown behind the drawer; clicking it closes the drawer.
     *
     * @param {jQuery} root The drawer root element.
     * @return {Promise} Resolves with the ModalBackdrop.
     */
    var getBackdrop = function(root) {
        if (!backdropPromise) {
            backdropPromise = Templates.render('core/modal_backdrop', {}).then(function(html) {
                var backdrop = new ModalBackdrop(html);
                var zindex = window.getComputedStyle(root[0]).zIndex;
                if (zindex) {
                    backdrop.setZIndex(zindex - 1);
                }
                backdrop.getAttachmentPoint().get(0).addEventListener('click', function(e) {
                    e.preventDefault();
                    Drawer.hide(root);
                });
                return backdrop;
            });
        }
        return backdropPromise;
    };

    /**
     * Lazy-load the Notes editor + recent notes into the drawer.
     *
     * Reuses the same fragment the note modal uses, so no note logic is duplicated.
     *
     * @param {Number} contextid The page context id.
     */
    var loadNotes = function(contextid) {
        if (notesLoaded) {
            return;
        }
        notesLoaded = true;
        var region = document.querySelector(SELECTORS.notesRegion);
        if (!region) {
            return;
        }
        var params = window.ltool_note_config || {};
        params.contextid = contextid;
        // Keep the drawer editor compact; the recent notes scroll above it.
        params.rows = 6;
        Fragment.loadFragment('ltool_note', 'get_note_form', contextid, params)
            .done(function(html, js) {
                Templates.replaceNodeContents(region, html, js);
                lastSavedContent = '';
                attachAutosave(contextid);
            })
            .fail(Notification.exception);
    };

    /**
     * Attach the blur listeners that auto-save the note when the option is enabled.
     *
     * @param {Number} contextid The page context id.
     */
    var attachAutosave = function(contextid) {
        if (!autosave) {
            return;
        }
        var textarea = document.querySelector(SELECTORS.noteForm + ' textarea[name="ltnoteeditor"]');
        if (!textarea) {
            return;
        }
        // Plain textarea fallback (non-TinyMCE editors).
        textarea.addEventListener('blur', function() {
            autoSaveNote(contextid);
        });
        // Hook the TinyMCE editor's blur once it has initialised.
        var tries = 0;
        var poll = setInterval(function() {
            tries++;
            var editor = getNoteEditor();
            if (editor) {
                editor.on('blur', function() {
                    autoSaveNote(contextid);
                });
                clearInterval(poll);
            } else if (tries > 30) {
                clearInterval(poll);
            }
        }, 300);
    };

    /**
     * Save the note currently in the drawer editor.
     *
     * @param {Number} contextid The page context id.
     */
    var saveNote = function(contextid) {
        var form = document.querySelector(SELECTORS.noteForm);
        if (!form) {
            return;
        }
        var formdata = new URLSearchParams(new FormData(form)).toString();
        Ajax.call([{
            methodname: 'ltool_note_save_usernote',
            args: {contextid: contextid, formdata: formdata},
            done: function(response) {
                var badge = document.querySelector(SELECTORS.noteBadge);
                if (badge && response) {
                    badge.classList.add('ticked');
                    badge.innerHTML = response;
                }
                Str.get_string('successnotemessage', 'local_learningtools').done(function(message) {
                    Notification.addNotification({message: message, type: 'success'});
                });
                // Reload the notes so the freshly saved note appears.
                notesLoaded = false;
                loadNotes(contextid);
            },
            fail: Notification.exception
        }]);
    };

    /**
     * Initialise the drawer behaviour.
     */
    var init = function() {
        var drawerContent = document.querySelector(SELECTORS.drawer);
        if (!drawerContent) {
            return;
        }
        var contextid = drawerContent.getAttribute('data-contextid');
        autosave = drawerContent.getAttribute('data-autosave') === '1';
        var root = Drawer.getDrawerRoot($(drawerContent));
        var toggle = $(SELECTORS.toggle);
        if (root.length && toggle.length) {
            Drawer.registerToggles(root, toggle);
            // Load the notes the first time the launcher is clicked.
            toggle.on('click', function() {
                loadNotes(contextid);
            });
        }
        $(drawerContent).on('click', SELECTORS.closeButton, function(e) {
            e.preventDefault();
            Drawer.hide(root);
        });
        $(drawerContent).on('click', SELECTORS.saveButton, function(e) {
            e.preventDefault();
            saveNote(contextid);
        });

        // Show/hide the backdrop in step with the drawer (like the messaging drawer).
        PubSub.subscribe(DrawerEvents.DRAWER_SHOWN, function(shown) {
            if (root.length && shown[0] === root[0]) {
                getBackdrop(root).then(function(backdrop) {
                    backdrop.show();
                    return backdrop;
                }).catch(Notification.exception);
            }
        });
        PubSub.subscribe(DrawerEvents.DRAWER_HIDDEN, function(hidden) {
            if (root.length && hidden[0] === root[0]) {
                // Persist any unsaved note when the drawer is closed.
                autoSaveNote(contextid);
                getBackdrop(root).then(function(backdrop) {
                    backdrop.hide();
                    return backdrop;
                }).catch(Notification.exception);
            }
        });
    };

    return {
        init: init
    };
});
