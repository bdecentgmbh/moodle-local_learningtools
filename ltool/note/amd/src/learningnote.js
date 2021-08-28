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
 * @package   ltool_note
 * @category  Classes - autoloading
 * @copyright 2021, bdecent gmbh bdecent.de
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery', 'core/modal_factory', 'core/str', 'core/fragment', 'core/modal_events', 'core/ajax', 'core/notification'],
    function($, ModalFactory, String, Fragment, ModalEvents, Ajax, notification){

    /**
     * Controls notes tool action.
     * @param {int} context id
     * @param {object} notes info params
     */
    function learning_tool_note_action(contextid, params) {
        show_modal_lttool(contextid, params);
        var sorttypefilter = document.querySelector(".ltnote-sortfilter i#notessorttype");
        if (sorttypefilter) {
            sorttypefilter.addEventListener("click", function() {
                var sorttype = this.getAttribute('data-type');
                note_sort_action_page(sorttype);
            });
        }
    }

    /**
     * Clean the url parameters.
     * @param {string} page url.
     * @param {string} url parameter.
     * @return {url} sort url
     */
    function removeURLParameter(url, parameter) {
        //prefer to use l.search if you have a location/link object
        var urlparts= url.split('?');
        if (urlparts.length>=2) {

            var prefix= encodeURIComponent(parameter)+'=';
            var pars= urlparts[1].split(/[&;]/g);

            //reverse iteration as may be destructive
            for (var i= pars.length; i-- > 0;) {
                //idiom for string.startsWith
                if (pars[i].lastIndexOf(prefix, 0) !== -1) {
                    pars.splice(i, 1);
                }
            }

            url= urlparts[0] + (pars.length > 0 ? '?' + pars.join('&') : "");
            return url;
        } else {
            return url;
        }
    }

    /**
     * Display the modal popup.
     * @param {int} context id
     * @param {object} notes info params
     * @return {void}
     */
    function show_modal_lttool(contextid, params) {

        var notesinfo = document.querySelector(".ltnoteinfo #ltnote-action");
        notesinfo.addEventListener("click", function() {
            var newnote = String.get_string('newnote', 'local_learningtools');

            $.when(newnote).done(function(localizedEditString) {
                // add class.
                var ltoolnotebody  = document.getElementsByTagName('body')[0];
                if (!ltoolnotebody.classList.contains('learningtool-note')) {
                    ltoolnotebody.classList.add('learningtool-note')
                }

                ModalFactory.create({
                    title: localizedEditString + get_popout_action(params),
                    type: ModalFactory.types.SAVE_CANCEL,
                    body: getnoteaction(contextid, params),
                    large: true
                }).then(function(modal){

                    modal.show();

                    modal.getRoot().on(ModalEvents.hidden, function() {
                        modal.destroy();
                    });

                    modal.getRoot().on(ModalEvents.save, function(e) {

                        e.preventDefault();
                        submitForm(modal);
                    });

                    document.querySelector("#popout-action").addEventListener('click', function() {
                        var url = M.cfg.wwwroot+"/local/learningtools/ltool/note/pop_out.php?contextid="+
                        params.contextid+"&pagetype="+params.pagetype+"&contextlevel="+params.contextlevel+
                        "&course="+params.course+"&user="+params.user+"&pageurl="+params.pageurl+"&pagetitle="+params.pagetitle
                        +"&heading="+params.heading;
                        modal.hide();
                        window.open(url, '_blank');
                    });

                    document.body.onsubmit = function (e) {
                        e.preventDefault();
                        submitFormData(modal, contextid, params)
                    };
                });
            });

        });
    }

    /**
     * Sort the notes list.
     * @param {string} sort type
     * @return {void}
     */
    function note_sort_action_page(sorttype) {

        var pageurl = window.location.href;
        pageurl = removeURLParameter(pageurl, 'sorttype');

        if(sorttype == 'asc') {
            sorttype = 'desc';
        } else if (sorttype == 'desc') {
            sorttype = 'asc';
        }

        if (pageurl.indexOf('?') > -1) {
            var para = '&';
        } else {
            var para = '?';
        }

        pageurl = pageurl+para+'sorttype='+ sorttype;
        window.open(pageurl, '_self');
    }

    /**
     * Popout url action html.
     * @param {object} notes params
     * @return {string} popout html
     */
    function get_popout_action(params) {
        var popouthtml = "<div class='popout-block'><button type='submit' id='popout-action' name='popoutsubmit'>Pop out</button><i class='fa fa-window-restore'></i></div>";
        return popouthtml;
    }

    /**
     * Submit the modal form.
     * @param {object} modal object
     */
    function submitForm(modal) {
        modal.getRoot().submit();
    }

    /**
     * Submit the modal data form.
     * @param {object} modal object
     * @param {int} context id
     * @return {void} ajax response.
     */
    function submitFormData(modal, contextid) {

        var modalform = document.querySelector('.ltoolusernotes form');
        var formData = serialize(modalform);
        var notesuccess = String.get_string('successnotemessage', 'local_learningtools');
        Ajax.call([{
            methodname: 'ltool_note_save_usernote',
            args: {contextid: contextid, formdata: formData},
            done: function(response) {
                // insert data into notes badge
                if (response) {
                    var noteinfo = document.querySelector(".ltnoteinfo span");
                    if (!noteinfo.classList.contains('ticked')) {
                        noteinfo.classList.add('ticked');
                    }
                    noteinfo.innerHTML = response;
                }

                modal.hide();
               // window.location.reload();
                $.when(notesuccess).done(function(localizedEditString){
                    notification.addNotification({
                        message: localizedEditString,
                        type: "success"
                    });
                });

                if (ltools.disappertimenotify != 0) {
                    setTimeout(function () {
                        document.querySelector("span.notifications").innerHTML = "";
                    }, ltools.disappertimenotify);
                }
            },
            fail: handleFailedResponse()
        }]);
    }

    function handleFailedResponse() {

    }

    /**
     * Submit the modal data form.
     * @param {int} contextid
     * @param {object} list of the notes params.
     */
    function getnoteaction(contextid, params) {
        params.contextid = contextid;
        if (params.pagetitle == "") {
            params.pagetitle = document.querySelector("title").innerHTML;
        }
        return Fragment.loadFragment('ltool_note', 'get_note_form', contextid, params);
    }

    /**
     * Get the form seialize data
     * @param {object} form object
     * @return {mixed} list of the form params.
     */
    function serialize(form) {
        if (!form || form.nodeName !== "FORM") {
                return;
        }
        var i, j, q = [];
        for (i = form.elements.length - 1; i >= 0; i = i - 1) {
            if (form.elements[i].name === "") {
                continue;
            }
            switch (form.elements[i].nodeName) {
                case 'INPUT':
                    switch (form.elements[i].type) {
                        case 'text':
                        case 'tel':
                        case 'email':
                        case 'hidden':
                        case 'password':
                        case 'button':
                        case 'reset':
                        case 'submit':
                            q.push(form.elements[i].name + "=" + encodeURIComponent(form.elements[i].value));
                            break;
                        case 'checkbox':
                        case 'radio':
                            if (form.elements[i].checked) {
                                    q.push(form.elements[i].name + "=" + encodeURIComponent(form.elements[i].value));
                            }
                            break;
                    }
                    break;
                    case 'file':
                    break;
                case 'TEXTAREA':
                        q.push(form.elements[i].name + "=" + encodeURIComponent(form.elements[i].value));
                        break;
                case 'SELECT':
                    switch (form.elements[i].type) {
                        case 'select-one':
                            q.push(form.elements[i].name + "=" + encodeURIComponent(form.elements[i].value));
                            break;
                        case 'select-multiple':
                            for (j = form.elements[i].options.length - 1; j >= 0; j = j - 1) {
                                if (form.elements[i].options[j].selected) {
                                        q.push(form.elements[i].name + "=" + encodeURIComponent(form.elements[i].options[j].value));
                                }
                            }
                            break;
                    }
                    break;
                case 'BUTTON':
                    switch (form.elements[i].type) {
                        case 'reset':
                        case 'submit':
                        case 'button':
                            q.push(form.elements[i].name + "=" + encodeURIComponent(form.elements[i].value));
                            break;
                    }
                    break;
                }
            }
        return q.join("&");
    }


    return {
        init: function(contextid, params) {
            learning_tool_note_action(contextid, params);
        }
    };
});