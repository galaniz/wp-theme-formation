<?php

/* 
 * Theme foundation core class
 * ---------------------------
 * 
 * Description: Provide base, utilities and added functionality for building themes
 * Author: Graciela Alaniz
 * Author URI: gracielaalaniz.com
 * Version: 1.0.0
 */

namespace Foundation;

// if file is called directly abort
if( !defined( 'ABSPATH' ) ) exit;

class Index {

   /*
	* Variables
	* ---------
	*
	* Pass query args to front end when loading posts with ajax.
	*
	* @var array $load_posts_query {
	*     @type string $id. Accepts array.
	* }
	*/

	public static $load_posts_query = [];

	/*
	 * Store custom post type names and meta.
	 *
	 * @var array $cpt {
	 *     @type string $post_type. Accepts array {
	 *     		@type string $slug. Accepts string.
	 *     		@type string $label. Accepts string.
	 *     		@type string $plural_label. Accepts string.
	 *     		@type string $taxonomy. Accepts string.
	 *		}
	 * }
	 */

	public static $cpt = [];

	/*
	 * Editor color palette theme support args.
	 *
	 * @var array $editor_color_palette {
	 *     	@type string $name. Accepts string.
	 *     	@type string $slug. Accepts string.
	 *     	@type string $color. Accepts string with hex code.
	 * }
	 */

	public $editor_color_palette = [];

	/*
	 * Custom image sizes to register
	 *
	 * @var array $image_sizes {
	 *     	@type string $name. Accepts int for size.
	 * }
	 */

	public $image_sizes = [];

	/*
	 * Nav menus to register.
	 *
	 * @var array $nav_menus {
	 *     	@type string $slug. Accepts string for label.
	 * }
	 */

	public $nav_menus = [];

	/*
	 * Handles of scripts that should be deferred.
	 *
	 * @var array $defer_script_handles {
	 *     	@type string $handle
	 * }
	 */

	public $defer_script_handles = [];

	/*
	 * Stylesheet url for admin editor styles.
	 *
	 * @var string $editor_style_url
	 */

	public $editor_style_url = '';

   /*
	* Constructor
	* -----------
	*/

	public function __construct() {
		$this->setup_actions();
		$this->setup_filters();
    }

   /*
	* Setup default hooks and actions.
	*
	* @uses add_action() add / remove various actions.
	*/

	private function setup_actions() {
		add_action( 'after_setup_theme', [$this, 'init'] );
		add_action( 'pre_get_posts', [$this, 'query_vars'] );
		add_action( 'wp_enqueue_scripts', [$this, 'scripts'] );

		self::ajax_actions();

		/* Admin customizations */

        add_action( 'admin_menu', [$this, 'remove_meta_boxes'], 10, 2 );
		add_action( 'admin_bar_menu', [$this, 'update_adminbar'], 999 );
		add_action( 'wp_dashboard_setup', [$this, 'remove_dashboard_widgets'] );

		/* Remove emoji styles and scripts */

		remove_action( 'wp_head', 'print_emoji_detection_script', 7 ); 
		remove_action( 'admin_print_scripts', 'print_emoji_detection_script' ); 
		remove_action( 'wp_print_styles', 'print_emoji_styles' ); 
		remove_action( 'admin_print_styles', 'print_emoji_styles' );
	}

   /*
	* Setup default filters
	*
	* @uses add_action() add various filters
	*/

	private function setup_filters() {
		add_filter( 'document_title_separator', [$this, 'title_separator'] );
		add_filter( 'nav_menu_css_class', [$this, 'cpt_nav_classes'], 10, 2 );
		add_filter( 'script_loader_tag', [$this, 'add_defer_async_attributes'], 10, 2 );
		add_filter( 'tiny_mce_before_init', [$this, 'tiny_mce_remove_h1'] );
	}

   /*
	* Action callbacks
	* ----------------
	*
	* Initalize theme.
	*
	* Register nav menus, images sizes and support for various features.
	*/

	public function init() {
		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'editor-styles' );
		add_theme_support( 'responsive-embeds' );

		// add default posts and comments RSS feed links to head
		add_theme_support( 'automatic-feed-links' );

		// disable custom colors in block color palette
		add_theme_support( 'disable-custom-colors' );

		// support HTML5 markup for search form, comment form, comments etc.
		add_theme_support( 'html5', 
			[
				'search-form',
				'comment-form',
				'comment-list',
				'gallery',
				'caption'
			]
		);

		// support for custom block editor color palette
		if( count( $this->editor_color_palette ) > 0 )
			add_theme_support( 'editor-color-palette', $this->editor_color_palette );

		// add custom image sizes
	 	if( count( $this->image_sizes ) > 0 ) {
	 		foreach( $this->image_sizes as $key => $size ) {
	 			add_image_size( $key, $size );
	 		}
	 	}

	 	// register navigation menus
	 	if( count( $this->nav_menus ) > 0 )
		 	register_nav_menus( $this->nav_menus );

