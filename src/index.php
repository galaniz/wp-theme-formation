<?php

/* 
 * Formation core class
 * --------------------
 * 
 * Description: Base, utilities and added functionality for building themes.
 * Author: Graciela Alaniz
 * Author URI: gracielaalaniz.com
 * Version: 1.1.0
 */

namespace Formation;

class Formation {

   /*
	* Variables
	* ---------
	*
	* Namespace for handles, option and meta names.
	*
	* @var string $namespace
	*/

	public static $namespace = 'frm';

   /*
	* Path from vendor to source.
	*
	* @var string $src_path
	*/

	public static $src_path = '/vendor/alanizcreative/wp-theme-formation/src/';
	public static $src_url = '';

   /*
	* Store custom post type names and unlimited meta data.
	*
	* @var array $cpt {
	*		@type string $post_type Accepts array {
	*     		@type string $slug Accepts string.
	*     		@type string $label Accepts string.
	*     		@type string $layout Accepts string.	
	*     		@type string $no_reading Accepts boolean.
	*     		@type string $no_slug Accepts boolean.
	*     		@type string $ajax_posts_per_page Accepts boolean.
	*		}
	* }
	*/

	public static $cpt = [];

   /*
	* Store layouts for post types.
	*
	* @var array $pt_layout {
	*		@type string $post_type Accepts string 
	* }
	*/

	public static $pt_layout = [];

   /*
	* Number of posts to display by type / post type
	*
	* @var array $posts_per_page {
	*     	@type string $type / $post_type Accepts int.
	* }
	*/

	public static $posts_per_page = [];

   /*
	* Editor color palette theme support args.
	*
	* @var array $editor_color_palette {
	*     	@type string $name Accepts string.
	*     	@type string $slug Accepts string.
	*     	@type string $color Accepts string with hex code.
	* }
	*/

	public $editor_color_palette = [];

   /*
	* Custom image sizes to register.
	*
	* @var array $image_sizes {
	*     	@type string $name Accepts int for size.
	* }
	*/

	public $image_sizes = [];

   /*
	* Nav menus to register.
	*
	* @var array $nav_menus {
	*     	@type string $slug Accepts string for label.
	* }
	*/

	public $nav_menus = [];

   /*
	* Stylesheet url for admin editor styles.
	*
	* @var string $editor_style_url
	*/

	public $editor_style_url = '';

   /*
	* Stylesheets to register.
	*
	* @var array $styles {
	*     	@type string $handle Accepts string.
	*		@type string $url Accepts string.
	*		@type string $dep Accepts array.
	*		@type string $ver Accepts string/boolean.
	* }
	*/

	public $styles = [];

   /*
	* Scripts to register.
	*
	* @var array $styles {
	*     	@type string $handle Accepts string.
	*		@type string $url Accepts string.
	*		@type string $dep Accepts array.
	*		@type string $ver Accepts string/boolean.
	*		@type string $footer Accepts boolean.
	*		@type string $defer Accepts boolean.
	*		@type string $data Accepts array.
	* }
	*/

	public $scripts = [];

   /*
	* Handles of scripts that should be deferred. Set in scripts callback.
	*
	* @var array $defer_script_handles {
	*     	@type string $handle
	* }
	*/

	public $defer_script_handles = [];

   /*
    * Markup for default loader icon.
    *
    * @var string $loader_icon
    */

	public static $loader_icon = '';

   /*
    * Optional classes to add to fields, labels, buttons...
    *
    * @var array $classes
    */

    public static $classes = [
        'field' => '',
        'button' => '',
        'label' => '',
        'input' => '',
        'icon' => ''
    ];

   /*
	* Stores svg sprite meta.
	*
	* @var array $sprites
	*/

