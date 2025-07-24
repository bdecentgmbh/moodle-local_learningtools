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

define(['jquery', 'core/modal_factory', 'core/str', 'core/fragment', 'core/modal_events', 'core/ajax', 'core/notification', 'core/utils', "core/config"],
    function ($, ModalFactory, String, Fragment, ModalEvents, Ajax, notification, Utils, Config) {

    /* global ltools, ltool_note_config */

    // Store reference to print window
    var printWindow = null

    /**
     * Controls notes tool action.
     * @param {int} contextid context id
     */
    function learningToolNoteAction(contextid) {
        // Get configuration from global variable
        const params = window.ltool_note_config || {}

        showModalLttool(contextid, params);
        var sorttypefilter = document.querySelector(".ltnote-sortfilter i#notessorttype");
        if (sorttypefilter) {
            sorttypefilter.addEventListener("click", function () {
                var sorttype = this.getAttribute('data-type');
                noteSortActionPage(sorttype);
            });
        }

        // Content designer note.
        $(document).on('click', '.content-designer-learningtool-note', function (e) {
            var button = $(this);
            var itemType = button.data('itemtype');
            var itemId = button.data('itemid');
            var pageurl = button.data('pageurl');
            params.itemtype = itemType;
            params.itemid = itemId;
            params.pageurl = pageurl;
            modalshowHandler(contextid, params, true);
        });

        var noteprintblock = document.querySelector(".note-print-block");
        if (noteprintblock) {
            noteprintblock.addEventListener("click", notePrintHandler.bind(contextid, params));
        }

        const clearIcon = document.querySelector('.ltool-navigation [data-action="clearsearch"]');
        var searchinput = document.querySelector('.ltool-navigation [data-action="search"]');

        if (clearIcon) {
            clearIcon.addEventListener('click', () => {
                searchinput.value = '';
                searchinput.focus();
                clearSearch(clearIcon);
                // Load default content without search
                performSearch("", contextid, params);
            });
        }

        if (searchinput) {
            searchinput.addEventListener('input', Utils.debounce(() => {
                if (searchinput.value === '') {
                    clearSearch(clearIcon);
                    // Load default content without search
                    performSearch("", contextid, params);
                } else {
                    activeSearch(clearIcon);
                    var search = searchinput.value.trim();
                    // If you have a search function, you can call it here.
                    console.log(searchinput.value.trim());
                    performSearch(search, contextid, params);
                }
            }, 1000));
        }

    }

    /**
     * Perform search and update the notes list
     * @param {string} searchTerm The search term
     * @param {int} contextid The context ID
     * @param {object} params The parameters
     */
    function performSearch(searchTerm, contextid, params) {
        var notesContainer = document.querySelector(".ltool-notes-container, .note-list-container, .ltool-notes-grid")

        if (!notesContainer) {
            // If no container found, reload page with search parameter
            var currentUrl = new URL(window.location.href)
            currentUrl.searchParams.set("search", searchTerm)
            window.location.href = currentUrl.toString()
            return
        }

        // Show loading indicator
        notesContainer.innerHTML =
            '<div class="text-center p-4"><i class="fa fa-spinner fa-spin fa-2x"></i><p class="mt-2">Searching notes...</p></div>'

        // Prepare fragment parameters
        var fragmentParams = {
            courseid: params.course,
            search: searchTerm,
            sectionid: 0,
            activity: 0,
            filter: "",
            print: false,
        }

        // Load search results using fragment
        Fragment.loadFragment("ltool_note", "get_notes_list", contextid, fragmentParams)
            .then((html) => {
                notesContainer.innerHTML = html
                // Update URL without reloading page
                var currentUrl = new URL(window.location.href)
                currentUrl.searchParams.set("search", searchTerm)
                window.history.pushState({}, "", currentUrl.toString())
            })
            .catch((error) => {
                console.error("Search error:", error)
                notesContainer.innerHTML =
                    '<div class="alert alert-danger">Error loading search results. Please try again.</div>'
            })
    }

    /**
     * Reset the search icon and trigger the init for the block.
     *
     * @param {HTMLElement} clearIcon Our closing icon to manipulate.
     */
    const clearSearch = (clearIcon) => {
        clearIcon.classList.add('d-none');
    };

    /**
     * Change the searching icon to its' active state.
     *
     * @param {HTMLElement} clearIcon Our closing icon to manipulate.
     */
    const activeSearch = (clearIcon) => {
        clearIcon.classList.remove('d-none');
    };

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


    function modalshowHandler(contextid, params, contentDesigner = false) {
        var newnote = String.get_string('newnote', 'local_learningtools');
        $.when(newnote).done(function (localizedEditString) {
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
            }).then(function (modal) {

                modal.show();

                modal.getRoot().on(ModalEvents.hidden, function () {
                    modal.destroy();
                });

                modal.getRoot().on(ModalEvents.save, function (e) {
                    e.preventDefault();
                    $(e.target).find("button[data-action=save]").attr("disabled", true);
                    modal.getRoot().find('form').submit();
                });

                modal.getRoot().on('submit', 'form', e => {
                    e.preventDefault();
                    submitFormData(modal, contextid, params, contentDesigner);
                });

                document.querySelector("#popout-action").addEventListener('click', function () {
                    var pageurlobj = params.pageurl.split("&");
                    var pageurljson = JSON.stringify(pageurlobj);
                    var url = M.cfg.wwwroot + "/local/learningtools/ltool/note/pop_out.php?contextid=" +
                        params.contextid + "&pagetype=" + params.pagetype + "&contextlevel=" + params.contextlevel + "&course="
                        + params.course + "&user=" + params.user + "&pageurl=" + pageurljson + "&pagetitle=" + params.pagetitle
                        + "&heading=" + params.heading + "&sesskey=" + params.sesskey;
                    if (params.itemtype) {
                        url += "&itemtype=" + params.itemtype + "&itemid=" + params.itemid;
                    }
                    modal.hide();
                    window.open(url, '_blank');
                });
                return modal;
            }).catch(notification.exception);
        });
    };

    /**
     * Display the modal popup.
     * @param {int} contextid context id
     * @param {object} params notes info params
     * @return {void}
     */
    function showModalLttool(contextid, params) {

        var notesinfo = document.querySelector(".ltnoteinfo #ltnote-action");
        if (notesinfo) {
            notesinfo.addEventListener("click", function () {
                params.itemtype = '';
                params.itemid = 0;
                modalshowHandler(contextid, params);
            });
            // Hover color.
            var notehovercolor = notesinfo.getAttribute("data-hovercolor");
            var notefontcolor = notesinfo.getAttribute("data-fontcolor");
            if (notehovercolor && notefontcolor) {
                notesinfo.addEventListener("mouseover", function () {
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
    function submitFormData(modal, contextid, params, contentDesigner = false) {
        var modalform = document.querySelectorAll('.ltoolusernotes form')[0];
        var formData = new URLSearchParams(new FormData(modalform)).toString();
        var notesuccess = String.get_string('successnotemessage', 'local_learningtools');
        Ajax.call([{
            methodname: 'ltool_note_save_usernote',
            args: { contextid: contextid, formdata: formData },
            done: function (response) {
                // Insert data into notes badge.
                if (response) {
                    // Check if this is a content designer note by looking for the trigger button
                    if (contentDesigner) {
                        // Try to refresh the chapter if content designer is available
                        require(['mod_contentdesigner/elements'], function (Elements) {
                            var chapterId = params.itemid;
                            if (chapterId) {
                                Elements.removeWarning();
                                Elements.refreshContent();
                            }
                        });
                    } else {
                        var noteinfo = document.querySelector(".ltnoteinfo span");
                        if (!noteinfo.classList.contains('ticked')) {
                            noteinfo.classList.add('ticked');
                        }
                        noteinfo.innerHTML = response;
                    }
                }

                modal.hide();
                $.when(notesuccess).done(function (localizedEditString) {
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

    /**
     * Handle print action
     * @param {object} args Configuration arguments
     */
    function notePrintHandler(args) {
      // Prevent multiple print windows
      if (printWindow && !printWindow.closed) {
        printWindow.focus()
        return true
      }

      // Create URL for print page
      var printUrl = Config.wwwroot + "/local/learningtools/ltool/note/print.php"
      var params = new URLSearchParams()

      params.append("contextid", args.contextid)
      params.append("courseid", args.course || 0)
      params.append("sesskey", args.sesskey)

      // Add current filter parameters if they exist
      var currentUrl = new URL(window.location.href)
      var search = currentUrl.searchParams.get("search") || ""
      var filter = currentUrl.searchParams.get("filter") || ""
      var sectionid = currentUrl.searchParams.get("sectionid") || 0
      var activity = currentUrl.searchParams.get("activity") || 0

      if (search) params.append("search", search)
      if (filter) params.append("filter", filter)
      if (sectionid) params.append("sectionid", sectionid)
      if (activity) params.append("activity", activity)

      var fullUrl = printUrl + "?" + params.toString()

      // Open print page in new window and store reference
      printWindow = window.open(
        fullUrl,
        "printNotes",
        "width=1000,height=700,scrollbars=yes,resizable=yes,toolbar=no,location=no,status=no",
      )

      // Focus the print window
      if (printWindow) {
        printWindow.focus()
      }

      return true
    }

    function getnotescontents(contextid, params) {
        return Fragment.loadFragment('ltool_note', 'get_notes_contents', contextid, params);
    }

    return {

        init: (contextid) => {
            learningToolNoteAction(contextid);
        },
    };
});
