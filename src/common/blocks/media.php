<?php

/*
 * Image / video block
 * -------------------
 */

namespace Formation\Common\Blocks;

/*
 * Imports
 * -------
 */

use Formation\Formation as FRM; 
use Formation\Common\Blocks\Blocks; 

class Media {
   
   /*
    * Variables
    * ---------
    *
    * Args for media blocks.
    *
    * @var array $blocks
    * @see class Blocks for args breakdown.
    */

    public static $blocks = [
        'media' => [
            'attr' => [
                'id' => ['type' => 'integer'],
                'url' => ['type' => 'string'],
                'type' => ['type' => 'string'],
                'subtype' => ['type' => 'string'],
                'alt' => ['type' => 'string'],
                'autoplay' => ['type' => 'boolean'],
                'loop' => ['type' => 'boolean'],
                'muted' => ['type' => 'boolean'],
                'controls' => ['type' => 'boolean'],
                'poster' => ['type' => 'string'],
                'height' => ['type' => 'integer'],
                'width' => ['type' => 'integer'],
                'src' => ['type' => 'object']
            ],
            'default' => [
                'id' => '',
                'url' => '',
                'type' => '',
                'subtype' => '',
                'alt' => '',
                'autoplay' => true,
                'loop' => true,
                'muted' => true,
                'controls' => false,
                'poster' => '',
                'height' => 0,
                'width' => 0,
                'src' => [
                    'mp4' => '',
                    'webm' => '',
                    'ogv' => ''
                ]
            ],
            'parent' => [],
            'render' => [__CLASS__, 'render_media'],
            'handle' => 'media',
            'script' => 'block-media.js'
        ]
    ];

   /*
	* Constructor
	* -----------
	*/

	public function __construct( $parent = [] ) {
        if( $parent )
            self::$blocks['media']['parent'] = $parent;

        add_action( 'init', [$this, 'register_blocks'] );
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
    * Render media
    * ------------
    *
    * Output media as json string.
    *
    * @param array $attributes
    * @return json string
    */
    
    public static function render_media( $attributes ) {
        $attr = array_replace_recursive( self::$blocks[FRM::$namespace . '/media']['default'], $attributes );
        return json_encode( $attr ) . '||';
    }

} // end Media
