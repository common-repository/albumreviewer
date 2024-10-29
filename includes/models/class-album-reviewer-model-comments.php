<?php

/**
 * Class responsible for model and database interaction
 *
 * @link       http://albumreviewer.co
 * @since      1.0.0
 *
 * @package    Album_Reviewer
 * @subpackage Album_Reviewer/includes/models
 */

define('PPP_ALBUM_REVIEW_DEBUG_COMMENTS_MODEL', 'true');

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
class Album_Reviewer_Model_Comments {

	/**
	 * Initialize the collections used to maintain the actions and filters.
	 *
	 * @since    1.0.0
	 */
	public function __construct( $pages ) {
		$this->log('Album_Reviewer_Model_Comments::__construct entered');
		$this->pages = $pages;
	}

	/**
	 * Reference to the pages model object.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $pages    Reference to the pages model object.
	 */
	private $pages;

	public function log( $logentry ){
		if( defined(PPP_ALBUM_REVIEW_DEBUG_COMMENTS_MODEL) ) {
			$upload_dir = wp_upload_dir();
			file_put_contents( $upload_dir['basedir'] . '/pp_album.log' , "[" . current_time( 'mysql' ) . " model:comments ] " . $logentry ."\n\n", FILE_APPEND | LOCK_EX);
		}
	}

	/**
	 * Returns schema for the page table.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function get_table_columns(){
		return array(
			'comment_id'   => '%d',
			'page_id'      => '%d',
			'comment'      => '%s',
			'comment_on'   => '%s',
			'commenter'    => '%d',
			'comment_read' => '%d',
		);
	}


	/**
	 * Inserts comments for a given album page.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	public function insert( $page_id, $comment, $commenter ) {

		require_once(ABSPATH . '/wp-includes/functions.php');

		$this->log('insert 1: ' . $page_id . ' ' . $comment . ' ' .$commenter );

		global $wpdb;
		$wpdb->pp_album_review2_comment_table = "{$wpdb->prefix}pp_album_review2_comment_table";

		$column_formats = $this->get_table_columns();

		// sanitize the input
		$comment = sanitize_text_field($comment);

		$data = array('comment_id'   => NULL,
					  'page_id'      => $page_id,
					  'comment'      => $comment,
					  'comment_on'   => date_i18n( 'Y-m-d H:i:s',  (int)current_time( 'timestamp' ) ),
					  'commenter'    => $commenter,
					  'comment_read' => '1');

		$error = $wpdb->insert( $wpdb->pp_album_review2_comment_table, $data, $column_formats );
		$this->log('insert 2: ' . $page_id . ' ' . $comment . ' ' .$commenter . ' ' . $error);

	}

	/**
	 * Read the comments for a given album page.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function read( $page_id , $post_id, $page_type = 'spreads' ){
		require_once(ABSPATH . '/wp-includes/functions.php');

		$this->log('pp_album_review_get_comment 1: ' . $page_id . ' ' . $post_id);

		global $wpdb;
		$wpdb->pp_album_review2_comment_table = "{$wpdb->prefix}pp_album_review2_comment_table";

		$sql_get_comments = "SELECT comment, comment_on, commenter, comment_id
							 FROM $wpdb->pp_album_review2_comment_table
							 WHERE page_id = '$page_id'
							 ORDER BY comment_on" ;


		$comments = $wpdb->get_results ($sql_get_comments);

		$all_comments = '<div class="commentArea">';

		$timezone = get_option('timezone_string');

		$pages = $this->pages->get_latest_revisions( $post_id );
		if( get_post_meta( $post_id, '_pp_album_first_page_on_right', true ))
			$firstPage = 'firstPage="1"';
		else
			$firstPage = '';

		if( get_post_meta( $post_id, '_pp_album_has_cover', true ))
			$is_cover = 'is_cover="1"';
		else
			$is_cover = '';

		if( get_post_meta( $post_id, '_pp_album_last_page_on_left', true ))
			$lastPage = 'lastPage="1"';
		else
			$lastPage = '';

		$j = 0;
		$pg_num = 0;

		foreach ( $pages as $page ) {
			if($page->page_id == $page_id)
				$pg_num = $j;
			$j++;
		}

//		$page_num = pp_album_review_get_page_number_heading($pg_num, $pages, $is_cover, $firstPage, $lastPage, $page_type);

	//	$this->log('pp_album_review_get_comment 2: ' . $page_num);

		foreach ( $comments as $comment ){
			if($timezone == ''){
				$offset = get_option('gmt_offset');
				if($offset) {
					$offint = intval($offset)*100;
					$offintform = sprintf('%+04d', $offint);
					$datetime = new DateTime($comment->comment_on . $offintform);
					$this->log('1 ' . $comment->comment_on . ' / ' . $offintform . ' / '. $offset);
				} else {
					$this->log($comment->comment_on);
					$datetime = new DateTime($comment->comment_on);
				}
			} else {
				$datetime = new DateTime($comment->comment_on, new DateTimeZone($timezone));
			}

			if($comment->commenter == PP_ALBUM_CLIENT)
				$all_comments .= '<div class="bubbledLeft"  comment_id=' . $comment->comment_id . '>';
			else
				$all_comments .= '<div class="bubbledRight" comment_id=' . $comment->comment_id . '>';

			$all_comments .= '<p>' . stripslashes($comment->comment) . " <abbr class='timeago' title='" . $datetime->format('c') . "'>" .$comment->comment_on ."</abbr></p>";
			$all_comments .= '<div class="delete_comment"><i class="icon-remove-sign"></i></div>';
			$all_comments .= '</div>';
		}
		$all_comments .= '</div><br></br><br></br>';
		return $all_comments;
	}


	/**
	 * Read the comments for the latest album pages.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function read_all( $post_id ){
		global $wpdb;
		$wpdb->pp_album_review_comment_table = "{$wpdb->prefix}pp_album_review2_comment_table";
		$this->log( "read_all 0 : " . $post_id );

		$pages = $this->pages->get_latest_revisions( $post_id );
		$this->log( "read_all 1 : " . json_encode( array_values( $pages ) ) );

		$page_ids = array();
		foreach($pages as $page){
			$this->log( "read_all 2 : " . $page->page_id );
			array_push( $page_ids, $page->page_id );
		}

		$this->log( "read_all 3 : " . serialize( $page_ids ) . ' count  = ' . count($page_ids) );

		if(count($page_ids) == 0){
			return array();
		}

		$sql_get_comments = "SELECT page_id, comment, comment_on, commenter
							 FROM $wpdb->pp_album_review_comment_table
							 WHERE page_id in ( " . implode(',',array_values( $page_ids ) ) ." )";

		$this->log( "read_all 4 : " . $sql_get_comments );

		$comments = $wpdb->get_results ($sql_get_comments);

//		$this->log( serialize($comments) );

		return $comments;
	}


}


