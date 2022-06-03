<?php
/**
 * Register custom gutenberg blocks
 *
 * @package wp-theme-formation
 */

namespace Formation\Common\Blocks;

/*
 * Imports
 */

use Formation\Formation as FRM;
use function Formation\additional_script_data;

class Blocks {

		/**
		 * Variables
		 *
		 * Folder url to register scripts with.
		 *
		 * @var string $folder_url
		 */

		public static $folder_url = '';

		/**
		 * Append blocks with args to register.
		 *
		 * @var array $blocks Accepts array {
		 *  @type string $name Accepts array {
		 *   @type string $attr Accepts array.
		 *   @type string $default Accepts array.
		 *   @type string $parent Accepts array.
		 *   @type string $render Accepts function.
		 *   @type string $handle Accepts string. Required.
		 *   @type string $script Accepts $string. Required.
		 *  }
		 * }
		 */

		public static $blocks = [];

		/**
		 * Append scripts when registering to enqueue later.
		 */

		public static $scripts = [];

		/**
		 * Block dependencies for scripts.
		 */

		public static $block_dep = [
			'wp-blocks',
			'wp-block-editor',
			'wp-components',
			'wp-compose',
			'wp-data',
			'wp-core-data',
			'wp-dom',
			'wp-dom-ready',
			'wp-editor',
			'wp-element',
			'wp-hooks',
		];

		/**
		 * Constructor
		 */

		public function __construct( $args = [] ) {
				$args = array_merge(
						[
							'folder_url' => '',
						],
						$args
				);

				[
					'folder_url' => $folder_url,
				] = $args;

				self::$folder_url = $folder_url;

				/* Add blocks */

				add_action( 'init', [$this, 'register_blocks'], 999 );

				/* Theme block category */

				add_filter( 'block_categories_all', [$this, 'block_theme_category'], 10, 2 );

				/* Editor assets */

				add_action( 'enqueue_block_editor_assets', [$this, 'block_assets'] );
		}

		/**
		 * Action callbacks
		 *
		 * Loop through blocks and register.
		 */

		public function register_blocks() {
				/* Check blocks and blocks folder url exist */

				if ( count( self::$blocks ) === 0 || ! self::$folder_url ) {
						return;
				}

				$counter = 0;

				$data = [
					'blocks' => [],
				];

				foreach ( self::$blocks as $name => $b ) {
						/* Check handle and script exist */

						if ( ! isset( $b['handle'] ) || ! isset( $b['script'] ) ) {
								continue;
						}

						$n      = FRM::$namespace . '/' . $name;
						$handle = FRM::$namespace . '_' . $b['handle'];

						if ( ! isset( $data['blocks'][ $n ] ) ) {
								$data['blocks'][ $n ] = $b;
						}

						if ( is_admin() ) {
								$folder_url      = isset( $b['frm'] ) ? FRM::$src_url . 'Common/assets/public/js/blocks/' : self::$folder_url;
								self::$scripts[] = [
									'handle' => $handle,
									'url'    => $folder_url . $b['script'],
								];
						}

						$register_args = ['editor_script' => $handle];

						if ( isset( $b['render'] ) ) {
								$register_args['render_callback'] = $b['render'];
						}

						if ( isset( $b['attr'] ) ) {
								$register_args['attributes'] = $b['attr'];
						}

						$r = register_block_type( $n, $register_args );

						$counter++;
				}

				additional_script_data( FRM::$namespace, $data, true, true );
		}

		/**
		 * Enqueue block assets.
		 */

		public function block_assets() {
				/* Utility scripts */

				$utils_script_handle = FRM::$namespace . '-block-utils-script';

				wp_enqueue_script(
						$utils_script_handle,
						FRM::$src_url . 'Common/assets/public/js/blocks/utils.js',
						[],
						FRM::$script_ver,
						true
				);

				wp_enqueue_script(
						FRM::$namespace . '-insert-block-script',
						FRM::$src_url . 'Common/assets/public/js/blocks/insert-block.js',
						[$utils_script_handle],
						FRM::$script_ver,
						true
				);

				wp_enqueue_script(
						FRM::$namespace . '-embed-variations-script',
						FRM::$src_url . 'Common/assets/public/js/blocks/embed-variations.js',
						[$utils_script_handle],
						FRM::$script_ver,
						true
				);

				/* Block scripts */

				foreach ( self::$scripts as $s ) {
						wp_register_script(
								$s['handle'],
								$s['url'],
								self::$block_dep,
								FRM::$script_ver,
								true
						);
				}
		}

		/**
		 * Add theme block category
		 *
		 * @param array $categories
		 * @param object $post
		 * @return array
		 */

		public function block_theme_category( $block_categories, $block_editor_context ) {
				return array_merge(
						$block_categories,
						[
							[
								'slug'  => 'theme-blocks',
								'title' => 'Theme Blocks',
							],
						]
				);
		}

} // End Blocks
