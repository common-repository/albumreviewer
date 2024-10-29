<?php

/**
 * Class responsible for public facing view
 *
 * @link       http://albumreviewer.co
 * @since      1.0.0
 *
 * @package    Album_Reviewer
 * @subpackage Album_Reviewer/includes
 */
define('PPP_ALBUM_REVIEW_DEBUG_RENDERER', 'true');

/**
 * Register all actions and filters for the plugin.
 *
 * Maintain a list of all hooks that are registered throughout
 * the plugin, and register them with the WordPress API. Call the
 * run function to execute the list of actions and filters.
 *
 * @package    Album_Reviewer
 * @subpackage Album_Reviewer/includes
 * @author     Photopress Support <support@photopressplugins.com>
 */
class Album_Reviewer_Renderer {

	/**
	 * The model is responsible for business logic and database interaction.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Album_Reviewer_Model    $pages
	 */
	protected $pages;
	protected $comments;

	/**
	 * Initialize the collections used to maintain the actions and filters.
	 *
	 * @since    1.0.0
	 */
	public function __construct( $pages, $comments ) {
		$this->pages = $pages;
		$this->comments = $comments;
	}

	/**
	 * Logging.
	 *
	 * @since    1.0.0
	 */
	public function log( $logentry ){
		if( defined(PPP_ALBUM_REVIEW_DEBUG_RENDERER) ) {
			$upload_dir = wp_upload_dir();
			file_put_contents( $upload_dir['basedir'] . '/pp_album.log' , "[" . current_time( 'mysql' ) . "] " . $logentry ."\n\n", FILE_APPEND | LOCK_EX);
		}
	}

	/**
	 * Renders the custom album review view
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	public function render( $album_post ){
		// kill any current output buffers
		if ( ob_get_length() > 0 ) {
			ob_end_clean();
		}

		// flush previous output buffers
		if ( ! ( substr_count( $_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip' ) && ob_start( "ob_gzhandler" ) ) ) {
			ob_start();
		}

		// start render
		print $this->head($album_post);
		print $this->body($album_post);
		print $this->footer($album_post);

		status_header( '200' );

		// end render
		ob_end_flush();
		die(); // don't allow anyone else to mess with our view
	}

	/**
	 * Generates head for the custom album review view
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function head( $album_post ) {

		$public_css =plugins_url('albumreviewer/public/css/album-reviewer-public.css');
		$bootstrap_css = plugins_url('albumreviewer/bower_components/bootstrap/dist/css/bootstrap.css');

		$head  = "<!DOCTYPE html><html lang='en'>\n";
		$head .= "<head>\n";
		$head .= "<link rel='stylesheet' href='" . $bootstrap_css . "'>\n";
		$head .= "<link rel='stylesheet' href='" . $public_css . "'>\n";
		$head .= "</head>\n";

		return $head;
	}

	/**
	 * Generates footer for custom album review view
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function footer( $album_post ) {

		$jquery_js = includes_url('js/jquery/jquery.js');
		$public_js = plugins_url('albumreviewer/public/js/album-reviewer-public.js');
		$bootstrap_js = plugins_url('albumreviewer/bower_components/bootstrap/dist/js/bootstrap.js');

		$footer  = "<footer>\n";

    	$footer .= "<script>var album_post = " . json_encode( ($album_post) ) . ";</script>\n" ;
		$pages = $this->pages->get_latest_revisions( $album_post['post_id'] );
		$comments = $this->comments->read_all( $album_post['post_id'] );
		$footer .= "<script> var pp_album_pages = " . json_encode(array_values( $pages ) ) . ";</script>\n";
		$footer .= "<script> var pp_album_comments = " . json_encode(array_values( $comments ) ) . ";</script>\n";

		$footer .= "<script> var ajaxAdminUrl = " . json_encode( array(	'url' => admin_url( 'admin-ajax.php' ) ) ) . " </script>\n";

		$footer .= "<script src='" . $jquery_js . "'></script>\n";
		$footer .= "<script src='" . $public_js . "'></script>\n";
		$footer .= "<script src='" . $bootstrap_js . "'></script>\n";
		$footer .= "</footer>\n";
		$footer .= "</html>\n";

		return $footer;
	}


	/**
	 * Generates footer for custom album review view
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function body( $album_post ) {
		$options = array();

		$options = apply_filters('pp_album_review_renderer_options', $options);

		if ( is_user_logged_in() )
			$comment_from = 'designer_comment';
		else
			$comment_from = 'client_comment';

		$logo = $album_post['logo'];
		$logo_scale = $album_post['logo_scale'];

		$body  = "<body>\n";
		$body  .= "<div class='main-content'>\n";
		$body .= "<input type='hidden' id='comment_from' value='" . $comment_from . "'/>\n";

    	$body .= "<div id='layout_style' layout='wide' data-role='content' data-scrollz='pull' style=''>";

		$body .= "<div id='my-row-fluid'>";
		$body .= "<div id='header_block' class='collapse in' style='opacity:1.0'>";
		$body .= "<span class='hidden-phone'>";
		$body .= "<div class='row-fluid'>";
		$body .= "<div align=center id='logo-wrapper'>";

		if(isset($options['logo_enabled'])){
			$body .= "<a href='" . home_url() . "'>";
			$body .= "<img id='logo' class='center img-responsive' " . "style='opacity:1.0' logo_scale='" . $logo_scale . "' src='" . $logo . "'>";
			$body .= "</a>";
		} else {
			$body .= "<a href='" . home_url() . "'>";
			$body .= get_option('pp_album_review_user_name');
			$body .= "</a>";
		}
		$body .= "</div>\n";
		$body .= "</div>\n";
		$body .= "</span>\n";
		$body .= "</div>\n";
		$body .= "</div>\n";
		$body .= "</div>\n";

		$body .= "<section id='album-view-wrapper row'>\n";
		$body .= "<div class='col-sm-12 col-sm-offset-0'>";
		$body .= "<div id='album-view'></div>";
		$body .= "</div>";
		$body .= "</section>\n";

		/* Render the control box (md) */
		$body .= "<div class='col-sm-12 hidden-md hidden-lg'>";
		$body .= "<div id='control-box'>\n";
		/* Render the album navigation controls */
		$body .= "<section class='page-controls-wrapper' style='opacity:0'>\n";
		$body .= "<div id='page-controls'>\n";
		$body .= "<div class='btn-group control-group' role='group'>\n";
		$body .= "<button id='page-control-first1' type='button' class='btn page-ctrl-btn btn-default'><<</button>\n";
		$body .= "<button id='page-control-prev1' type='button' class='btn page-ctrl-btn btn-default'><</button>\n";
		$body .= "<input  id='page-control-disp1' type='text' class='btn page-ctrl-btn btn-default' value='' readonly></input>\n";
		$body .= "<button id='page-control-next1' type='button' class='btn page-ctrl-btn btn-default'>></button>\n";
		$body .= "<button id='page-control-last1' type='button' class='btn page-ctrl-btn btn-default'>>></button>\n";
		$body .= "</div>\n";
		$body .= "</div>\n";
		$body .= "</section>\n";
		$body .= "</div>\n";
		$body .= "</div>\n";



