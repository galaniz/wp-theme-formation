<?php

/*
 * Utility methods
 * ---------------
 */

namespace Formation;

use Formation\Utils;

trait Utils_Render {

 /*
	* Output for social media links / sharing
	*
	* Note: assumes svg sprite with social icons.
	*
	* @param array $args {
	*		@type string $links Accepts string of menu location.
	*		@type string $share Accepts array.
	*		@type string $div Accepts boolean.
	*		@type string $class Accepts string.
	*		@type string $list_class Accepts string.
	* }
	* @return string of html output
	*/

	public static function render_social( $args = [] ) {
		$links = $args['links'] ?? '';
		$share = $args['share'] ?? [];
		$div = $args['div'] ?? false;
		$class = $args['class'] ?? '';
		$list_class = $args['list_class'] ?? '';
		$list_attr = $args['list_attr'] ?? [];

		if( !$links && !$share )
			return '';

		$tag = $div ? 'div' : 'ul';
		$child_tag = $div ? 'div' : 'li';

		$item_class = 'o-social__item';

		if( $class )
			$item_class .= " $class";

		$list_classes = "o-social l-flex";
		$list_classes .= $list_class ? ' ' . $list_class : '';
		$list_attr = Utils::get_attr_as_str( $list_attr );

		$output = "<$tag class='$list_classes' data-wrap data-align='center'$list_attr>";
		$data = [];

		if( $share ) {
			$blog_name = get_bloginfo( 'name' );
			$url = get_permalink();
			$share_meta = [
				'Facebook' => 'https://www.facebook.com/sharer.php?' . http_build_query(
					['u' => $url]
				),
				'Twitter' => 'https://twitter.com/intent/tweet?' . http_build_query(
					[
						'text' => get_the_title() . ' | ' . $blog_name,
						'url' => $url
					]
				),
				'Linkedin' => 'https://www.linkedin.com/shareArticle?' . http_build_query(
					[
						'mini' => true,
						'url' => $url,
						'title' => get_the_title() . ' | ' . $blog_name,
						'summary' => get_the_excerpt(),
						'source' => ''
					]
				),
				'Email' =>
					'mailto:?' .
					'&subject=' . get_the_title() . '%20%7C%20' . $blog_name .
					'&body=' . get_the_excerpt() . '%0D%0A%0D%0A' . $url
			];

			foreach( $share as $s ) {
				if( !array_key_exists( $s, $share_meta ) )
					continue;

				$item = [
					'url' => $share_meta[$s],
					'id' => $s
				];

				$data[] = $item;
			}
		}

		if( $links ) {
			$theme_locations = get_nav_menu_locations();

			if( isset( $theme_locations[$links] ) ) {
				$social_links = wp_get_nav_menu_items( $theme_locations[$links] );

				foreach( $social_links as $s ) {
					if( !array_key_exists( $s->post_title, static::$sprites ) )
						continue;

					$item = [
						'url' => $s->url,
						'id' => $s->post_title
					];

					$data[] = $item;
				}
			}
		}

		foreach( $data as $d ) {
			$url = $d['url'];
			$id = $d['id'];
			$w = '';

			if( $share && $id !== 'email' ) {
				$w_width = 600;
				$w_height = 500;
				$w = " onclick=\"window.open( '$url', 'newwindow', 'width=$w_width, height=$w_height' ); return false;\"";
			}

			$output .=
				"<$child_tag class='$item_class'>".
					'<a' . ( $share && $w ? $w : '' ) . ' class="o-social__link" href="' . $url . '">' .
						'<span class="u-v-h">' . ucwords( $id ) . '</span>' .
						"<div class='o-social__icon' data-type='$id'></div>" .
					'</a>' .
				"</$child_tag>";
		}

		$output .= "</$tag>";

		return $output;
	}

 /*
	* Output for loader animation.
	*
	* @param array $args
	* @return string of html output
	*/

