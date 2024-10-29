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

define('PPP_ALBUM_REVIEW_DEBUG_DB', 'true');


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
class Album_Reviewer_Model_Pages {

	/**
	 * Initialize the collections used to maintain the actions and filters.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		$this->log('Album_Reviewer_Model_Pages::__construct entered');

	}

	public function log( $logentry ){
		if( defined(PPP_ALBUM_REVIEW_DEBUG_DB) ) {
			$upload_dir = wp_upload_dir();
			file_put_contents( $upload_dir['basedir'] . '/pp_album.log' , "[" . current_time( 'mysql' ) . " model:pages ] " . $logentry ."\n\n", FILE_APPEND | LOCK_EX);
		}
	}

	public function insert( $post_id, $attachments) {
		$this->log('Album_Reviewer_Model_Pages::insert entered');
		$this->log('post_id = ' . $post_id);
		$this->log(serialize($attachments));

    	require_once(ABSPATH . '/wp-includes/functions.php');
    	require_once(ABSPATH . '/wp-includes/media.php');

    	global $wpdb;
    	$wpdb->pp_album_review2_page_table = "{$wpdb->prefix}pp_album_review2_page_table";

    	$column_formats = $this->get_table_columns();

    	$pg_num = $this->get_page_count( $post_id );

    	$this->log('pg_num : ' . $pg_num);

		usort($attachments, array($this,"cmp") );

    	$this->log('sorted : ' . serialize($attachments));

    	$page_array = array();

		$query  = "INSERT INTO " . $wpdb->pp_album_review2_page_table . "\n";
		$query .= "(`post_id`, `attachment_id`, `filename`, `file_location_orig`,`width_orig`,`height_orig`,`file_location_screen`,`width_screen`,`height_screen`,`file_location_thumb`,`width_thumb`,`height_thumb`,`file_location_tablet`,`width_tablet`,`height_tablet`,`page_number`,`approved_on`,`approved`)\n";
		$query .= "VALUES\n";

    	foreach ( $attachments as $attachment ) {
    		$origfile = '';
    		$thumbfile = '';
    		$screenfile = '';
    		$tabletfile = '';

    		$this->log ( serialize( $attachment ) );
    		$this->log ( intval( $attachment['attachmentId'] ) );

    		$parsed_attachments = $this->get_attachments( intval( $attachment['attachmentId'] ) );

    		$this->log ( serialize($parsed_attachments));

    		$origfile = $attachment['url'];
    		$width_orig = $attachment['width'];
    		$height_orig = $attachment['height'];

    		$query .= "('" . $post_id . "',";
    		$query .= intval( $attachment['attachmentId'] ) . ",";
    		$query .= "'" . $attachment['filename'] . "',";

    		$query .= "'" . $origfile . "',";
    		$query .= $width_orig . ",";
    		$query .= $height_orig . ",";


    		$screenfile = $parsed_attachments[0][0];
    		$width_screen = $parsed_attachments[0][1];
    		$height_screen = $parsed_attachments[0][2];

    		$query .= "'" . $screenfile . "',";
    		$query .= $width_screen . ",";
    		$query .= $height_screen . ",";


    		$thumbfile = $parsed_attachments[1][0];
    		$width_thumb = $parsed_attachments[1][1];
    		$height_thumb = $parsed_attachments[1][2];

    		$query .= "'" . $thumbfile . "',";
    		$query .= $width_thumb . ",";
    		$query .= $height_thumb . ",";

    		$tabletfile = $parsed_attachments[2][0];
    		$width_tablet = $parsed_attachments[2][1];
    		$height_tablet = $parsed_attachments[2][2];

    		$query .= "'" . $tabletfile . "',";
    		$query .= $width_tablet . ",";
    		$query .= $height_tablet . ",";

    		$query .= ++$pg_num . ",";
    		$query .=  "'',";
    		$query .= 0 . "),\n";

    	}
		$query = substr_replace($query, "", -2);

  		$this->log( $query );

  		$result = $wpdb->query( $query );

  		$this->log( 'query result = ' . $result );

  		return $result;
    }

	private function cmp($a, $b)
	{
		return strcmp($a['filename'], $b['filename']);
	}

	/**
	 * Returns schema for the page table.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	 private function get_table_columns(){
		return array(
			'page_id'      				=> '%d',
			'post_id'      				=> '%d',
			'attachment_id'             => '%d',
			'file_location_orig'		=> '%s',
			'width_orig'                => '%d',
			'height_orig'               => '%d',
			'file_location_screen'		=> '%s',
			'width_screen'              => '%d',
			'height_screen'             => '%d',
			'file_location_thumb'		=> '%s',
			'width_thumb'               => '%d',
			'height_thumb'              => '%d',
			'file_location_tablet'		=> '%s',
			'width_tablet'              => '%d',
			'height_tablet'             => '%d',
			'page_number'  				=> '%d',
			'revision_number' 			=> '%d',
			'approved_on'  				=> '%s',
			'approved'     				=> '%d',
		);
	}

	/**
	 * Returns page count for album review.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function get_page_count( $post_id ) {
		global $wpdb;
		$wpdb->pp_album_review2_page_table = "{$wpdb->prefix}pp_album_review2_page_table";

		$this->log('Album_Reviewer_Model_Pages::get_page_count entered');


		$sql_get_pages =  "SELECT page_number
							 FROM $wpdb->pp_album_review2_page_table
							WHERE post_id = '$post_id'
							ORDER BY page_number DESC" ;

		$pages = $wpdb->get_results ($sql_get_pages);

		if($pages)
			return $pages[0]->page_number;
		else
			return 0;
	}

	/**
	 * Extracts attachment location info
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function get_attachments( $attachment_id ) {
		$this->log('Album_Reviewer_Model_Pages::get_attachments entered');

		// 1. Check if resized screen version exist
		$screen = wp_get_attachment_image_src($attachment_id,'album-page');

		// 2. Check for thumbnail
		$thumb = wp_get_attachment_image_src($attachment_id,'album-thumb');

		// 3. Check for tablet file
		$tablet = wp_get_attachment_image_src($attachment_id,'album-tablet');

		$resized_attachments = array($screen, $thumb, $tablet);

		return $resized_attachments;
	}


	/**
	 * Returns thumbnails for all pages
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	 public function get_page_thumbnails( $post_id ) {
		$pages = $this->get_latest_revisions( $post_id );

		$all_pages = array();
		foreach ( $pages as $page )	{
			array_push($all_pages, $page->file_location_thumb );
		}

		return $all_pages;
	}

	/**
	 * Returns file locations for all pages
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function get_page_file_locations( $post_id ) {
		$pages = $this->get_latest_revisions( $post_id );
		$all_pages = array();
		foreach ( $pages as $page )	{
			$path_parts = pathinfo($page->file_location_orig);
			array_push($all_pages, $path_parts['basename'] );
		}

		return $all_pages;
	}


	/**
	 * Returns returns ids for all pages
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function get_page_ids( $post_id ) {
		$page_ids = $this->get_latest_revisions( $post_id );
		$all_page_ids = array();

		foreach ( $page_ids as $page_id ) {
			array_push($all_page_ids , strval($page_id->page_id) );
		}

		return $all_page_ids;
	}


	/**
	 * Returns returns ids for all pages
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	public function get_latest_revisions( $post_id ) {
		global $wpdb;
		$wpdb->pp_album_review_page_table = "{$wpdb->prefix}pp_album_review2_page_table";

		$sql_all_get_pages = "SELECT *
								FROM $wpdb->pp_album_review_page_table
							   WHERE post_id = $post_id
							ORDER BY page_number, revision_number";

		$all_pages = $wpdb->get_results ($sql_all_get_pages);

		$this->log('get_latest_revisions: ' . serialize( $all_pages) );

		$pg_tbl = array('0' => 0);

		foreach($all_pages as $page_revision)
		{
			if($page_revision->page_number < count($pg_tbl))
			{
				if($pg_tbl[$page_revision->page_number])
				{
					// check if current is larger than value in table
					if( $page_revision->revision_number > $pg_tbl[$page_revision->page_number]->revision_number )
						$pg_tbl[$page_revision->page_number] = $page_revision;
				}
				else
					$pg_tbl[$page_revision->page_number] = $page_revision;
			}
			else
			{
				$pg_tbl[$page_revision->page_number] = $page_revision;
			}
		}
		unset($pg_tbl['0']);
		return $pg_tbl;
	}

}


