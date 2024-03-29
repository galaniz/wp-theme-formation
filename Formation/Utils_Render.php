<?php
/**
 * Utility methods (output)
 *
 * @package wp-theme-formation
 */

namespace Formation;

/**
 * Imports
 */

use function Formation\additional_script_data;

/**
 * Trait
 */

trait Utils_Render {

	/**
	 * Output for social media links/sharing.
	 *
	 * @param array $args {
	 *  @type string $links Accepts string of menu location.
	 *  @type string $share Accepts array.
	 *  @type string $div Accepts boolean.
	 *  @type string $class
	 *  @type string $list_class
	 *  @type array $list_attr
	 *  @type string $link_class
	 *  @type array $link_attr
	 *  @type string $icon_class
	 *  @type array $icon_classes
	 *  @type array $icon_paths
	 * }
	 * @return string Html output.
	 */

	public static function render_social( $args = [] ) {
		$args = array_merge(
			[
				'links'        => '',
				'share'        => [],
				'div'          => false,
				'item_class'   => '',
				'list_class'   => '',
				'list_attr'    => [],
				'link_class'   => '',
				'link_attr'    => [],
				'icon_class'   => '',
				'icon_classes' => [],
				'icon_paths'   => [],
				'a11y_class'   => '',
			],
			$args
		);

		/* Destructure */

		[
			'links'        => $links,
			'share'        => $share,
			'div'          => $div,
			'item_class'   => $item_class,
			'list_class'   => $list_class,
			'list_attr'    => $list_attr,
			'link_class'   => $link_class,
			'link_attr'    => $link_attr,
			'icon_class'   => $icon_class,
			'icon_classes' => $icon_classes,
			'icon_paths'   => $icon_paths,
			'a11y_class'   => $a11y_class,
		] = $args;

		if ( ! $links && ! $share ) {
			return;
		}

		/* Html tags */

		$tag_name       = $div ? 'div' : 'ul';
		$child_tag_name = $div ? 'div' : 'li';

		/* Add share or links to data array */

		$data = [];

		if ( $share ) {
			$blog_name  = get_bloginfo( 'name' );
			$url        = get_permalink();
			$share_meta = [
				'Facebook' =>
					'https://www.facebook.com/sharer.php?' .
					http_build_query(
						['u' => $url]
					),
				'Twitter'  =>
					'https://twitter.com/intent/tweet?' .
					http_build_query(
						[
							'text' => get_the_title() . ' | ' . $blog_name,
							'url'  => $url,
						]
					),
				'Linkedin' =>
					'https://www.linkedin.com/shareArticle?' .
					http_build_query(
						[
							'mini'    => true,
							'url'     => $url,
							'title'   => get_the_title() . ' | ' . $blog_name,
							'summary' => get_the_excerpt(),
							'source'  => '',
						]
					),
				'Email'    =>
					'mailto:?' .
					'&subject=' . get_the_title() . '%20%7C%20' . $blog_name .
					'&body=' . get_the_excerpt() . '%0D%0A%0D%0A' . $url,
			];

			foreach ( $share as $s ) {
				if ( ! array_key_exists( $s, $share_meta ) ) {
					continue;
				}

				$item = [
					'url' => $share_meta[ $s ],
					'id'  => $s,
				];

				$data[] = $item;
			}
		}

		if ( $links ) {
			$menu_locations = get_nav_menu_locations();

			if ( isset( $menu_locations[ $links ] ) ) {
				$social_links = wp_get_nav_menu_items( $menu_locations[ $links ] );

				foreach ( $social_links as $s ) {
					$item = [
						'url' => $s->url,
						'id'  => $s->post_title,
					];

					$data[] = $item;
				}
			} else {
				return '';
			}
		}

		/* Classes */

		$list_class = $list_class ? ' class="' . esc_attr( $list_class ) . '"' : '';
		$item_class = $item_class ? ' class="' . esc_attr( $item_class ) . '"' : '';
		$link_class = $link_class ? ' class="' . esc_attr( $link_class ) . '"' : '';
		$icon_class = $icon_class ? ' class="' . esc_attr( $icon_class ) . '"' : '';
		$a11y_class = $a11y_class ? ' class="' . esc_attr( $a11y_class ) . '"' : '';

		/* Attributes */

		$list_attr = static::get_attr_as_str( $list_attr );
		$link_attr = static::get_attr_as_str( $link_attr );

		if ( $list_attr ) {
			$list_attr = " $list_attr";
		}

		if ( $link_attr ) {
			$link_attr = " $link_attr";
		}

		/* Output */

		$output = "<$tag_name$list_class$list_attr>";

		foreach ( $data as $d ) {
			$url             = esc_url( $d['url'] );
			$data_id         = esc_attr( $d['id'] );
			$icon_path       = $icon_paths[ $data_id ] ?? false;
			$icon_class_attr = "$icon_class" . ( isset( $icon_classes[ $data_id ] ) ? ' ' . $icon_classes[ $data_id ] : '' );
			$window          = '';

			if ( $share && 'email' !== $data_id ) {
				$width  = 600;
				$height = 500;
				$window = esc_js( "window.open( '$url', 'newwindow', 'width=$width, height=$height' ); return false;" );

				$link_attr += " onclick='$window'";
			}

			$output .=
				"<$child_tag_name$item_class>" .
					"<a href='$url'$link_class$link_attr>" .
						"<span$a11y_class>" . ucwords( $data_id ) . '</span>' .
						"<div$icon_class_attr data-type='" . strtolower( $data_id ) . "'>" .
							/* phpcs:ignore */
							( $icon_path ? file_get_contents( $icon_path ) : '' ) . // Ignore: local path
						'</div>' .
					'</a>' .
				"</$child_tag_name>";
		}

		$output .= "</$tag_name>";

		return $output;
	}

