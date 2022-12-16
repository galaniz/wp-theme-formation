<?php
/**
 * Formation
 *
 * Description: Base, utilities and added functionality for building themes.
 * Author: Graciela Alaniz
 * Author URI: alanizcreative.com
 * Version: 3.0.0
 *
 * @package wp-theme-formation
 */

namespace Formation;

/**
 * Imports
 */

use Formation\Common\Field\Field;

/**
 * Class
 */

class Formation {

	/**
	 * Namespace for handles, option and meta names.
	 *
	 * @var string $namespace
	 */

	public static $namespace = 'frm';

	/**
	 * Path from vendor to source.
	 *
	 * @var string $src_path
	 */

	public static $src_path = '/vendor/alanizcreative/wp-theme-formation/Formation/';

	/**
	 * Url from vendor to source. Set in setup_actions.
	 *
	 * @var string $src_url
	 */

	public static $src_url = '';

	/**
	 * Store post type names and meta data.
	 *
	 * @var array $pt {
	 *  @type string $post_type Accepts array {
	 *   @type string $slug Accepts string.
	 *   @type string $label Accepts string.
	 *   @type string $layout Accepts string.
	 *   @type string $reading Accepts boolean.
	 *   @type string $ajax_posts_per_page Accepts boolean.
	 *  }
	 * }
	 */

	public static $pt = [];

	/**
	 * Store layouts for post types.
	 *
	 * @var array $pt_layout {
	 *  @type string $post_type Accepts string
	 * }
	 */

	public static $pt_layout = [];

	/**
	 * Store taxonomy post types.
	 *
	 * @var array $tax_pt
	 */

	public static $tax_pt = [
		'category' => 'post',
		'post_tag' => 'post',
	];

	/**
	 * Number of posts to display by type/post type.
	 *
	 * @var array $posts_per_page {
	 *  @type string $type/$post_type Accepts int.
	 * }
	 */

	public static $posts_per_page = [];

	/**
	 * Custom image sizes to register.
	 *
	 * @var array $image_sizes {
	 *  @type string $name Accepts int for size.
	 * }
	 */

	public static $image_sizes = [];

	/**
	 * Nav menus to register.
	 *
	 * @var array $nav_menus {
	 *  @type string $slug Accepts string for label.
	 * }
	 */

	public static $nav_menus = [];

	/**
	 * Editor color palette theme support args.
	 *
	 * @var array $editor_color_palette {
	 *  @type string $name Accepts string.
	 *  @type string $slug Accepts string.
	 *  @type string $color Accepts string with hex code.
	 * }
	 */

	public static $editor_color_palette = [];

	/**
	 * Editor font size theme support args.
	 *
	 * @var array $editor_font_sizes {
	 *  @type string $name Accepts string.
	 *  @type int $size Accepts int.
	 *  @type string $slug Accepts string.
	 * }
	 */

	public static $editor_font_sizes = [];

	/**
	 * Options for gap in block options.
	 *
	 * @var array $gap_options
	 */

	public static $gap_options = [];

	/**
	 * Options for gap option in contact form block.
	 *
	 * @var array $field_gap_options
	 */

	public static $field_gap_options = [];

	/**
	 * Options for width in block options.
	 *
	 * @var array $width_options
	 */

	public static $width_options = [];

	/**
	 * Restrict embed variations in blocks.
	 *
	 * @var array $embed_variations
	 */

	public static $embed_variations = [];

	/**
	 * If child theme change styles path.
	 *
	 * @var boolean $child
	 */

	public static $child = false;

	/**
	 * Stylesheet path for admin editor styles relative to theme root.
	 *
	 * @var string $editor_style
	 */

	public static $editor_style = '';

	/**
	 * Stylesheets to register.
	 *
	 * @var array $styles {
	 *  @type string $handle Accepts string.
	 *  @type string $url Accepts string.
	 *  @type string $dep Accepts array.
	 *  @type string $ver Accepts string/boolean.
	 * }
	 */

	public static $styles = [];

