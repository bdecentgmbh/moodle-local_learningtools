define(['jquery', 'core/str'], function($, str) {

    function learning_tools_action(viewbookmarks, viewnote) {

        //add fab button
        $(fabbuttonhtml).insertBefore("footer");
        $(".floating-button #tool-action-button").on('click', function(){
            if ($(".list-learningtools").hasClass('show')) {
                $(".list-learningtools").removeClass('show');
            } else{
                $(".list-learningtools").addClass('show');
            }
        });
    
        if (disappertimenotify != 0) {
            setTimeout(function () {
                $("span.notifications").empty();
            }, disappertimenotify);
        } 
        /*<a href="http://localhost/moodle/moodle-311/grade/report/overview/index.php" class="dropdown-item menu-action" role="menuitem" data-title="grades,grades" aria-labelledby="actionmenuaction-3">
                                <i class="icon fa fa-table fa-fw " aria-hidden="true"></i>
                                <span class="menu-action-text" id="actionmenuaction-3">Grades</span>
                        </a>*/
        var bookmarkstring = str.get_string('bookmarks', 'local_learningtools');
        $.when(bookmarkstring).done(function(bookmarkstr) {
            var bookmarkhtml = '<a href="' + M.cfg.wwwroot + '/local/learningtools/ltool/bookmarks/ltbookmarks_list.php" class="dropdown-item menu-action"'+ 
            'role="menuitem" data-title="bookmark,bookmarks" aria-labelledby="actionmenuaction-custom" >' + 
            '<i class="icon fa fa-bookmark" aria-hidden="true"></i> <span class="menu-action-text" id="actionmenuaction-custom">'+ bookmarkstr +'</span>';
            if (viewbookmarks) {
                 $(bookmarkhtml).insertAfter(".usermenu .dropdown-menu a:nth-child(4)");
            }
        });

        var notestring = str.get_string('note', 'local_learningtools');
        $.when(notestring).done(function(notestr) {

            var notehtml = '<a href="' + M.cfg.wwwroot + '/local/learningtools/ltool/note/ltnote_list.php" class="dropdown-item menu-action"'+
            'role="menuitem" data-title="note,notes" aria-labelledby="actionmenuaction-custom" >' + 
            '<i class="icon fa fa-sticky-note" aria-hidden="true"></i> <span class="menu-action-text" id="actionmenuaction-custom">'+ notestr +'</span>';

            if (viewnote) {
                $(notehtml).insertAfter(".usermenu .dropdown-menu a:nth-child(5)");
            }
        });
        //console.log(val);
    }

    return {
        init: function(viewbookmarks, viewnote) {
            learning_tools_action(viewbookmarks, viewnote);
        }
    };
});