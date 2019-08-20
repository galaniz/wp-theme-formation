<?php

/*
 * Settings field generation
 * -------------------------
 */

namespace Foundation\Admin\Settings;

/*
 * Imports
 */

use function \Foundation\additional_script_data;
use \Foundation\Common\Field;

class Settings {

   /*
    * Variables
    * ---------
    */

    // store fields
  	private $fields = [];
  	private $page = '';

  	// store options data
  	private $data = [];

    // multi item copies
    public static $localize_data = [
    	'multi' => []
    ];

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
    * Helper methods
    * --------------
    */

    // filter out empty multi fields
	public static function filter_multi_fields( &$array, $required = [] ) {
		if( !is_array( $array ) )
			return;

		foreach( $array as $k => &$v ) {
			if( is_array( $v ) ) {
				$unset = false;

				// check if values required and if empty remove parent
				foreach( $v as $q => $r ) {
					if( in_array( $q, $required ) && !$r )
						$unset = true;
				}

				if( $unset )
                    unset( $array[$k] ); 

				self::filter_multi_fields( $v );

				continue;
			}
		}
	}

   /*
    * Register and render fields
    * --------------------------
    */

	public function setup() {
	    foreach( $this->fields as $field ) {
	    	if( !isset( $field['fields'] ) )
	    		$field['label_hidden'] = true;

	    	$register_args = [];

	    	if( isset( $field['on_save'] ) ) {
	    		if( is_callable( $field['on_save'] ) ) {
	    			$register_args['sanitize_callback'] = $field['on_save'];
	    		}
	    	}

	    	register_setting( 
	    		$this->page, 
	    		Field::get_top_level_name( $field['name'] ), 
	    		$register_args 
		    );

	        add_settings_field( 
	        	$field['name'], 
	        	$field['label'], 
	        	[$this, 'fields'], 
	        	$this->page, 
	        	$field['section'], 
	        	$field 
	        );
	    }
	}

	public function fields( $args ) {
		// get top level name in case an array
		$top_level_name = Field::get_top_level_name( $args['name'] );

		if( isset( $this->data[$top_level_name] ) ) {
			$value = $this->data[$top_level_name];
		} else {
			$value = get_option( $top_level_name, '' );
			$this->data[$top_level_name] = $value;
		}

		$fields = isset( $args['fields'] ) ? $args['fields'] : [$args];
		$multi = isset( $args['multi'] ) ? true : false;

		// get count for multi fields
		$count = $multi && isset( $value[0] ) ? count( $value ) : 1;

		$output = '<div class="c-section-' . $top_level_name . '">';
		$copy = '';

		if( $multi )
			$output .= '<div class="o-multi">';

		for( $i = 0; $i < $count; $i++ ) {
			Field::render([
				'fields' => $fields,
				'index' => $i,
				'data' => $this->data,
				'value' => $value,
				'multi' => $multi
			], $output);

			if( $multi && $i === 0 )
				Field::render([
					'fields' => $fields,
					'index' => $i,
					'data' => $this->data,
					'value' => $value,
					'multi' => $multi,
					'copy' => true
				], $copy);
		}

		if( $multi ) {
			$output .= '</div>';

			if( $copy ) {
				self::$localize_data['multi'][$top_level_name] = $copy;
				additional_script_data( 'fg', self::$localize_data, true );
			}
		}

		$output .= '</div>';

		echo $output;
	}

   /*
    * Scripts and styles
    * ------------------
    */

    public static function scripts() {
        wp_enqueue_style( 
            'fg_settings_styles', 
            get_template_directory_uri() . '/src/admin/assets/public/css/settings.css' 
        );

        wp_enqueue_script(
            'fg_settings_script', 
            get_template_directory_uri() . '/src/admin/assets/public/js/settings.js',
            [],
            NULL,
            true
        );
    }

} // end Settings
