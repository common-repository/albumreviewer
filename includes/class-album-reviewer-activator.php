<?php

/**
 * Fired during plugin activation
 *
 * @link       http://albumreviewer.co
 * @since      1.0.0
 *
 * @package    Album_Reviewer
 * @subpackage Album_Reviewer/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Album_Reviewer
 * @subpackage Album_Reviewer/includes
 * @author     Photopress Support <support@photopressplugins.com>
 */
class Album_Reviewer_Activator {

	public static function create_page_table( $table_name )
	{
		global $wpdb;
		global $charset_collate;

		// Create the page table
		$sql_create_table = "CREATE TABLE IF NOT EXISTS {$table_name} (
			page_id bigint(20) unsigned NOT NULL auto_increment,
			post_id bigint(20) unsigned NOT NULL,
			attachment_id int NOT NULL,
			filename varchar(512) NOT NULL,
			file_location_orig varchar(512) NOT NULL,
			width_orig int NOT NULL,
			height_orig int NOT NULL,
			file_location_screen varchar(512) NOT NULL,
			width_screen int NOT NULL,
			height_screen int NOT NULL,
			file_location_thumb varchar(512) NOT NULL,
			width_thumb int NOT NULL,
			height_thumb int NOT NULL,
			file_location_tablet varchar(512) NOT NULL,
			width_tablet int NOT NULL,
			height_tablet int NOT NULL,
			page_number int unsigned NOT NULL,
			revision_number int unsigned NOT NULL,
			approved_on datetime default '0000-00-00 00:00:00',
			approved boolean NOT NULL default '0',
			PRIMARY KEY  (page_id),
			KEY abc (post_id)
			) $charset_collate; ";

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql_create_table);
	}

	public static function create_comment_table( $table_name )
	{
		global $wpdb;
		global $charset_collate;

		// Create the comment table
		$sql_create_table = "CREATE TABLE IF NOT EXISTS {$table_name} (
			comment_id bigint(20) unsigned NOT NULL auto_increment,
			page_id bigint(20) unsigned NOT NULL,
			comment varchar(1024) NOT NULL,
			comment_on datetime default '0000-00-00 00:00:00',
			commenter int unsigned NOT NULL default 0,
			comment_read boolean NOT NULL default '0',
			PRIMARY KEY  (comment_id),
			KEY abc (page_id)
			) $charset_collate; ";

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql_create_table);
	}


	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		global $wpdb;

		$table_name = $wpdb->prefix . 'pp_album_review2_page_table';
		if ($wpdb->get_var( "SHOW TABLES LIKE '{$table_name}'") != $table_name) {

			self::create_page_table( $table_name );
		}

		$table_name = $wpdb->prefix . 'pp_album_review2_comment_table';
		if ($wpdb->get_var( "SHOW TABLES LIKE '{$table_name}'") != $table_name) {

			self::create_comment_table( $table_name );
		}

		$plugins = get_plugins();
		$active_plugins = get_option( 'active_plugins', array() );

		if(!get_option( 'pp_album_review_ast') )
			update_option('pp_album_review_ast',current_time( 'timestamp' ));

		// Setup default msg to client messages
		if(!get_option('pp_album_review_notify_msg_to_client'))	{
			$default_msg_to_client = 'Hi ____ and _____!  We hope you are doing well!  Thank you so much for your patience.  We are excited to share the first draft of your album design!

	Please review each page and add comments if you would like changes.  When you have finished your comments, please click the "Send Comments" button.  Otherwise, if you are happy with the design, please click "I Approve My Album".

	thank you,
	________';
			update_option( 'pp_album_review_notify_msg_to_client' , stripslashes($default_msg_to_client) );
		}

		if(!get_option('pp_album_review_notify_msg_to_client2'))	{
			$default_notify_msg_to_client = 'Hi ____ and _____!  We hope you are doing well!  Thank you so much for your patience.  We are excited to share the first draft of your album design!

	thank you,
	_______';
			update_option( 'pp_album_review_notify_msg_to_client2' , stripslashes($default_notify_msg_to_client) );
		}
		flush_rewrite_rules();
	}
}
