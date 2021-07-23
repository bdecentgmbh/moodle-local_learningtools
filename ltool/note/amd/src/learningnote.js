define(['jquery', 'core/modal_factory', 'core/str', 'core/fragment', 'core/modal_events', 'core/ajax', 'core/notification'],
    function($, ModalFactory, String, Fragment, ModalEvents, Ajax, notification){

    function learning_tool_note_action(contextid, params) {
        show_modal_lttool(contextid, params);
    }

    function show_modal_lttool(contextid, params) {
   
        $(".ltnoteinfo #ltnote-action").on('click', function() {
            var newnote = String.get_string('newnote', 'local_learningtools');
            
            $.when(newnote).done(function(localizedEditString) {

                ModalFactory.create({
                    title: localizedEditString + get_popout_action(),
                    type: ModalFactory.types.SAVE_CANCEL,
                    body: getnoteaction(contextid, params),
                    large: true
                }).then(function(modal){
                    modal.show();
                    modal.getRoot().on(ModalEvents.hidden, function() {
                        modal.destroy();
                    });
        
                    modal.getRoot().on( ModalEvents.save, function(e) { 				
                        e.preventDefault();
                        submitForm(modal);
                    } );
        
                    modal.getRoot().delegate('form', 'submit', function(e) {
                        e.preventDefault();	
                        submitFormData(modal, contextid, params) 			
                    });
                });
            });

        });
    }

    function get_popout_action() {
        return '<a href = "' + M.cfg.wwwroot + '/local/learningtools/ltool/note/ltnote_list.php" id="popout-action" target="_blank"><p> Pop Out </p><i class="fa fa-window-restore"></i></a>';  
    }

    function submitForm(modal) {
		modal.getRoot().find('form').submit();
	}

    function submitFormData(modal, contextid) {
		var formData = modal.getRoot().find('form').serialize();
        var notesuccess = String.get_string('successnotemessage', 'local_learningtools');
		Ajax.call([{
			methodname: 'ltool_note_save_usernote',
			args: {contextid: contextid, formdata: formData},
			done: function(response) {

				modal.hide();
			   // window.location.reload();
                $.when(notesuccess).done(function(localizedEditString){
                    notification.addNotification({
                        message: localizedEditString,
                        type: "success"
                    });
                });

                if (disappertimenotify != 0) {
                    setTimeout(function () {
                        $("span.notifications").empty();
                    }, disappertimenotify);
                }   
			},
			fail: handleFailedResponse()
		}]);  
	}

    function handleFailedResponse() {

    }

    function getnoteaction(contextid, params) {
        return Fragment.loadFragment('ltool_note', 'get_note_form', contextid, params);
    }



    return {
        init: function(contextid, params) {
            learning_tool_note_action(contextid, params);
        }
    };
});