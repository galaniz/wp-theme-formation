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
     *      @type string $vertical Accepts boolean.
     *      @type string $center Accepts boolean.
     *      @type string $lg Accepts boolean.
     *      @type string $div Accepts boolean.
     *      @type string $hover Accepts boolean.
     *      @type string $class Accepts string.
     * }
     * @return string of html output
     */

	public static function render_social( $args = [] ) {
		$links = $args['links'] ?? '';
		$share = $args['share'] ?? [];
		$vertical = $args['vertical'] ?? false;
		$center = $args['center'] ?? false;
		$lg = $args['lg'] ?? false;
		$div = $args['div'] ?? false;
        $hover = $args['hover'] ?? false;
        $class = $args['class'] ?? '';

		if( !$links && !$share )
			return '';

		$tag = $div ? 'div' : 'ul';
		$child_tag = $div ? 'div' : 'li';

        $item_class = 'o-social__item';

        if( $class )
            $item_class .= " $class";

		$list_classes = "o-social l-flex --wrap --align-center";
		$list_classes .= $center ? ' --justify-center' : '';
		$list_classes .= $vertical ? ' --vertical' : '';
		$list_classes .= $lg ? ' --s-lg' : '';
        $list_classes .= $hover ? ' --hover' : '';

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
     * Output for button loader animation.
     *
     * Note: can be overwritten by user.
     *
     * @param string $add_class
     * @return string / array of html output
     */

    public static function render_button_loader( $add_class = '' ) {
        $class = '';

        if( $add_class )
            $class = " $add_class";

        return 
            "<div class='o-loader$class'>" .
                '<div class="o-loader__icon l-flex --align-center --justify-center u-position-center">' .
                    '<div></div>' .
                    '<div></div>' .
                    '<div></div>' .
                '</div>' .
            '</div>';
    }

} // end Utils_Render
