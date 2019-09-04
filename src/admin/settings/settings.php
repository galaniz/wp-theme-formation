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

use Formation\Formation as FRM;  
use \Formation\Common\Field\Field;

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
	    	$name = FRM::get_namespaced_str( $field['name'] );
	    	$top_level_name = Field::get_top_level_name( $name );
	    	$register_args = [];

	    	if( !isset( $field['fields'] ) )
	    		$field['label_hidden'] = true;

	    	if( isset( $field['on_save'] ) ) {
	    		if( is_callable( $field['on_save'] ) ) {
	    			$register_args['sanitize_callback'] = $field['on_save'];
	    		}
	    	}

	    	register_setting( 
	    		$this->page, 
	    		$top_level_name, 
	    		$register_args 
		    );

	        add_settings_field( 
	        	$name, 
	        	$field['label'], 
	        	function( $args ) use ( $top_level_name ) { // $args = $field
	        		$args['data'] = [
	        			$top_level_name => get_option( $top_level_name, '' )
	        		];
	        		
	        		echo Field::render( $args );
	        	}, 
	        	$this->page, 
	        	$field['section'], 
	        	$field 
	        );
	    }
	}

} // end Settings
