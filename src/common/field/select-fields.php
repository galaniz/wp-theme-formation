<?php

/*
 * Select fields to output form
 * ----------------------------
 */

namespace Formation\Common\Field;

/*
 * Imports
 * -------
 */

use Formation\Formation as FRM;  
use Formation\Common\Field\Field; 

class Select_Fields {

   /*
    * Get fields.
    *
    * @param string $name 
    * @return array
    */

	public static function get( $name ) {
		return [
            [
                'name' => $name . '[%i][label]',
                'label' => 'Label'
            ],
            [
                'type' => 'select',
                'name' => $name . '[%i][type]',
                'attr' => [
                    'onchange' => 'showHiddenFields( event )'
                ],
                'label' => 'Type',
                'options' => [
                    'text' => 'Text',
                    'checkbox' => 'Checkbox',
                    'radio' => 'Radio',
                    'email' => 'Email',
                    'select' => 'Select',
                    'textarea' => 'Text Area'
                ]
            ],
            [
                'type' => 'textarea',
                'name' => $name . '[%i][options]',
                'label' => 'Options (value : label)',
                'hidden' => true
            ],
            [
                'type' => 'checkbox',
                'label' => 'Required',
                'name' => $name . '[%i][required]',
                'value' => 1
            ]
        ];
	}

   /*
    * Output fields.
    *
    * @param array $data 
    * @return string of markup
    */

	public static function render( $data ) {
		var_dump( 'BLAHHHH', $data );

		if( !$data )
			return '';

		$output = '';

		return $output;
	}

   /*
    * Filter value by required fields.
    *
    * @param array $value 
    * @return array
    */

	public static function filter( $value ) {
        Field::filter_multi_fields( $value, ['type'] );
        return $value;
	}

   /*
    * Scripts to enqueue.
    */

	public static function scripts() {
		$path = '/vendor/alanizcreative/wp-theme-formation/src/common/assets/public/';
        $handle = FRM::$namespace . '-select-fields-script';

        wp_enqueue_script(
            $handle, 
            get_template_directory_uri() . $path . 'js/select-fields.js',
            [],
            NULL,
            true
        );
	}

} // end Select_Fields