	/**
	 * Scripts to register.
	 *
	 * @var array $styles {
	 *  @type string $handle Accepts string.
	 *  @type string $url Accepts string.
	 *  @type string $dep Accepts array.
	 *  @type string $ver Accepts string/boolean.
	 *  @type string $footer Accepts boolean.
	 *  @type string $defer Accepts boolean.
	 *  @type string $data Accepts array.
	 * }
	*/

	public static $scripts = [];

	/**
	 * Handles of scripts that should be deferred. Set in scripts callback.
	 *
	 * @var array $defer_script_handles {
	 *  @type string $handle
	 * }
	 */

	public static $defer_script_handles = [];

	/**
	 * Handles of scripts that should be async. Set in scripts callback.
	 *
	 * @var array $async_script_handles {
	 *  @type string $handle
	 * }
	 */

	public static $async_script_handles = [];

	/**
	 * Handles and attribute strings for scripts. Set in scripts callback.
	 *
	 * @var array $script_attributes {
	 *  @type string $handle => string $attr
	 * }
	 */

	public static $script_attributes = [];

	/**
	 * Class prefix for fields.
	 *
	 * @var string $field_class_prefix
	 */

	public static $field_class_prefix = 'o-form';

	/**
	 * Upload directory path.
	 *
	 * @see Common\Field
	 * @var string $uploads_dir
	 */

	public static $uploads_dir = '';

	/**
	 * Upload directory url.
	 *
	 * @see Common\Field
	 * @var string $uploads_url
	 */

	public static $uploads_url = '';

	/**
	 * Fields for attachments.
	 *
	 * @var array $attachment_fields
	 */

	public static $attachment_fields = [];

	/**
	 * Media position dropdown for attachments.
	 *
	 * @var bool $media_pos_add
	 */

	public static $media_pos_add = false;

	/**
	 * Media position class prefix.
	 *
	 * @var string $media_pos_class_pre
	 */

	public static $media_pos_class_pre = 'l-object-';

	/**
	 * Media position select options.
	 *
	 * @var array $media_pos
	 */

	public static $media_pos = [
		''              => '— Select —',
		'left-top'      => 'Left Top',
		'left-center'   => 'Left Center',
		'left-bottom'   => 'Left Bottom',
		'right-top'     => 'Right Top',
		'right-center'  => 'Right Center',
		'right-bottom'  => 'Right Bottom',
		'center-top'    => 'Center Top',
		'center-center' => 'Center Center',
		'center-bottom' => 'Center Bottom',
	];

	/**
	 * Script version for wp_enqueue.
	 *
	 * @var string $script_ver
	 */

	public static $script_ver = '3.0.0';

	/**
	 * Script priority for front-end wp_enqueue.
	 *
	 * @var int $enqueue_priority
	 */

	public static $enqueue_priority = 10;

	/**
	 * Enqueue scripts remove Gutenberg assets.
	 *
	 * @var bool $dequeue_gutenberg
	 */

	public static $dequeue_gutenberg = false;

	/**
	 * Enqueue scripts remove embed script.
	 *
	 * @var bool $dequeue_embed
	 */

	public static $dequeue_embed = false;

	/**
	 * Constructor
	 */

	public function __construct() {
		$this->setup_actions();
		$this->setup_filters();

		/* Add and save field for media pos  */

		if ( static::$media_pos_add ) {
			static::$attachment_fields[] = [
				'name'    => 'media_pos',
				'label'   => 'Cover position',
				'type'    => 'select',
				'options' => static::$media_pos,
			];

			add_filter( 'attachment_fields_to_edit', [$this, 'add_attachment_fields'], 11, 2 );
			add_action( 'attachment_fields_to_save', [$this, 'save_attachment_fields'], 11, 2 );
		}
	}

	/**
	 * Setup default hooks and actions.
	 *
	 * @uses add_action() to add/remove various actions.
	 */

