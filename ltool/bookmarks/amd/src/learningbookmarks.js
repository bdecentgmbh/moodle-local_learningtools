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
 * Bookmarks ltool define js.
 * @package   ltool_bookmarks
 * @category  Classes - autoloading
 * @copyright 2021, bdecent gmbh bdecent.de
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['core/str', 'core/ajax', 'core/notification'],
    function(String, Ajax, notification){


    /**
     * Controls bookmarks tool action.
     * @param {int} context id
     * @param {object} bookmarks info params
     */
    function learning_tool_bookmarks_action(contextid, params) {

        var bookmarkmarked = document.getElementById('bookmarks-marked');
        if (bookmarkmarked) {
            if (pagebookmarks) {
                bookmarkmarked.classList.add('marked');
            } else {
                bookmarkmarked.classList.remove('marked');
            }
        }

        var bookmarksform = document.getElementById('ltbookmarks-action');
        if (bookmarksform) {
            bookmarksform.addEventListener("click", function(e) {
                e.preventDefault();
                submitFormdata(contextid, params);
            });
        }
        var bookmarkssorttype = document.getElementById("bookmarkssorttype");

        if (bookmarkssorttype) {
            bookmarkssorttype.addEventListener("click", function() {
                var sorttype = this.getAttribute("data-type");
                bookmarks_sort_action_page(sorttype);
            });
        }

    }

    /**
     * Sort the bookmarks list.
     * @param {string} sort type
     * @return {void}
     */
    function bookmarks_sort_action_page(sorttype) {

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
     * Bookmarks submit the form data.
     * @param {int} context id.
     * @param {object} form instance data.
     * @return {void} ajax response
     */
    function submitFormdata(contextid, formData) {

        if (formData.pagetitle == "")  {
            formData.pagetitle = document.querySelector('title').innerHTML;
        }
        var Formdata = JSON.stringify(formData);
        Ajax.call([{
            methodname: 'ltool_bookmarks_save_userbookmarks',
            args: {contextid: contextid, formdata: Formdata},
            done: function(response) {

                notification.addNotification({
                    message: response.bookmarksmsg,
                    type: response.notificationtype
                });

                let bookmarkmarked = document.getElementById('bookmarks-marked');
                if (response.bookmarksstatus) {
                    bookmarkmarked.classList.add('marked');
                } else {
                    bookmarkmarked.classList.remove('marked');
                }

                if (ltools.disappertimenotify != 0) {
                    setTimeout(function () {
                        var notifications = document.querySelector("span.notifications").innerHTML = "";
                    }, ltools.disappertimenotify);
                }

            },
            fail: handleFailedResponse()
        }]);
    }


    function handleFailedResponse() {

    }
    return {
        init: function(contextid, params) {
            learning_tool_bookmarks_action(contextid, params);
        }
    };
});
