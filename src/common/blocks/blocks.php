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
use function Formation\additional_script_data;
use function Formation\write_log;

class Blocks {

   /*
    * Variables
    * ---------
    *
    * Folder url to register scripts with.
    *
    * @var string $folder_url 
    */

    public static $folder_url = '';

   /*
    * Optional extend media block.
    *
    * @var boolean $extend_media 
    */

    public static $extend_media = false;

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

	public function __construct( $args = [] ) {
        $args = array_merge( [
            'folder_url' => '',
            'extend_media' => false
        ], $args );

        extract( $args );

        write_log('FUCKCCC');
        write_log($extend_media);

        self::$folder_url = $folder_url;
        self::$extend_media = $extend_media;

        // add blocks
        add_action( 'init', [$this, 'register_blocks'], 999 );

        // modify media output if extend media
        if( $extend_media )
            add_filter( 'render_block', [$this, 'extend_media_filter'], 10, 2 );

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
        if( count( self::$blocks ) == 0 || !self::$folder_url )
            return;

        $counter = 0;

        $data = [
            'blocks' => []
        ];

        foreach( self::$blocks as $name => $b ) {
            // check handle and script exist
            if( !isset( $b['handle'] ) || !isset( $b['script'] ) )
                continue;

            $n = FRM::$namespace . '/' . $name;
            $handle = FRM::$namespace . '_' . $b['handle'];

            if( !isset( $data['blocks'][$n] ) )
                $data['blocks'][$n] = $b;

            if( is_admin() ) {
                $folder_url = isset( $b['frm'] ) ? FRM::$src_url . 'common/assets/public/js/blocks/' : self::$folder_url; 

                wp_register_script(
                    $handle,
                    $folder_url . $b['script'], 
                    ['wp-blocks', 'wp-element', 'wp-editor', 'wp-blocks'],
                    NULL, 
                    true 
                );

                // wp_localize_script( $handle, FRM::$namespace, $data );
            }

            $register_args = ['editor_script' => $handle];

            if( isset( $b['render'] ) )
                $register_args['render_callback'] = $b['render'];

            if( isset( $b['attr'] ) ) 
                $register_args['attributes'] = $b['attr'];

            $r = register_block_type( $n, $register_args );

            if( $counter === 0 ) {
                if( is_admin() ) {
                    $utils_script_handle = FRM::$namespace . '-block-utils-script';

                    wp_enqueue_script(
                        $utils_script_handle, 
                        FRM::$src_url . 'common/assets/public/js/blocks/utils.js',
                        [],
                        NULL,
                        true
                    );

                    wp_enqueue_script(
                        FRM::$namespace . '-insert-block-script', 
                        FRM::$src_url . 'common/assets/public/js/blocks/insert-block.js',
                        [$utils_script_handle],
                        NULL,
                        true
                    );

                    if( self::$extend_media ) {
                        $scripts = ['attr', 'control'];

                        foreach( $scripts as $s ) {
                            wp_enqueue_script(
                                FRM::$namespace . '-extend-media-' . $s . '-script',
                                FRM::$src_url . "common/assets/public/js/blocks/extend-media/$s.js",
                                ['wp-element', 'wp-blocks', 'wp-editor', 'wp-hooks'],
                                NULL, 
                                true 
                            );
                        }
                    }
                }
            }

            $counter++;
        }

        additional_script_data( FRM::$namespace, $data, true, true );
    } 

   /*
    * Render block callback to extend media.
    *
    * @param string $block_content
    * @pass array $block
    * @return string $block_content.
    */ 

    public static function extend_media_filter( $block_content, $block ) {
        $name = $block['blockName'];
        $classes = '';

        // target core blocks
        if( substr( $name, 0, 4 ) == 'core' ) {
            $embed = false;

            $embed_names = [
                'core/image',
                'core/video', 
                'core-embed/youtube', 
                'core-embed/vimeo'
            ];

            if( in_array( $name, $embed_names ) )
                $embed = true;

            if( !$embed )
                return $block_content;

            $b_attr = $block['attrs'];

            $caption = strpos( $block_content, 'figcaption' ) !== false ? true : false; 
            $caption_modifier = '--no-caption';
            $width = '100';
            $breakout = false;

            if( isset( $b_attr['containerWidth'] ) ) {
                $w = $b_attr['containerWidth'];

                if( $w != 'breakout' ) {
                    $width = $w;
                } else {
                    $breakout = true;
                }
            }

            if( $caption )
                $caption_modifier = '--caption';

            $classes = "l-$width $caption_modifier";

            if( $breakout ) { 
                $classes .= ' --breakout';

                $block_content = 
                    '<div class="l-breakout">' . 
                        $block_content .
                    '</div>';
            }

            $block_content = sprintf( "<div class='$classes'>%s</div>", $block_content );
        }

        return $block_content;
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