	private function setup_actions() {
		add_action( 'after_setup_theme', [__CLASS__, 'init'] );
		add_action( 'pre_get_posts', [__CLASS__, 'query_vars'] );
		add_action( 'wp_enqueue_scripts', [__CLASS__, 'scripts'], static::$enqueue_priority );

		if ( static::$dequeue_gutenberg ) {
			remove_filter( 'render_block', 'wp_render_layout_support_flag', 10, 2 );
			remove_filter( 'render_block', 'gutenberg_render_layout_support_flag', 10, 2 );
		}

		add_action(
			'wp_head',
			function() {
				$theme_head_scripts = get_option( static::$namespace . '_scripts_head', '' );

				if ( $theme_head_scripts ) {
					/* phpcs:ignore */
					echo $theme_head_scripts; // Admin/Settngs/Theme wp_kses on_save
				}
			}
		);

		add_action(
			'wp_print_footer_scripts',
			function() {
				$theme_footer_scripts = get_option( static::$namespace . '_scripts_footer', '' );

				if ( $theme_footer_scripts ) {
					/* phpcs:ignore */
					echo $theme_footer_scripts; // Admin/Settngs/Theme wp_kses on_save
				}
			}
		);

		static::ajax_actions();

		/* Admin customizations */

		add_action( 'admin_menu', [__CLASS__, 'remove_meta_boxes'], 10, 2 );
		add_action( 'admin_bar_menu', [__CLASS__, 'update_adminbar'], 999 );
		add_action( 'wp_dashboard_setup', [__CLASS__, 'remove_dashboard_widgets'] );

		/* Remove emoji styles and scripts */

		static::clean_up_emoji();

		/* Pass namespace to front end */

		additional_script_data( 'namespace', static::$namespace, true, true );
		additional_script_data( 'namespace', static::$namespace, false, true );

		/* Pass options for access in blocks */

		additional_script_data( static::$namespace, ['color_options' => static::$editor_color_palette ], true, true );
		additional_script_data( static::$namespace, ['gap_options' => static::$gap_options ], true, true );
		additional_script_data( static::$namespace, ['field_gap_options' => static::$field_gap_options ], true, true );
		additional_script_data( static::$namespace, ['width_options' => static::$width_options ], true, true );
		additional_script_data( static::$namespace, ['embed_variations' => static::$embed_variations ], true, true );

		$ajax_url = ['ajax_url' => admin_url( 'admin-ajax.php' )];

		additional_script_data( static::$namespace, $ajax_url, true, true );

		/* Set uploads variables */

		static::$uploads_dir = WP_CONTENT_DIR . '/' . static::$namespace . '_uploads/';
		static::$uploads_url = get_site_option( 'siteurl' ) . '/wp-content/' . static::$namespace . '_uploads/';

		/* Set source url */

		static::$src_url = ( static::$child ? get_stylesheet_directory_uri() : get_template_directory_uri() ) . static::$src_path;
	}

	/**
	 * Setup default filters.
	 *
	 * @uses add_action() to add various filters
	 */

	private function setup_filters() {
		add_filter( 'document_title_separator', [__CLASS__, 'title_separator'] );
		add_filter( 'nav_menu_css_class', [__CLASS__, 'pt_nav_classes'], 10, 2 );
		add_filter( 'excerpt_more', [__CLASS__, 'excerpt_more'] );
		add_filter( 'script_loader_tag', [__CLASS__, 'add_script_attributes'], 10, 2 );
		add_filter( 'image_size_names_choose', [__CLASS__, 'custom_image_sizes'] );

		/* Admin customizations */

		add_filter( 'tiny_mce_before_init', [__CLASS__, 'tiny_mce_remove_h1'] );
	}

	/**
	 * Setup pt layout variable.
	 */

	public static function setup_pt_layout() {
		foreach ( static::$pt as $p => $meta ) {
			if ( isset( $meta['layout'] ) ) {
				static::$pt_layout[ $p ] = $meta['layout'];
			}
		}
	}

	/**
	 * Initalize theme.
	 *
	 * Register nav menus, images sizes and support for various features.
	 */