	/**
	 * Output logo.
	 *
	 * @param string $class
	 * @param boolean $old_browser_compat
	 * @return string Html output.
	 */

	public static function render_logo( $class = '', $old_browser_compat = false ) {
		$n      = static::$namespace;
		$svg    = get_option( $n . '_svg_logo_meta', false );
		$img    = $n . '_logo';
		$output = '';

		if ( $svg ) {
			$meta   = explode( '|', $svg );
			$width  = (int) $meta[0];
			$height = (int) $meta[1];
			$path   = $meta[2];

			if ( $old_browser_compat && $width && $height ) {
				$output .= "<div style='padding-top:" . ( ( $height / $width ) * 100 ) . "%'></div>";
			}

			/* phpcs:ignore */
			$output .= file_get_contents( $path ); // Ignore: local path
		} else {
			$id = get_option( $img, 0 );

			if ( ! $id ) {
				return '';
			}

			$image = static::get_image( $id, 'large' );

			if ( ! $image ) {
				return '';
			}

			$src    = esc_url( $image['url'] );
			$alt    = esc_attr( $image['alt'] );
			$srcset = esc_attr( $image['srcset'] );
			$sizes  = esc_attr( $image['sizes'] );
			$class  = esc_attr( $class );

			$output = "<img class='$class' src='$src' alt='$alt' srcset='$srcset' sizes='$sizes'>";
		}

		return $output;
	}

	/**
	 * Output for search form.
	 *
	 * @param array $args
	 * @return string Html output.
	 */

