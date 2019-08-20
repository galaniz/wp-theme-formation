<?php

/*
 * Utility methods
 * ---------------
 */

namespace FG;

trait Utils {

	// get posts per page by post type
	public static function get_posts_per_page( $post_type = 'post' ) {
		if( !$post_type )
			return 0;

		$ppp = $post_type == 'post' ? (int) get_option( 'posts_per_page' ) : (int) get_option( $post_type . '_posts_per_page' );

		if( !$ppp )
			$ppp = POSTS_PER_PAGE[$post_type];

		if( is_single() ) {
			if( $post_type == 'post' || $post_type == 'fg_projects' )
				$ppp = 3;
		}

		return $ppp;
	}

	// get first category for post
	public static function get_first_cat( $id = 0, $taxonomy = '' ) {
		// get category
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

	// get id early ( init hook )
	public static function get_id_early() {
		$id = 0;

        // get id in admin
        if( isset( $_GET['post'] ) )
            $id = $_GET['post'];

        return (int) $id;
	}

	// get excerpt post, page, author etc
    public static function get_excerpt( $args ) {
        $content = $args['content'] ?? false;
        $words = $args['words'] ?? false;
        $max = $args['length'] ?? 55;

        if( !$content ) {
            $post_id = $args['post_id'] ?? get_the_ID();
            $post = $args['post'] ?? get_post( $post_id );

            // check for meta description
            $content = get_post_meta( $post_id, 'cm_meta_description', true );

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

	// ajax load more posts fallback 
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

	// ajax load more comments fallback 
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
