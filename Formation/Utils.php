<?php
/**
 * Utility methods (getters)
 *
 * @package wp-theme-formation
 */

namespace Formation;

/**
 * Trait
 */

trait Utils {

	/**
	 * Prefix string with namespace only if not already prefixed.
	 *
	 * @param string $name
	 * @return string Prefixed name.
	 */

	public static function get_namespaced_str( $name = '' ) {
		$n = static::$namespace;

		if ( substr( $name, 0, strlen( $n ) ) !== $n ) {
			$name = $n . '_' . $name;
		}

		return $name;
	}

	/**
	 * Get posts per page by post type.
	 *
	 * @param string $post_type
	 * @return integer Posts per page.
	 */

	public static function get_posts_per_page( $post_type = 'post' ) {
		if ( ! $post_type ) {
			return 0;
		}

		$ppp = 'post' === $post_type ? (int) get_option( 'posts_per_page' ) : (int) get_option( self::get_namespaced_str( $post_type ) . '_posts_per_page' );

		if ( ! $ppp && static::$posts_per_page ) {
			if ( isset( static::$posts_per_page[ $post_type ] ) ) {
				$ppp = static::$posts_per_page[ $post_type ];
			}
		}

		return $ppp;
	}

	/**
	 * Get first category for post.
	 *
	 * @param integer $id
	 * @param string $taxonomy
	 * @return array {
	 *  @type string category name
	 *  @type string category url
	 * }
	 */

	public static function get_first_cat( $id = 0, $taxonomy = '' ) {
		$category = ! $taxonomy ? get_the_category() : get_the_terms( $id, $taxonomy );

		if ( ! $category ) {
			return false;
		}

		$first_cat = [];

		foreach ( $category as $cat ) {
			/* Exclude uncatgorized */

			if ( 1 === $cat->cat_ID ) {
				continue;
			}

			$name = $cat->name;

			if ( $taxonomy ) {
				$url = get_term_link( $cat->term_id, $taxonomy );
			} else {
				$url = get_category_link( $cat->term_id );
			}

			$first_cat = [$name, $url];

			break;
		}

		return $first_cat;
	}

	/**
	 * Get current post id outside loop.
	 *
	 * @return int Post id.
	 */

	public static function get_id_outside_loop() {
		global $post;

		if ( is_object( $post ) && property_exists( $post, 'ID' ) ) {
			return $post->ID;
		}

		return 0;
	}

	/**
	 * Get excerpt from post, page, any string...
	 *
	 * @param array $args {
	 *  @type string $content Accepts string.
	 *  @type string $words trim by words. Accepts boolean.
	 *  @type string $length in words or characters. Accepts int.
	 *  @type string $post_id Accepts int.
	 *  @type string $post Accepts string.
	 * }
	 * @return string
	 */

	public static function get_excerpt( $args = [] ) {
		$post_id          = $args['post_id'] ?? get_the_ID();
		$content          = $args['content'] ?? '';
		$words            = $args['words'] ?? false;
		$length           = $args['length'] ?? 55;
		$remove_shortcode = $args['remove_shortcode'] ?? false;

		if ( has_excerpt( $post_id ) ) {
			$content = get_the_excerpt( $post_id );
		}

		if ( ! $content ) {
			$post = $args['post'] ?? get_post( $post_id );

			if ( $post->post_excerpt ) {
				$content = $post->post_excerpt;
				$length  = 0;
			} else {
				$content = $post->post_content;
			}
		}

		if ( $content ) {
			if ( $remove_shortcode ) {
				$content = preg_replace( '~(?:\[/?)[^/\]]+/?\]~s', '', $content );
			}

			$content = wp_strip_all_tags( $content, true );
			$content = strip_shortcodes( $content );

			if ( $length ) {
				if ( $words ) { // Trim words
					$content = wp_trim_words( $content, $length );
				} else { // Trim characters
					$content = mb_strimwidth( $content, 0, $length );
				}
			}
		} else {
			$content = '';
		}

		return $content;
	}

	/**
	 * Ajax load more posts fallback.
	 *
	 * @return boolean|string Url of next posts page.
	 */