	public static function render_form_search( $args = [] ) {
		$args = array_merge(
			[
				'form_class'   => '',
				'field_class'  => '',
				'input_class'  => '',
				'button_class' => '',
				'icon_class'   => '',
				'icon_path'    => '',
				'a11y_class'   => '',
			],
			$args
		);

		/* Destructure */

		[
			'form_class'   => $form_class,
			'field_class'  => $field_class,
			'input_class'  => $input_class,
			'button_class' => $button_class,
			'icon_class'   => $icon_class,
			'icon_path'    => $icon_path,
			'a11y_class'   => $a11y_class,
		] = $args;

		/* Escape */

		$form_class   = esc_attr( $form_class );
		$field_class  = esc_attr( $field_class );
		$input_class  = esc_attr( $input_class );
		$button_class = esc_attr( $button_class );
		$icon_class   = esc_attr( $icon_class );
		$action       = esc_url( home_url( '/' ) );
		$query        = esc_attr( get_search_query() );

		if ( $form_class ) {
			$form_class = " class='$form_class'";
		}

		if ( $field_class ) {
			$field_class = " class='$field_class'";
		}

		if ( $input_class ) {
			$input_class = " class='$input_class'";
		}

		if ( $button_class ) {
			$button_class = " class='$button_class'";
		}

		if ( $icon_class ) {
			$icon_class = " class='$icon_class'";
		}

		if ( $a11y_class ) {
			$a11y_class = " class='$a11y_class'";
		}

		/* Label ID */

		$unique_id = 'search-' . uniqid();

		return (
			"<form$form_class role='search' method='get' action='$action'>" .
				"<div$field_class>" .
					"<label$a11y_class for='$unique_id'>Search</label>" .
					"<input$input_class type='search' id='$unique_id' placeholder='Search' value='$query' name='s' />" .
					"<button$button_class type='submit'>" .
						"<span$a11y_class>Submit search query</span>" .
						"<span$icon_class>" .
							/* phpcs:ignore */
							( $icon_path ? file_get_contents( $icon_path ) : '' ) . // Ignore: local path
						'</span>' .
					'</button>' .
				'</div>' .
			'</form>'
		);
	}

	/**
	 * Output for general forms (contact, sign ups...)
	 *
	 * @param array $args
	 * @return string Html output.
	 */

