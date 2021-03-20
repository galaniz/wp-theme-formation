<?php

/*
 * Utility methods
 * ---------------
 */

namespace Formation;

use Formation\Utils;
use function Formation\additional_script_data;

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
		$args = array_merge(
			[
				'links' => '',
				'share' => [],
				'div' => false,
				'item_class' => '',
				'list_class' => '',
				'list_attr' => [],
				'link_class' => '',
				'link_attr' => [],
				'icon_class' => '',
				'icon_paths' => []
			],
			$args
		);

		extract( $args );

		if( !$links && !$share )
			return '';

		$tag = $div ? 'div' : 'ul';
		$child_tag = $div ? 'div' : 'li';

		$list_class = 'o-social l-flex' . ( $list_class ? " $list_class" : '' );
		$item_class = 'o-social__item' . ( $item_class ? " $item_class" : '' );
		$link_class = 'o-social__link' . ( $link_class ? " $link_class" : '' );
		$icon_class = 'o-social__icon' . ( $icon_class ? " $icon_class" : '' );

		$list_attr = Utils::get_attr_as_str( $list_attr );
		$link_attr = Utils::get_attr_as_str( $link_attr );

		if( $list_attr )
			$list_attr = " $list_attr";

		if( $link_attr )
			$link_attr = " $link_attr";

		$output = "<$tag class='$list_class'$list_attr>";

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
			$menu_locations = get_nav_menu_locations();

			if( isset( $menu_locations[$links] ) ) {
				$social_links = wp_get_nav_menu_items( $menu_locations[$links] );

				foreach( $social_links as $s ) {
					$item = [
						'url' => $s->url,
						'id' => $s->post_title
					];

					$data[] = $item;
				}
			} else {
				return '';
			}
		}

		foreach( $data as $d ) {
			$url = $d['url'];
			$id = $d['id'];
			$icon_html = '';
			$w = '';

			if( $share && $id !== 'email' ) {
				$w_width = 600;
				$w_height = 500;
				$w = " onclick=\"window.open( '$url', 'newwindow', 'width=$w_width, height=$w_height' ); return false;\"";
			}

			if( isset( $icon_paths[$id] ) )
				$icon_html = file_get_contents( $icon_paths[$id] );

			$output .=
				"<$child_tag class='$item_class'>".
					'<a' . ( $share && $w ? $w : '' ) . " class='$link_class' href='$url'$link_attr>" .
						'<span class="u-v-h">' . ucwords( $id ) . '</span>' .
						"<div class='$icon_class' data-type='" . strtolower( $id ) . "'>$icon_html</div>" .
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
				'icon_attr' => [],
				'id' => '',
				'hide' => false,
				'html' => ''
			],
			$args
		);

		extract( $args );

		/* Loader */

		if( $loader_class )
			$loader_class = " $loader_class";

		$loader_attr = Utils::get_attr_as_str( $loader_attr );

		if( $loader_attr )
			$loader_attr = " $loader_attr";

		/* Icon */

		if( $icon_class )
			$icon_class = " $icon_class";

		$icon_attr = Utils::get_attr_as_str( $icon_attr );

		if( $icon_attr )
			$icon_attr = " $icon_attr";

		/* ID */

		if( $id )
			$id = " id='$id'";

		/* Hide */

		$hide = $hide ? " data-hide" : '';

		/* Markup */

		$html = $html ? $html : static::$loader_icon;

		return
			"<div class='o-loader$loader_class'$id$hide$loader_attr>" .
				"<div class='o-loader__icon u-p-c l-flex$icon_class' data-justify='center' data-align='center'$icon_attr>" .
					$html .
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
				'fields_gap' => 'sm',
				'fields_attr' => [],
				'button_class' => '',
				'button_attr' => [],
				'submit_label' => 'Submit',
				'result_gap' => 'xs',
				'success_message' => ''
			],
			$args
		);

		extract( $args );

		/* Form classes prefix */

		$pre = static::$classes['field_prefix'];

		/* Form attributes */

		$form_attr['data-type'] = $form_data_type;
		$form_attr = Utils::get_attr_as_str( $form_attr );

		if( $form_attr )
			$form_attr = " $form_attr";

		/* Fields attributes */

		$fields_attr = Utils::get_attr_as_str( $fields_attr );

		if( $fields_attr )
			$fields_attr = " $fields_attr";

		/* Button */

		$button_class = ( static::$classes['button'] ? ' ' . static::$classes['button'] : '' ) . ( $button_class ? ' ' . $button_class : '' );
		$button_attr = Utils::get_attr_as_str( $button_attr );

		if( $button_attr )
			$button_attr = " $button_attr";

		/* Success message */

		if( $success_message ) 
			additional_script_data( static::$namespace, ["form_$form_id" => ['success_message' => $success_message] ], false, false );

		return sprintf(
			'<form class="o-form js-' . static::$namespace . '-form%1$s" id="%2$s"%3$s novalidate>' .
				'<div class="u-p-r l-flex" data-gap="%4$s" data-wrap%5$s>' .
					'%6$s' .
					"<div class='" . $pre . ( $pre != 'o-field' ? '__field' : '' ) . "' data-type='submit'>" .
						'<button class="o-button js-submit%7$s" type="submit"%8$s>' .
							static::render_loader( [
								'icon_class' => static::$classes['icon'],
								'hide' => true
							] ) .
							'<div>%9$s</div>' .
						'</button>' .
					'</div>' .
				'</div>' .
				'<div class="o-result">' .
					'<div class="o-result__message">' .
						'<div class="l-flex" data-gap="%10$s" data-align="center" aria-live="polite">' .
							'<div>' .
								'<div class="o-result__icon u-p-r">' .
									'<div class="o-result__error u-p-c">%11$s</div>' .
									'<div class="o-result__success u-p-c">%12$s</div>' .
								'</div>' .
							'</div>' .
							'<div>' .
								'<div class="o-result__text"></div>' .
							'</div>' .
						'</div>' .
					'</div>' .
				'</div>' .
			'</form>',
			$form_class,
			$form_id,
			$form_attr,
			$fields_gap,
			$fields_attr,
			$fields,
			$button_class,
			$button_attr,
			$submit_label,
			$result_gap,
			static::$form_svg['error'], 
			static::$form_svg['success']
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
