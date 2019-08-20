<?php

/*
 * Utility methods
 * ---------------
 */

namespace Foundation;

trait Utils {

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

		if( !$ppp && self::$posts_per_page )
			$ppp = self::$posts_per_page[$post_type];

		return $ppp;
	}

    /*
     * Get first category for post.
     *
     * @param int $id
     * @param string $taxonomy
     * @return array {
     *      @type string category name
     *      @type string category url
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
            $url = get_category_link( $cat->term_id );
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
     * Get excerpt from post, page, any string...
     *
     * @param array $args {
     *      @type string $content. Accepts string.
     *      @type string $words trim by words. Accepts boolean.
     *      @type string $length in words or characters. Accepts int.
     *      @type string $post_id. Accepts int.
     *      @type string $post. Accepts string.
     * }
     * @return string trimmed to specified length 
     */

    public static function get_excerpt( $args ) {
        $content = $args['content'] ?? '';
        $words = $args['words'] ?? false;
        $max = $args['length'] ?? 55;

        if( !$content ) {
            $post_id = $args['post_id'] ?? get_the_ID();
            $post = $args['post'] ?? get_post( $post_id );

            // check for meta description
            $content = get_post_meta( $post_id, 'meta_description', true );

            if( !$content )
                $content = $post->post_excerpt ? $post->post_excerpt : $post->post_content;
        }

        if( $content ) {
            $content = wp_strip_all_tags( $content, true );
            $content = strip_shortcodes( $content );

            if( $words ) { // trim words
                $content = wp_trim_words( $content, $max );
            } else { // trim characters
                $content = mb_strimwidth( $content, 0, $max );
            }
        } else {
            $content = '';
        }

        return $content;
    }

    /*
     * Ajax load more posts fallback 
     *
     * @return string url of next posts page
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
     * Ajax load more comments fallback
     *
     * @return string url of next comments page
     */

    public static function get_next_comments_link() {
		if( !is_singular() )
			return;

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

			/* url redirected on /comment-page-1/ so doesn't reflect oldest page correctly... https://wordpress.stackexchange.com/questions/9129/stop-wordpress-redirecting-comment-page-1-to-the-post-page */
		}

		return esc_url( get_comments_pagenum_link( $nextpage, $max_page ) );
    }
	
} // end Utils
