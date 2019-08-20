<?php

/*
 * Custom gutenberg blocks
 * -----------------------
 */

namespace FG\Common\Blocks;

// imports
use FG\Index as FG; 
use function \FG\write_log;

class Blocks {

   /*
    * Variables
    * ---------
    */

    public static $blocks = [];

   /*
	* Constructor
	* -----------
	*/

	public function __construct() {
        // add blocks
        add_action( 'init', [$this, 'register_blocks'] );

        // ajax callbacks for previewing blocks in editor
        add_action( 'wp_ajax_nopriv_preview_blocks', [__CLASS__, 'preview_blocks'] );
        add_action( 'wp_ajax_preview_blocks', [__CLASS__, 'preview_blocks'] );

        // add extend media script
        add_action( 'enqueue_block_editor_assets', [__CLASS__, 'extend_media_block'] );
    }

   /*
	* Action callbacks
	* ----------------
	*/

    public function register_blocks() {
        if( count( self::$blocks ) == 0 )
            return;

        $counter = 0;
        $data = [
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'blocks' => self::$blocks,
            // 'post_type' => get_current_screen()->post_type
        ];

        foreach( self::$blocks as $name => $b ) {
            $handle = $b['handle'];

            if( is_admin() )
                wp_register_script(
                    $handle,
                    get_template_directory_uri() . '/src/common/assets/public/js/' . $b['script'], 
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
                wp_localize_script( $handle, 'fg', $data );
            }

            $counter++;
        }
    } 
    
    public static function extend_media_block() {
        $scripts = [
            'attr' => 'block-extend-media-attr.js',
            'control' => 'block-extend-media-control.js'
        ];

        foreach( $scripts as $s => $script ) {
            wp_enqueue_script(
                'fg_extend_media_' . $s,
                get_template_directory_uri() . '/src/common/assets/public/js/' . $script,
                ['wp-element', 'wp-blocks', 'wp-editor', 'wp-hooks'],
                NULL, 
                true 
            );
        }
    }

   /*
    * Render inner blocks
    * -------------------
    */ 

    public static function render_inner_blocks( $blocks, &$output ) {
        if( is_array( $blocks ) ) {
            if( count( $blocks ) > 0 ) {
                foreach( $blocks as $b ) {
                    $name = $b['name'] ?? $b['[name'];
                    $attr = $b['attributes'] ?? $b['[attributes'];

                    if( strpos( $name, 'fg' ) !== false ) {
                        $callback = self::$blocks[$name]['render'];
                        call_user_func_array( $callback, $attr );
                    } else {
                        $output .= self::render_core_block( $name, $attr );
                    }
                }
            }
        }
    }

    public static function render_core_block( $name = '', $attr = [] ) {
        if( !$name || !$attr )
            return '';

        $output = '';

        switch( $name ) {
            case 'core/paragraph':
                $output = '<p>' . $attr['content'] . '</p>';
                break;
            case 'core/list':
                $output = '<ul>' . $attr['values'] . '</ul>';
                break; 
            case 'core/html':
                $output = $attr['content'];
                break; 
            default:
                $output = $attr['content'];
        }

        return $output;
    }

   /*
    * Preview block in editor
    * -----------------------
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
        } catch( Exception $e ) {
            header( http_response_code( 500 ) );
            echo $e->getMessage();
            // echo 'Error getting posts';
            exit;
        }
    }

} // end Blocks