	public static $sprites = [
		'Facebook' => [
			'id' => 'facebook',
			'w' => 16,
			'h' => 28
		],
		'Twitter' => [
			'id' => 'twitter',
			'w' => 26,
			'h' => 28
		],
		'Linkedin' => [
			'id' => 'linkedin',
			'w' => 24,
			'h' => 28
		],
		'YouTube' => [
			'id' => 'youtube',
			'w' => 28,
			'h' => 28
		],
		'Vimeo' => [
			'id' => 'vimeo',
			'w' => 28,
			'h' => 28
		],
		'Pinterest' => [
			'id' => 'pinterest',
			'w' => 20,
			'h' => 28
		],
		'Instagram' => [
			'id' => 'instagram',
			'w' => 24,
			'h' => 28
		],
		'Email' => [
			'id' => 'email',
			'w' => 28,
			'h' => 28
		],
		'Location' => [
			'id' => 'location',
			'w' => 32,
			'h' => 32,
		],
		'Caret' => [
			'id' => 'caret',
			'w' => 24,
			'h' => 24
		],
		'Carousel' => [
			'id' => 'carousel',
			'w' => 20,
			'h' => 20
		],
		'Play' => [
			'id' => 'play',
			'w' => 20,
			'h' => 23
		],
		'Error' => [
			'id' => 'error',
			'w' => 32,
			'h' => 32
		],
		'Success' => [
			'id' => 'success',
			'w' => 20,
			'h' => 20
		],
		'Search' => [
			'id' => 'search',
			'w' => 26,
			'h' => 28
		]
	];

   /*
	* Upload directory and url.
	*
	* @see Common\Field
	* @var string $uploads_dir
	* @var string $uploads_url
	*/

	public static $uploads_dir = '';
	public static $uploads_url = '';

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
	* @uses add_action() to add / remove various actions.
	*/