	public static function render_form( $args = [] ) {
		$args = array_merge(
			[
				'form_class'           => '',
				'form_attr'            => [],
				'form_id'              => uniqid(),
				'form_data_type'       => 'default',
				'fields'               => '',
				'fields_class'         => '',
				'fields_attr'          => [],
				'button_field_class'   => '',
				'button_class'         => '',
				'button_attr'          => [],
				'button_label'         => 'Send',
				'button_loader'        => '',
				'honeypot_field_class' => '',
				'honeypot_label_class' => '',
				'honeypot_class'       => '',
				'error_summary'        => '',
				'error_result'         => '',
				'error_message'        => [],
				'success_result'       => '',
				'success_message'      => [],
			],
			$args
		);

		/* Classes */

		$default_form_class   = 'js-' . static::$namespace . '-form';
		$default_button_class = 'js-submit';

		/* Destructure */

		[
			'form_class'           => $form_class,
			'form_attr'            => $form_attr,
			'form_id'              => $form_id,
			'form_data_type'       => $form_data_type,
			'fields'               => $fields,
			'fields_class'         => $fields_class,
			'fields_attr'          => $fields_attr,
			'button_field_class'   => $button_field_class,
			'button_class'         => $button_class,
			'button_attr'          => $button_attr,
			'button_label'         => $button_label,
			'button_loader'        => $button_loader,
			'honeypot_field_class' => $honeypot_field_class,
			'honeypot_label_class' => $honeypot_label_class,
			'honeypot_class'       => $honeypot_class,
			'error_summary'        => $error_summary,
			'error_result'         => $error_result,
			'error_message'        => $error_message,
			'success_result'       => $success_result,
			'success_message'      => $success_message,
		] = $args;

		/* Escape */

		$form_class         = esc_attr( $default_form_class . ( $form_class ? " $form_class" : '' ) );
		$form_id            = esc_attr( $form_id );
		$fields_class       = esc_attr( $fields_class );
		$button_field_class = esc_attr( $button_field_class );
		$button_class       = esc_attr( $default_button_class . ( $button_class ? " $button_class" : '' ) );
		$button_label       = esc_attr( $button_label );

		if ( $fields_class ) {
			$fields_class = " class='$fields_class'";
		}

		if ( $button_field_class ) {
			$button_field_class = " class='$button_field_class'";
		}

		/* Attributes */

		$form_attr['data-type'] = $args['form_data_type'];
		$form_attr              = static::get_attr_as_str( $form_attr );
		$fields_attr            = static::get_attr_as_str( $fields_attr );
		$button_attr            = static::get_attr_as_str( $button_attr );

		if ( $form_attr ) {
			$form_attr = " $form_attr";
		}

		if ( $fields_attr ) {
			$fields_attr = " $fields_attr";
		}

		if ( $button_attr ) {
			$button_attr = " $button_attr";
		}

		/* Result messages */

		if ( $success_message ) {
			additional_script_data(
				[
					'name'  => static::$namespace,
					'data'  => [
						"form_$form_id" => [
							'success_message' => [
								'primary'   => $success_message['primary'] ?? '',
								'secondary' => $success_message['secondary'] ?? '',
							],
							'error_message'   => [
								'primary'   => $error_message['primary'] ?? '',
								'secondary' => $error_message['secondary'] ?? '',
							],
						],
					],
					'admin' => false,
					'head'  => false,
				]
			);
		}

		/* Result markup */

		$error_summary_id = $form_id . '_error_summary';
		$error_summary    = sprintf( $error_summary, $error_summary_id, $error_summary_id );

		$error_result_id = $form_id . '_error_result';
		$error_result    = sprintf( $error_result, $error_result_id, $error_result_id );

		$success_result_id = $form_id . '_success_result';
		$success_result    = sprintf( $success_result, $success_result_id, $success_result_id );

		/* Honeypot */

		$honeypot_id          = uniqid();
		$honeypot_name        = static::$namespace . '_asi';
		$honeypot_label_id    = uniqid();
		$honeypot_label_class = $honeypot_label_class ? " class='$honeypot_label_class'" : '';
		$honeypot_field_class = $honeypot_field_class ? " class='$honeypot_field_class'" : '';
		$honeypot_class       = $honeypot_class ? " class='$honeypot_class js-input'" : ' class="js-input"';

		$honeypot = (
			"<div$honeypot_field_class data-asi>" .
				"<label$honeypot_label_class id='$honeypot_label_id' for='$honeypot_id'>Website</label>" .
				"<input type='url' name='$honeypot_name' id='$honeypot_id' value='' autocomplete='off'$honeypot_class>" .
			'</div>'
		);

		/* Output */

		return (
			"<form class='$form_class' id='$form_id'$form_attr novalidate>" .
				"<div$fields_class$fields_attr>" .
					$error_summary .
					$fields .
					$honeypot .
					$error_result .
					"<div$button_field_class data-type='submit'>" .
						"<button class='$button_class' type='submit'$button_attr>" .
							$button_loader .
							"<span>$button_label</span>" .
						'</button>' .
					'</div>' .
					$success_result .
				'</div>' .
			'</form>'
		);
	}

	/**
	 * Output for static page archives.
	 *
	 * @param array $args
	 * @return boolean
	 */

	public static function render_cpt_archive( $args = [] ) {
		$args = array_merge(
			[
				'post_type'        => '',
				'templates_before' => [],
				'templates_after'  => [],
			],
			$args
		);

		/* Destructure */

		[
			'post_type'        => $post_type,
			'templates_before' => $templates_before,
			'templates_after'  => $templates_after,
		] = $args;

		if ( ! $post_type ) {
			return false;
		}

		/* Check if page assigned to it */

		$page_id = (int) get_option( $post_type . '_page', 0 );

		if ( ! $page_id ) {
			return false;
		}

		$query = new \WP_Query(
			[
				'page_id' => $page_id,
			]
		);

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();

				foreach ( $templates_before as $tb ) {
					get_template_part( $tb['slug'], $tb['name'] );
				}

				the_content();

				foreach ( $templates_after as $ta ) {
					get_template_part( $ta['slug'], $ta['name'] );
				}
			}

			/* Restore original post data */

			wp_reset_postdata();

			return true;
		} else {
			return false;
		}
	}

}
