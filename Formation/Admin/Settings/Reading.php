<?php

/*
 * Add fields to reading settings page
 * -----------------------------------
 */

namespace Formation\Admin\Settings;

/*
 * Imports
 * -------
 */

use Formation\Formation as FRM;  
use Formation\Admin\Settings\Settings; 

class Reading {

 /*
	* Variables
	* ---------
	*/

	public static $additional_fields = [];

 /*
	* Constructor
	* -----------
	*/

	public function __construct() {
		if( count( FRM::$cpt ) == 0 )
			return;

		// register settings
		add_action( 'admin_init', [$this, 'setup'] );

		// admin pages list add state if associated with custom post type
		add_filter( 'display_post_states', function( $post_states, $post ) {
			foreach( FRM::$cpt as $c => $meta ) {
				$cpt_page_id = (int) get_option( FRM::get_namespaced_str( $c ) . '_page', 0 );

				if( $cpt_page_id === $post->ID )
					$post_states[] = $meta['label'] . ' Page';
			}

			return $post_states; 
		}, 10, 2 ); 
	}

 /*
	* Register and render fields
	* --------------------------
	*/

	public function setup() {
		$page_options = [0 => '— Select —'];

		$get_pages = get_pages( 'hide_empty=0' );

		foreach( $get_pages as $page )
			$page_options[$page->ID] = esc_attr( $page->post_title );

		$fields = [
			[
				'name' => 'post_more_label',
				'label' => 'Post more posts label',
				'section' => 'default',
				'type' => 'text'
			]
		];

		foreach( FRM::$cpt as $c => $meta ) {
			if( isset( $meta['no_reading'] ) )
				continue;
			
			$name = $c . '_page';

			$fields[] = [
				'name' => $name,
				'label' => $meta['label'] . ' post page',
				'section' => 'default',
				'type' => 'select',
				'value' => (int) get_option( $name, 0 ),
				'options' => $page_options,
				'label_hidden' => true,
				'on_save' => function( $value ) use ( $c, $meta ) {
					$v = sanitize_text_field( $value );

					if( isset( $meta['no_slug'] ) )
						return $v; 

					$id = (int) $v;

					// get page slug of assigned page
					$slug = get_post_field( 'post_name', $id );

					update_option( $c . '_slug', $slug );

					flush_rewrite_rules();

					return $v;
				}
			];

			$fields[] = [
				'name' => $c . '_posts_per_page',
				'label' => $meta['label'] . ' page shows at most',
				'section' => 'default',
				'type' => 'number',
				'label_hidden' => true,
				'class' => 'c-cpt',
				'attr' => [
					'step' => '1',
					'min' => '-1'
				]
			];

			$fields[] = [
				'name' => $c . '_more_label',
				'label' => $meta['label'] . ' more posts label',
				'section' => 'default',
				'type' => 'text'
			];

			if( isset( $meta['ajax_posts_per_page'] ) ) {
				$fields[] = [
					'name' => $c . '_ajax_posts_per_page',
					'label' => $meta['label'] . ' page loads at most (ajax)',
					'section' => 'default',
					'type' => 'number',
					'label_hidden' => true,
					'class' => 'c-cpt',
					'attr' => [
						'step' => '1',
						'min' => '1'
					]
				];
			}
		}	

		if( self::$additional_fields ) 
			$fields = array_merge( $fields, self::$additional_fields );

		// add fields
		new Settings( [
			'fields' => $fields,
			'page' => 'reading'
		] );
	}

} // end Reading
