(function( $ ) {
	'use strict';
	var file_frame;
	var font;
	var title_font;
	var logo_orig_w;
	var logo_orig_h;

	$(window).load(function() {
		if($('#license_valid').length)
			$('#license_notification').remove();
	});

	$(document).ready(function(){
		$('#logo_select_button').click(function(e){
			var button = $(this);
			e.preventDefault();

			// If the media frame already exists, close it and start a new one.
			if ( file_frame ) {
				file_frame.close();
			}

			// Create the media frame.
			file_frame = wp.media.frames.file_frame = wp.media({
				title: button.data( 'uploader_title' ),
				button: {
					text: button.data( 'uploader_button_text' ),
				},
				library: {
					type: 'image'
				},
				multiple: false  // Set to true to allow multiple files to be selected
			});

			// Disable unneeded menu items
			file_frame.on( 'menu:render:default', function(view) {
				// Store our views in an object.
				var views = {};

				// Unset default menu items
				view.unset('library-separator');
				view.unset('gallery');
				view.unset('featured-image');
				view.unset('embed');

				// Initialize the views in our view object.
				view.set(views);
			});

			// When an image is selected, run a callback.
			file_frame.on( 'select', function() {
				// User clicked select button.

				var selection = file_frame.state().get('selection');

				var selects = selection.map( function( attachment ) {

					attachment = attachment.toJSON();

					// Do something with attachment.id and/or attachment.url here
					return attachment.url;
				});

				if(selects[0] != null)
				{
					$('#logo_remove_button').attr('style','');

					$('#logo').attr('src',selects[0]);
					$('#logo').load(function(){
						$('#logo').attr('style','');
						console.log('got new logo');
						logo_orig_w = this.width;
						logo_orig_h = this.height;
						var s = $('#logo_slider').attr('value');
						s = s/100.0;
						$('#logo').width(s*logo_orig_w);
						$('#logo').height(s*logo_orig_h);
					});

				}
			});

			// Finally, open the modal
			file_frame.open();
		}); // logo_select_button

		$('#bg_pattern_select_button').click(function(e){
			var button = $(this);
			e.preventDefault();

			// If the media frame already exists, close it and start a new one.
			if ( file_frame ) {
				file_frame.close();
			}

			// Create the media frame.
			file_frame = wp.media.frames.file_frame = wp.media({
				title: button.data( 'uploader_title' ),
				button: {
					text: button.data( 'uploader_button_text' ),
				},
				library: {
					type: 'image'
				},
				multiple: false  // Set to true to allow multiple files to be selected
			});

			// Disable unneeded menu items
			file_frame.on( 'menu:render:default', function(view) {
				// Store our views in an object.
				var views = {};

				// Unset default menu items
				view.unset('library-separator');
				view.unset('gallery');
				view.unset('featured-image');
				view.unset('embed');

				// Initialize the views in our view object.
				view.set(views);
			});

			// When an image is selected, run a callback.
			file_frame.on( 'select', function() {
				// User clicked select button.

				var selection = file_frame.state().get('selection');

				var selects = selection.map( function( attachment ) {

					attachment = attachment.toJSON();

					// Do something with attachment.id and/or attachment.url here
					return attachment.url;
				});

				if(selects[0] != null)
				{
					$('#bg_pattern').attr('src',selects[0]);
					$('#bg_pattern_repeat_type').attr('style','');
					$('#bg_pattern_remove_button').attr('style','');
				}
			});

			// Finally, open the modal
			file_frame.open();
		}); // bg_pattern_select_button


		$('#logo_remove_button').click(function(e){
			$('#logo').attr('src','');
			$('#logo_remove_button').hide();
		});

		$('#bg_pattern_remove_button').click(function(e){
			$('#bg_pattern').attr('src','');
			$('#bg_pattern_remove_button').hide();
		});


		$('#logo').load(function(){
			console.log('logo loaded.');
			$('#logo').attr('style','');
			logo_orig_w = this.width;
			logo_orig_h = this.height;
			var s = $('#logo_slider').attr('value');
			s = s/100.0;
			$('#logo').width(s*logo_orig_w);
			$('#logo').height(s*logo_orig_h);
		});


		console.log('initiate slider');
		var value = parseInt( $('#sl1').attr('value'), 10 );
		$('#sl1_text').html(value + '%');

		$('#sl1').slider({
			change: function( event, ui ) {
				console.log(ui.value);
				$('#sl1_text').html(ui.value + '%');
				$('#sl1').attr('value', parseInt(ui.value,10));
				$('#greeting_font_size').attr('value', parseInt(ui.value,10));
			}
		});

		value = parseInt( $('#sl2').attr('value'), 10 );
		$('#sl2_text').html(value + '%');

		$('#sl2').slider({
			change: function( event, ui ) {
				console.log(ui.value);
				$('#sl2_text').html(ui.value + '%');
				$('#sl2').attr('value', parseInt(ui.value,10));
				$('#title_font_size').attr('value', parseInt(ui.value,10));
			}
		});

		value = parseInt( $('#logo_slider').attr('value'), 10 );
		$('#logo_slider_text').html(value + '%');

		$('#logo_slider').slider({
			change: function( event, ui ) {
				console.log(ui.value);
				$('#logo_slider_text').html(ui.value + '%');
				$('#logo_slider').attr('value', parseInt(ui.value,10));
				$('#logo_size').attr('value', parseInt(ui.value,10));
				if($('#logo').length > 0)
				{
					console.log('logo exists ' + value);
					var s = ui.value / 100.0;
					var l_w = logo_orig_w*s;
					var l_h = logo_orig_h*s;
					$('#logo').width(l_w);
					$('#logo').height(l_h);
				}
			}
		});


		if($('#logo').attr('src'))
			$('#logo_remove_button').attr('style','');

		if($('#bg_pattern').attr('src')){
			$('#bg_pattern_remove_button').show();
			$('#bg_pattern_repeat_type').show();
		}
		$(".submit_button").click(function(){
			console.log('submit_button');

			var user_name = $('#user_name').attr('value');
			var user_email = $('#user_email').attr('value');
			var logo = $('#logo').attr('src');
			var logo_size = $('#logo_slider').attr('value');
			var bg_pattern = $('#bg_pattern').attr('src');
			var bg_color = $('#background_color_picker').attr('value');
			var msg_to_client = $('#msg_to_client').val();
			var notify_msg_to_client = $('#notify_msg_to_client').val();
			var title_font_style = $('.font-select:first').find('span').attr('style');
			var font_style = $('.font-select:last').find('span').attr('style');  ///// re-do search
			var font_color = $('#font_color_picker').attr('value');
			var block_color = $('#block_color_picker').attr('value');
			var greeting_font_size = $('#sl1').attr('value');
			var title_font_size = $('#sl2').attr('value');
			var clr_db = '';
			var draw_divider = '';
			var disable_comments = '';
			var pagination = '';
			var ga_property_id = $('#ga_property_id').attr('value');
			var custom_css_code = $('#custom_css_code').attr('value');
			var custom_js_code = $('#custom_js_code').attr('value');
			var redirect_page = $('#redirect_page').attr('value');


			$('.submit_button').button('loading');

			var bg_pattern_repeat_type = $("#bg_pattern_selection option:selected").val();

			if($('#clear_database').is(':checked'))
				clr_db = '1';

			if($('#disable_comments').is(':checked'))
				disable_comments = '1';

			if($('#draw_divider').is(':checked'))
				draw_divider = '1';

			if($('#use_spreads').is(':checked'))
				pagination = 'spreads';
			else
				pagination = 'pages';

			var data = {
				action: 'pp_album_save_settings',
				user_name: user_name,
				user_email: user_email,
				logo: logo,
				logo_size: logo_size,
				bg_pattern: bg_pattern,
				bg_pattern_repeat_type: bg_pattern_repeat_type,
				bg_color: bg_color,
				msg_to_client: msg_to_client,
				notify_msg_to_client: notify_msg_to_client,
				title_font_style: title_font_style,
				font_style: font_style,
				font_color: font_color,
				title_font_size: title_font_size,
				greeting_font_size: greeting_font_size,
				block_color: block_color,
				clr_db: clr_db,
				disable_comments: disable_comments,
				pagination: pagination,
				draw_divider: draw_divider,
				ga_property_id: ga_property_id,
				custom_css_code: custom_css_code,
				custom_js_code: custom_js_code,
				redirect_page: redirect_page,
			};

			if (typeof font == 'string' || (font instanceof String))
				data.font = font;

			if (typeof title_font == 'string' || (font instanceof String))
				data.title_font = title_font;

			setTimeout(function () {
				$('.submit_button').button('reset')
			}, 1500)

			console.log(data);

			$.post( pp_album_review_admin_settings.ajaxurl, data)
			.done(function(data){
				console.log("settings posted via AJAX");
				console.log(data);
				var status  = $(data).find('response_data').text();
				var message = $(data).find('supplemental message').text();
				if( status == 'success')
				{
					console.log("modal_user_comments success: " + message);
				} else {
					console.log("modal_user_comments failed: " + message);
				}

			});
		});


		/*
                var bg_color = $('#background_color_picker').attr('value');
                $("#background_color_picker").spectrum({
                    color: bg_color,
                    showPalette: true,
                    preferredFormat: "hex6",
                    showInput: true,
                    change: function(color) {
                        $("#background_color_picker").attr('value', color);
                    }
                });

                var font_color = $('#font_color_picker').attr('value');
                $("#font_color_picker").spectrum({
                    color: font_color,
                    showPalette: true,
                    preferredFormat: "hex6",
                    showInput: true,
                    change: function(color) {
                        $("#font_color_picker").attr('value', color);
                    }
                });

                var block_color = $('#block_color_picker').attr('value');
                $("#block_color_picker").spectrum({
                    color: block_color,
                    showPalette: true,
                    preferredFormat: "hex6",
                    showInput: true,
                    change: function(color) {
                        $("#block_color_picker").attr('value', color);
                    }
                });*/
/*
		$('#title-font').fontselect().change(function(){
			// replace + signs with spaces for css
			title_font = $(this).val();
		});

		$('#font').fontselect().change(function(){
			// replace + signs with spaces for css
			font = $(this).val();
		});*/

/*
		$('#user_email').valid(
			{
				debug: true,
				rules: {
					user_email: {
					}
				},
				messages: {
					client_email: {
						multiemail: "You must enter a valid email, or comma separate multiple"
					}
				},
				submitHandler: function(form) {
					return false;
				}
			});
*/
/*

		$('#logo_select_button').qtip2({
			content:
			{
				title: "Logo",
				text: "You can upload your logo as .jpg, .gif, or .png for the Header Block shown below. Tip: use a .png logo to allow a more seamless look with the background. <br>" + $('#logo_img1').html(),
				button: true,
			},
			position: {
				target: 'mouse', // Track the mouse as the positioning target
				adjust: { x: 5, y: 5 },// Offset it slightly from under the mouse
				my: 'center left',  // Position my top left...
				at: 'right center', // at the bottom right of...
			},
			show: {
				effect: function() {
					$(this).fadeTo(20, 1);
				}
			},
			style: {
				classes: 'qtip-bootstrap'
			}
		});

		$('#msg_to_client').qtip2({
			content:
			{
				text: "This is the default text that appears above the client album. Please feel free to edit the greeting below as you like!" + $('#msg_to_client_group_img1').html(),
				title: "Default Greeting Message",
				button: true,
			},
			position: {
				my: 'bottom center',  // Position my top left...
				at: 'top center', // at the bottom right of...

			},
			show: {
				effect: function() {
					$(this).fadeTo(20, 1);
				}
			},
			hide: {
				inactive: 3000,
				delay: 750,
				effect: function() {
					$(this).slideUp();
				},
			},
			style: {
				classes: 'qtip-bootstrap'
			}
		});

		$('#user_email').qtip2({
			content:
			{
				text: "Please make sure your email uses your blogs domain name to ensure reliable delivery. (e.g. info@mystudio.com if your website is http://mystudio.com) Spam filters will often mark your email as spam if the domain name does not match.",
				title: "Studio Email",
				button: true,
			},
			position: {
				my: 'bottom center',  // Position my top left...
				at: 'top center', // at the bottom right of...

			},
			show: {
				effect: function() {
					$(this).fadeTo(20, 1);
				}
			},
			hide: {
				inactive: 6000,
				delay: 750,
				effect: function() {
					$(this).slideUp();
				},
			},
			style: {
				classes: 'qtip-bootstrap'
			}
		});

		$('#title_font_group').qtip2({
			content:
			{
				text: "This sets the font for the Album Review title. <br>" + $('#title_group_img1').html(),
				title: "Title Font",
				button: true,
			},
			position: {
				target: 'mouse', // Track the mouse as the positioning target
				adjust: { x: 5, y: 5 },// Offset it slightly from under the mouse
				my: 'center left',  // Position my top left...
				at: 'right center', // at the bottom right of...
			},
			show: {
				effect: function() {
					$(this).fadeTo(20, 1);
				}
			},
			hide: {
				inactive: 3000,
				delay: 750,
				effect: function() {
					$(this).slideUp();
				},
			},
			style: {
				classes: 'qtip-bootstrap'
			}
		});



		$('#greeting_font_group').qtip2({
			content:
			{
				text: "This sets the font for the client greeting message. <br>" + $('#msg_to_client_group_img1').html(),
				title: "Default Greeting Message",
				button: false,
			},
			position: {
				target: 'mouse', // Track the mouse as the positioning target
				adjust: { x: 5, y: 5 },// Offset it slightly from under the mouse
				my: 'center left',  // Position my top left...
				at: 'right center', // at the bottom right of...
			},
			show: {
				effect: function() {
					$(this).fadeTo(20, 1);
				}
			},
			hide: {
				inactive: 3000,
				delay: 750,
				effect: function() {
					$(this).slideUp();
				},
			},
			style: {
				classes: 'qtip-bootstrap'
			}
		});

		$('#block_color_picker_group').qtip2({
			content:
			{
				text: "This sets the background for the header block. <br>" + $('#header_block_img1').html(),
				title: "Default Greeting Message",
				button: false,
			},
			position: {
				target: 'mouse', // Track the mouse as the positioning target
				adjust: { x: 5, y: 5 },// Offset it slightly from under the mouse
				my: 'center left',  // Position my top left...
				at: 'right center', // at the bottom right of...
			},
			show: {
				effect: function() {
					$(this).fadeTo(20, 1);
				}
			},
			hide: {
				inactive: 3000,
				delay: 750,
				effect: function() {
					$(this).slideUp();
				},
			},
			style: {
				classes: 'qtip-bootstrap'
			}
		});

		$('#notify_msg_to_client_group').qtip2({
			content:
			{
				text: "This is the default email template when you click the \'Notify\' button. Please feel free to edit the template below as you like!",
				title: "Default Client Notification Email Template",
				button: true,
			},
			position: {
				my: 'bottom center',  // Position my top left...
				at: 'top center', // at the bottom right of...
			},
			show: {
				effect: function() {
					$(this).fadeTo(20, 1);
				}
			},
			hide: {
				inactive: 3000,
				delay: 750,
				effect: function() {
					$(this).slideUp();
				},
			},
			style: {
				classes: 'qtip-bootstrap'
			}
		});

		$('#bg_pattern_select_button').qtip2({
			content:
			{
				text: "You can upload a background file (.png, .gif, .jpg) to be used as the background. The Background Pattern will be overlaid on top of the Background Color. <br>" + $('#bg_pattern_img1').html(),
				title: "Background Pattern",
				button: true,
			},
			position: {
				target: 'mouse', // Track the mouse as the positioning target
				adjust: { x: 5, y: 5 },// Offset it slightly from under the mouse
				my: 'center left',  // Positpngion my top left...
				at: 'right center', // at the bottom right of...

			},
			show: {
				effect: function() {
					$(this).fadeTo(20, 1);
				}
			},
			hide: {
				inactive: 3000,
				delay: 750,
				effect: function() {
					$(this).slideUp();
				},
			},
			style: {
				classes: 'qtip-bootstrap'
			}
		});

		$('#ga_group').qtip2({
			content:
			{
				text: "Add your Google Analytics Web Property ID (UA-XXXXXX-YY) to track client views.  (Note: Analytics for logged in users will not be tracked.)",
				title: "Google Analytics",
				button: true,
			},
			position: {
				target: 'mouse', // Track the mouse as the positioning target
				adjust: { x: 5, y: 5 },// Offset it slightly from under the mouse
				my: 'center left',  // Position my top left...
				at: 'right center', // at the bottom right of...

			},
			show: {
				effect: function() {
					$(this).fadeTo(20, 1);
				}
			},
			hide: {
				inactive: 3000,
				delay: 750,
				effect: function() {
					$(this).slideUp();
				},
			},
			style: {
				classes: 'qtip-bootstrap'
			}
		});

		$('#custom_css_code_group').qtip2({
			content:
			{
				text: "WARNING: ADVANCED USERS ONLY.  You may enter custom CSS code here to further customize the look and feel of your album reviews.",
				title: "Custom CSS Code",
				button: true,
			},
			position: {
				target: 'mouse', // Track the mouse as the positioning target
				adjust: { x: 5, y: 5 },// Offset it slightly from under the mouse
				my: 'center left',  // Position my top left...
				at: 'right center', // at the bottom right of...

			},
			show: {
				effect: function() {
					$(this).fadeTo(20, 1);
				}
			},
			hide: {
				inactive: 3000,
				delay: 750,
				effect: function() {
					$(this).slideUp();
				},
			},
			style: {
				classes: 'qtip-bootstrap'
			}
		});

		$('#custom_js_code_group').qtip2({
			content:
			{
				text: "WARNING: ADVANCED USERS ONLY.  You may enter custom Javascript code here to further customize the behavior your album reviews.",
				title: "Custom Javascript Code",
				button: true,
			},
			position: {
				target: 'mouse', // Track the mouse as the positioning target
				adjust: { x: 5, y: 5 },// Offset it slightly from under the mouse
				my: 'center left',  // Position my top left...
				at: 'right center', // at the bottom right of...

			},
			show: {
				effect: function() {
					$(this).fadeTo(20, 1);
				}
			},
			hide: {
				inactive: 3000,
				delay: 750,
				effect: function() {
					$(this).slideUp();
				},
			},
			style: {
				classes: 'qtip-bootstrap'
			}
		});
*/


	});


})( jQuery );
