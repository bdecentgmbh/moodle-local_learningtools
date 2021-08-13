define(['jquery', 'core/str', 'core/ajax', 'core/notification'],
    function($, String, Ajax, notification){

    function learning_tool_bookmarks_action(contextid) {
    	
    	$('.ltbookmarksinfo').delegate('form#bookmarks', 'submit', function(e) {
    		e.preventDefault();
    		submitFormdata(contextid);
    	});
    }

    function submitFormdata(contextid) {
    	var formData  = $('.ltbookmarksinfo').find('form#bookmarks').serialize();
    	Ajax.call([{
			methodname: 'ltool_bookmarks_save_userbookmarks',
			args: {contextid: contextid, formdata: formData},
			done: function(response) { 
                
				notification.addNotification({
	                message: response.bookmarksmsg,
	                type: response.notificationtype
                });

                if (response.bookmarksstatus) {
                	$(".ltbookmarksinfo i").addClass('marked');
                } else {
                	$(".ltbookmarksinfo i").removeClass('marked');
                }

                if (ltools.disappertimenotify != 0) {
                    setTimeout(function () {
                        $("span.notifications").empty();
                    }, ltools.disappertimenotify);
                } 

			},
			fail: handleFailedResponse()
		}]);
    	
    }

     function handleFailedResponse() {

    }
   	return {
        init: function(contextid) {
            learning_tool_bookmarks_action(contextid);
        }
    };
});