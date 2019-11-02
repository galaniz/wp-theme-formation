<?php

/*
 * Utility methods
 * ---------------
 */

namespace Formation;

trait Utils_Render {

    /*
     * Output for social media links / sharing
     *
     * Note: assumes svg sprite with social icons.
     *
     * @param array $args {
     *      @type string $links Accepts string of menu location.   
     *      @type string $share Accepts array.
     *      @type string $div Accepts boolean.
     *      @type string $class Accepts string.
     *      @type string $list_class Accepts string.
     * }
     * @return string of html output
     */

	public static function render_social( $args = [] ) {
		$links = $args['links'] ?? '';
		$share = $args['share'] ?? [];
		$div = $args['div'] ?? false;
        $class = $args['class'] ?? '';
        $list_class = $args['list_class'] ?? '';

		if( !$links && !$share )
			return '';

		$tag = $div ? 'div' : 'ul';
		$child_tag = $div ? 'div' : 'li';

        $item_class = 'o-social__item';

        if( $class )
            $item_class .= " $class";

		$list_classes = "o-social l-flex --wrap --align-center";
        $list_classes .= $list_class ? ' ' . $list_class : '';

		$output = "<$tag class='$list_classes'>";
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

				$item = static::$sprites[$s];
				$item['url'] = $share_meta[$s];
				$data[] = $item;
            }
        }

		if( $links ) {
			$theme_locations = get_nav_menu_locations(); 

			if( isset( $theme_locations[$links] ) ) {
				$social_links = wp_get_nav_menu_items( $links );

				foreach( $social_links as $s ) {
					if( !array_key_exists( $s->post_title, static::$sprites ) )
						continue;

					$item = static::$sprites[$s->post_title];
					$item['url'] = $s->url;
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
						'<span class="u-visually-hidden">' . ucwords( $id ) . '</span>' .
						'<svg class="o-social__icon u-position-relative" width="' . $d['w'] . '" height="' . $d['h'] . '" viewBox="0 0 ' . $d['w'] . ' ' . $d['h'] . '">' .
							'<use xlink:href="#sprite-' . $id . '" />' .
						'</svg>' .
					'</a>' .
				"</$child_tag>";
		}

		$output .= "</$tag>";

		return $output;
	}

    /*
     * Output posts requested through ajax.
     *
     * Note: meant to be overwritten by user.
     *
     * @param string $post_type 
     * @param array $query_args
     * @return string / array of html output
     */

    public static function render_ajax_posts( $post_type = 'post', $query_args = [] ) {
        return '';
    }

    /*
     * Output for loader animation.
     *
     * Note: can be overwritten by user.
     *
     * @param string $add_class
     * @return string / array of html output
     */

    public static function render_loader( $loader_class = '', $icon_class = '', $id = '' ) {
        if( $loader_class )
            $loader_class = " $loader_class";

        if( $icon_class )
            $icon_class = " $icon_class";

        if( $id )
            $id = " id='$id'";

        return 
            "<div class='o-loader$loader_class'$id>" .
                "<div class='o-loader__icon u-position-center l-flex --align-center --justify-center$icon_class'>" .
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
                'class' => '',
                'id' => uniqid(),
                'data_type' => 'default',
                'fields' => '',
                'single_field' => false,
                'button_class' => '',
                'submit_label' => 'Submit'
            ],
            $args
        );

        extract( $args );

        $icon_error = static::$sprites['Error'];
        $icon_success = static::$sprites['Success'];

        $icon_error_width = $icon_error['w'];
        $icon_error_height = $icon_error['h'];
        $icon_success_width = $icon_success['w'];
        $icon_success_height = $icon_success['h'];

        $class = $class ? ' ' . $class : '';
        $button_class = ( static::$classes['button'] ? ' ' . static::$classes['button'] : '' ) . ( $button_class ? ' ' . $button_class : '' );
        $button_field = 'o-field';

        if( $single_field ) {
            $button_class .= ' --fill-inherit';
            $button_field .= ' --single';
        }

        return sprintf(
            '<form class="js-form%1$s" id="%2$s" data-type="%3$s" novalidate>' .
                '<div class="o-field-container u-position-relative">' .
                    '%4$s' .
                    "<div class='$button_field'>" .
                        '<button class="o-button js-submit%5$s" type="submit">' .
                            '<div class="o-button__text u-position-relative">%6$s</div>' .
                            static::render_loader( '--hide' ) .
                        '</button>' .
                    '</div>' .
                '</div>' .
                '<div class="o-result">' .
                    '<div class="o-result__message l-flex --align-center" aria-live="polite">' .
                        '<div class="o-result__icon u-position-relative u-flex-shrink-0">' .
                            '<svg width="%7$s" height="%8$s" viewBox="0 0 %7$s %8$s" class="o-result__svg u-position-center --error">' .
                                '<use xlink:href="#sprite-error" />' .
                            '</svg>' .
                            '<svg width="%9$s" height="%10$s" viewBox="0 0 %9$s %10$s" class="o-result__svg u-position-center --success">' .
                                '<use xlink:href="#sprite-success" />' .
                            '</svg>' .
                        '</div>' .
                        '<div class="o-result__text"></div>' .
                    '</div>' .
                '</div>' .
            '</form>', 
            $class, 
            $id,
            $data_type,
            $fields,
            $button_class,
            $submit_label,
            $icon_error_width,
            $icon_error_height,
            $icon_success_width,
            $icon_success_height
        );
    }

} // end Utils_Render
