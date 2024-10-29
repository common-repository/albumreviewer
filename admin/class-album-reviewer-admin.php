<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://albumreviewer.co
 * @since      1.0.0
 *
 * @package    Album_Reviewer
 * @subpackage Album_Reviewer/admin
 */

define( 'PP_ALBUM_BASE_WIDTH', 2400 );
define( 'PP_ALBUM_BASE_HEIGHT', 2400 );
define( 'PP_ALBUM_TABLET_WIDTH', 900 );
define( 'PP_ALBUM_TABLET_HEIGHT', 900 );
define( 'PP_ALBUM_THUMB_WIDTH', 360 );
define( 'PP_ALBUM_THUMB_HEIGHT', 360 );
define( 'PP_ALBUM_LOGO_WIDTH', 600 );
define( 'PP_ALBUM_LOGO_HEIGHT', 250 );
define( 'PP_ALBUM_DESIGNER', '0' );
define( 'PP_ALBUM_CLIENT', '1' );
define( 'DONOTCACHEPAGE', true );

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Album_Reviewer
 * @subpackage Album_Reviewer/admin
 * @author     Photopress Support <support@photopressplugins.com>
 */
class Album_Reviewer_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $album_reviewer    The ID of this plugin.
	 */
	private $album_reviewer;

	/**
	 * Reference to the pages model object.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $pages    Reference to the pages model object.
	 */
	private $pages;


	/**
	 * Reference to the comments model object.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $comments    Reference to the comments model object.
	 */
	private $comments;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $album_reviewer       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $album_reviewer, $pages, $comments, $version ) {

		$this->album_reviewer = $album_reviewer;
		$this->version = $version;
		$this->pages = $pages;
		$this->comments = $comments;
		$this->log('Album_Reviewer_Admin::__construct');
		$this->log(serialize($album_reviewer));
		$this->log(serialize($pages));

		add_action( 'add_meta_boxes', array( $this, 'add_post_settings_meta_box' ), 10, 2 );
		add_action( 'admin_menu', array( $this, 'add_album_review_settings_submenu' ) );
		add_action( 'admin_init', array( $this, 'admin_init' ) );


		/*--------------- Backend "Routes" --------------*/
		add_action('wp_ajax_nopriv_pp_album_save_settings', array( $this, 'pp_album_review_save_settings' ) );
		add_action('wp_ajax_pp_album_save_settings', array( $this, 'pp_album_review_save_settings' ) );


		add_action('wp_ajax_nopriv_pp_album_review_post_pages', array( $this, 'insert_pages' ) );
		add_action('wp_ajax_pp_album_review_post_pages', array( $this, 'insert_pages' ) );

		add_action('wp_ajax_nopriv_pp_album_review_insert_comments', array( $this, 'insert_comments' ) );
		add_action('wp_ajax_pp_album_review_insert_comments', array( $this, 'insert_comments' ) );

//		add_meta_box("pp_album_review_page_meta", "Album Pages", "pp_album_review_page_meta", "pp_album", "normal", "high");
	}




	/**
	 * Admin initializations.
	 *
	 * @since    1.0.0
	 */
	public function admin_init( ){
		add_image_size( 'album-page', PP_ALBUM_BASE_WIDTH, PP_ALBUM_BASE_HEIGHT );
		add_image_size( 'album-thumb', PP_ALBUM_THUMB_WIDTH, PP_ALBUM_THUMB_HEIGHT );
		add_image_size( 'album-tablet', PP_ALBUM_TABLET_WIDTH, PP_ALBUM_TABLET_HEIGHT );
	}

	/**
	 * Logging.
	 *
	 * @since    1.0.0
	 */
	public function log( $logentry ){
		if( !defined(WP_DEBUG_LOG) ) {
			$upload_dir = wp_upload_dir();
			file_put_contents( $upload_dir['basedir'] . '/pp_album.log' , "[" . current_time( 'mysql' ) . "] " . $logentry ."\n\n", FILE_APPEND | LOCK_EX);
		}
	}

	/**
	 * Register admin settings meta box
	 *
	 * @since    1.0.0
	 */
	public function add_album_review_settings_submenu( ) {
		add_submenu_page( 'edit.php?post_type=pp_album', 'Album Reviewer Settings', 'Settings', 'manage_options', 'album-reviewer-settings-menu', array( $this, 'pp_album_review_view_settings' ) );
	}

	/**
	 * Register post settings meta box
	 *
	 * @since    1.0.0
	 */
	public function add_post_settings_meta_box( $post_type, $post ) {
		$this->log('add_post_settings_meta_box ' . $post_type);

		if ( 'pp_album' == $post_type ){
			add_meta_box(
				'pp_album_review_post_settings_meta_box'
				,__( 'Album Spreads', 'pp_album_review_textdomain' )
				,array( $this, 'post_settings_meta_box' )
				,'pp_album'
				,'normal'
				,'high'
			);

		}
	}

	function post_settings_meta_box() {
	  global $post;
	?>
	<?php
	if(get_post_meta( get_the_ID(), '_pp_album_pages', true) == 'pages')
	{
	?>
		<button type="button" class="btn btn-primary" id="upload_page_button" data-loading-text="Loading...">Upload Pages</button>
	<?php
	} else if (get_post_meta( get_the_ID(), '_pp_album_pages', true) == 'spreads' /*|| pp_album_review_get_page_count( get_the_ID()) > 0 */) {
	?>
		<button type="button" class="btn btn-primary" id="upload_spread_button" data-loading-text="Loading...">Upload Spreads</button>
	<?php
	} else {
	?>
		<button type="button" class="btn btn-primary" id="upload_spread_button" data-loading-text="Loading...">Upload Spreads</button><span id='upload_txt'> <!-- or </span>
		<button type="button" class="btn btn-primary" id="upload_page_button" data-loading-text="Loading...">Upload Pages</button> -->
	<?php
	}
	?>
		<div class='btn-toolbar' style='diplay:block; float:right; margin:0px'>
		<div class="btn-group">
<!--
		  <a id="parent_sort_btn" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" href="#">
			Sort
			<span class="caret"></span>
		  </a>
		  <ul class="dropdown-menu">
				<li id='sort_filename_ascend'><a disabled>Sort ascending filenames</a></li>
				<li id='sort_filename_descend'><a disabled>Sort decending filenames</a></li>
		  </ul>
-->
		</div>
<!--
		<div class='btn-group'  id='thumb_btn_group'>

		 <button type="button" class='btn btn-primary' id='thumbs_s'><i class='icon-minus-sign'></i></button>
		 <button type="button" class='btn btn-primary' id='thumbs_m'><i class='icon-th-large'></i></button>
		 <button type="button" class='btn btn-primary' id='thumbs_l'><i class='icon-plus-sign'></i></button>
		</div>
-->
		</div>

		<div id="result-album-pages">
		</div>

		<script id="album-pages" type="text/x-handlebars-template">
			<div id="result">
				{{#list pages}}
					<div class='col-sm-1'><i class='fa fa-bars'></i></div>
					<div class='col-sm-3'>{{filename}}</div>
					<div class='col-sm-3'><img src='{{thumb}}'/></div>
				{{/list}}
			</div>
		</script>

	<script type="text/x-tmpl" id="tmpl-demo">
		<h3>{%=o.title%}</h3>
		<h4>Pages (Click and drag a thumbnail to sort)</h4>
		<ul id="sort-me">
			<li></li>
		{% for (var i=0; i<o.pages.length; i++) { %}

			<li>
			<div class='row-fluid sort-me-row'>
				<span class="span1 grabber"><i class='icon-reorder' style='color:black;'></i></span>
				<img class='span6 album_thumbs' src="{%=o.pages[i]%}" style='width:200px;' />
				<span class='name span2 offset1' value="{%=o.IDs[i]%}">{%=o.filenames[i]%}</span>
				<a class="replace span1 offset1" value="{%=o.IDs[i]%}" >
					<span> Replace </span>
				</a>
				<a class="delete span1" value="{%=o.IDs[i]%}" >
					<span> Delete </span>
				</a>
				<div class="span1" value="{%=o.IDs[i]%}" >
					<input type="checkbox" name="deletebox" class="deletebox" value="{%=o.IDs[i]%}" />
				</div>
			</div>
			</li>
		{% } %}
		</ul>


	</script>
		</p>
	  <input type="hidden" id="the_post_id" value="<?php $post_id=get_the_ID(); echo $post_id;?>"/>

	<?php
	}

	/**
	 * Register settings meta box
	 *
	 * @since    1.0.0
	 */
	function pp_album_review_view_settings(){
		$options = array();
		$options = apply_filters('pp_album_review_admin_view_options', $options);

		$user_name = get_option('pp_album_review_user_name');
		$user_email = get_option('pp_album_review_user_email');
		$msg_to_client = get_option('pp_album_review_notify_msg_to_client');
	  $msg_to_client = urldecode($msg_to_client);

	  $notify_msg_to_client = get_option('pp_album_review_notify_msg_to_client2');
	  $notify_msg_to_client = urldecode($notify_msg_to_client);

		$logo = get_option('$this->logo');
	  $bg_pattern = get_option('pp_album_review_bg_pattern');
	  $bg_pattern_repeat_type = get_option('pp_album_review_bg_pattern_repeat_type');
		$pagination = get_option('pp_album_review_use_page_num');

		if($pagination = '' || $pagination == 'pages')
		{
			$radio_val_pages = "checked='checked'";
			$radio_val_spreads = "";
		}
		else
		{
			$radio_val_spreads = "checked='checked'";
			$radio_val_pages = "";
		}
		$bg_color = get_option('pp_album_review_bg_color');
		$font_color = get_option('pp_album_review_font_color');
		$block_color = get_option('pp_album_review_block_color');
		$greeting_font_size	= get_option('pp_album_review_greeting_font_size');
		$greeting_font_size = $greeting_font_size ? $greeting_font_size : 100;
	  $title_font_size = get_option('pp_album_review_title_font_size');
	  $title_font_size = $title_font_size ? $title_font_size : 100;
	  $logo_size = get_option('$this->logo_size');
	  $logo_size = $logo_size ? $logo_size : 100;

	  $ga_code = get_option('pp_album_review_ga_property_id');
	  $custom_css_code = get_option('pp_album_review_custom_css_code');
	  $custom_js_code = get_option('pp_album_review_custom_js_code');
	  $redirect_page = get_option('pp_album_review_redirect_page');

	  $user = wp_get_current_user();

	?>
	<div class="col-sm-12 col-sm-offset-0">
	<h2>Album Reviewer Settings </h2>

	<div class="row">
	<div class="col-sm-8">

	<div class="tabbable">
	  <ul class="nav nav-tabs">
		<li class="active"><a href="#tab1" data-toggle="tab">Basic Settings</a></li>
<?php
	if(isset($options['pro_enabled'])){
?>
		<li><a href="#tab2" data-toggle="tab">Appearance</a></li>
<?php
	}
?>
		<!-- <li><a href="#tab3" data-toggle="tab">Advanced</a></li> -->
		<li><a href="#tab4" data-toggle="tab">About</a></li>
	  </ul>

	   <div class="tab-content">
		   <div class="tab-pane active" id="tab1">

	<div class='offset0' style='margin-bottom:20px;'><button class='btn btn-lg btn-success submit_button' type='button' data-loading-text='Settings Saved' id=''>Save Settings</button><br></div>
	<form id='album_reviewer_settings' class="form-horizontal">
	  <div class='control-group'>
		  <label class='control-label' for="user_name">Studio Name</label>
		  <div class='controls'>
			  <div class="input-prepend">
				<span class="add-on"><i class="icon-camera"></i></span>
				<input type="text" class="span4" id="user_name" name="user_name" placeholder="Studio Name" value="<?php echo $user_name; ?>">
			  </div>
		  </div>
	  </div>
	  <div class='control-group'>
		  <label class='control-label' for="user_email">Studio Email</label>
		  <div class='controls'>
			  <div class="input-prepend">
				<span class="add-on"><i class="icon-envelope"></i></span>
				<input type="email" class="span4" id="user_email" name="user_email" placeholder="email@studio.com" value="<?php echo $user_email; ?>">
			  </div>
		  </div>
	  </div>
<?php
	if(isset($options['pro_enabled'])){
?>

	  <div class='control-group' id='msg_to_client_group'>
		  <label class='control-label' for="msg_to_client">Default Greeting Message</label>
		  <div id="msg_to_client_group_img1" style='display:none'>
		  <img src='<?php $pluginpath = plugins_url() . '/albumreviewer/img/client-greeting.jpg'; echo $pluginpath; ?>' width='180px'>
		  </div>
		  <div class='controls'>
			 <textarea class="span8 required" id="msg_to_client" name="msg_to_client" rows=5><?php echo $msg_to_client; ?></textarea>
		  </div>
	  </div>

	  <div class='control-group' id='notify_msg_to_client_group'>
		  <label class='control-label' for="notify_msg_to_client">Default Client Email Template</label>
		<div class='controls'>
		   <textarea class="span8 required" id="notify_msg_to_client" name="notify_msg_to_client" rows=5><?php echo $notify_msg_to_client; ?></textarea>
		  </div>
	  </div>

<?php
	}
?>

	</form>


	</div>


	<div class="tab-pane" id="tab2">
	<div class='offset0' style='margin-bottom:20px;'><button class='btn btn-lg btn-success submit_button' type='' data-loading-text='Settings Saved' id=''>Save Settings</button><br></div>
	<form id='album_reviewer_settings_2' class="form-horizontal">

<!--
	  <div class='control-group' id='title_font_group'>
		  <label class="control-label" id='title_font_label'>Title Font</label>
		  <div id="title_group_img1" style='display:none'>
		  <img src='<?php $pluginpath = plugins_url() . '/albumreviewer/img/title-font.jpg'; echo $pluginpath; ?>' width='180px'>
		  </div>
		  <div class='controls' id='title_font_control'>
			 <input id="title-font" type="text" />
		  </div>
	  </div>

	  <div class='control-group'>
		<label class="control-label">Title Font Size %</label>
	    <div class='controls'>
		  <div id="sl2"  style="width: 260px; margin: 15px;" data-slider-value="<?php echo $title_font_size; ?>" data-slider-min="25" data-slider-max="200" data-slider-step="1"><span id="sl2_text" style="display:block;margin-left:300px;line-height:20px;"></span></div>
		</div>
	  </div>

	  <div class='control-group' id='greeting_font_group'>
		  <label class="control-label">Greeting Font</label>
		  <div class='controls'>
			 <input id="font" type="text" />
		  </div>
	  </div>

	  <div class='control-group'>
		 <label class="control-label">Greeting Font Size %</label>
	   <div class='controls'>
		  <div id="sl1"  style="width: 260px; margin: 15px;" data-slider-value="<?php echo $greeting_font_size; ?>" data-slider-min="20" data-slider-max="200" data-slider-step="1"><span id="sl1_text" style="display:block;margin-left:300px;line-height:20px;"></span></div>

		 </div>
	  </div>

	  <div class='control-group'>
		 <label class="control-label">Title/Greeting Font Color</label>
		 <div class='controls'>
			<input type='hidden' id="font_color_picker" value='<?php echo $font_color; ?>'>
		 </div>
	  </div>
-->
<?php
	if(isset($options['pro_enabled'])){
?>

	<!-- Multiple Radios -->
	<div class="control-group">
	  <label class="control-label">Pagination Terminology</label>
	  <div class="controls">
		<label class="radio">
		  <input type="radio" name="pagination" value="pages" id="use_pages" <?php echo $radio_val_pages; ?>>
		  Use Pages (e.g. Page 1 to 2)
		</label>
		<label class="radio">
		  <input type="radio" name="pagination" value="spreads" id="use_spreads" <?php echo $radio_val_spreads; ?>>
		  Use Spreads (e.g. Spread 1)
		</label>
	  </div>
	</div>

	<div class='control-group'>
	  <label class="control-label">Studio Logo</label>
	  <div id="logo_img1" style='display:none'>
		<img src='<?php $pluginpath = plugins_url() . '/albumreviewer/img/logo.jpg'; echo $pluginpath; ?>' width='180px'>
	  </div>
		<div class='controls'>
			<button class='btn span2 btn-primary' type='button' id='logo_select_button'>Select Logo &raquo;</button>
		<button class='btn span2 btn-primary' type='button' id='logo_remove_button' style='display:none;'>Remove Logo &raquo;</button>
	  </div>
	</div>

	<div class='control-group'>
	  <label class="control-label">Logo Size</label>
	  <div class='controls'>
		  <div id="logo_slider"  style="width: 260px; margin: 15px;" data-slider-value="<?php echo $greeting_font_size; ?>" data-slider-min="10" data-slider-max="200" data-slider-step="1"><span id="logo_slider_text" style="display:block;margin-left:300px;line-height:20px;"></span></div>

	  </div>

	  <div class='controls' id='logo_div'>
			  <img id='logo' src='<?php echo $logo; ?>' style="display:block;" />
	  </div>
	</div>
<?php
	}
?>


<!--
	<div class='control-group' id='block_color_picker_group'>
	  <label class="control-label">Header Block Background Color</label>
	  <div id="header_block_img1" style='display:none'>
		<img src='<?php $pluginpath = plugins_url() . '/albumreviewer/img/header-block-color.jpg'; echo $pluginpath; ?>' width='180px'>
	  </div>
		<div class='controls'>
			<input type='hidden' id="block_color_picker" value='<?php echo $block_color; ?>'/>
		</div>
	</div>

	<div class='control-group'>
		<label class="control-label">Background Color</label>
		<div class='controls'>
			<input type='hidden' id="background_color_picker" value='<?php echo $bg_color; ?>' />
		</div>
	</div>

	<div class='control-group'>
		<label class="control-label">Background Pattern</label>
	  <div id="bg_pattern_img1" style='display:none'>
		<img src='<?php $pluginpath = plugins_url() . '/albumreviewer/img/bg-pattern.jpg'; echo $pluginpath; ?>' width='180px'>
	  </div>
	  <div class='controls'>
		<button class='btn span2 btn-primary' type='button' id='bg_pattern_select_button'>Select Pattern &raquo;</button>
		<button class='btn span2 btn-primary' type='button' id='bg_pattern_remove_button' style='display:none;'>Remove Pattern &raquo;</button>
	  </div>
	  <div id='bg_pattern_div' class='controls'>
		<img id='bg_pattern' src='<?php echo $bg_pattern; ?>' style="display:block; max-width:350px;"/>
	  </div>
	</div>


	<div class='control-group' id='bg_pattern_repeat_type' style='display:none;'>
		<label class="control-label">Background Pattern Repeat</label>
	  <div class='controls'>
	<select name='bg_pattern_selection' id='bg_pattern_selection'>
	  <option <?php if($bg_pattern_repeat_type=='1'){echo "selected='selected'";} ?> value='1'>Horizontally and Vertically</option>
	  <option <?php if($bg_pattern_repeat_type=='2'){echo "selected='selected'";} ?> value='2'>Horizontally</option>
	  <option <?php if($bg_pattern_repeat_type=='3'){echo "selected='selected'";} ?> value='3'>Vertically</option>
	  <option <?php if($bg_pattern_repeat_type=='4'){echo "selected='selected'";} ?> value='4'>None</option>
	</select>
	  </div>
	</div>

	<div class="control-group">
	  <label class="control-label">Draw page divider overlay on album spreads</label>
	  <div class="controls">
		<label class="checkbox">
		  <input type="checkbox" name="draw_divider" id='draw_divider' <?php if(get_option('pp_album_review_draw_divider') == '1') echo "checked='checked'";?>>
		  Check me to draw a black line in the middle of album spreads to show users where the page splits.
		</label>
	  </div>
	</div>
-->
	</form>


	</div>

<!--
	<div class="tab-pane" id="tab3">
	<div class='offset0' style='margin-bottom:20px;'><button class='btn btn-lg btn-success submit_button' type='' data-loading-text='Settings Saved' id=''>Save Settings</button><br></div>
	<form id='album_reviewer_settings_2' class="form-horizontal">

	  <div class='control-group' id='ga_group'>
	  <label class='control-label' for="ga_property_id">Google Analyics Web Property ID</label>
		<div class='controls'>
		  <div class="input-prepend">
		  <span class="add-on"><i class="icon-bar-chart"></i></span>
			  <input type="text" class="span4" id="ga_property_id" name="ga_property_id" placeholder="UA-XXXXX-Y" value="<?php echo $ga_code; ?>">
		  </div>
		  </div>
	  </div>

	  <div class='control-group' style="display:none">
		  <label class="control-label">Album Title Font</label>
		<div class='controls'>
		   <input id="title-font-dummy" type="text" />
		</div>
	  </div>


	  <div class='control-group' id='custom_css_code_group'>
		  <label class='control-label' for="custom_css_code">Custom CSS</label>
		<div class='controls'>
		   <textarea class="span8 required" id="custom_css_code" name="custom_css_code" rows=5><?php echo $custom_css_code; ?></textarea>
		  </div>
	  </div>

	  <div class='control-group' id='custom_js_code_group'>
		  <label class='control-label' for="custom_js_code">Custom Javascript</label>
		<div class='controls'>
		   <textarea class="span8 required" id="custom_js_code" name="custom_js_code" rows=5><?php echo $custom_js_code; ?></textarea>
		  </div>
	  </div>

	  <div class="control-group">
		<label class="control-label">Disable Comments When Album is Approved</label>
		<div class="controls">
		  <label class="checkbox">
			<input type="checkbox" name="disable_comments" id='disable_comments' <?php if(get_option('pp_album_review_auto_diable_comments') == '1') echo "checked='checked'";?>>
			Check this option to disable comments once the client has approved his/her album.
		  </label>
		</div>
	  </div>

	  <div class="control-group">
		<label class="control-label">Clear All Data on Deactivation</label>
		<div class="controls">
		  <label class="checkbox">
			<input type="checkbox" name="clear_database" id='clear_database' <?php if(get_option('pp_album_review_clr_db') == '1') echo "checked='checked'";?>>
			Check me to clear all data on deactivation.
		  </label>
		</div>
	  </div>

	  <div class='control-group' id='redirect_page_group'>
		  <label class='control-label' for="redirect_page">Approval Page Redirection</label>
		<div class='controls'>
		   <input type="text" class="span10" id="redirect_page" name="redirect_page" placeholder="http://mysite.com/album_approved" value="<?php echo $redirect_page; ?>">
		  </div>
	  </div>

	</form>

	</div>
-->

	<div class='tab-pane' id='tab4'>
	  <h1>Version</h1>
	  <p>You are currently using Album Reviewer Version <?php echo $this->version; ?>.</p>
	  <p>You are currently using WordPress Version <?php echo get_bloginfo('version'); ?>.</p>
<!--
	  <h1>Support</h1>
	  <p>For support, please send us <a href='mailto:support@photopressplugins.com?subject=Support Request&body='>email</a>.</p>
	  <p>Please describe the issue you are seeing and include as much information as possible:</p>
	  <ul class="list-group">
	  <li class="list-group-item">   1) a description of the problem</li>
	  <li class="list-group-item">   2) a URL link to your album review (if applicable)</li>
	  <li class="list-group-item">   3) screenshots (if applicable) </li>
	  <li class="list-group-item">   4) WordPress and Album Revewer plugin version (copy/paste the version info above).</li>
	  </ul>
	  <h1>News</h1>
	  <p>For the latest news and future product releases, please join our <a href='https://www.facebook.com/PhotopressPlugins'><i class='icon-ok icon-facebook-sign' style='color:blue'> Facebook Page</i></a></p>