	public static function render_loader( $args = [] ) {
		$args = array_merge(
			[
				'loader_class' => '',
				'loader_attr' => [],
				'icon_class' => '',
				'id' => '',
				'hide' => false
			],
			$args
		);

		extract( $args );

		if( $loader_class )
			$loader_class = " $loader_class";

		$loader_attr = Utils::get_attr_as_str( $loader_attr );

		if( $icon_class )
			$icon_class = " $icon_class";

		if( $id )
			$id = " id='$id'";

		$hide = $hide ? " data-hide" : '';

		return
			"<div class='o-loader$loader_class'$id$hide$loader_attr>" .
				"<div class='o-loader__icon u-p-c l-flex$icon_class' data-justify='center' data-align='center'>" .
					static::$loader_icon .
				'</div>' .
			'</div>';
	}

 /*
	* Output for general forms ( contact, sign ups )
	*
	* @param array $args
	* @return string of html output
	*/

	public static function render_form( $args = [] ) {
		$args = array_merge(
			[
				'form_class' => '',
				'form_attr' => [],
				'form_id' => uniqid(),
				'form_data_type' => 'default',
				'fields' => '',
				'fields_gap' => 'sm'
				'button_class' => '',
				'submit_label' => 'Submit',
				'result_gap' => 'xs',
			],
			$args
		);

		extract( $args );

		/* Form attributes */

		if( $form_attr ) {
			$form_attr_formatted = [];

			foreach( $form_attr as $k => $v )
				$form_attr_formatted[] = $k . '="' . $v . '"';

			$form_attr = ' ' . implode( ' ', $form_attr_formatted );
		} else {
			$form_attr = '';
		}

		if( $form_data_type )
			$form_attr .= ( $form_attr ? ' ' : '' ) . "data-type='$form_data_type'";

		/* Button */

		$button_class = ( static::$classes['button'] ? ' ' . static::$classes['button'] : '' ) . ( $button_class ? ' ' . $button_class : '' );

		return sprintf(
			'<form class="o-form js-' . static::$namespace . '-form%1$s" id="%2$s"%3$s novalidate>' .
				'<div class="u-p-r l-flex" data-gap="%4$s" data-wrap>' .
					'%5$s' .
					"<div class='o-field' data-type='submit'>" .
						'<button class="o-button js-submit%6$s" type="submit">' .
							static::render_loader( [
								'icon_class' => static::$classes['icon'],
								'hide' => true
							] ) .
							'<div>%7$s</div>' .
						'</button>' .
					'</div>' .
				'</div>' .
				'<div class="o-result">' .
					'<div class="o-result__message l-flex" data-gap="%8$s" data-align="center" aria-live="polite">' .
						'<div class="o-result__icon u-p-r">' .
							'<div class="o-result__error u-p-c"></div>' .
							'<div class="o-result__success u-p-c"></div>' .
						'</div>' .
						'<div class="o-result__text"></div>' .
					'</div>' .
				'</div>' .
			'</form>',
			$form_class,
			$form_id,
			$form_attr,
			$fields_gap,
			$fields,
			$button_class,
			$submit_label,
			$result_gap
		);
	}

 /*
	* Output for static page archives.
	*
	* @param array $args
	* @return boolean
	*/

	public static function render_cpt_archive( $args = [] ) {
		$args = array_merge(
			[
				'post_type' => '',
				'templates_before' => [],
				'templates_after' => [],
				'content_before' => '',
				'content_after' => ''
			],
			$args
		);

		extract( $args );

		if( !$post_type )
			return false;

		// check if page assigned to it
		$page_id = (int) get_option( $post_type . '_page', 0 );

		if( !$page_id )
			return false;

		$query = new \WP_Query( [
			'page_id' => $page_id
		] );

		if( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();

				foreach( $templates_before as $tb )
					get_template_part( $tb['slug'], $tb['name'] );

				echo $content_before;

				the_content();

				echo $content_after;

				foreach( $templates_after as $ta )
					get_template_part( $ta['slug'], $ta['name'] );
			}

			// restore original post data
			wp_reset_postdata();

			return true;
		} else {
			return false;
		}
	}

} // end Utils_Render
