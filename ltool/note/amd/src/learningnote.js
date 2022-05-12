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
 * Notes ltool define js.
 * @module   ltool_note
 * @category  Classes - autoloading
 * @copyright 2021, bdecent gmbh bdecent.de
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery', 'core/modal_factory', 'core/str', 'core/fragment', 'core/modal_events', 'core/ajax', 'core/notification'],
    function($, ModalFactory, String, Fragment, ModalEvents, Ajax, notification) {

    /* global ltools */

    /**
     * Controls notes tool action.
     * @param {int} contextid context id
     * @param {object} params notes info params
     */
    function learningToolNoteAction(contextid, params) {
        showModalLttool(contextid, params);
        var sorttypefilter = document.querySelector(".ltnote-sortfilter i#notessorttype");
        if (sorttypefilter) {
            sorttypefilter.addEventListener("click", function() {
                var sorttype = this.getAttribute('data-type');
                noteSortActionPage(sorttype);
            });
        }
    }

    /**
     * Clean the url parameters.
     * @param {string} url page url.
     * @param {string} parameter url parameter.
     * @return {url} sort url
     */
    function removeURLParameter(url, parameter) {
        // Prefer to use l.search if you have a location/link object.
        var urlparts = url.split('?');
        if (urlparts.length >= 2) {

            var prefix = encodeURIComponent(parameter) + '=';
            var pars = urlparts[1].split(/[&;]/g);

            // Reverse iteration as may be destructive.
            for (var i = pars.length; i-- > 0;) {
                // Idiom for string.startsWith.
                if (pars[i].lastIndexOf(prefix, 0) !== -1) {
                    pars.splice(i, 1);
                }
            }

            url = urlparts[0] + (pars.length > 0 ? '?' + pars.join('&') : "");
            return url;
        } else {
            return url;
        }
    }

    /**
     * Display the modal popup.
     * @param {int} contextid context id
     * @param {object} params notes info params
     * @return {void}
     */
    function showModalLttool(contextid, params) {

        var notesinfo = document.querySelector(".ltnoteinfo #ltnote-action");
        if (notesinfo) {
            notesinfo.addEventListener("click", function() {
                var newnote = String.get_string('newnote', 'local_learningtools');

                $.when(newnote).done(function(localizedEditString) {
                    // Add class.
                    var ltoolnotebody = document.getElementsByTagName('body')[0];
                    if (!ltoolnotebody.classList.contains('learningtool-note')) {
                        ltoolnotebody.classList.add('learningtool-note');
                    }

                    ModalFactory.create({
                        title: localizedEditString + getPopoutAction(),
                        type: ModalFactory.types.SAVE_CANCEL,
                        body: getnoteaction(contextid, params),
                        large: true
                    }).then(function(modal) {

                        modal.show();

                        modal.getRoot().on(ModalEvents.hidden, function() {
                            modal.destroy();
                        });

                        modal.getRoot().on(ModalEvents.save, function(e) {
                            e.preventDefault();
                            $(e.target).find("button[data-action=save]").attr("disabled", true);
                            modal.getRoot().find('form').submit();
                        });

                        modal.getRoot().on('submit', 'form', e => {
                            e.preventDefault();
                            submitFormData(modal, contextid);
                        });

                        document.querySelector("#popout-action").addEventListener('click', function() {
                            var pageurlobj = params.pageurl.split("&");
                            var pageurljson = JSON.stringify(pageurlobj);
                            var url = M.cfg.wwwroot + "/local/learningtools/ltool/note/pop_out.php?contextid=" +
                            params.contextid + "&pagetype=" + params.pagetype + "&contextlevel=" + params.contextlevel + "&course="
                            + params.course + "&user=" + params.user + "&pageurl=" + pageurljson + "&pagetitle=" + params.pagetitle
                            + "&heading=" + params.heading + "&sesskey=" + params.sesskey;
                            modal.hide();
                            window.open(url, '_blank');
                        });
                        return modal;
                    }).catch(notification.exception);
                });

            });
            // Hover color.
            var notehovercolor = notesinfo.getAttribute("data-hovercolor");
            var notefontcolor = notesinfo.getAttribute("data-fontcolor");
            if (notehovercolor && notefontcolor) {
                notesinfo.addEventListener("mouseover", function() {
                    document.querySelector('#ltnoteinfo p').style.background = notehovercolor;
                    document.querySelector('#ltnoteinfo p').style.color = notefontcolor;
                });
            }
        }
    }

    /**
     * Sort the notes list.
     * @param {string} sorttype sort type
     * @return {void}
     */
    function noteSortActionPage(sorttype) {

        var pageurl = window.location.href;
        pageurl = removeURLParameter(pageurl, 'sorttype');

        if (sorttype == 'asc') {
            sorttype = 'desc';
        } else if (sorttype == 'desc') {
            sorttype = 'asc';
        }
        var para = '';
        if (pageurl.indexOf('?') > -1) {
            para = '&';
        } else {
            para = '?';
        }

        pageurl = pageurl + para + 'sorttype=' + sorttype;
        window.open(pageurl, '_self');
    }

    /**
     * Popout url action html.
     * @return {string} popout html
     */
    function getPopoutAction() {
        var popouthtml = "<div class='popout-block'><button type='submit' id='popout-action'"
        + "name='popoutsubmit'>Pop out</button> <i class='fa fa-window-restore'></i></div>";
        return popouthtml;
    }

    /**
     * Submit the modal data form.
     * @param {object} modal object
     * @param {int} contextid context id
     * @return {void} ajax respoltoolsnse.
     */
    function submitFormData(modal, contextid) {

        var modalform = document.querySelectorAll('.ltoolusernotes form')[0];
        var formData = new URLSearchParams(new FormData(modalform)).toString();
        var notesuccess = String.get_string('successnotemessage', 'local_learningtools');
        Ajax.call([{
            methodname: 'ltool_note_save_usernote',
            args: {contextid: contextid, formdata: formData},
            done: function(response) {
                // Insert data into notes badge.
                if (response) {
                    var noteinfo = document.querySelector(".ltnoteinfo span");
                    if (!noteinfo.classList.contains('ticked')) {
                        noteinfo.classList.add('ticked');
                    }
                    noteinfo.innerHTML = response;
                }

                modal.hide();
                $.when(notesuccess).done(function(localizedEditString) {
                    notification.addNotification({
                        message: localizedEditString,
                        type: "success"
                    });
                });

                if (ltools.disappertimenotify != 0) {
                    setTimeout(function() {
                        document.querySelector("span.notifications").innerHTML = "";
                    }, ltools.disappertimenotify);
                }
            },
        }]);
    }

    /**
     * Submit the modal data form.
     * @param {int} contextid
     * @param {object} params list of the notes params.
     * @return {string} displayed the note editor form
     */
    function getnoteaction(contextid, params) {
        params.contextid = contextid;
        if (params.pagetitle == "") {
            params.pagetitle = document.querySelector("title").innerHTML;
        }
        return Fragment.loadFragment('ltool_note', 'get_note_form', contextid, params);
    }
    return {
        init: function(contextid, params) {
            learningToolNoteAction(contextid, params);
        }
    };
});