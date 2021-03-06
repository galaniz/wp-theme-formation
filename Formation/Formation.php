<?php

/*
 * Formation core class
 * --------------------
 *
 * Description: Base, utilities and added functionality for building themes.
 * Author: Graciela Alaniz
 * Author URI: gracielaalaniz.com
 * Version: 1.2.0
 */

namespace Formation;

/*
 * Imports
 * -------
 */

use Formation\Common\Field\Field;
use function Formation\write_log;

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

	public static $src_path = '/vendor/alanizcreative/wp-theme-formation/Formation/';
	public static $src_url = '';

 /*
	* Store custom post type names and unlimited meta data.
	*
	* @var array $cpt {
	*		@type string $post_type Accepts array {
	*			@type string $slug Accepts string.
	*			@type string $label Accepts string.
	*			@type string $layout Accepts string.
	*			@type string $no_reading Accepts boolean.
	*			@type string $no_slug Accepts boolean.
	*			@type string $ajax_posts_per_page Accepts boolean.
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
	*		@type string $type / $post_type Accepts int.
	* }
	*/

	public static $posts_per_page = [];

 /*
	* Editor color palette theme support args.
	*
	* @var array $editor_color_palette {
	*		@type string $name Accepts string.
	*		@type string $slug Accepts string.
	*		@type string $color Accepts string with hex code.
	* }
	*/

	public $editor_color_palette = [];

 /*
	* Custom image sizes to register.
	*
	* @var array $image_sizes {
	*		@type string $name Accepts int for size.
	* }
	*/

	public $image_sizes = [];

 /*
	* Nav menus to register.
	*
	* @var array $nav_menus {
	*		@type string $slug Accepts string for label.
	* }
	*/

	public $nav_menus = [];

 /*
	* If child theme change styles path
	*
	* @var boolean $child
	*/

	public static $child = false;

 /*
	* Stylesheet path for admin editor styles relative to theme root.
	*
	* @var string $editor_style
	*/

	public $editor_style = '';

 /*
	* Stylesheets to register.
	*
	* @var array $styles {
	*		@type string $handle Accepts string.
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
	*		@type string $handle Accepts string.
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
	*		@type string $handle
	* }
	*/

	public $defer_script_handles = [];

 /*
	* Handles and attribute strings for scripts. Set in scripts callback.
	*
	* @var array $script_attributes {
	*		@type string $handle => string $attr
	* }
	*/

	public $script_attributes = [];

 /*
	* Markup for default loader icon.
	*
	* @var string $loader_icon
	*/

	public static $loader_icon = '';

 /*
	* Options for gap in flex layouts.
	*
	* @var array $loader_icon
	*/

	public static $gap_options = [];

 /*
	* Optional classes to add to fields, labels, buttons...
	*
	* @var array $classes
	*/

	public static $classes = [
		'field_prefix' => 'o-field',
		'field' => '',
		'button' => '',
		'label' => '',
		'input' => '',
		'icon' => ''
	];

 /*
	* Svg output for error and success in forms.
	*
	* @var array $form_svg
	*/

	public static $form_svg = [
		'error' => '',
		'success' => ''
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
	* Fields for attachments.
	*
	* @var array $attachment_fields
	*/

	public static $attachment_fields = [];

 /*
	* Media position options for attachments.
	*
	* @var bool $media_pos_add
	* @var string $media_pos_class_pre
	* @var array $media_pos
	*/

	public static $media_pos_add = false;
	public static $media_pos_class_pre = 'u-p-';
	public static $media_pos = [
		'' => '— Select —',
		'lt' => 'Left Top',
		'lc' => 'Left Center',
		'lb' => 'Left Bottom',
		'rt' => 'Right Top',
		'rc' => 'Right Center',
		'rb' => 'Right Bottom',
		'ct' => 'Center Top',
		'cc' => 'Center Center',
		'cb' => 'Center Bottom'
	];

 /*
	* Constructor
	* -----------
	*/

	public function __construct() {
		$this->setup_actions();
		$this->setup_filters();

		/* Add and save field for media pos  */

		if( self::$media_pos_add ) {
			self::$attachment_fields[] = [
        'name' => 'media_pos',
        'label' => 'Cover position',
        'type' => 'select',
        'options' => self::$media_pos
			];

			add_filter( 'attachment_fields_to_edit', [$this, 'add_attachment_fields'], 11, 2 );
			add_action( 'edit_attachment', [$this, 'save_attachment_fields'], 11, 1 );
		}
	}

 /*
	* Setup default hooks and actions.
	*
	* @uses add_action() to add / remove various actions.
	*/

	private function setup_actions() {
		add_action( 'after_setup_theme', [$this, 'init'] );
		add_action( 'pre_get_posts', [$this, 'query_vars'] );
		add_action( 'wp_head', [$this, 'head'] );
		add_action( 'wp_enqueue_scripts', [$this, 'scripts'] );
		
		add_action( 'wp_print_head_scripts', function() {
			$theme_head_scripts = get_option( static::$namespace . '_scripts_head', '' );
			
			if( $theme_head_scripts )
				echo $theme_head_scripts;
		} );

		add_action( 'wp_print_footer_scripts', function() {
			$theme_footer_scripts = get_option( static::$namespace . '_scripts_footer', '' ); 
			
			if( $theme_footer_scripts )
				echo $theme_footer_scripts;
		} );

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
		additional_script_data( static::$namespace, ['gap_options' => self::$gap_options ], true, true );

		$ajax_url = ['ajax_url' => admin_url( 'admin-ajax.php' )];

		additional_script_data( static::$namespace, $ajax_url, true, true );

		/* Set uploads variables */

		self::$uploads_dir = WP_CONTENT_DIR . '/' . static::$namespace . '_uploads/';
		self::$uploads_url = get_site_option( 'siteurl' ) . '/wp-content/' . static::$namespace . '_uploads/';

		/* Set source url */
		
		self::$src_url = ( self::$child ? get_stylesheet_directory_uri() : get_template_directory_uri() ) . self::$src_path;
	}

 /*
	* Setup default filters
	*
	* @uses add_action() to add various filters
	*/

	private function setup_filters() {
		add_filter( 'document_title_separator', [$this, 'title_separator'] );
		add_filter( 'nav_menu_css_class', [$this, 'cpt_nav_classes'], 10, 2 );
		add_filter( 'excerpt_more', [$this, 'excerpt_more'] );
		add_filter( 'script_loader_tag', [$this, 'add_script_attributes'], 10, 2 );
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

		if( $this->editor_style && is_admin() )
			add_editor_style( $this->editor_style );
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
	* Insert custom scripts in head.
	*/

	public function head() {
		// google analytics
		$ga = get_option( static::$namespace . '_analytics_id' );

		if( $ga && !is_admin() ) { ?>
			<!-- Google Analytics -->
			<script>
				window.ga=window.ga||function(){(ga.q=ga.q||[]).push(arguments)};ga.l=+new Date;
				ga('create', '<?php echo $ga; ?>', 'auto');
				ga('send', 'pageview');
			</script>
			<script async src='https://www.google-analytics.com/analytics.js'></script>
			<!-- End Google Analytics -->
		<?php }
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
	* Change excerpt end to ellipsis.
	*/

	public function excerpt_more( $more ) {
		return '&hellip;';
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
				// get slug of nav item
				$nav_object_slug = get_post_field( 'post_name', (int) $item->object_id );

				// check if slug matches cpt or tax
				if( $nav_object_slug == $meta['slug'] )
					$classes[] = 'current-menu-item';
			}
		}

		return $classes;
	}

 /*
	* Add attributes to $this->defer_script_handles and $this->script_attributes.
	*/

	public function add_script_attributes( $tag, $handle ) {
		foreach( $this->defer_script_handles as $script ) {
			if( $script === $handle )
				$tag = str_replace( ' src', ' defer="defer" async="async" src', $tag );
		}

		foreach( $this->script_attributes as $script => $attr ) {
			$s = static::$namespace . '-' . $script;
			
			if( $s === $handle && $attr )
				$tag = str_replace( ' src', " $attr src", $tag );
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

 /*
  * Render fields to attachment.
  *
  * @param array $form_fields
  * @param object $post
  * @return array $form_fields
  */

	public function add_attachment_fields( $form_fields, $post ) {
    $post_id = $post->ID;

    foreach( self::$attachment_fields as $f ) {
      $output = '';

      $name = self::get_namespaced_str( $f['name'] );
      $data = get_post_meta( $post_id, $name, true );
      $label = $f['label'];

      unset( $f['label'] );

      Field::render( [
        'fields' => [$f],
        'data' => [
          $name => $data
        ] 
      ], $output );

      $form_fields[$name] = [
        'value' => $data ? $data : '',
        'label' => $label,
        'input' => 'html',
        'html' => $output
      ];
    }

    return $form_fields;
	}

 /*
  * Save fields to attachment.
  *
  * @param int $attachment_id
  */

	public function save_attachment_fields( $attachment_id ) {
		foreach( self::$attachment_fields as $f ) {
			$name = self::get_namespaced_str( $f['name'] );

			if( isset( $_REQUEST[$name] ) )
				update_post_meta( $attachment_id, $name, $_REQUEST[$name] );
		}
	}

} // end Formation
