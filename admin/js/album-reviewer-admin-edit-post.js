// Uploading files

var file_frame;

function scaleSizeWH(maxW, maxH, currW, currH) {
    var ratio = currW / currH;
    if (ratio > 1) {
        currW = maxW;
        currH = currW / ratio;
    } else {
        currH = maxH;
        currW = currH / ratio;
    }
    return [currW, currH];
}

function scaleSize(scale, minW, minH, maxW, maxH, currW, currH) {
    var outW = scale * currW;
    var outH = scale * currH;
    if (outW > maxW) {
        outW = maxW;
        outH = outW / currW * currH;
    }
    if (outH > maxH) {
        outH = maxH;
        outW = outH / currH * currW;
    }
    if (outW < minW) {
        outW = minW;
        outH = outW / currW * currH;
    }
    if (outH < minH) {
        outH = minH;
        outW = outH / currH * currW;
    }

    return [outW, outH];
}

function RemoveLastDirectoryPartOf(the_url, post_id) {
    var the_arr = the_url.split('/');
    the_arr.pop();
    the_arr.push('post.php?post=' + post_id + '&action=edit');
    return ( the_arr.join('/') );
}

function srt(on, descending) {
    on = on && on.constructor === Object ? on : {};
    return function (a, b) {
        if (on.string || on.key) {
            a = on.key ? a[on.key] : a;
            a = on.string ? String(a).toLowerCase() : a;
            b = on.key ? b[on.key] : b;
            b = on.string ? String(b).toLowerCase() : b;
            // if key is not present, move to the end
            if (on.key && (!b || !a)) {
                return !a && !b ? 1 : !a ? 1 : -1;
            }
        }
        return descending ? ~~(on.string ? b.localeCompare(a) : a < b)
            : ~~(on.string ? a.localeCompare(b) : a > b);
    };
}