	public static function get_next_posts_link() {
		global $wp_query;
		global $paged;

		$page = $paged;

		if ( empty( $page ) ) {
			$page = 1;
		}

		if ( 0 === $page ) {
			$page = 1;
		}

		$page++;

		$total_pages = $wp_query->max_num_pages;

		if ( $page > $total_pages ) {
			return false;
		}

		return get_pagenum_link( $page );
	}

	/**
	 * Ajax load more comments fallback.
	 *
	 * @return string Url of next comments page.
	 */

	public static function get_next_comments_link() {
		if ( ! is_singular() ) {
			return '';
		}

		$page     = get_query_var( 'cpage' );
		$max_page = 0;

		if ( get_option( 'default_comments_page' ) === 'oldest' ) { // Oldest to newest
			global $wp_query;

			$max_page = (int) $wp_query->max_num_comment_pages;

			if ( ! $page ) {
				$page = 1;
			}

			$nextpage = intval( $page ) + 1;

			if ( empty( $max_page ) ) {
				$max_page = get_comment_pages_count();
			}

			if ( $nextpage > $max_page ) {
				return '';
			}
		} else { // Newest to oldest
			if ( intval( $page ) <= 1 ) {
				return '';
			}

			$nextpage = intval( $page ) - 1;

			/* Url redirected on /comment-page-1/ so doesn't reflect oldest page correctly...https://wordpress.stackexchange.com/questions/9129/stop-wordpress-redirecting-comment-page-1-to-the-post-page */
		}

		return get_comments_pagenum_link( $nextpage, $max_page );
	}

	/**
	 * Convert string to array of link data
	 *
	 * @see Field class
	 * @return boolean|array
	 */

	public static function get_link( $str = '' ) {
		if ( ! $str ) {
			return false;
		}

		$v = explode( '|', $str );

		if ( $v ) {
			$target = $v[2] ?? '';

			if ( 'null' === $target ) {
				$target = '';
			}

			return [
				'text'   => $v[0] ?? '',
				'url'    => $v[1] ?? '',
				'target' => $target,
			];
		} else {
			return false;
		}
	}

	/**
	 * Get image from id.
	 *
	 * @return boolean|array
	 */

	public static function get_image( $id = 0, $size = 'thumbnail' ) {
		if ( ! $id ) {
			return false;
		}

		$single = false;

		if ( is_string( $size ) ) {
			$single = true;
			$size   = [$size];
		}

		$urls    = [];
		$srcsets = [];
		$sizes   = [];
		$widths  = [];
		$heights = [];

		foreach ( $size as $s ) {
			$image = wp_get_attachment_image_src( $id, $s );

			if ( $image ) {
				$urls[]    = $image[0];
				$srcsets[] = wp_get_attachment_image_srcset( $id, $s );
				$sizes[]   = wp_get_attachment_image_sizes( $id, $s );
				$widths[]  = $image[1];
				$heights[] = $image[2];
			}
		}

		if ( $urls ) {
			return [
				'url'    => $single ? $urls[0] : $urls,
				'title'  => get_the_title( $id ),
				'alt'    => get_post_meta( $id, '_wp_attachment_image_alt', true ),
				'srcset' => $single ? $srcsets[0] : $srcsets,
				'sizes'  => $single ? $sizes[0] : $sizes,
				'width'  => $single ? $widths[0] : $widths,
				'height' => $single ? $heights[0] : $heights,
			];
		}

		return false;
	}

	/**
	 * Get array of attributes as a string.
	 *
	 * @return string
	 */

	public static function get_attr_as_str( $attr = [], $callback = false ) {
		if ( $attr ) {
			$attr_formatted = [];

			foreach ( $attr as $a => $v ) {
				$attr_formatted[] = esc_html( $a ) . '="' . esc_attr( $v ) . '"';

				if ( is_callable( $callback ) ) {
					call_user_func_array( $callback, [$a, $v] );
				}
			}

			$attr = implode( ' ', $attr_formatted );
		} else {
			$attr = '';
		}

		return $attr;
	}

