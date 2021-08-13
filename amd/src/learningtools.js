define(['jquery', 'core/str'], function($, str) {
    /**
     * Controls learningtools action.
     * @param {bool} viewbookmarks 
     * @param {bool} viewnote 
     * 
     */
    function learning_tools_action(viewbookmarks, viewnote, loggedin) {
        // Add fab button.
        if (loggedin) {
            $(fabbuttonhtml).insertBefore("footer");
        }

        $(".floating-button #tool-action-button").on('click', function(){
            if ($(".list-learningtools").hasClass('show')) {
                $(".list-learningtools").removeClass('show');
            } else{
                $(".list-learningtools").addClass('show');
            }
        });

        $(".sort-block i#bookmarkssorttype").on('click', function() {
            var sorttype = $(this).attr('data-type');
            sort_action_page(sorttype);

        });


         $(".ltnote-sortfilter i#notessorttype").on('click', function() {
            var sorttype = $(this).attr('data-type');
            sort_action_page(sorttype);
        });

        if (ltools.pagebookmarks) {
            $(".ltbookmarksinfo i").addClass('marked');
        } else {
            $(".ltbookmarksinfo i").removeClass('marked');
        }

        // trigger button

        if (ltools.notestrigger == 'trigger') {
            setTimeout(function(){ 
                 $('#ltnoteinfo #ltnote-action').trigger('click');
            }, 3000);
        }
    
    }

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

            // alert(parameter);
            url= urlparts[0] + (pars.length > 0 ? '?' + pars.join('&') : "");
            return url;
        } else {
            return url;
        }
    }

    function sort_action_page(sorttype) {

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

    return {
        init: function(viewbookmarks, viewnote, loggedin) {
            learning_tools_action(viewbookmarks, viewnote, loggedin);
        }
    };
});