<?php

/*
 * Utility methods
 * ---------------
 */

namespace Formation;

use function Formation\write_log;

trait Utils {

 /*
  * Prefix string with namespace only if not already prefixed.
  *
  * @param string $name
  * @return string prefixed name
  */

  public static function get_namespaced_str( $name = '' ) {
    if( substr( $name, 0, strlen( static::$namespace ) ) !== static::$namespace  )
      return static::$namespace . '_' . $name;

    return $name;
  }

 /*
  * Get posts per page by post type.
  *
  * @param string $post_type
  * @return int posts per page
  */

	public static function get_posts_per_page( $post_type = 'post' ) {
		if( !$post_type )
			return 0;

		$ppp = $post_type == 'post' ? (int) get_option( 'posts_per_page' ) : (int) get_option( $post_type . '_posts_per_page' );

		if( !$ppp && static::$posts_per_page ) {
      if( isset( static::$posts_per_page[$post_type] ) )
        $ppp = static::$posts_per_page[$post_type];
    }

		return $ppp;
	}

 /*
  * Get first category for post.
  *
  * @param int $id
  * @param string $taxonomy
  * @return array {
  *    @type string category name
  *    @type string category url
  * }
  */

	public static function get_first_cat( $id = 0, $taxonomy = '' ) {
    $category = !$taxonomy ? get_the_category() : get_the_terms( $id, $taxonomy );

    if( !$category )
    	return false;

    $first_cat = [];

    foreach( $category as $cat ) {
      // exclude uncatgorized
      if( $cat->cat_ID === 1 )
        continue;

      $name = $cat->name;

      if( $taxonomy ) {
        $url = get_term_link( $cat->term_id, $taxonomy );
      } else {
        $url = get_category_link( $cat->term_id );
      }

      $first_cat = [$name, $url];

      break;
    }

    return $first_cat;
	}

 /*
  * Get id early in admin.
  *
  * @return int post id
  */

	public static function get_id_early_admin() {
		$id = 0;

    if( is_admin() && isset( $_GET['post'] ) )
      $id = $_GET['post'];

    return (int) $id;
	}

 /*
  * Get current post id outside loop.
  *
  * @return int post id
  */

  public static function get_id_outside_loop() {
    global $post;

    if( is_object( $post ) && property_exists( $post, 'ID' ) )
      return $post->ID;

    return 0;
  }

 /*
  * Get excerpt from post, page, any string...
  *
  * @param array $args {
  *   @type string $content Accepts string.
  *   @type string $words trim by words. Accepts boolean.
  *   @type string $length in words or characters. Accepts int.
  *   @type string $post_id Accepts int.
  *   @type string $post Accepts string.
  * }
  * @return string trimmed to specified length
  */

  public static function get_excerpt( $args = [] ) {
    $post_id = $args['post_id'] ?? get_the_ID();
    $content = $args['content'] ?? '';
    $words = $args['words'] ?? false;
    $length = $args['length'] ?? 55;

    // get excerpt
    if( has_excerpt( $post_id ) )
      $content = get_the_excerpt( $post_id );

    if( !$content ) {
      $post = $args['post'] ?? get_post( $post_id );

      if( $post->post_excerpt ) {
        $content = $post->post_excerpt;
        $length = 0;
      } else {
        $content = $post->post_content;
      }
    }

    if( $content ) {
      $content = wp_strip_all_tags( $content, true );
      $content = strip_shortcodes( $content );

      if( $length ) {
        if( $words ) { // trim words
          $content = wp_trim_words( $content, $length );
        } else { // trim characters
          $content = mb_strimwidth( $content, 0, $length );
        }
      }
    } else {
      $content = '';
    }

    return $content;
  }

 /*
  * Ajax load more posts fallback.
  *
  * @return boolean|string url of next posts page
  */

  public static function get_next_posts_link() {
    global $wp_query;
    global $paged;

    if( empty( $paged ) )
      $paged = 1;

    if( $paged === 0 )
      $paged = 1;

    $paged++;

    $total_pages = $wp_query->max_num_pages;

    if( $paged > $total_pages )
      return false;

    return get_pagenum_link( $paged );
  }

 /*
  * Ajax load more comments fallback.
  *
  * @return string url of next comments page
  */

  public static function get_next_comments_link() {
		if( !is_singular() )
			return '';

		$page = get_query_var( 'cpage' );
		$max_page = 0;

		if( get_option( 'default_comments_page' ) == 'oldest' ) { // oldest to newest
			global $wp_query;

			$max_page = (int) $wp_query->max_num_comment_pages;

			if( !$page )
				$page = 1;

			$nextpage = intval( $page ) + 1;

			if( empty( $max_page ) )
				$max_page = get_comment_pages_count();

			if( $nextpage > $max_page )
				return '';
		} else { // newest to oldest
			if( intval( $page ) <= 1 )
				return '';

			$nextpage = intval( $page ) - 1;

			/* url redirected on /comment-page-1/ so doesn't reflect oldest page correctly...https://wordpress.stackexchange.com/questions/9129/stop-wordpress-redirecting-comment-page-1-to-the-post-page */
		}

		return esc_url( get_comments_pagenum_link( $nextpage, $max_page ) );
  }

 /*
  * Convert string to array of link data
  *
  * @see Field class
  * @return boolean|array
  */

  public static function get_link( $str = '' ) {
    if( !$str )
      return false;

    $v = explode( '|', $str );

    if( $v ) {
      $target = $v[2] ?? '';

      if( $target === 'null' )
        $target = '';

      return [
        'text' => $v[0] ?? '',
        'url' => $v[1] ?? '',
        'target' => $target
      ];
    } else {
      return false;
    }
  }

