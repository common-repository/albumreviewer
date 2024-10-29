<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://albumreviewer.co
 * @since      1.0.0
 *
 * @package    Album_Reviewer
 * @subpackage Album_Reviewer/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Album_Reviewer
 * @subpackage Album_Reviewer/includes
 * @author     Photopress Support <support@photopressplugins.com>
 */
class Album_Reviewer {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Album_Reviewer_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The render that's responsible for rendering all public facing views.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Album_Reviewer_Render    $renderer    Maintains and registers all hooks for the plugin.
	 */
	protected $renderer;

	/**
	 * The model is responsible for business logic and database interaction with album pages.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Album_Reviewer_Model    $pages
	 */
	protected $pages;


	/**
	 * The model is responsible for business logic and database interaction with album comments.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Album_Reviewer_Model    $comments
	 */
	protected $comments;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $album_reviewer    The string used to uniquely identify this plugin.
	 */
	protected $album_reviewer;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		$this->album_reviewer = 'album-reviewer';
		$this->version = '2.0.0';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

		// custom post setup
		$this->define_custom_post_type();
		$this->define_cpt_icon();
		$this->define_check_album_review();
		$this->define_save_cpt_hook();
	}

	public function log( $logentry ){
		if( !defined(WP_DEBUG_LOG) ) {
			$upload_dir = wp_upload_dir();
			file_put_contents( $upload_dir['basedir'] . '/pp_album.log' , "[" . current_time( 'mysql' ) . "] " . $logentry ."\n\n", FILE_APPEND | LOCK_EX);
		}
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Album_Reviewer_Loader. Orchestrates the hooks of the plugin.
	 * - Album_Reviewer_i18n. Defines internationalization functionality.
	 * - Album_Reviewer_Admin. Defines all hooks for the admin area.
	 * - Album_Reviewer_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-album-reviewer-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-album-reviewer-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-album-reviewer-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-album-reviewer-public.php';

		/**
		 * The class responsible for rendering the public facing views of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-album-reviewer-renderer.php';


		/**
		 * The class responsible for rendering the public facing views of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/models/class-album-reviewer-model-pages.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/models/class-album-reviewer-model-comments.php';

		$this->loader = new Album_Reviewer_Loader();
		$this->pages = new Album_Reviewer_Model_Pages();
		$this->comments = new Album_Reviewer_Model_Comments( $this->pages );
		$this->renderer = new Album_Reviewer_Renderer( $this->pages, $this->comments );
//		$this->admin = new Album_Reviewer_Admin();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Album_Reviewer_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Album_Reviewer_i18n();
		$plugin_i18n->set_domain( $this->get_album_reviewer() );

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {
		$this->log('got define_admin_hooks');

		$plugin_admin = new Album_Reviewer_Admin( $this->get_album_reviewer(), $this->pages, $this->comments, $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
//		$this->loader->add_action( 'add_meta_boxes', $plugin_admin, 'add_post_settings_meta_box' );
//		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_album_review_settings_submenu' );
		$this->loader->add_action( 'admin_notices', $this, 'save_error_notice', 1, 0 );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Album_Reviewer_Public( $this->get_album_reviewer(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

	}

	/**
	 * Register custom post type for album reviews
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_custom_post_type() {
		$this->loader->add_action( 'init', $this, 'set_album_review_post_types' );
		$this->log('got define_custom_post_type');

	}

	public function set_album_review_post_types(){
		$this->log('got set_album_review_post_types');


		/* Setup the arguments for the 'pp_album' post type. */
		$labels = array(
			'name'                => _x( 'Album Reviews', 'Post Type General Name', 'pp_album_review_textdomain' ),
			'singular_name'       => _x( 'Album Review', 'Post Type Singular Name', 'pp_album_review_textdomain' ),
			'menu_name'           => __( 'Album Reviews', 'pp_album_review_textdomain' ),
			'parent_item_colon'   => __( 'Parent Album Review:', 'pp_album_review_textdomain' ),
			'all_items'           => __( 'All Album Reviews', 'pp_album_review_textdomain' ),
			'view_item'           => __( 'View Album Review', 'pp_album_review_textdomain' ),
			'add_new_item'        => __( 'Add New Album Review', 'pp_album_review_textdomain' ),
			'add_new'             => __( 'New Album Review', 'pp_album_review_textdomain' ),
			'edit_item'           => __( 'Edit Album Review', 'pp_album_review_textdomain' ),
			'update_item'         => __( 'Update Album Review', 'pp_album_review_textdomain' ),
			'search_items'        => __( 'Search Album Reviews', 'pp_album_review_textdomain' ),
			'not_found'           => __( 'No Album Reviews found', 'pp_album_review_textdomain' ),
			'not_found_in_trash'  => __( 'No Album Reviews found in Trash', 'pp_album_review_textdomain' ),
		);
		$pp_album_args = array(
			'label'               => __( 'Album Reviews', 'pp_album_review_textdomain' ),
			'description'         => __( 'Client album proofing', 'pp_album_review_textdomain' ),
			'labels'              => $labels,
			'supports'            => array( 'title', 'thumbnail' ),
			'taxonomies'          => array(),
			'hierarchical'        => false,
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_nav_menus'   => true,
			'show_in_admin_bar'   => true,
			'menu_position'       => 20,
			'menu_icon'           => '',
			'can_export'          => true,
			'has_archive'         => 'hidden-album-proofing-archive',
			'exclude_from_search' => true,
			'publicly_queryable'  => true,
			'rewrite' 			  => array('slug'=>'album-proofing','with_front'=>false, 'pages'=>false),
			'capability_type'     => 'page',
			'query_var'           => 'pp_album',
		);

		/* Register the album post type. */
		register_post_type( 'pp_album', $pp_album_args );

		if(!get_option( 'pp_album_activiated' ) ) {
			update_option('pp_album_activiated','1');
		}
	}

	/**
	 * Add template album review
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_check_album_review(){
		$this->loader->add_action('parse_request', $this, 'check_album_review',1,0);
	}

	/**
	 * Checks to see if the post type is an album review
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function check_album_review() {
		global $post_type;
		global $post;

		if ( is_admin() ) {
			return;
		}
		$current = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		$part = substr( $current, strlen( site_url() ) );

		$slug = null;

//		$this->log(get_option('permalink_structure'));

		$permalink_structure = get_option('permalink_structure');

		if( strcmp( "/%year%/%monthnum%/%day%/%postname%/", $permalink_structure ) == 0 ||
			strcmp( "/%year%/%monthnum%/%postname%/", $permalink_structure ) == 0 ||
			strcmp( "/%postname%/", $permalink_structure ) == 0 ) {
			$part = explode('/', $part);
			array_pop($part);
			$slug = array_pop($part);
			// $this->log('slug = ' . $slug);
			// $result = get_page_by_path($slug);
		} else {
			$this->log('got no slug');
			return;
		}

		// check root
		if($slug == null || $slug == '' || $slug == 'index.php')
			return;

		$args = array('name' => $slug, 'post_type' => 'pp_album');
		$query = new WP_Query( $args );

		// Proceed only if published posts with thumbnails exist
		if ( $query->have_posts() ) {
			$query->the_post();
			$this->log('got post: ' . $query->post->ID );

			wp_reset_postdata();

			// get post data
			$post_data = $this->get_post_data( $query->post );

			// don't call anything after the render
			$this->renderer->render( $post_data );
		} else {
			return;
		}

	}

	/**
	 * Gets album review post model data needed for rendering the album review view
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function get_post_data( $post ){
		$post_data = array (
			post_id => get_the_ID(),
			user_name => get_option('pp_album_review_user_name'),
			user_email => get_option('pp_album_review_user_email'),
			client_name => get_post_meta( get_the_ID(), '_pp_album_client_name', true ),
			client_email => get_post_meta( get_the_ID(), '_pp_album_client_email', true ),
			disable_comments => get_post_meta( get_the_ID(), '_pp_album_disable_comments', true),
			logo => get_option('pp_album_review_logo'),
			comment_from => is_user_logged_in() ? 'designer_comment' : 'client_comment',
			font_style => get_option('pp_album_review_font_style'),
			font_color => get_option('pp_album_review_font_color'),
			font_size => get_option('pp_album_review_greeting_font_size'),
			logo_scale => get_option('pp_album_review_logo_size'),
			redirect_page =>  get_option( 'pp_album_review_redirect_page'),
			album_title => get_post_meta(get_the_ID(), '_pp_album_title', true),
			msg_to_client => get_post_meta(get_the_ID(), '_pp_album_msg_to_client', true),
			draw_divider => get_option('pp_album_review_draw_divider')

		);

		$post_data['logo_scale'] = $post_data['logo_scale'] ? $post_data['logo_scale'] : 100;
		$post_data['font_size'] = $post_data['font_size'] ? $post_data['font_size'] : 100;
		$post_data['title_font_size'] = $post_data['title_font_size'] ? $post_data['title_font_size'] : 100;

		return $post_data;
	}


	/**
	 * Register custom post type icon
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_cpt_icon(){
		$this->loader->add_action('admin_head', $this, 'set_cpt_icon',1,0);
	}

	/**
	 * Sets the custom post type icon in the admin side menu
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	 public function set_cpt_icon( ) {
		global $post_type;

		$this->log('got set_cpt_icon');

		?>
			<style type="text/css">#menu-posts-pp_album .wp-menu-image:before { content: "\f331";}</style>
		<?php
	}

	/**
	 * Register save custom post type hook
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_save_cpt_hook(){
		$this->loader->add_action('save_post', $this, 'save_cpt', 1, 1);
	}

	public function save_error_notice() {
		$count_posts = wp_count_posts('pp_album');
		if($count_posts->publish < 5){
			return;
		}
		$class = "error";
		$message = "Howdy! You have reached the maximum number of Album Review. Please upgrade to the <a href='http://albumreviewer.co' target='_blank'>Album Reviewer Pro</a> version for unlimited Album Reviews or delete any unneeded Album Reviews.";
			echo"<div class=\"$class\"> <p>$message</p></div>";
	}

	/**
	 * Save custom post type hook
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	public function save_cpt( $post_id ) {
		$options = array();
		$options = apply_filters('pp_album_review_admin_view_options', $options);

		$slug = 'pp_album';

		$_POST += array("{$slug}_edit_nonce" => '');

		$chk = array_key_exists('post_type',$_POST);

		$count_posts = wp_count_posts('pp_album');

		if(isset($options['pro_enabled']) ){

			die();
		}

		if($chk)
		{
			if ($slug != $_POST['post_type']){
				return;
			}
			if ( !current_user_can( 'edit_post', $post_id ) ) {
				return;
			}
		}

		$this->log('got save_cpt2 ');

		$client_name         = array_key_exists('client_name',$_POST) ? $_POST['client_name'] : '';
		$client_email        = array_key_exists('client_email',$_POST) ? trim($_POST['client_email']) : '';
		$msg_to_client       = array_key_exists('msg_to_client',$_POST) ? $_POST['msg_to_client'] : '';
		$disable_comments 	 = isset($_POST['disable_comments']) ? '1' : '';
		$first_page_on_right = isset($_POST['first_page_on_right']) ? '1' : '';
		$last_page_on_left = isset($_POST['last_page_on_left']) ? '1' : '';
		$has_cover = isset($_POST['has_cover']) ? '1' : '';


		/* this tag is used to let template_include hook know we are viewing a pp_album post */
		update_post_meta( $post_id, '_wp_page_template', 'ppp-album-review-template.php');
		update_post_meta( $post_id, '_pp_album_title', get_the_title($post_id));
		update_post_meta( $post_id, '_pp_album_client_name', $client_name);
		update_post_meta( $post_id, '_pp_album_disable_comments', $disable_comments);
		update_post_meta( $post_id, '_pp_album_client_email', $client_email);
		update_post_meta( $post_id, '_pp_album_msg_to_client', $msg_to_client);
		update_post_meta( $post_id, '_pp_album_first_page_on_right', $first_page_on_right);
		update_post_meta( $post_id, '_pp_album_last_page_on_left', $last_page_on_left);
		update_post_meta( $post_id, '_pp_album_has_cover', $has_cover);


		$creation_date = get_post_meta( get_the_ID(), '_pp_album_created_on', true);
		if($creation_date == NULL)
		{
			update_post_meta( $post_id, '_pp_album_created_on', date_i18n( 'Y-m-d H:i:s',  (int)current_time( 'timestamp' ), true ));
		}
		if(!get_post_meta( $post_id, '_pp_album_approved', true ))
			update_post_meta( $post_id, '_pp_album_approved', '');

		$last_update = get_post_meta( $post_id, '_pp_album_comments_sent_on', true);
		if($last_update == NULL)
		{
			update_post_meta( $post_id, '_pp_album_comments_sent_on', current_time( 'timestamp' ));
		}

		return;
	}



	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_album_reviewer() {
		return $this->album_reviewer;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Album_Reviewer_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
