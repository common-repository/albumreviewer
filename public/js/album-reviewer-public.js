(function( $ ) {
	'use strict';

	function getAlbumPageHtml(albumPages, pageNum){
		var screenImgUrl;
		if(pageNum < albumPages.length && pageNum >= 0)
			screenImgUrl = albumPages[pageNum].file_location_screen;
		else if(pageNum >= albumPages.length)
			screenImgUrl = albumPages[albumPages.length-1].file_location_screen;
		else
			screenImgUrl = albumPages[0].file_location_screen;

		var html = "<img id='album-display' class='img-responsive album-display' src='" + screenImgUrl + "' />";
		return html;
	}

	function getAlbumPageComments(albumPages, albumComments, pageNum){
		var pageId;
		if(	!albumComments || !albumComments.length ){
			return null;
		}

		pageId = getPageId(albumPages, pageNum);

		// find comments based on current page's pageId
		var comments = albumComments.filter(function(c){
			return c.page_id === pageId ? true : false;
		});

		return comments;
	}

	function updatePageNumberDisp(curPg, totalPgs){
		$('#page-control-disp1,#page-control-disp2').val('Spread ' + (curPg+1) + ' of ' + totalPgs);
	}

	function getPageId(albumPages, pageNum){
		var pageId;
		// get the pageId
		if(pageNum < albumPages.length && pageNum >= 0)
			pageId = albumPages[pageNum].page_id;
		else if(pageNum >= albumPages.length)
			pageId = albumPages[albumPages.length-1].page_id;
		else
			pageId = albumPages[0].page_id;

		return pageId;
	}

	function updatePageComments(comments){

		if(!comments || !comments.length){
			$('#comment-box').html("<span class='album-comment-notice'>There are no comments currently for this spread.</span>");
			return;
		}

		var parsedComments = comments.map(function(c){

			var comment;
			var bubble = parseInt( c.commenter ) ? 'bubbledLeft' : 'bubbledRight';
			comment = "<span class=" + bubble +  ">";
			comment += decodeURI(c.comment);
			comment += "</span>";
			return comment;
		});

		var commentsHtml = parsedComments.join('<br>');

		$('#comment-box').html(commentsHtml);
	}

	function pageUpdate(albumPages, albumComments, curPg){
		$('#album-view').html(getAlbumPageHtml(albumPages, curPg));
		updatePageNumberDisp(curPg, albumPages.length);

		var comments = getAlbumPageComments(albumPages, albumComments, curPg);
		updatePageComments(comments);
	}


	$(document).ready(function () {

		var albumPages = pp_album_pages;
		var albumComments = pp_album_comments;

		var curPg = 0;

		// initialize the view with the first album page
		pageUpdate(albumPages, albumComments, curPg);

		// unhide navigation controls when the image is done loading
		var logo = document.getElementById('album-display');
		logo.onload = function () {
			$('.page-controls-wrapper').attr('style','opacity:1');
		};


		$('#page-control-next1,#page-control-next2').on('click',function(){
			curPg = (curPg+1) > pp_album_pages.length-1 ? pp_album_pages.length-1 : curPg+1;

			pageUpdate(albumPages, albumComments, curPg);
		});

		$('#page-control-prev1,#page-control-prev2').on('click',function(){
			curPg = (curPg-1) < 0 ? 0 : curPg-1;

			pageUpdate(albumPages, albumComments, curPg);
		});

		$('#page-control-first1, #page-control-first2').on('click',function(){
			curPg = 0;

			pageUpdate(albumPages, albumComments, curPg);
		});

		$('#page-control-last1,#page-control-last2').on('click',function(){
			curPg = pp_album_pages.length-1;

			pageUpdate(albumPages, albumComments, curPg);
		});

		$('#enter-new-comment').on('click', function(){
			var newComment = $('#new-comment-input').val();
			$('#new-comment-input').val('');

			var pageId = getPageId(pp_album_pages, curPg);

			var body = {
				action : 'pp_album_review_insert_comments',
				post_id : album_post.post_id,
				page_id : pageId,
				comment : newComment,
				comment_from: $('#comment_from').val()
			};

			$.post( ajaxAdminUrl.url, body, function(data) {
				if(data.data === 'success'){
					albumComments = data.message;
					pageUpdate(albumPages, albumComments, curPg);
				}
			});
		});

	});
})( jQuery );
