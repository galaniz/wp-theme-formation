<?php

/*
 * Register custom gutenberg blocks
 * --------------------------------
 */

namespace Formation\Common\Blocks;

/*
 * Imports
 * -------
 */

use Formation\Formation as FRM; 

class Blocks {

   /*
    * Variables
    * ---------
    *
    * Folder url to register scripts with.
    *
    * @var string $blocks_folder_url 
    */

    public static $blocks_folder_url = '';

   /*
    * Append blocks with args to register.
    *
    * @var array $blocks Accepts array {
    *       @type string $name Accepts array {
    *           @type string $attr Accepts array.             
    *           @type string $default Accepts array.
    *           @type string $parent Accepts array.
    *           @type string $render Accepts function.
    *           @type string $handle Accepts string. Required.
    *           @type string $script Accepts $string. Required.
    *       }
    * }
    */

    public static $blocks = [];

   /*
	* Constructor
	* -----------
	*/

	public function __construct( $blocks_folder_url = '' ) {
        // set folder url
        self::$blocks_folder_url = $blocks_folder_url;

        // add blocks
        add_action( 'init', [$this, 'register_blocks'] );

        // ajax callbacks for previewing blocks in editor
        add_action( 'wp_ajax_nopriv_preview_blocks', [__CLASS__, 'preview_blocks'] );
        add_action( 'wp_ajax_preview_blocks', [__CLASS__, 'preview_blocks'] );
    }

   /*
	* Action callbacks
	* ----------------
    *
    * Loop through blocks and register.
	*/

    public function register_blocks() {
        // check blocks and blocks folder url exist
        if( count( self::$blocks ) == 0 || !self::$blocks_folder_url )
            return;

        $counter = 0;

        $data = [
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'blocks' => self::$blocks
        ];

        foreach( self::$blocks as &$name => $b ) {
            // check handle and script exist
            if( !isset( $b['handle'] ) || !isset( $b['script'] ) )
                continue;

            $name = FRM::$namespace . '/';
            $handle = FRM::$namespace . '_' . $b['handle'];

            if( is_admin() )
                wp_register_script(
                    $handle,
                    self::$blocks_folder_url . $b['script'], 
                    ['wp-blocks', 'wp-element', 'wp-editor', 'wp-blocks'],
                    NULL, 
                    true 
                );

            $register_args = ['editor_script' => $handle];

            if( isset( $b['render'] ) )
                $register_args['render_callback'] = $b['render'];

            if( isset( $b['attr'] ) ) 
                $register_args['attributes'] = $b['attr'];

            $r = register_block_type( $name, $register_args );

            if( $counter === 0 ) {
                // pass data to front end
                wp_localize_script( $handle, FRM::$namespace, $data );
            }

            $counter++;
        }
    } 

   /*
    * Ajax callback to preview block in editor.
    *
    * @pass string $name
    * @pass array $attr
    * @echo string of markup.
    */ 

    public static function preview_blocks() {
        try {
            $name = $_POST['name'];
            $attr = stripslashes( $_POST['attr'] );
            $attr = json_decode( $attr, true );

            $callback = self::$blocks[$name]['render'];
            call_user_func_array( $callback, $attr );

            echo json_encode( $output );

            exit;
        } catch( \Exception $e ) {
            header( http_response_code( 500 ) );
            echo $e->getMessage();
            exit;
        }
    }

} // end Blocks