	public static function init() {
		/* Let WordPress manage the document title */

		add_theme_support( 'title-tag' );

		/* Enable support for Post Thumbnails on posts and pages */

		add_theme_support( 'post-thumbnails' );

		/* Backend editor styles */

		add_theme_support( 'editor-styles' );

		/* Responsive block embeds */

		add_theme_support( 'responsive-embeds' );

		/* Add default posts and comments RSS feed links to head */

		add_theme_support( 'automatic-feed-links' );

		/* Disable custom colors + fonts in Gutenberg */

		add_theme_support( 'disable-custom-colors' );
		add_theme_support( 'disable-custom-font-sizes' );
		add_theme_support( 'disable-custom-gradients' );
		remove_theme_support( 'core-block-patterns' );

		/* Support HTML5 markup for search form, comment form, comments etc. */

		add_theme_support(
			'html5',
			[
				'search-form',
				'comment-form',
				'comment-list',
				'gallery',
				'caption',
				'style',
				'script',
			]
		);

		/* Support for custom block editor color palette */

		add_theme_support( 'editor-color-palette', static::$editor_color_palette );

		/* Support for custom block editor font sizes */

		add_theme_support( 'editor-font-sizes', static::$editor_font_sizes );

		/* Excerpts for pages */

		add_post_type_support( 'page', 'excerpt' );

		/* Add custom image sizes */

		if ( static::$image_sizes ) {
			foreach ( static::$image_sizes as $key => $size ) {
				add_image_size( $key, $size );
			}
		}

		/* Register navigation menus */

		if ( static::$nav_menus ) {
			register_nav_menus( static::$nav_menus );
		}

		if ( static::$editor_style && is_admin() ) {
			add_editor_style( static::$editor_style );
		}
	}

	/**
	 * Alter query vars for posts when not in admin.
	 */

	public static function query_vars( $query ) {
		if ( ! is_admin() && $query->is_main_query() ) {
			$post_type = $query->get( 'post_type' );
			$ppp       = 0;

			/* Update posts_per_page set in Reading settings */

			if ( $query->is_home || $query->is_archive ) {
				$ppp = static::get_posts_per_page( 'post' );
			}

			if ( $query->is_search ) {
				$ppp = static::get_posts_per_page( 'search' );
			}

			if ( $query->is_author ) {
				$ppp = static::get_posts_per_page( 'author' );
			}

			if ( $query->is_tax ) {
				foreach ( static::$tax_pt as $tpt => $pt ) {
					if ( $query->is_tax( $tpt ) ) {
						$ppp = static::get_posts_per_page( $pt );
					}
				}
			}

			if ( $query->is_post_type_archive ) {
				$ppp = static::get_posts_per_page( $post_type );
			}

			if ( $ppp ) {
				$query->set( 'posts_per_page', $ppp );
			}
		}
	}

	/**
	 * Register and enqueue scripts and styles.
	 */