	/**
	 * Get lat and lng coordinates from address.
	 *
	 * @return array|boolean
	 */

	public static function get_lat_lng( $address = '' ) {
		if ( ! $address ) {
			return false;
		}

		$key = get_option( static::$namespace . '_geocode_key' );

		if ( ! $key ) {
			return false;
		}

		/* Mapbox API url */

		$address = rawurlencode( $address );
		$url     = "https://api.mapbox.com/geocoding/$address.json?types=address&access_token=$key";

		/* Get JSON response */

		$response = wp_remote_get( $url );

		if ( is_array( $response ) && ! is_wp_error( $response ) ) {
			/* Decode JSON */

			$resp = json_decode( $response['body'], true );

			/* Get coordinates */

			if ( isset( $resp['features'][0]['center'] ) ) {
				$center = $resp['features'][0]['center'];

				$lat = $center[1];
				$lng = $center[0];

				return [$lat, $lng];
			}
		}

		return false;
	}

	/**
	 * Get terms as associative array of name, value and checked.
	 *
	 * @return array
	 */

	public static function get_terms_as_options( $tax = 'category', $all_label = 'All' ) {
		$arr = [];

		$terms = get_terms(
			[
				'taxonomy'   => $tax,
				'hide_empty' => true,
				'exclude'    => 1,
			]
		);

		if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
			$any_checked = false;

			$terms = array_map(
				function( $c ) use ( &$any_checked, $tax ) {
					$checked = is_tax( $tax, $c->term_id );

					if ( $checked ) {
						$any_checked = true;
					}

					return [
						'checked' => $checked,
						'value'   => $c->term_id,
						'label'   => $c->name,
					];
				},
				$terms
			);

			array_unshift(
				$terms,
				[
					'checked' => ! $any_checked ? true : false,
					'value'   => 'null',
					'label'   => $all_label,
				]
			);

			$arr = $terms;
		}

		return $arr;
	}

	/**
	 * Get archive as associative array months and years.
	 *
	 * @return array
	 */

	public static function get_archive_as_options( $post_type = 'post' ) {
		$arr = [];

		$first_post = get_posts(
			[
				'numberposts' => 1,
				'order'       => 'ASC',
				'post_type'   => $post_type,
			]
		);

		$last_post = get_posts(
			[
				'numberposts' => 1,
				'post_type'   => $post_type,
			]
		);

		if ( isset( $first_post[0] ) && isset( $last_post[0] ) ) {
			$first_post = $first_post[0];
			$last_post  = $last_post[0];

			$first_date = new \DateTime( $first_post->post_date );
			$last_date  = new \DateTime( $last_post->post_date );
			$f          = new \DateTime( $first_date->format( 'Y-m' ) );

			$months = [];
			$years  = [];
			$year   = '';

			while ( $f <= $last_date ) { // Check if the date is before last date
				$m = $f->format( 'm' );
				$y = $f->format( 'Y' );

				if ( ! in_array( $m, $months, true ) ) {
					$months[ $m ] = $f->format( 'M' );
				}

				if ( ! in_array( $y, $years, true ) ) {
					$year        = $y;
					$years[ $y ] = $y;
				}

				$f->modify( '+1 month' ); // Add month and repeat
			}

			ksort( $months );
			ksort( $years );

			$months = ['null' => 'Month'] + $months;
			$years  = ['null' => 'Year'] + $years;

			$arr = [
				'months' => $months,
				'years'  => $years,
			];
		}

		return $arr;
	}

	/**
	 * Check if url is external.
	 *
	 * @link https://bit.ly/3aPpU3O
	 * @param string $url
	 * @return array
	 */

	public static function is_external_url( $url = '' ) {
		if ( ! $url ) {
			return false;
		}

		$is_external = false;

		/* Parse home URL and parameter URL */

		$link_url = wp_parse_url( $url );
		$home_url = wp_parse_url( home_url() );

		$link_host = $link_url['host'] ?? false;
		$home_host = $home_url['host'] ?? false;

		if ( $link_host ) {
			if ( $link_host !== $home_host ) {
				$is_external = true;
			}
		}

		return $is_external;
	}

}