		/* Render the comment/control section */
		$body .= "<section id='comment-controls-wrapper' style='opacity:1'>\n";
		$body .= "<div class='col-sm-12 col-sm-offset-0' id='comment-controls-inner-wrapper'>";
		$body .= "<div id='comment-view'>";

		/* Render the comment box */
		$body .= "<div id='comment-control-combo-box' class='col-md-8 col-sm-12'>";

		$body .= "<div id='comment-box' class='row'>";
		$body .= "comment section<br>";
		$body .= "</div>\n";

		/* Render the new comment box*/
		$body .= "<div id='new-comment-outer-wrapper' class='row'>";
		$body .= "<div id='new-comment-inner-wrapper' class='col-md'>\n";
		$body .= "<div class='input-group'>\n";
		$body .= "<input id='new-comment-input' type='text' placeholder='Enter a new comment here' class='form-control'>\n";
		$body .= "<span id='enter-new-comment' class='input-group-addon btn-enable'>Send</span>\n";
		$body .= "</div>\n";
		$body .= "</div>\n";
		$body .= "</div>\n";

		$body .= "</div>\n";

		/* Render the control box */
		$body .= "<div class='col-md-4 col-lg-4 hidden-sm hidden-xs'>";
		$body .= "<div id='control-box'>\n";
		/* Render the album navigation controls */
		$body .= "<section class='page-controls-wrapper' style='opacity:0'>\n";
		$body .= "<div id='page-controls'>\n";
		$body .= "<div class='btn-group control-group' role='group'>\n";
		$body .= "<button id='page-control-first2' type='button' class='btn page-ctrl-btn btn-default'><<</button>\n";
		$body .= "<button id='page-control-prev2' type='button' class='btn page-ctrl-btn btn-default'><</button>\n";
		$body .= "<input  id='page-control-disp2' type='text' class='btn page-ctrl-btn btn-default' value='' readonly></input>\n";
		$body .= "<button id='page-control-next2' type='button' class='btn page-ctrl-btn btn-default'>></button>\n";
		$body .= "<button id='page-control-last2' type='button' class='btn page-ctrl-btn btn-default'>>></button>\n";
		$body .= "</div>\n";
		$body .= "</div>\n";
		$body .= "</section>\n";
		$body .= "</div>\n";
		$body .= "</div>\n";

		$body .= "</div>\n";
		$body .= "</div>\n"; // main-content

		$body .= "<div id='footer-email-link' class='row'>\n";
		$body .= "Send <a href='mailto:" . get_option('pp_album_review_user_email') . "'>". get_option('pp_album_review_user_email') ."</a> when done entering comments.\n"; // main-content
		$body .= "</div>\n";

		$body .= "</section>\n";
		$body .= "</div>\n";
		$body .= "</body>\n";

		return $body;
	}

}