	public static function scripts() {
		$n  = static::$namespace . '_';
		$nh = static::$namespace . '-';

		$localize_data = [
			'ajax_url' => admin_url( 'admin-ajax.php' ),
		];

		$localize_script_handle = '';

		$enqueue_scripts = [];
		$enqueue_styles  = [];

		/* Register styles */

		foreach ( static::$styles as $st ) {
			$handle = $nh . $st['handle'];
			$dep    = $st['dep'] ?? [];

			$enqueue_styles[] = $handle;

			/* Filter out if dependency */

			if ( $dep ) {
				$dep = array_map(
					function( $v ) use ( $nh ) {
						return $nh . $v;
					},
					$dep
				);

				array_filter(
					$enqueue_styles,
					function( $v ) use ( $dep ) {
						return ! in_array( $v, $dep, true );
					}
				);
			}

			wp_register_style(
				$handle,
				$st['url'],
				$dep,
				$st['ver'] ?? null
			);
		}

		/* Register scripts */

		foreach ( static::$scripts as $i => $sc ) {
			$handle = $nh . $sc['handle'];
			$defer  = $sc['defer'] ?? false;
			$data   = $sc['data'] ?? false;
			$dep    = $sc['dep'] ?? [];

			$enqueue_scripts[] = $handle;

			if ( $defer ) {
				static::$defer_script_handles[] = $handle;
			}

			if ( $data ) {
				array_merge( $localize_data, $data );
			}

			/* Filter out if dependency */

			if ( $dep ) {
				$dep = array_map(
					function( $v ) use ( $nh ) {
						return $nh . $v;
					},
					$dep
				);

				array_filter(
					$enqueue_scripts,
					function( $v ) use ( $dep ) {
						return ! in_array( $v, $dep, true );
					}
				);
			}

			wp_register_script(
				$handle,
				$sc['url'],
				$dep,
				$sc['ver'] ?? null,
				$sc['footer'] ?? true
			);

			if ( 0 === $i ) {
				$localize_script_handle = $handle;
			}
		}

		/* Localize scripts */

		if ( $localize_script_handle && $localize_data ) {
			wp_localize_script( $localize_script_handle, static::$namespace, $localize_data );
		}

		/* Enqueue scripts */

		foreach ( $enqueue_scripts as $sc_handle ) {
			wp_enqueue_script( $sc_handle );
		}

		/* Enqueue styles */

		foreach ( $enqueue_styles as $st_handle ) {
			wp_enqueue_style( $st_handle );
		}

		/* Remove Gutenberg assets */

		if ( static::$dequeue_gutenberg ) {
			wp_dequeue_style( 'wp-block-library' );
			wp_dequeue_style( 'wp-block-library-theme' );
			wp_dequeue_style( 'wc-block-style' ); // Removes woocommerce block css
			wp_dequeue_style( 'global-styles' ); // Removes theme.json

			remove_action( 'wp_enqueue_scripts', 'wp_enqueue_global_styles' );
			remove_action( 'wp_body_open', 'wp_global_styles_render_svg_filters' );
		}

		/* Remove embed script */

		if ( static::$dequeue_embed ) {
			wp_deregister_script( 'wp-embed' );
		}

		/* JS for moving comment box on reply */

		if ( is_singular( 'post' ) && comments_open() && get_option( 'thread_comments' ) ) {
			wp_enqueue_script( 'comment-reply' );
		}
	}

	/**
	 * Remove unnecessary emoji scripts.
	 */

