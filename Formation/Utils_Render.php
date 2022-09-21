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
	 * @return string html
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

		$list_class = $list_class ? " $list_class" : '';
		$item_class = $item_class ? " $item_class" : '';
		$link_class = $link_class ? " $link_class" : '';
		$icon_class = $icon_class ? " $icon_class" : '';

		/* Escape */

		$list_class = esc_attr( $list_class );
		$item_class = esc_attr( $item_class );
		$link_class = esc_attr( $link_class );
		$icon_class = esc_attr( $icon_class );

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

		$output = "<$tag_name class='o-social l-flex$list_class'$list_attr>";

		foreach ( $data as $d ) {
			$url             = esc_url( $d['url'] );
			$data_id         = esc_attr( $d['id'] );
			$icon_path       = $icon_paths[ $data_id ] ?? false;
			$icon_class_attr = "o-social__icon$icon_class" . ( isset( $icon_classes[ $data_id ] ) ? ' ' . $icon_classes[ $data_id ] : '' );
			$window          = '';

			if ( $share && 'email' !== $data_id ) {
				$width  = 600;
				$height = 500;
				$window = esc_js( "window.open( '$url', 'newwindow', 'width=$width, height=$height' ); return false;" );
			}

			$output .=
				"<$child_tag_name class='o-social__item$item_class'>" .
					"<a onclick='$window' class='o-social__link$link_class' href='$url'$link_attr>" .
						"<span class='u-v-h'>" . ucwords( $data_id ) . '</span>' .
						"<div class='$icon_class_attr' data-type='" . strtolower( $data_id ) . "' aria-hidden='true'>" .
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
	 * Output for loader animation.
	 *
	 * @param array $args
	 * @return string html
	 */

	public static function render_loader( $args = [] ) {
		$args = array_merge(
			[
				'loader_class' => '',
				'loader_attr'  => [],
				'icon_class'   => '',
				'icon_attr'    => [],
				'html'         => '',
			],
			$args
		);

		/* Destructure */

		[
			'loader_class' => $loader_class,
			'loader_attr'  => $loader_attr,
			'icon_class'   => $icon_class,
			'icon_attr'    => $icon_attr,
			'html'         => $html,
		] = $args;

		/* Html */

		$html = $html ? $html : static::$loader_icon;

		/* Classes */

		$loader_class = $loader_class ? " $loader_class" : '';
		$icon_class   = $icon_class ? " $icon_class" : '';

		/* Escape */

		$loader_class = esc_attr( $loader_class );
		$icon_class   = esc_attr( $icon_class );

		/* Attributes */

		$loader_attr = static::get_attr_as_str( $loader_attr );
		$icon_attr   = static::get_attr_as_str( $icon_attr );

		if ( $loader_attr ) {
			$loader_attr = " $loader_attr";
		}

		if ( $icon_attr ) {
			$icon_attr = " $icon_attr";
		}

		/* Output */

		return (
			"<div class='o-loader$loader_class'$loader_attr>" .
				"<div class='o-loader__icon u-p-c l-flex$icon_class' data-justify='center' data-align='center'$icon_attr>" .
					$html .
				'</div>' .
			'</div>'
		);
	}

	/**
	 * Output for general forms (contact, sign ups...)
	 *
	 * @param array $args
	 * @return string html
	 */

	public static function render_form( $args = [] ) {
		$args = array_merge(
			[
				'form_class'      => '',
				'form_attr'       => [],
				'form_id'         => uniqid(),
				'form_data_type'  => 'default',
				'fields'          => '',
				'fields_gap'      => 's',
				'fields_attr'     => [],
				'button_class'    => '',
				'button_attr'     => [],
				'submit_label'    => 'Send',
				'result_gap'      => 'xs',
				'success_message' => '',
			],
			$args
		);

		/* Classes */

		$static_form_class   = 'o-form js-' . static::$namespace . '-form';
		$static_button_class = 'o-button js-submit' . ( static::$classes['button'] ? ' ' . static::$classes['button'] : '' );

		/* Destructure */

		[
			'form_class'      => $form_class,
			'form_attr'       => $form_attr,
			'form_id'         => $form_id,
			'fields'          => $fields,
			'fields_gap'      => $fields_gap,
			'fields_attr'     => $fields_attr,
			'button_class'    => $button_class,
			'button_attr'     => $button_attr,
			'submit_label'    => $submit_label,
			'result_gap'      => $result_gap,
			'success_message' => $success_message,
		] = $args;

		/* Escape */

		$form_class      = esc_attr( $static_form_class . ( $form_class ? " $form_class" : '' ) );
		$form_id         = esc_attr( $form_id );
		$fields_gap      = esc_attr( $fields_gap );
		$button_class    = esc_attr( $static_button_class . ( $button_class ? " $button_class" : '' ) );
		$submit_label    = esc_attr( $submit_label );
		$result_gap      = esc_attr( $result_gap );
		$success_message = esc_html( $success_message );

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

		/* Success message */

		if ( $success_message ) {
			additional_script_data(
				static::$namespace,
				[
					"form_$form_id" => [
						'success_message' => $success_message,
					],
				],
				false,
				false,
			);
		}

		/* Output */

		return (
			"<form class='$form_class' id='$form_id'$form_attr novalidate>" .
				"<div class='u-p-r l-flex' data-gap='$fields_gap'$fields_attr data-wrap>" .
					$fields .
					"<div class='o-form__field' data-type='submit'>" .
						"<button class='$button_class' type='submit'$button_attr>" .
							static::render_loader(
								[
									'icon_class'  => static::$classes['icon'],
									'loader_attr' => [
										'data-hide' => true,
									],
								]
							) .
							"<span>$submit_label</span>" .
						'</button>' .
					'</div>' .
				'</div>' .
				"<div class='o-result'>" .
					"<div class='o-result__message'>" .
						"<div class='l-flex' data-gap='$result_gap' data-align='center' aria-live='polite'>" .
							'<div>' .
								"<div class='o-result__icon u-p-r'>" .
									"<div class='o-result__error u-p-c'>" . static::$form_svg['error'] . '</div>' .
									"<div class='o-result__success u-p-c'>" . static::$form_svg['success'] . '</div>' .
								'</div>' .
							'</div>' .
							'<div>' .
								"<div class='o-result__text'></div>" .
							'</div>' .
						'</div>' .
					'</div>' .
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

} // End Utils_Render