 /*
  * Get image from id.
  *
  * @return boolean|array
  */

  public static function get_image( $id = 0, $size = 'thumbnail' ) {
    if( !$id )
      return false;

    $single = false;

    if( is_string( $size ) ) {
      $single = true;
      $size = [$size];
    }

    $urls = [];
    $srcsets = [];
    $sizes = [];

    foreach( $size as $s ) {
      $image = wp_get_attachment_image_src( $id, $s );

      if( $image ) {
        $urls[] = $image[0];
        $srcsets[] = wp_get_attachment_image_srcset( $id, $s );
        $sizes[] = wp_get_attachment_image_sizes( $id, $s );
      }
    }

    if( $urls ) {
      return [
        'url' => $single ? $urls[0] : $urls,
        'title' => get_the_title( $id ),
        'alt' => get_post_meta( $id, '_wp_attachment_image_alt', true ),
        'srcset' => $single ? $srcsets[0] : $srcsets,
        'sizes' => $single ? $sizes[0] : $sizes
      ];
    }

    return false;
  }

 /*
  * Get array of attributes as a string.
  *
  * @return string
  */

  public static function get_attr_as_str( $attr = [], $callback = false ) {
    if( $attr ) {
      $attr_formatted = [];

      foreach( $attr as $a => $v ) {
        $attr_formatted[] = $a . '="' . $v . '"';

        if( is_callable( $callback ) )
          call_user_func_array( $callback, [$a, $v] );
      }

      $attr = implode( ' ', $attr_formatted );
    } else {
      $attr = '';
    }

    return $attr;
  }

 /*
  * Get lat and lng coordinates from address.
  *
  * @return array/boolean
  */

  public static function get_lat_lng( $address = '' ) {
    if( !$address )
      return false;

    $key = get_option( static::$namespace . '_geocode_key' );

    if( !$key )
      return false;

    // Google API url
    $address = urlencode( $address );
    $url = "https://maps.googleapis.com/maps/api/geocode/json?address={$address}&key=$key";

    // get the JSON response
    $resp_json = file_get_contents( $url );

    // decode the JSON
    $resp = json_decode( $resp_json, true );

    // response status will be 'OK', if able to geocode given address
    if( $resp['status'] == 'OK' ) {
      // get the important data
      $lat = isset($resp['results'][0]['geometry']['location']['lat']) ? $resp['results'][0]['geometry']['location']['lat'] : '';
      $lng = isset($resp['results'][0]['geometry']['location']['lng']) ? $resp['results'][0]['geometry']['location']['lng'] : '';

      // verify if data is complete
      if( $lat && $lng ) {
        return [$lat, $lng];
      } else {
        return false;
      }
    } else {
      return false;
    }
  }

 /*
  * Get terms as associative array of name, value and checked.
  *
  * @return array
  */

  public static function get_terms_as_options( $tax = 'category', $all_label = 'All' ) {
    $arr = [];

    $terms = get_terms( [
      'taxonomy' => $tax,
      'hide_empty' => true,
      'exclude' => 1
    ] );

    if( $terms ) {
      $any_checked = false;

      $terms = array_map( function( $c ) use( &$any_checked, $tax ) {
        $checked = is_tax( $tax, $c->term_id );

        if( $checked )
          $any_checked = true;

        return [
          'checked' => $checked,
          'value' => $c->term_id,
          'label' => $c->name
        ];
      }, $terms );

      array_unshift( $terms, [
        'checked' => !$any_checked ? true : false,
        'value' => 'null',
        'label' => $all_label
      ] );

      $arr = $terms;
    }

    return $arr;
  }

 /*
  * Get archive as associative array months and years.
  *
  * @return array
  */

  public static function get_archive_as_options( $post_type = 'post' ) {
    $arr = [];

    $first_post = get_posts( [
      'numberposts' => 1,
      'order' => 'ASC',
      'post_type' =>  $post_type
    ] );

    $last_post = get_posts( [
      'numberposts' => 1,
      'post_type' =>  $post_type
    ] );

    if( isset( $first_post[0] ) && isset( $last_post[0] ) ) {
      $first_post = $first_post[0];
      $last_post = $last_post[0];

      $first_date = new \DateTime( $first_post->post_date );
      $last_date = new \DateTime( $last_post->post_date );
      $f = new \DateTime( $first_date->format( 'Y-m' ) );

      $months = [];
      $years = [];
      $year = '';

      while( $f <= $last_date ) { // check if the date is before last date
        $m = $f->format( 'm' );
        $y = $f->format( 'Y' );

        if( !in_array( $m, $months ) )
          $months[$m] = $f->format( 'M' );

        if( !in_array( $y, $years ) ) {
          $year = $y;
          $years[$y] = $y;
        }

        $f->modify( '+1 month' ); // add month and repeat
      }

      ksort( $months );
      ksort( $years );

      $months = ['null' => 'Month'] + $months;
      $years = ['null' => 'Year'] + $years;

      $arr = [
        'months' => $months,
        'years' => $years
      ];
    }

    return $arr;
  }

 /*
  * Check if url is external.
  *
  * source: https://bit.ly/3aPpU3O
  *
  * @param string $url
  * @return array
  */

  public static function is_external_url( $url = '' ) {
    if( !$url )
      return false;

    $is_external = false;

    // parse home URL and parameter URL
    $link_url = parse_url( $url );    
    $home_url = parse_url( home_url() );

    if( $link_url['host'] ) {
      if( $link_url['host'] !== $home_url['host'] )
        $is_external = true;
    }

    return $is_external;
  }

} // end Utils