	public static function clean_up_emoji() {
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );
		remove_action( 'admin_print_styles', 'print_emoji_styles' );
		remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
		remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
		remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );

		add_filter(
			'tiny_mce_plugins',
			function( $plugins ) {
				if ( is_array( $plugins ) ) {
					return array_diff( $plugins, ['wpemoji'] );
				} else {
					return [];
				}
			}
		);
	}

	/**
	 * Remove unnecessary items from admin toolbar.
	 */

	public static function update_adminbar( $wp_adminbar ) {
		$wp_adminbar->remove_node( 'wp-logo' );
		$wp_adminbar->remove_node( 'customize' );
		$wp_adminbar->remove_node( 'comments' );
	}

	/**
	 * Remove wp news metabox.
	 */

	public static function remove_dashboard_widgets() {
		remove_meta_box( 'dashboard_primary', 'dashboard', 'side' );
	}

	/**
	 * Remove meta boxes and tag link.
	 */

	public static function remove_meta_boxes() {
		remove_meta_box( 'commentstatusdiv', 'post', 'normal' );
		remove_meta_box( 'tagsdiv-post_tag', 'post', 'normal' );
		remove_meta_box( 'tagsdiv-post_tag', 'post', 'advanced' );
		remove_submenu_page( 'edit.php', 'edit-tags.php?taxonomy=post_tag' );
	}

	/**
	 * Add custom image sizes for users to select.
	 */

	public static function custom_image_sizes( $sizes ) {
		if ( ! static::$image_sizes ) {
			return $sizes;
		}

		foreach ( static::$image_sizes as $key => $size ) {
			$sizes[ $key ] = str_replace( '_', ' ', ucfirst( $key ) );
		}

		return $sizes;
	}

	/**
	 * Ajax callbacks.
	 */

	use Pub\Ajax;

	/**
	 * Utility methods.
	 */

	use Utils;
	use Utils_Render;

	/**
	 * Output posts requested through ajax.
	 *
	 * Note: meant to be overwritten by user.
	 *
	 * @param array $args
	 * @return string/array of html output
	 */

	public static function render_ajax_posts( $args = [] ) {
		return '';
	}

	/**
	 * Separator for title tag.
	 */

	public static function title_separator( $sep ) {
		$sep = '|';
		return $sep;
	}

	/**
	 * Change excerpt end to ellipsis.
	 */

	public static function excerpt_more( $more ) {
		return '&hellip;';
	}

	/**
	 * Adjust current nav item in archive or custom post type.
	 */

	public static function pt_nav_classes( $classes, $item ) {
		$current_pt = get_queried_object()->post_type ?? false;

		if ( ! $current_pt ) {
			$current_tax = get_queried_object()->taxonomy ?? false;

			if ( $current_tax ) {
				$current_pt = static::$tax_pt[ $current_tax ] ?? false;
			}
		}

		if ( ! $current_pt ) {
			return $classes;
		}

		foreach ( static::$pt as $pt => $info ) {
			if ( ! isset( $info['nav'] ) ) {
				continue;
			}

			$nav_pt = $item->object;

			/* Check if blog page */

			if ( 'page' === $nav_pt ) {
				if ( (int) get_option( 'page_for_posts' ) === (int) $item->object_id ) {
					$nav_pt = 'post';
				}
			}

			if ( $pt !== $current_pt ) {
				continue;
			}

			if ( $current_pt === $nav_pt ) {
				$classes[] = 'current-menu-item';
			}
		}

		return $classes;
	}

	/**
	 * Add attributes to $defer_script_handles and $script_attributes.
	 */

	public static function add_script_attributes( $tag, $handle ) {
		foreach ( static::$defer_script_handles as $value ) {
			if ( $value === $handle ) {
				$tag = str_replace( ' src', ' defer="defer" src', $tag );
			}
		}

		foreach ( static::$async_script_handles as $value ) {
			if ( $value === $handle ) {
				$tag = str_replace( ' src', ' async="async" src', $tag );
			}
		}

		foreach ( static::$script_attributes as $key => $value ) {
			$s = static::$namespace . '-' . $key;

			if ( $s === $handle && $value ) {
				$tag = str_replace( ' src', " $value src", $tag );
			}
		}

		return $tag;
	}

	/**
	 * Remove h1 from heading options in admin.
	 */

	public static function tiny_mce_remove_h1( $init ) {
		$init['block_formats'] = 'Paragraph=p;Heading 2=h2;Heading 3=h3;Heading 4=h4;';
		return $init;
	}

	/**
	 * Render fields to attachment.
	 *
	 * @param array $form_fields
	 * @param object $post
	 * @return array $form_fields
	 */

	public function add_attachment_fields( $form_fields, $post ) {
		$post_id = $post->ID;

		foreach ( static::$attachment_fields as $f ) {
			$output = '';
			$name   = static::get_namespaced_str( $f['name'] );
			$data   = get_post_meta( $post_id, $name, true );
			$label  = $f['label'];

			unset( $f['label'] );

			Field::render(
				[
					'fields' => [$f],
					'data'   => [
						$name => $data,
					],
				],
				$output
			);

			$form_fields[ $name ] = [
				'value' => $data ? $data : '',
				'label' => $label,
				'input' => 'html',
				'html'  => $output,
			];
		}

		return $form_fields;
	}

	/**
	 * Save fields to attachment.
	 *
	 * @param int $attachment_id
	 */

	public function save_attachment_fields( $post, $attachment ) {
		foreach ( static::$attachment_fields as $f ) {
			$name = static::get_namespaced_str( $f['name'] );

			if ( isset( $attachment[ $name ] ) ) {
				update_post_meta( $post['ID'], $name, $attachment[ $name ] );
			}
		}
	}

} // End Formation