		if( $this->editor_style_url && is_admin() )
			add_editor_style( $this->editor_style_url );
	}

   /*
	* Alter query vars for posts when not in admin.
	*/

	public function query_vars( $query ) {
		if( !is_admin() && $query->is_main_query() ) {
			/*if( is_home() || is_category() || is_archive() ) {
				$query->set( 'posts_per_page', self::get_posts_per_page( 'post' ) );	
			}

			if( is_tax() || is_post_type_archive() ) {
				$post_type = $query->get( 'post_type' );
				$query->set( 'posts_per_page', self::get_posts_per_page( $post_type ) );
			}

			if( is_search() )
				$query->set( 'posts_per_page', POSTS_PER_PAGE['search'] );

			$query->set( 'ignore_sticky_posts', 1 );*/
		}
	}

   /*
	* Register scripts and styles.
	*/ 

	public function scripts() {
		/* Register theme css and js */ 

		// custom fonts
		/*wp_register_style( 
			'al_fonts', 
			// 'https://fonts.googleapis.com/css?family=Oxygen:400,700',
			// 'https://fonts.googleapis.com/css?family=Poppins:400,700',
			'https://fonts.googleapis.com/css?family=Oxygen:400,700|Poppins:700',
			[], 
			NULL 
		);
		
		// theme stylesheet
		wp_register_style( 
			'al_styles', 
			get_stylesheet_uri(), 
			['al_fonts'], 
			NULL 
		);

		// theme js main
		wp_register_script( 
			'al_scripts', 
			get_template_directory_uri() . '/assets/public/js/al.js', 
			[], 
			NULL, 
			true 
		);

		// data for scripts
		$data = [
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'recaptcha_site_key' => API_KEYS['recaptcha']['site']
		];

		// pass data to frontend
		wp_localize_script( 'al_scripts', 'al', $data );

		wp_enqueue_style( 'al_styles' );
		wp_enqueue_script( 'al_scripts' );

		// recaptcha script
		wp_enqueue_script( 
			'al_recaptcha', 
			'https://www.google.com/recaptcha/api.js?render=' . API_KEYS['recaptcha']['site'], 
			[], 
			NULL, 
			false 
		);

		// remove Gutenberg CSS
		wp_dequeue_style( 'wp-block-library' );

		// remove embed script
		wp_deregister_script( 'wp-embed' );

		// js for moving comment box on reply
		if( is_singular( 'post' ) && comments_open() && get_option( 'thread_comments' ) )
			wp_enqueue_script( 'comment-reply' );*/
	}

   /*
	* Filter callbacks
	* ----------------
	*/

	// separator for title tag
	public function title_separator( $sep ) {
		$sep = '|';
		return $sep;
	}

	// remove current from blog when on cpt
	public function cpt_nav_classes( $classes, $item ) {
		foreach( self::$cpt as $c => $meta ) {
			$c_archive = is_post_type_archive( $c );
			$c_tax = isset( $meta['taxonomy'] ) ? is_tax( $meta['taxonomy'] ) : false;
			$c_single = is_singular( $c );

			if( $c_archive || $c_tax || $c_single ) {
	        	// if on blog page remove current page parent class
	        	if( get_post_meta( $item->ID, '_menu_item_object_id', true ) == get_option( 'page_for_posts' ) )
	        		$classes = array_diff( $classes, ['current_page_parent'] );

	        	// get slug of nav item
	        	$nav_object_slug = get_post_field( 'post_name', (int) $item->object_id );

	        	// check if slug matches cpt or tax
	        	if( $nav_object_slug == $meta['slug'] )
	        		$classes[] = 'current_page_parent'; 
	        }
		}

        return $classes;
	}

	// add defer / async attributes to specified script tags
	public function add_defer_async_attributes( $tag, $handle ) {
		foreach( $this->defer_script_handles as $script ) {
			if( $script === $handle )
				return str_replace( ' src', ' defer="defer" async="async" src', $tag );
		}

		return $tag;
	}

   /*
	* Admin customizations
	* --------------------
	*/

	// remove h1 from heading options
	public function tiny_mce_remove_h1( $init ) {
		// add block format elements you want to show in dropdown
		$init['block_formats'] = 'Paragraph=p;Heading 2=h2;Heading 3=h3;Heading 4=h4;';
		
		return $init;
	}

	// update toolbar
	public function update_adminbar( $wp_adminbar ) {
		// remove unnecessary items
		$wp_adminbar->remove_node( 'wp-logo' );
		$wp_adminbar->remove_node( 'customize' );
		$wp_adminbar->remove_node( 'comments' );
	}

	public function remove_dashboard_widgets() {
		remove_meta_box( 'dashboard_primary', 'dashboard', 'side' );
	}

    // remove meta boxes and tag link
    public function remove_meta_boxes() {
        remove_meta_box( 'commentstatusdiv', 'post', 'normal' );
        remove_meta_box( 'tagsdiv-post_tag', 'post', 'normal' );
        remove_meta_box( 'tagsdiv-post_tag', 'post', 'advanced' );
        remove_submenu_page( 'edit.php', 'edit-tags.php?taxonomy=post_tag' );
    }

} // end Index
