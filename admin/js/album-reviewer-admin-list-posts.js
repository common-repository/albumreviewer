
(function($) {

    $(document).ready(function(){
        $('.view').children('a').attr('target','_blank');

        $('.notify_client_modal').each(function() {
            var post_id = $(this).attr('value');
            var client_email = $(this).attr('client_email');
            var client_name = $(this).attr('client_name');
            var studio_email = $(this).attr('studio_email');
            var studio_name = $(this).attr('studio_name');
            var notify_msg_to_client = $(this).attr('notify_msg_to_client');
            var msg_to_client = '';
            var password = $(this).attr('password');
            var link = $(this).attr('link');
            console.log(post_id + client_name + client_email);

            if(link!='')
            {
                msg_to_client = notifY_msg_to_client + "<br><br />Please visit the following link to see your album: <br><br /><a href='" +link+ "'>" + link + "</a><br><br />";
            }
            if(password!='')
            {
                msg_to_client = notifY_msg_to_client + "<br><br />Please use the following password when prompted: <br><br />" + password;
            }

            $(this).html("<a href='#myModal"+post_id+"' role='button' class='btn span2 notify_button' data-toggle='modal'>Notify " + client_name + "</a><div id='myModal"+post_id+"' class='modal hide fade' tabindex='-1' role='dialog' aria-labelledby='myModalLabel' aria-hidden='true'>\
			  <div class='modal-header'>\
			    <button type='button' class='close' data-dismiss='modal' aria-hidden='true'>×</button>\
			    <h3 id='myModalLabel'>Notify Client</h3>\
			  </div>\
			  <div class='modal-body'>\
			<form class='form-horizontal'>\
				<div class='control-group'>\
				<label class='control-label' for='toEmail'>To</label>\
			    <div class='controls'>\
			      <input class='span5' type='text' id='inputEmail' placeholder='Email' value='"
                + client_name + " <" + client_email + ">' disabled>\
			    </div>\
			    </div>\
			    <div class='control-group'>\
			    <label class='control-label' for='fromEmail'>From</label>\
			    <div class='controls'>\
			      <input class='span5' type='text' id='inputPassword' placeholder='From' value='"
                + studio_name + " <" + studio_email + ">' disabled>\
					    </div>\
					    </div>\
			    <div class='control-group'>\
			    <label class='control-label' for='user_comments'>Comments</label>\
			    <div class='controls'>\
				<div class='editable span5 msg_box' rows='10' id='modal_notify_client_text_area' contenteditable='true'>Hi "
                + client_name + ",<br>"  + notify_msg_to_client +  "<br>Thanks,<br>"
                + studio_name + "</div>\
		  	</div>\
		  	</div>\
			</form></div> <div class='modal-footer'> <button class='btn' data-dismiss='modal' aria-hidden='true'>Cancel</button>\
		  <button class='btn btn-primary send_notify_client_button' id='"+ post_id +"'>Notify Client</button>\
		</div>\
		</div>");

        });

        $('.send_notify_client_button').click(function() {

            var data = {
                action: 'pp_album_review_send_client_notification',
                post_id: $(this).attr('id'),
                body: $(this).parent().parent().parent().find('#modal_notify_client_text_area').html(),

            };

            jQuery.post( pp_album_review_admin_edit.ajaxurl, data, function(data){
                var status  = $(data).find('response_data').text();
                var message = $(data).find('supplemental message').text();
                if( status == 'success')
                {
                    console.log("modal_user_comments success: " + message);
                } else {
                    console.log("modal_user_comments failed: " + message);
                }
            });

            $('#modal_notify_client_text_area').val("");
            $('#myModal').modal('hide');
        });

        $('.mark_as_read_button').click(function(){
            $(this).prop("disabled",true);
            var post_id = $(this).attr('value');
            console.log(post_id);

            var data = {
                action: 'pp_album_review_mark_as_read',
                post_id: post_id,
            };

            jQuery.post( pp_album_review_admin_edit.ajaxurl, data, function(data){
                var status  = $(data).find('response_data').text();
                var message = $(data).find('supplemental message').text();
                if( status == 'success')
                {
                    console.log("modal_user_comments success: " + message);
                    $('#pending-comment-' + message).prev().hide();
                    $('#alert-placeholder-' + message).show();
                    $('#alert-placeholder-' + message).html('             Done          ');
                } else {
                    console.log("modal_user_comments failed: " + message);
                }
            });
        });

        $('.approved').find('i').click(function() {
            var post_id = $(this).parent().parent().find('th').find('input').attr('value');
            var my_icon = $(this);
            console.log('approved clicked ' + post_id);

            if(my_icon.hasClass('icon-minus-sign')){
                my_icon.removeClass('icon-minus-sign');
                my_icon.addClass('icon-spinner icon-spin');
                my_icon.css('color','grey');
                my_icon.attr('album_approve', '1');
            } else {
                my_icon.removeClass('icon-ok');
                my_icon.addClass('icon-spinner icon-spin');
                my_icon.css('color','grey');
                my_icon.attr('album_approve', '2');
            }


            var data = {
                action: 'pp_album_review_toggle_approve',
                post_id: post_id,
            };

            jQuery.post( pp_album_review_admin_edit.ajaxurl, data, function(data){
                var status  = $(data).find('response_data').text();
                var message = $(data).find('supplemental message').text();
                if( status == 'success')
                {
                    console.log("modal_user_comments success: " + message);
                    console.log(my_icon.attr('album_approve'));
                    if(my_icon.attr('album_approve') === '1'){
                        my_icon.removeClass('icon-spinner');
                        my_icon.removeClass('icon-spin');
                        my_icon.addClass('icon-ok');
                        my_icon.css('color','green');
                    } else {
                        my_icon.removeClass('icon-spinner');
                        my_icon.removeClass('icon-spin');
                        my_icon.addClass('icon-minus-sign');
                        my_icon.css('color','red');
                    }

                    var text;
                    /*
                     $(my_icon).qtip2('destroy');
                     */
                    if($(my_icon).hasClass('icon-ok'))
                        text = "Click to unapprove album."
                    else
                        text = "Click to approve album."


                    /*                    $(my_icon).qtip2({
                     content:
                     {
                     text: text,
                     button: false,
                     },
                     position: {
                     target: 'mouse', // Track the mouse as the positioning target
                     adjust: { x: 0, y: -5 },// Offset it slightly from under the mouse
                     my: 'bottom center',  // Position my top left...
                     at: 'top center', // at the bottom right of...
                     },
                     style: {
                     classes: 'qtip-dark'
                     }
                     });*/

                } else {
                    console.log("modal_user_comments failed: " + message);
                }
            });
        });

        $('.approved').find('i').each(function() {
            var text;
            if($(this).hasClass('icon-ok'))
                text = "Click to unapprove album."
            else
                text = "Click to approve album."


            /*            $(this).qtip2({
             content:
             {
             text: text,
             button: false,
             },
             position: {
             target: 'mouse', // Track the mouse as the positioning target
             adjust: { x: 0, y: -5 },// Offset it slightly from under the mouse
             my: 'bottom center',  // Position my top left...
             at: 'top center', // at the bottom right of...
             },
             style: {
             classes: 'qtip-dark'
             }
             });*/
        });


    });

})(jQuery);