-->

	</div>

	</div>

	</div>


	</div>
	<div class='col-sm-4'>
	  <div class='col-sm-10'>
		<form action="https://www.getdrip.com/forms/6106001/submissions" method="post" target="_blank" data-drip-embedded-form="6106001">
		  <h3 data-drip-attribute="headline">Upgrade to Pro!</h3>
		  <p data-drip-attribute="description">Get 20% off. Submit your name and email and we'll send you a coupon for 20% off your upgrade to the pro version</p>
			<div>
				<label for="fields[email]">Email Address</label><br />
				<input type="email" name="fields[email]" value="<?php echo esc_attr( $user->user_email ); ?>" />
			</div>
		  <div>
			<input type="submit" name="submit" value="Get my discount" data-drip-attribute="sign-up-button" />
		  </div>
		</form>
	  </div>
	</div>
	</div>

	</div>

	<?php
	}

	/**
	 * Register settings meta box
	 *
	 * @since    1.0.0
	 */
	public function add_settings_meta_box(){
//		$this->log('add_settings_meta_box');
		add_meta_box("pp_album_review_settings", "Album Settings", array( $this , "render_settings_view" ), "pp_album", "normal", "high");
	}


	/**
	 * Renders the settings view.
	 *
	 * @since    1.0.0
	 */
	public function render_settings_view ( $post ) {
//		$this->log('render_settings_view');
		echo "<h1>Settings</h1>";
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles( $hook ) {
		$post_type = isset($_REQUEST['post']) ? get_post_type($_REQUEST['post']) : null;

		$this->log('enqueue_styles ' . $hook);
		if(isset($_REQUEST['post_type'])){
			$post_type = $_REQUEST['post_type'];
		}
		$this->log('post_type: ' . $post_type);


		$bootstrap_css = plugins_url('albumreviewer/bower_components/bootstrap/dist/css/bootstrap.css');
		$bootstrap_slider_css = plugins_url('albumreviewer/bower_components/seiyria-bootstrap-slider/dist/css/bootstrap-slider.min.css');
		$fontawesome_css = plugins_url('albumreviewer/bower_components/font-awesome/css/font-awesome.min.css');

		switch( $hook ){
			case 'pp_album_page_album-reviewer-settings-menu' :
				$this->log( $bootstrap_css );
				wp_enqueue_style( 'pp_album_review_bootstrap',
					$bootstrap_css );
				wp_enqueue_style( 'pp_album_review_bootstrap_slider',
					$bootstrap_slider_css );
				wp_enqueue_style( $this->album_reviewer, plugin_dir_url( __FILE__ ) . 'css/album-reviewer-admin.css', array(), $this->version, 'all' );
				wp_enqueue_style( 'pp_album_review_font_awesome', $fontawesome_css, array(), $this->version, 'all' );

				break;
			case 'post.php':
			case 'post-new.php':
				if('pp_album' != $post_type){
					return;
				}
				$this->log( $bootstrap_css );
				wp_enqueue_style( 'pp_album_review_bootstrap',
					$bootstrap_css );
				wp_enqueue_style( 'pp_album_review_bootstrap_slider',
					$bootstrap_slider_css );
				wp_enqueue_style( $this->album_reviewer, plugin_dir_url( __FILE__ ) . 'css/album-reviewer-admin.css', array(), $this->version, 'all' );
				wp_enqueue_style( 'pp_album_review_font_awesome', $fontawesome_css, array(), $this->version, 'all' );

				break;
		}

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts( $hook ) {
		$post_type = isset($_REQUEST['post']) ? get_post_type($_REQUEST['post']) : null;

		$this->log('enqueue_scripts ' . $hook);
		$this->log(serialize($_REQUEST));
		$this->log($post_type);

		switch( $hook ){

			case 'pp_album_page_album-reviewer-settings-menu' :

				/* Setup AJAX hooks for album-admin-settings */
				wp_register_script( $this->album_reviewer, plugin_dir_url( __FILE__ ) . 'js/album-reviewer-admin-settings.js');
				$params = array(
						'ajaxurl' => admin_url( 'admin-ajax.php' )
				);
				wp_localize_script( $this->album_reviewer, 'pp_album_review_admin_settings', $params );
				wp_enqueue_script( $this->album_reviewer );

				wp_enqueue_script( 'pp_album_review_bootstrap',
					plugins_url('albumreviewer/bower_components/bootstrap/dist/js/bootstrap.js'),
					array( 'jquery' ),
					$this->version,
					false);

				wp_enqueue_script( 'pp_album_review_bootstrap_slider',
					plugins_url('albumreviewer/bower_components/seiyria-bootstrap-slider/dist/bootstrap-slider.min.js'),
					array( 'jquery' ),
					$this->version,
					false);

				break;

			case 'post-new.php':
				$post_type = isset($_REQUEST['post_type']) ?($_REQUEST['post_type']) : null;

			case 'post.php':
				if( $hook == 'post.php' )
					$post_type = isset($_REQUEST['post']) ? get_post_type($_REQUEST['post']) : null;

				if('pp_album' != $post_type){
					return;
				}

				// load jquery
				wp_enqueue_script( 'jquery-ui-core' );
				wp_enqueue_script( 'jquery-ui-sortable' );
				wp_enqueue_script( 'jquery-ui-draggable' );
				wp_enqueue_script( 'jquery-ui-droppable' );
				wp_enqueue_script( 'jquery-ui-selectable' );
				wp_enqueue_script( 'pp_album_review_validate_js',
					plugins_url('albumreviewer/bower_components/jquery-validation/dist/jquery.validate.min.js'),
					array( 'jquery' ),
					$this->version,
					false);

				wp_enqueue_script( 'pp_album_review_bootstrap',
					plugins_url('albumreviewer/bower_components/bootstrap/dist/js/bootstrap.js'),
					array( 'jquery' ),
					$this->version,
					false);

				wp_enqueue_script( 'pp_album_review_handlebars',
					plugins_url('albumreviewer/bower_components/handlebars/handlebars.min.js'),
					array( 'jquery' ),
					$this->version,
					false);

				/* Setup AJAX hooks for album-admin-settings */
				wp_register_script( $this->album_reviewer, plugin_dir_url( __FILE__ ) . 'js/album-reviewer-admin-edit-post.js');
				$params = array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) );
				wp_localize_script( $this->album_reviewer, 'pp_album_review_admin', $params );
				wp_enqueue_script( $this->album_reviewer );


				break;

			default :
				break;

		}


		if(function_exists('wp_enqueue_media')) {
			wp_enqueue_media();
		}
		else {
			wp_enqueue_script('media-upload');
			wp_enqueue_script('thickbox');
			wp_enqueue_style('thickbox');
		}


	}


	/**
	 * Handler for admin settings save.
	 *
	 * @since    1.0.0
	 */
	public function pp_album_review_save_settings( ){
		$this->log('pp_album_review_save_settings entered');

		update_option( 'pp_album_review_user_name' ,  $_REQUEST['user_name']);
		update_option( 'pp_album_review_user_email' ,  trim($_REQUEST['user_email']));
		update_option( 'pp_album_review_use_page_num' , $_REQUEST['pagination']);
		update_option( 'pp_album_review_notify_msg_to_client' , (stripslashes($_REQUEST['msg_to_client'])));
		update_option( 'pp_album_review_notify_msg_to_client2' , (stripslashes($_REQUEST['notify_msg_to_client'])));
		update_option( 'pp_album_review_draw_divider', $_REQUEST['draw_divider']);
		update_option( '$this->logo' , $_REQUEST['logo']);
		update_option( 'pp_album_review_bg_pattern' , $_REQUEST['bg_pattern']);
		update_option( 'pp_album_review_bg_pattern_repeat_type', $_REQUEST['bg_pattern_repeat_type']);
		update_option( 'pp_album_review_bg_color', $_REQUEST['bg_color']);
		update_option( 'pp_album_review_block_color', $_REQUEST['block_color']);
		update_option( 'pp_album_review_clr_db', $_REQUEST['clr_db']);
		update_option( 'pp_album_review_auto_diable_comments', $_REQUEST['disable_comments']);

		update_option('pp_album_review_custom_css_code', (stripslashes($_REQUEST['custom_css_code'])));
		update_option('pp_album_review_custom_js_code', (stripslashes($_REQUEST['custom_js_code'])));

		if(array_key_exists('font', $_REQUEST))
			update_option( 'pp_album_review_font', $_REQUEST['font']);
		if(array_key_exists('font_color', $_REQUEST))
			update_option( 'pp_album_review_font_color', $_REQUEST['font_color']);
		if(array_key_exists('font_style', $_REQUEST))
			update_option( 'pp_album_review_font_style', $_REQUEST['font_style']);
		if(array_key_exists('title_font', $_REQUEST))
			update_option( 'pp_album_review_title_font', $_REQUEST['title_font']);
		if(array_key_exists('title_font_style', $_REQUEST))
			update_option( 'pp_album_review_title_font_style', $_REQUEST['title_font_style']);
		if(array_key_exists('greeting_font_size', $_REQUEST))
			update_option( 'pp_album_review_greeting_font_size', $_REQUEST['greeting_font_size']);
		if(array_key_exists('title_font_size', $_REQUEST))
			update_option( 'pp_album_review_title_font_size', $_REQUEST['title_font_size']);
		if(array_key_exists('logo_size', $_REQUEST))
			update_option( '$this->logo_size', $_REQUEST['logo_size']);
		if(array_key_exists('ga_property_id', $_REQUEST))
			update_option( 'pp_album_review_ga_property_id', $_REQUEST['ga_property_id']);
		if(array_key_exists('redirect_page', $_REQUEST))
			update_option( 'pp_album_review_redirect_page', $_REQUEST['redirect_page']);



		$this->log('pp_album_review_save_settings ' . serialize($_REQUEST) . ' ' . (stripslashes($_REQUEST['msg_to_client'])));

		require_once(ABSPATH . '/wp-includes/class-wp-ajax-response.php');

		$response = new WP_Ajax_Response;
		$response->add( array('data' => 'success',
						'supplemental' => array(
						'message' => 'success'),
						)
		);

		$this->log('pp_album_review_save_settings response send');
		$response->send();

		die();
	}


	/**
	 * Saves album spread selections.
	 *
	 * @since    1.0.0
	 */
	 public function insert_pages( ){
		// need to check if each page has a resized version.
		$this->log('insert_pages entered');

		$post_id = absint($_REQUEST['post_id']);
		$attachments = $_REQUEST['attachments'];
		if(array_key_exists('page_type', $_REQUEST)) {
			$page_type = $_REQUEST['page_type'];
			if($page_type == 'spreads')
				update_post_meta($post_id, '_pp_album_pages', 'spreads');
			else if($page_type == 'pages')
				update_post_meta($post_id, '_pp_album_pages', 'pages');
		} else {
			$page_type = '';
		}

		$this->log($post_id);
		$this->log(serialize($attachments));

		if($attachments[0] != '0') {
			$this->log('calling pages insert');
			$result = $this->pages->insert($post_id, $attachments);
			$this->log(serialize($attachments));
		}

		// auto publish post after pages are posted
	//	wp_publish_post( $post_id );

		$all_pages = $this->pages->get_page_thumbnails( $post_id );
		$this->log('get_page_thumbnails: ' . serialize( $all_pages) );

		$all_filenames = $this->pages->get_page_file_locations( $post_id );
		$this->log('get_page_file_locations: ' . serialize( $all_filenames) );

		$all_page_ids = $this->pages->get_page_ids( $post_id );
		$this->log('get_page_ids: ' . serialize( $all_page_ids) );
		$this->log('page_type: ' . serialize( $page_type) );

		$this->log('now generate response ' . count( $all_pages) );

		$output = array();

    	for ( $i = 0; $i < count( $all_pages ); ++$i) {
			array_push($output, array(
			'thumb' => $all_pages[$i],
			'id' => $all_page_ids[$i],
			'filename' => $all_filenames[$i] ) );
    	}

		wp_send_json( array(
			'data' => 'success',
			'result'   => $output,
			'page_type' => $page_type
		) );
	}

	/**
	 * Saves album spread selections.
	 *
	 * @since    1.0.0
	 */
	public function insert_comments( ){
		$post_id = absint( $_REQUEST['post_id']);
		$page_id = absint( $_REQUEST['page_id']);
		$comment = $_REQUEST['comment'];
		$comment = sanitize_text_field($comment);

		$comment_from = $_REQUEST['comment_from'];

		if($comment_from === 'designer_comment')
			$commenter = PP_ALBUM_DESIGNER;
		else
			$commenter = PP_ALBUM_CLIENT;

		if( !$post_id || !$page_id ){
			wp_send_json( array(
				'data' => 'error',
				'result' => 'Invalid post_id or page_id.'
			) );
		}


		$result = $this->comments->insert($page_id, $comment, $commenter);

		$this->log('pp_album_review_insert_comment_ajax: ' . $post_id . ' ' . $page_id .' '.$commenter);

		$comments = $this->comments->read_all( $post_id );
//		$comments = 'testing';

		if($commenter == PP_ALBUM_CLIENT){
			add_post_meta($post_id, 'pp_album-view_stat_time_ip', 'PP_ALBUM_COMMENT_ADDED' . ',' .
				$_SERVER['REMOTE_ADDR'] . ',' .
				date_i18n( 'Y-m-d H:i:s',  (int)current_time( 'timestamp' )) . ',' .
				$page_id . ',' .
				$comment);
			update_post_meta($post_id, 'pp_album_new_comments', '1');
		}

		wp_send_json( array(
			'data' => 'success',
			'post_id'  => $post_id,
			'page_id'  => $page_id,
			'message' => $comments
		) );
	}



}
