<?php
/**
 * Add fields to reading settings page
 *
 * @package wp-theme-formation
 */

namespace Formation\Admin\Settings;

/**
 * Imports
 */

use Formation\Formation as FRM;
use Formation\Admin\Settings\Settings;

/**
 * Class
 */

class Reading {

		/**
		 * Variables
		 */

		public static $additional_fields = [];

		/*
		 * Constructor
		 */

		public function __construct() {
				if ( count( FRM::$pt ) === 0 ) {
						return;
				}

				/* Register settings */

				add_action( 'admin_init', [$this, 'setup'] );

				/* Admin pages list add state if associated with custom post type */

				add_filter(
						'display_post_states',
						function( $post_states, $post ) {
								foreach ( FRM::$pt as $p => $meta ) {
										$pt_page_id = (int) get_option( FRM::get_namespaced_str( $p ) . '_page', 0 );

										if ( $pt_page_id === $post->ID ) {
												$post_states[] = $meta['label'] . ' Page';
										}
								}

								return $post_states;
						},
						10,
						2
				);
		}

		/**
		 * Register and render fields
		 */

		public function setup() {
				$page_options = [0 => '— Select —'];

				$get_pages = get_pages( 'hide_empty=0' );

				foreach ( $get_pages as $page ) {
						$page_options[ $page->ID ] = esc_attr( $page->post_title );
				}

				$fields = [];

				foreach ( FRM::$pt as $p => $meta ) {
						$meta = array_merge(
								[
									'slug'                => '',
									'reading'             => true,
									'label'               => '',
									'more_label'          => '',
									'ajax_posts_per_page' => 0,
								],
								$meta
						);

						[
							'slug'                => $slug,
							'reading'             => $reading,
							'label'               => $label,
							'more_label'          => $more_label,
							'ajax_posts_per_page' => $ajax_posts_per_page,
						] = $meta;

						if ( 'post' === $p && $more_label ) {
								$fields[] = [
									[
										'name'    => 'post_more_label',
										'label'   => 'Post more posts label',
										'section' => 'default',
										'type'    => 'text',
									],
								];

								continue;
						}

						if ( ! $reading ) {
								continue;
						}

						$name = $p . '_page';

						if ( $slug ) {
								$fields[] = [
									'name'         => $name,
									'label'        => $label . ' post page',
									'section'      => 'default',
									'type'         => 'select',
									'value'        => (int) get_option( $name, 0 ),
									'options'      => $page_options,
									'label_hidden' => true,
									'on_save'      => function( $value ) use ( $p, $slug ) {
										$v = sanitize_text_field( $value );

										$id = (int) $v;

										/* Get page slug of assigned page */

										$s = get_post_field( 'post_name', $id );

										update_option( $p . '_slug', $s );

										flush_rewrite_rules();

										return $v;
									},
								];
						}

						$fields[] = [
							'name'         => $p . '_posts_per_page',
							'label'        => $label . ' page shows at most',
							'section'      => 'default',
							'type'         => 'number',
							'label_hidden' => true,
							'class'        => 'c-cpt',
							'attr'         => [
								'step' => '1',
								'min'  => '-1',
							],
						];

						if ( $more_label ) {
								$fields[] = [
									'name'    => $p . '_more_label',
									'label'   => $label . ' more posts label',
									'section' => 'default',
									'type'    => 'text',
								];
						}

						if ( $ajax_posts_per_page ) {
								$fields[] = [
									'name'         => $p . '_ajax_posts_per_page',
									'label'        => $label . ' page loads at most (ajax)',
									'section'      => 'default',
									'type'         => 'number',
									'label_hidden' => true,
									'class'        => 'c-cpt',
									'attr'         => [
										'step' => '1',
										'min'  => '1',
									],
								];
						}
				}

				if ( self::$additional_fields ) {
						$fields = array_merge( $fields, self::$additional_fields );
				}

				/* Add fields */

				new Settings(
						[
							'fields' => $fields,
							'page'   => 'reading',
						]
				);
		}

} // End Reading