(function ($) {
    $(document).ready(function () {

        function renderPages( data ){
            var source = $("#album-pages").html();
            var template = Handlebars.compile( source );
            var context = { pages : data.result };

            var html = template( context );

//            console.log(html);
//            $('#result').html( html );
            $('#result-album-pages').html( html);
        }

        function get_sorted_IDs() {
            var my_sorted_ids = [];
            $(".album_thumbs").each(function (i, el) {
                var at = $(el).attr('src');

                var id = $(el).next('.name').attr('value');
                my_sorted_ids.push({'file': at, 'id': id});
            });
            return my_sorted_ids;
        }

        Handlebars.registerHelper('list', function(items, options) {
            var out = "<div class='album-page-table'>\n";

            for(var i=0, l=items.length; i<l; i++) {
                out += "<div class='album-page-row row'>\n" +
                            options.fn(items[i]) +
                        "</div>\n";
            }

            return out + "</div>\n";
        });

        function post_album_spreads_cb() {
            if ($('#notify_client_button').length == 0)
                attach_client_notify();

            if (data.page_type == 'spreads') {
                // remove upload pages button
                $('#upload_page_button').remove();
                $('#upload_txt').remove();
            } else if (data.page_type == 'pages') {
                $('#upload_spread_button').remove();
                $('#upload_txt').remove();
            }

            // need to bind delete button.
            $('.delete').on('click', function () {
                console.log('delete button pressed' + $('#post_ID').attr('value') + ' ' + $(this).attr('value'));

                var data = {
                    action: 'pp_album_review_delete_page',
                    post_id: $('#post_ID').attr('value'),
                    page_id: $(this).attr('value'),
                };

                var current_url = location.href;
                console.log(current_url);

                var new_url = RemoveLastDirectoryPartOf(current_url, $('#post_ID').attr('value'));
                console.log(current_url);

                jQuery.post(
                    pp_album_review_admin.ajaxurl,
                    data, function () {
                        console.log('delete done');
                        location.replace(new_url);
                    }
                );

            });

            // need to bind replace button.
            $('.replace').on('click', function (e) {
                var the_page_id = $(this).attr('value');
                e.preventDefault();

                // Create the media frame.
                file_frame = wp.media.frames.file_frame = wp.media({
                    title: jQuery(this).data('uploader_title'),
                    button: {
                        text: jQuery(this).data('uploader_button_text'),
                    },
                    multiple: false  // Set to true to allow multiple files to be selected
                });

                // When an image is selected, run a callback.
                file_frame.on('select', function () {
                    // User clicked select button.

                    var selection = file_frame.state().get('selection');

                    var selects = selection.map(function (attachment) {

                        attachment = attachment.toJSON();

                        // Do something with attachment.id and/or attachment.url here
                        return attachment.url;
                    });

                    // AJAX code goes here.
                    var data = {
                        action: 'pp_album_review_revise_page',
                        post_id: $('#post_ID').attr('value'),
                        page: selects,
                        page_id: the_page_id
                    };

                    var current_url = location.href;
                    var new_url = RemoveLastDirectoryPartOf(current_url, $('#post_ID').attr('value'));

                    jQuery.post(
                        pp_album_review_admin.ajaxurl,
                        data, function () {
                            console.log('replace done');
                            location.replace(new_url);
                        }
                    );

                });

                // Finally, open the modal
                file_frame.open();
            });

            /*
             $('#sort-me').sortable({
             stop: function (event, ui) {
             var listOrder = "";
             var data = {
             action: 'pp_album_review_update_page_nums',
             page_ids: []
             };

             $(".name").each(function (i, el) {
             var p = $(el).attr("value");
             data.page_ids.push(p);
             });

             console.log(data.page_ids);
             $('#sort-me').sortable();

             jQuery.post(
             pp_album_review_admin.ajaxurl,
             data, function () {
             console.log('sorted list saved');

             });
             }
             });
             $('#sort-me').disableSelection();

             // page delete checkboxes
             $('.deletebox').click(function () {
             console.log('checkbox');
             attach_delete_page();
             });

             // create the checkbox range selector, with a callback function
             //                        $('input[name="deletebox"]').createCheckboxRange();

             attach_thumb_resize();
             */
        }

        function post_album_spreads(data) {
            jQuery.post(
                pp_album_review_admin.ajaxurl,
                data,
                function (data) {
                    var status = $(data).find('response_data').text();

                    console.log(data);
                    if (data.data == 'success') {

                        // kickoff jquery tmpl to update results
//                        $('#result').html(tmpl("tmpl-demo", data));

                        renderPages( data , function(){
                            post_album_spreads_cb();
                        });



                    } else {
                        $('#result').hide();
                    }

                } // callback
            );	// post
        } // post_album_spreads

        jQuery.validator.addMethod(
            "multiemail",
            function (value, element) {
                var email = value.split(/[;,]+/); // split element by , and ;
                valid = true;
                for (var i in email) {
                    value = email[i];
                    valid = valid && jQuery.validator.methods.email.call(this, $.trim(value), element);
                }
                return valid;
            },
            "You must enter a valid email, or multiple coma-separated emails (e.g. 1234@asdf.com,abcd@xyz.com)"
        );
        /*
         $('#client_email').valid(
         {
         debug: true,
         rules: {
         client_email: {
         multiemail: true
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
         });*/

        $('#logo_select_button').click(function (e) {
            var button = $(this);
            e.preventDefault();

            // If the media frame already exists, close it and start a new one.
            if (file_frame) {
                file_frame.close();
            }

            // Create the media frame.
            file_frame = wp.media.frames.file_frame = wp.media({
                title: button.data('uploader_title'),
                button: {
                    text: button.data('uploader_button_text'),
                },
                library: {
                    type: 'image'
                },
                multiple: false  // Set to true to allow multiple files to be selected
            });

            // Disable unneeded menu items
            file_frame.on('menu:render:default', function (view) {
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
            file_frame.on('select', function () {
                // User clicked select button.

                var selection = file_frame.state().get('selection');

                var selects = selection.map(function (attachment) {

                    attachment = attachment.toJSON();

                    // Do something with attachment.id and/or attachment.url here
                    return attachment.url;
                });

                if (selects[0] != null) {
                    $('#post_logo').attr('value', selects[0]);
                    $('#logo').attr('src', selects[0]);
                    $('#logo_remove_button').attr('style', '');
                }
            });

            // Finally, open the modal
            file_frame.open();
        }); // logo_select_button

        $('#bg_pattern_select_button').click(function (e) {
            var button = $(this);
            e.preventDefault();

            // If the media frame already exists, close it and start a new one.
            if (file_frame) {
                file_frame.close();
            }

            // Create the media frame.
            file_frame = wp.media.frames.file_frame = wp.media({
                title: button.data('uploader_title'),
                button: {
                    text: button.data('uploader_button_text'),
                },
                library: {
                    type: 'image'
                },
                multiple: false  // Set to true to allow multiple files to be selected
            });

            // Disable unneeded menu items
            file_frame.on('menu:render:default', function (view) {
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
            file_frame.on('select', function () {
                // User clicked select button.

                var selection = file_frame.state().get('selection');

                var selects = selection.map(function (attachment) {

                    attachment = attachment.toJSON();

                    // Do something with attachment.id and/or attachment.url here
                    return attachment.url;
                });

                if (selects[0] != null) {
                    $('#post_bg_pattern').attr('value', selects[0]);
                    $('#bg_pattern').attr('src', selects[0]);
                    $('#bg_pattern_repeat_type').attr('style', '');
                    $('#bg_pattern_remove_button').attr('style', '');
                }
            });

            // Finally, open the modal
            file_frame.open();
        }); // bg_pattern_select_button


        $('#upload_spread_button').click(function (e) {
            console.log("upload_spread_button clicked");
            var button = $(this);
            e.preventDefault();

            // If the media frame already exists, reopen it.
            if (file_frame) {
                file_frame.open();
                return;
            }

            // Create the media frame.
            file_frame = wp.media.frames.file_frame = wp.media({
                title: button.data('uploader_title'),
                button: {
                    text: button.data('uploader_button_text'),
                },
                multiple: true  // Set to true to allow multiple files to be selected
            });

            file_frame.on('menu:render:default', function (view) {
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
            file_frame.on('select', function () {
                // User clicked select button.

                var selection = file_frame.state().get('selection');
                var postId = $('#post_ID').val();

                var attachments = selection.map(function (attachmentRaw) {
                    var attachment = attachmentRaw.toJSON();
                    console.log(attachment);

                    // Do something with attachment.id and/or attachment.url here
                    return {
                        url : attachment.url,
                        attachmentId : attachment.id,
                        filename : attachment.filename,
                        height : attachment.height,
                        width : attachment.width
                    };
                });

                console.log(attachments);

                var data = {
                    action: 'pp_album_review_post_pages',
                    post_id: postId,
                    attachments: attachments,
                    page_type: 'spreads'
                };
                console.log(data);

                // after user clicks select we have user selections.
                // post the selections to save via AJAX.
                post_album_spreads(data);
            });

            // Finally, open the modal
            file_frame.open();
        });

        $('#upload_page_button').click(function (e) {
            console.log("upload_spread_button clicked");
            var button = $(this);
            e.preventDefault();

            // If the media frame already exists, reopen it.
            if (file_frame) {
                file_frame.open();
                return;
            }

            // Create the media frame.
            file_frame = wp.media.frames.file_frame = wp.media({
                title: button.data('uploader_title'),
                button: {
                    text: button.data('uploader_button_text'),
                },
                multiple: true  // Set to true to allow multiple files to be selected
            });

            file_frame.on('menu:render:default', function (view) {
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
            file_frame.on('select', function () {
                // User clicked select button.

                var selection = file_frame.state().get('selection');

                var selects = selection.map(function (attachment) {

                    attachment = attachment.toJSON();

                    // Do something with attachment.id and/or attachment.url here
                    return attachment.url;
                });

                console.log(selects);

                var data = {
                    action: 'pp_album_review_post_pages',
                    post_id: $('#post_ID').attr('value'),
                    attachments: selects,
                    page_type: 'pages'
                };
                console.log(data);

                // after user clicks select we have user selections.
                // post the selections to save via AJAX.
                post_album_spreads(data);
            });

            // Finally, open the modal
            file_frame.open();
        });

        $('#logo_remove_button').click(function (e) {
            $('#logo').attr('src', '');
            $('#post_logo').attr('value', '');
            $('#logo_remove_button').hide();
        });

        $('#bg_pattern_remove_button').click(function (e) {
            $('#bg_pattern').attr('src', '');
            $('#post_bg_pattern').attr('value', '');
            $('#bg_pattern_remove_button').hide();
        });

        $("#bg_pattern_selection").change(function () {
            $("#bg_pattern_selection option:selected").each(function () {
                $('#post_bg_pattern_repeat_type').val($(this).val());
            });
        }).trigger("change");

        $('#sort_filename_ascend, #sort_filename_descend').click(function (e) {
            var sorted_IDs = get_sorted_IDs();

            if ($(this).attr('id') === 'sort_filename_ascend')
                sorted_IDs = sorted_IDs.sort(srt({key: 'file', string: true}));
            else
                sorted_IDs = sorted_IDs.sort(srt({key: 'file', string: true}, true));
            console.log(sorted_IDs);

            $('#parent_sort_btn').html('Reloading page. Pease wait.');

            var i, page_ids = [];

            for (i = 0; i < sorted_IDs.length; i++) {
                page_ids.push(sorted_IDs[i].id);
            }

            var data = {
                action: 'pp_album_review_update_page_nums',
                page_ids: page_ids
            };

            console.log(data.page_ids);

            jQuery.post(
                pp_album_review_admin.ajaxurl,
                data, function () {
                    console.log('sorted list saved');
                    location.reload();
                }
            );

        });


        $('#pp_album_review_album_meta').removeClass('postbox').addClass('stuffbox');
        $('#pp_album_review_album_meta').children('.handlediv').remove();
        $('#pp_album_review_album_meta').children('.hndle').attr('style', 'background-color:#2e2e2e;color:white;');


        $('#pp_album_review_page_meta').removeClass('postbox').addClass('stuffbox');
        $('#pp_album_review_page_meta').children('.handlediv').remove();
        $('#pp_album_review_page_meta').children('.hndle').attr('style', 'background-color:#2e2e2e;color:white;');

        $('#view-post-btn').children().attr('target', '_blank');

        if ($('#logo').attr('src'))
            $('#logo_remove_button').attr('style', '');

        if ($('#bg_pattern').attr('src')) {
            $('#bg_pattern_remove_button').show();
            $('#bg_pattern_repeat_type').attr('style', '');
        }

        console.log('initiate slider');
        var value = parseInt($('#sl1').attr('value'), 10);
        $('#sl1_text').html(value + '%');

        /*        $('#sl1').slider({
         value: value,
         range: "min",
         animate: false,
         orientation: "horizontal",
         max: 200,
         min: 25,
         change: function( event, ui ) {
         console.log(ui.value);
         $('#sl1_text').html(ui.value + '%');
         $('#sl1').attr('value', parseInt(ui.value,10));
         $('#greeting_font_size').attr('value', parseInt(ui.value,10));
         }
         });*/

        var value = parseInt($('#sl2').attr('value'), 10);
        $('#sl2_text').html(value + '%');

        /*        $('#sl2').slider({
         value: value,
         range: "min",
         animate: false,
         orientation: "horizontal",
         max: 200,
         min: 25,
         change: function( event, ui ) {
         console.log(ui.value);
         $('#sl2_text').html(ui.value + '%');
         $('#sl2').attr('value', parseInt(ui.value,10));
         $('#title_font_size').attr('value', parseInt(ui.value,10));
         }
         });*/
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
         });

         $('#title-font').fontselect().change(function(){
         // replace + signs with spaces for css
         // replace + signs with spaces for css
         var font = $(this).val().replace(/\+/g, ' ');

         // split font into family and weight
         font = font.split(':');
         font = font.join(' ');

         // set family on paragraphs
         console.log(font);
         $('#title_font_style').attr('value',font);
         });

         $('#font').fontselect().change(function(){
         // replace + signs with spaces for css
         // replace + signs with spaces for css
         var font = $(this).val().replace(/\+/g, ' ');

         // split font into family and weight
         font = font.split(':');
         font = font.join(' ');

         // set family on paragraphs
         console.log(font);
         $('#font_style').attr('value',font);
         });
         */

        $('#publish').attr('value', 'Save');

        var data = {
            action: 'pp_album_review_post_pages',
            post_id: $('#post_ID').attr('value'),
            attachments: [0]
        };

        var post_ID = $('#post_ID').attr('value');
        console.log(post_ID);

        // Get pages/spreads to display
        jQuery.post(
            pp_album_review_admin.ajaxurl,
            data,
            function (data) {
    			console.log(data);
                if (data.data == 'success' && data.result && data.result.length) {
                    // display
                    renderPages( data );

                    // page delete checkboxes
                    $('.deletebox').click(function () {
                        console.log('checkbox');
                        attach_delete_page();
                    });

                    $('.deletebox').each(function () {
                        var delete_str;
                        if ($('#upload_spread_button').html() == 'Upload Spreads')
                            delete_str = 'Delete Spreads';
                        else
                            delete_str = 'Delete Pages';

                        $(this).qtip2({
                            content: {
                                text: "Select for deletion. Click '" + delete_str + "' button above when ready to delete.",
                                button: false,
                            },
                            position: {
                                target: 'mouse', // Track the mouse as the positioning target
                                adjust: {x: 0, y: -5},// Offset it slightly from under the mouse
                                my: 'bottom center',  // Position my top left...
                                at: 'top center', // at the bottom right of...
                            },
                            style: {
                                classes: 'qtip-dark'
                            }
                        });

                    });

                    // create the checkbox range selector, with a callback function
//                    $('input[name="deletebox"]').createCheckboxRange(function (e, checked) { });

                    attach_client_notify();

                    // need to bind delete button.
                    $('.delete').on('click', function () {
                        console.log('delete button pressed' + $('#post_ID').attr('value'), +$(this).attr('value'));

                        var data = {
                            action: 'pp_album_review_delete_page',
                            post_id: $('#post_ID').attr('value'),
                            page_id: $(this).attr('value')
                        };

                        jQuery.post(
                            pp_album_review_admin.ajaxurl,
                            data, function () {
                                console.log('delete done');
                                location.reload();
                            });

                    });

                    // need to bind replace button.
                    $('.replace').on('click', function (e) {
                        var the_page_id = $(this).attr('value');
                        e.preventDefault();

                        // If the media frame already exists, reopen it.
                        if (file_frame) {
                            file_frame.open();
                            return;
                        }

                        // Create the media frame.
                        file_frame = wp.media.frames.file_frame = wp.media({
                            title: jQuery(this).data('uploader_title'),
                            button: {
                                text: jQuery(this).data('uploader_button_text'),
                            },
                            multiple: false  // Set to true to allow multiple files to be selected
                        });

                        // When an image is selected, run a callback.
                        file_frame.on('select', function () {
                            // User clicked select button.

                            var selection = file_frame.state().get('selection');

                            var selects = selection.map(function (attachment) {

                                attachment = attachment.toJSON();

                                // Do something with attachment.id and/or attachment.url here
                                return attachment.url;
                            });

                            // AJAX code goes here.
                            var data = {
                                action: 'pp_album_review_revise_page',
                                post_id: $('#post_ID').attr('value'),
                                page: selects,
                                page_id: the_page_id
                            };

                            jQuery.post(
                                pp_album_review_admin.ajaxurl,
                                data, function () {
                                    console.log('replace done');
                                    location.reload();
                                }
                            );

                        });

                        // Finally, open the modal
                        file_frame.open();
                    });

                    $('#sort-me').sortable({
                        stop: function (event, ui) {
                            var listOrder = "";
                            var data = {
                                action: 'pp_album_review_update_page_nums',
                                page_ids: []
                            };

                            $(".name").each(function (i, el) {
                                var p = $(el).attr("value");
                                data.page_ids.push(p);
                            });

                            console.log(data.page_ids);
                            $('#sort-me').sortable();

                            jQuery.post(
                                pp_album_review_admin.ajaxurl,
                                data, function () {
                                    console.log('sorted list saved')
                                });
                        }
                    });
                    $('#sort-me').disableSelection();

                    attach_thumb_resize();

                }
            } // callback
        );

        /*        $('#upload_spread_button').qtip2({
         content:
         {
         text: "Use this option if the files you are uploading are spreads. Before uploading, please resize your spreads to 1800px or less on long side for faster upload times.",
         title: "Upload Album Spreads",
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
         delay: 750,
         effect: function() {
         $(this).slideUp();
         },
         },
         style: {
         classes: 'qtip-bootstrap'
         }
         });

         $('#upload_page_button').qtip2({
         content:
         {
         text: "Use this option if the files you are uploading are single pages (not 2 page spreads). Before uploading, please resize pages to 900px or less on long side for faster uploads.",
         title: "Upload Album Pages",
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
         delay: 750,
         effect: function() {
         $(this).slideUp();
         },
         },
         style: {
         classes: 'qtip-bootstrap'
         }
         });


         $('#first_page_label').qtip2({
         content:
         {
         text: "Check this option if the first page of the album starts to the right of the page gutter and left to the page gutter is blank.",
         button: true,
         },
         position: {
         my: 'left center',  // Position my top left...
         at: 'right center', // at the bottom right of...
         },
         show: {
         effect: function() {
         $(this).fadeTo(20, 1);
         }
         },
         hide: {
         delay: 750,
         effect: function() {
         $(this).slideUp();
         },
         },
         style: {
         classes: 'qtip-bootstrap'
         }
         });

         $('#last_page_label').qtip2({
         content:
         {
         text: "Check this option if the last page of the album is to the left of the page gutter and right to the page gutter is blank.",
         button: true,
         },
         position: {
         my: 'left center',  // Position my top left...
         at: 'right center', // at the bottom right of...
         },
         show: {
         effect: function() {
         $(this).fadeTo(20, 1);
         }
         },
         hide: {
         delay: 750,
         effect: function() {
         $(this).slideUp();
         },
         },
         style: {
         classes: 'qtip-bootstrap'
         }
         });

         $('#album_cover_label').qtip2({
         content:
         {
         text: "Check this option if the album cover is displayed first.",
         button: true,
         },
         position: {
         my: 'left center',  // Position my top left...
         at: 'right center', // at the bottom right of...
         },
         show: {
         effect: function() {
         $(this).fadeTo(20, 1);
         }
         },
         hide: {
         delay: 750,
         effect: function() {
         $(this).slideUp();
         },
         },
         style: {
         classes: 'qtip-bootstrap'
         }
         });

         $('#client_email').qtip2({
         content:
         {
         text: "You can add multiple email addresses by separating each address with a comma (e.g. 1234@asdf.com,abcd@xyz.com)",
         button: true,
         },
         position: {
         my: 'left center',  // Position my top left...
         at: 'right center', // at the bottom right of...
         },
         show: {
         effect: function() {
         $(this).fadeTo(20, 1);
         }
         },
         hide: {
         delay: 750,
         effect: function() {
         $(this).slideUp();
         },
         },
         style: {
         classes: 'qtip-bootstrap'
         }
         });*/

    });

    function attach_thumb_resize() {
        $('#thumbs_s').click(function () {
            console.log('small thumbs');
            $('.album_thumbs').each(function () {
                cw = $(this).width();
                ch = $(this).height();
                console.log('cw,ch = ' + cw + ' ' + ch);
                new_size = scaleSize(0.75, 60, 30, 600, 300, cw, ch);
                console.log('newh,newh = ' + new_size[0] + ' ' + new_size[1]);
                $(this).width(new_size[0]);
                $(this).height(new_size[1]);
            });
        });

        $('#thumbs_m').click(function () {
            console.log('med thumbs');
            $('.album_thumbs').each(function () {
                cw = $(this).width();
                ch = $(this).height();
                console.log('cw,ch = ' + cw + ' ' + ch);
                new_size = scaleSizeWH(200, 200, cw, ch);
                console.log('newh,newh = ' + new_size[0] + ' ' + new_size[1]);
                $(this).width(new_size[0]);
                $(this).height(new_size[1]);
            });
        });

        $('#thumbs_l').click(function () {
            console.log('large thumbs');
            $('.album_thumbs').each(function () {
                cw = $(this).width();
                ch = $(this).height();
                console.log('cw,ch = ' + cw + ' ' + ch);
                new_size = scaleSize(1.5, 60, 30, 600, 300, cw, ch);
                console.log('newh,newh = ' + new_size[0] + ' ' + new_size[1]);
                $(this).width(new_size[0]);
                $(this).height(new_size[1]);
            });
        });
    }

    function attach_client_notify() {
        return;
        // Only allow user to send notification if album is published.
        if ($('#post_published').attr('value') == '0')
            return;

        var post_id = $('#post_ID').attr('value');
        var client_email = $('#client_email').attr('value');
        var client_name = $('#client_name_field').attr('value');
        var studio_email = $('#studio_email').attr('value');
        var studio_name = $('#studio_name').attr('value');
        var msg_to_client = $('#notify_msg_to_client').attr('value');
//		var msg_to_client = '';
        var password = $('#album_password').attr('value');
        var link = $('#album_link').attr('value');

        if (link != '') {
            msg_to_client = msg_to_client + "%0D%0D" + encodeURIComponent("Please visit the following link to see your album: " + link);
        }
        if (password != '') {
            msg_to_client = msg_to_client + "%0D%0D" + encodeURIComponent("Please use the following password when prompted:" + password);
        }

        var maillink = "mailto:" + client_email + "?subject=Your album design is ready for review&body=" + msg_to_client;

        if ($('#upload_page_button').is(':visible')) {
            $('#upload_page_button').after("<a href='" + maillink + "' role='button' class='btn btn-primary' data-toggle='modal' id='notify_client_button' style='margin-left:5px;'>Notify " + client_name + "</a>");
        }
        else
            $('#upload_spread_button').after("<a href='" + maillink + "' role='button' class='btn btn-primary' data-toggle='modal' id='notify_client_button' style='margin-left:5px;'>Notify " + client_name + "</a>");

/*        $('#notify_client_button').qtip2({
            content: {
                text: "After uploading pages, or making changes or comments, click this button to notify your clients via email.  You can set a default client notification email template under the Settings->Basic Settings menu.",
                title: "Notify Client",
                button: true,
            },
            position: {
                my: 'bottom center',  // Position my top left...
                at: 'top center', // at the bottom right of...
            },
            show: {
                effect: function () {
                    $(this).fadeTo(20, 1);
                }
            },
            hide: {
                delay: 750,
                effect: function () {
                    $(this).slideUp();
                },
            },
            style: {
                classes: 'qtip-bootstrap'
            }
        });*/

    }

    function attach_delete_page() {
        var none_checked = true;
        var delete_pages = [];
        $('.deletebox').each(function () {
            if ($(this).is(':checked')) {
                none_checked = false;
            }
        });

        if (none_checked && $('#delete_pages_button').length != 0)
            $('#delete_pages_button').remove();
        else if (!none_checked && $('#delete_pages_button').length == 0) {
            var delete_str;
            if ($('#upload_spread_button').html() == 'Upload Spreads')
                delete_str = 'Delete Spreads';
            else
                delete_str = 'Delete Pages';

            $('#notify_client_button').after("<button class='btn btn-primary' id='delete_pages_button' style='margin-left:5px;'>" + delete_str + "</button>");

            $('#delete_pages_button').click(function () {
                // post pages to delete.
                console.log('delete_pages_button clicked');
                var data = {
                    action: 'pp_album_review_delete_pages',
                    post_id: $('#post_ID').attr('value'),
                    page_ids: []
                };

                $('.deletebox').each(function () {
                    if ($(this).is(':checked')) {
                        data.page_ids.push($(this).val());
                    }
                });

                console.log(data);

                var current_url = location.href;
                console.log(current_url);

                var new_url = RemoveLastDirectoryPartOf(current_url, $('#post_ID').attr('value'));
                console.log(new_url);

                jQuery.post(
                    pp_album_review_admin.ajaxurl,
                    data, function () {
                        console.log('delete done');
                        location.replace(new_url);
                    }
                );


            });
        }

    }


})(jQuery);