	private function setup_actions() {
		add_action( 'after_setup_theme', [$this, 'init'] );
		add_action( 'pre_get_posts', [$this, 'query_vars'] );
		add_action( 'wp_enqueue_scripts', [$this, 'scripts'] );

		static::ajax_actions();

		/* Admin customizations */

        add_action( 'admin_menu', [$this, 'remove_meta_boxes'], 10, 2 );
		add_action( 'admin_bar_menu', [$this, 'update_adminbar'], 999 );
		add_action( 'wp_dashboard_setup', [$this, 'remove_dashboard_widgets'] );

		/* Remove emoji styles and scripts */

		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );
		remove_action( 'admin_print_styles', 'print_emoji_styles' );	
		remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
		remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );	
		remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
		
		add_filter( 'tiny_mce_plugins', function( $plugins ) {
			if( is_array( $plugins ) ) {
				return array_diff( $plugins, ['wpemoji'] );
			} else {
				return [];
			}
		} );

		/* Pass namespace to front end */
		
		additional_script_data( 'namespace', static::$namespace, true, true );
		additional_script_data( 'namespace', static::$namespace, false, true );

		$ajax_url = ['ajax_url' => admin_url( 'admin-ajax.php' )];

		additional_script_data( static::$namespace, $ajax_url, true, true );

		/* Set uploads variables */

		self::$uploads_dir = WP_CONTENT_DIR . '/' . static::$namespace . '_uploads/';
		self::$uploads_url = get_site_option( 'siteurl' ) . '/wp-content/' . static::$namespace . '_uploads/';

		/* Set source url */

		self::$src_url = get_template_directory_uri() . self::$src_path;
	}

   /*
	* Setup default filters
	*
	* @uses add_action() to add various filters
	*/

	private function setup_filters() {
		add_filter( 'document_title_separator', [$this, 'title_separator'] );
		add_filter( 'nav_menu_css_class', [$this, 'cpt_nav_classes'], 10, 2 );
		add_filter( 'script_loader_tag', [$this, 'add_defer_async_attributes'], 10, 2 );
		add_filter( 'image_size_names_choose', [$this, 'custom_image_sizes'] );
		add_filter( 'render_block', [$this, 'filter_block'], 10, 2 );

		/* Admin customizations */

		add_filter( 'tiny_mce_before_init', [$this, 'tiny_mce_remove_h1'] );
	}

   /*
	* Setup pt layout variable
	*/

	public function setup_pt_layout() {
		foreach( self::$cpt as $c => $meta ) {
			if( isset( $meta['layout'] ) )
				self::$pt_layout[$c] = $meta['layout'];
		}
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
		if( $this->editor_color_palette )
			add_theme_support( 'editor-color-palette', $this->editor_color_palette );

		// add custom image sizes
	 	if( $this->image_sizes ) {
	 		foreach( $this->image_sizes as $key => $size ) {
	 			add_image_size( $key, $size );
	 		}
	 	}

	 	// register navigation menus
	 	if( $this->nav_menus )
		 	register_nav_menus( $this->nav_menus );

		if( $this->editor_style_url && is_admin() )
			add_editor_style( $this->editor_style_url );
	}

   /*
	* Alter query vars for posts when not in admin.
	*/

	public function query_vars( $query ) {
		if( !is_admin() && $query->is_main_query() ) {
			if( is_home() || is_category() || is_archive() ) {
				$ppp = static::get_posts_per_page();

				if( $ppp )
					$query->set( 'posts_per_page', $ppp );	
			}

			if( is_tax() || is_post_type_archive() ) {
				$post_type = $query->get( 'post_type' );
				$ppp = static::get_posts_per_page( $post_type );

				if( $ppp )
					$query->set( 'posts_per_page', $ppp );
			}

			if( is_search() && isset( static::$posts_per_page['search'] ) )
				$query->set( 'posts_per_page', static::$posts_per_page['search'] );

			if( is_author() && isset( static::$posts_per_page['author'] ) )
				$query->set( 'posts_per_page', static::$posts_per_page['author'] );
		}
	}

   /*
	* Register and enqueue scripts and styles.
	*/ 

	public function scripts() {
		$n = static::$namespace . '_';
		$nh = static::$namespace . '-';

		$localize_data = [
			'ajax_url' => admin_url( 'admin-ajax.php' )
		];
		
		$localize_script_handle = '';

		$enqueue_scripts = [];
		$enqueue_styles = [];

		/* Register styles */

		foreach( $this->styles as $st ) {
			$handle = $nh . $st['handle'];
			$dep = $st['dep'] ?? [];

			$enqueue_styles[] = $handle;

			// filter out if dependency
			if( $dep ) {
				$dep = array_map( function( $v ) use ( $nh ) {
					return $nh . $v;
				}, $dep );

				array_filter( $enqueue_styles, function( $v ) use ( $dep ) {
					return !in_array( $v, $dep );
				} );
			}

			wp_register_style(
				$handle,
				$st['url'],
				$dep,
				$st['ver'] ?? NULL
			);
		}

		/* Register scripts */

		$recaptcha_secret_key = get_option( $n . 'recaptcha_secret_key', '' );
		$recaptcha_site_key = get_option( $n . 'recaptcha_site_key', '' );

		if( $recaptcha_secret_key ) {
			$this->scripts[] = [
				'handle' => 'recaptcha',
				'url' => 'https://www.google.com/recaptcha/api.js?render=' . $recaptcha_site_key,
				'footer' => false,
				'defer' => true
			];

			$localize_data['recaptcha_site_key'] = $recaptcha_site_key;
		}

		foreach( $this->scripts as $i => $sc ) {
			$handle = $nh . $sc['handle'];
			$defer = $sc['defer'] ?? false;
			$data = $sc['data'] ?? false;
			$dep = $sc['dep'] ?? [];

			$enqueue_scripts[] = $handle;

			if( $defer )
				$this->defer_script_handles[] = $handle;

			if( $data )
				array_merge( $localize_data, $data );

			// filter out if dependency
			if( $dep ) {
				$dep = array_map( function( $v ) use ( $nh ) {
					return $nh . $v;
				}, $dep );

				array_filter( $enqueue_scripts, function( $v ) use ( $dep ) {
					return !in_array( $v, $dep );
				} );
			}

			wp_register_script( 
				$handle,
				$sc['url'],
				$dep,
				$sc['ver'] ?? NULL,
				$sc['footer'] ?? true
			);

			if( $i == 0 )
				$localize_script_handle = $handle;
		}

		// localize scripts
		if( $localize_script_handle && $localize_data )
			wp_localize_script( $localize_script_handle, static::$namespace, $localize_data );

		// enqueue scripts
		foreach( $enqueue_scripts as $sc_handle ) 
			wp_enqueue_script( $sc_handle );

		// enqueue styles
		foreach( $enqueue_styles as $st_handle ) 
			wp_enqueue_style( $st_handle );

		// remove Gutenberg CSS
		wp_dequeue_style( 'wp-block-library' );

		// remove embed script
		wp_deregister_script( 'wp-embed' );

		// js for moving comment box on reply
		if( is_singular( 'post' ) && comments_open() && get_option( 'thread_comments' ) )
			wp_enqueue_script( 'comment-reply' );
	}

   /*
	* Remove unnecessary items from admin toolbar.
	*/ 

	public function update_adminbar( $wp_adminbar ) {
		$wp_adminbar->remove_node( 'wp-logo' );
		$wp_adminbar->remove_node( 'customize' );
		$wp_adminbar->remove_node( 'comments' );
	}

   /*
	* Remove wp news metabox.
	*/ 

	public function remove_dashboard_widgets() {
		remove_meta_box( 'dashboard_primary', 'dashboard', 'side' );
	}

   /*
	* Remove meta boxes and tag link.
	*/ 

    public function remove_meta_boxes() {
        remove_meta_box( 'commentstatusdiv', 'post', 'normal' );
        remove_meta_box( 'tagsdiv-post_tag', 'post', 'normal' );
        remove_meta_box( 'tagsdiv-post_tag', 'post', 'advanced' );
        remove_submenu_page( 'edit.php', 'edit-tags.php?taxonomy=post_tag' );
    }

   /*
	* Add custom image sizes for users to select.
	*/ 

	public function custom_image_sizes( $sizes ) {
		if( !$this->image_sizes )
			return $sizes;

		foreach( $this->image_sizes as $key => $size )
			$sizes[$key] = str_replace( '_', ' ', ucfirst( $key ) );

		return $sizes;
	}

   /*
	* Add to gutenberg block content
	*/ 

	public function filter_block( $block_content, $block ) {
		if( preg_match( '~^core/|core-embed/~', $block['blockName'] ) ) {
			if( $block['blockName'] == 'core/video' ) {
				$autoplay = strpos( $block_content, 'autoplay' ) !== false;

				if( $autoplay )
					$block_content = str_replace( '<video', '<video playsinline', $block_content );
			}
		}

		return $block_content;
	}

   /*
	* Ajax callbacks
	*/ 

	use Pub\Ajax;

   /*
	* Utility methods
	* ---------------
	*/

	use Utils;
	use Utils_Render;

    /*
     * Output posts requested through ajax.
     *
     * Note: meant to be overwritten by user.
     *
     * @param array $args
     * @return string / array of html output
     */

    public static function render_ajax_posts( $args = [] ) {
    	return '';
    }

   /*
	* Filter callbacks
	* ----------------
	*
	* Separator for title tag.
	*/ 
	
	public function title_separator( $sep ) {
		$sep = '|';
		return $sep;
	}

   /*
	* Remove current from blog when on custom post type.
	*/ 

	public function cpt_nav_classes( $classes, $item ) {
		foreach( static::$cpt as $c => $meta ) {
			if( isset( $meta['no_slug'] ) )
				continue;

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

   /*
	* Add defer / async attributes to $this->defer_script_handles.
	*/ 

	public function add_defer_async_attributes( $tag, $handle ) {
		foreach( $this->defer_script_handles as $script ) {
			if( $script === $handle )
				return str_replace( ' src', ' defer="defer" async="async" src', $tag );
		}

		return $tag;
	}

   /*
	* Remove h1 from heading options in admin.
	*/ 

	public function tiny_mce_remove_h1( $init ) {
		$init['block_formats'] = 'Paragraph=p;Heading 2=h2;Heading 3=h3;Heading 4=h4;';
		return $init;
	}

} // end Formation
