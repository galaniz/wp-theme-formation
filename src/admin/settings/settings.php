<?php

/*
 * Settings field generation
 * -------------------------
 */

namespace Formation\Admin\Settings;

/*
 * Imports
 * -------
 */

use \Formation\Common\Field;

class Settings {

   /*
    * Variables
    * ---------
    *
    * Store fields.
    *
    * @var array $fields
    * @see \Formation\Common\Field for default properties.
    * 
    * Note: section and on_save additional properties.
    */

   	private $fields = [];

   /*
    * Page to register setting.
    *
    * @var string $page
    */

  	private $page = '';

   /*
    * Constructor
    * -----------
    */

	public function __construct( $fields = false, $page = '' ) {
		if( !$fields || !$page )
			return;

		$this->fields = $fields;
		$this->page = $page;
		$this->setup();
	}

   /*
    * Register and render fields
    * --------------------------
    */

	public function setup() {
	    foreach( $this->fields as $field ) {
	    	$top_level_name = Field::get_top_level_name( $field['name'] );
	    	$register_args = [];

	    	if( !isset( $field['fields'] ) )
	    		$field['label_hidden'] = true;

	    	if( isset( $field['on_save'] ) ) {
	    		if( is_callable( $field['on_save'] ) ) {
	    			$register_args['sanitize_callback'] = $field['on_save'];
	    		}
	    	}

	    	$field['data'] = get_option( $top_level_name, '' );

	    	register_setting( 
	    		$this->page, 
	    		$top_level_name, 
	    		$register_args 
		    );

	        add_settings_field( 
	        	$field['name'], 
	        	$field['label'], 
	        	function( $args ) { // $args = $field
	        		echo Field::render( $args );
	        	}, 
	        	$this->page, 
	        	$field['section'], 
	        	$field 
	        );
	    }
	}

} // end Settings
