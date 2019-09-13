<?php

/*
 * Contact form block
 * ------------------
 */

namespace Formation\Common\Blocks;

/*
 * Imports
 * -------
 */

use Formation\Formation as FRM; 
use Formation\Common\Field\Field;
use Formation\Common\Blocks\Blocks; 

class Contact_Form {

   /*
    * Variables
    * ---------
    *
    * Args for contact form and field blocks.
    *
    * @var array $blocks
    * @see class Blocks for args breakdown.
    */

    public static $blocks = [
        'contact-form' => [
            'attr' => [
                'id' => ['type' => 'string'],
                'email' => ['type' => 'string'],
                'subject' => ['type' => 'string'],
                'submit_label' => ['type' => 'string']
            ],
            'default' => [
                'id' => '',
                'email' => '',
                'subject' => '',
                'submit_label' => 'Submit'
            ],
            'render' => [__CLASS__, 'render_contact_form'],
            'handle' => 'contact_form',
            'script' => 'contact-form.js'
        ], 
        'contact-form-field' => [
            'attr' => [
                'type' => ['type' => 'string'],
                'name' => ['type' => 'string'],
                'label' => ['type' => 'string'],
                'placeholder' => ['type' => 'string'],
                'required' => ['type' => 'boolean'],
                'attr' => ['type' => 'string'],
                'options' => ['type' => 'string'],
                'width' => ['type' => 'string'],
            ],
            'default' => [
                'type' => 'text',
                'name' => '',
                'label' => '',
                'placeholder' => '',
                'required' => false,
                'attr' => '',
                'options' => '',
                'width' => '100'
            ],
            'render' => [__CLASS__, 'render_contact_form_field'],
            'handle' => 'contact_form_field',
            'script' => 'contact-form-field.js'
        ]
    ];

   /*
	* Constructor
	* -----------
	*/

	public function __construct() {
        // add blocks
        add_action( 'init', [$this, 'register_blocks'] );

        // register meta and routes
        add_action( 'rest_api_init', function() {
            register_rest_route(
                FRM::$namespace,
                '/preview-contact-form',
                [
                    'methods' => 'GET',
                    'callback' => [$this, 'preview_contact_form']
                ]
            );
        } );
    }

   /*
    * Pass blocks to Blocks class
    * ---------------------------
    */

    public function register_blocks() {
        foreach( self::$blocks as $name => $b ) {
            $b['frm'] = true;
            Blocks::$blocks[$name] = $b;
        }
    }

   /*
    * Helpers
    * -------
    *
    * Get associative array from string of key value pairs.
    *
    * @param string $str 
    * @param string $order of key value pairs.
    * @return array 
    */

    public static function get_assoc_array_from_str( $str = '', $order = 'key:value' ) {
        if( !$str )
            return [];

        $array = [];

        $a = explode( "\n", $str );

        if( isset( $a[0] ) ) {
            foreach( $a as $b ) {
                $c = explode( ' : ', $b );

                if( isset( $c[0] ) ) {
                    $key = $c[0];
                    $value = $c[1];

                    if( $order == 'key:value' ) {
                        $array[$key] = $value;
                    } else {
                        $array[$value] = $key;
                    }
                }
            }
        }

        return $array;
    }

   /*
    * Render callbacks
    * ----------------
    *
    * Output contact form.
    *
    * @param array $attributes
    * @param string $content
    * @return string of markup
    */

    public static function render_contact_form( $attributes, $content = '' ) {
        $attr = array_replace_recursive( self::$blocks[FRM::$namespace . '/contact-form']['default'], $attributes );
        extract( $attr );

        if( !$email )
            $email = get_option( 'admin_email', '' );

        if( !$subject )
            $subject = get_bloginfo( 'name' ) . ' Contact Form';

        if( $id ) {
            // make sure $id not greater than 64 characters
            $id = substr( $id, 0, 40 );

            update_option( FRM::$namespace . '_' . $id, [
                'email' => $email,
                'subject' => $subject
            ] );
        } 

        $s_error = FRM::$sprites['Error'];
        $s_success = FRM::$sprites['Success'];

        return FRM::render_form( [
            'id' => $id,
            'data_type' => 'contact',
            'fields' => $content,
            'submit_label' => $submit_label
        ] );
    }

   /*
    * Output contact form field.
    *
    * @param array $attributes
    * @return string of markup
    */

    public static function render_contact_form_field( $attributes ) {
        $attr = array_replace_recursive( self::$blocks[FRM::$namespace . '/contact-form-field']['default'], $attributes );
        extract( $attr );

        $output = '';
        $field = [
            'name' => FRM::$namespace . '_' . $name,
            'label' => $label,
            'type' => $type,
            'placeholder' => $placeholder,
            'field_class' => "l-$width"
        ];

        $attr_array = self::get_assoc_array_from_str( $attr );
        $options_array = self::get_assoc_array_from_str( $attr, 'value:key' );

        if( $required )
            $attr_array['aria-required'] = true;

        if( $type == 'textarea' && !isset( $attr_array['rows'] ) )
            $attr_array['rows'] = 8;

        $field['attr'] = $attr_array;
        $field['options'] = $options_array;

        Field::render( ['fields' => [$field]], $output );

        return $output;
    }

   /*
    * Preview contact form with rest api callback.
    *
    * @param array $data
    * @return string of markup
    */

    public function preview_contact_form( $data ) {
        $req = $data->get_param( 'required' );

        if( is_string( $req ) ) 
            $req = $req == 'true' ? true : false;

        $args = [
            'type' => $data->get_param( 'type' ),
            'name' => $data->get_param( 'name' ),
            'label' => $data->get_param( 'label' ),
            'placeholder' => $data->get_param( 'placeholder' ),
            'required' => $req,
            'attr' => $data->get_param( 'attr' ),
            'options' => $data->get_param( 'options' ),
            'width' => $data->get_param( 'width' )
        ];

        return self::render_contact_form_field( $args );
    }

} // end Contact_Form
