<?php

/*
 * Handle file upload
 * ------------------
 */

namespace Formation\Common\Field;

/*
 * Imports
 * -------
 */

use function Formation\write_log; 
use Formation\Formation as FRM; 

class File_Upload {

  /*
   * Helpers
   * -------
   *
   * Change _FILES array to cleaner one.
   *
   * @source: https://bit.ly/2q0xxy5
   * @param array $files
   * @return array
   */

  private function normalize_files( &$files ) {
    $_files = [];
    $_files_count = count( $files['name'] );
    $_files_keys = array_keys( $files );

    for( $i = 0; $i < $_files_count; $i++ )
      foreach( $_files_keys as $key )
        $_files[$i][$key] = $files[$key][$i];

    return $_files;
  }

  /*
   * Check if from valid origin.
   *
   * @param string $origin
   * @return boolean
   */

  private function validate_origin( $origin ) {
    if( strpos( $origin, 'localhost' ) !== false ) {
      return true;
    } else {
      return false;
    }
  }

  /*
   * Verify file extension.
   *
   * @param string $ext
   * @return boolean
   */

  private function validate_extension( $ext ) {
    $valid_extensions = [
      'jpg', 
      'jpeg', 
      'png', 
      'gif',
      'ico',
      'svg',
      'pdf',
      'doc',
      'docx',
      'ppt',
      'pptx',
      'pps',
      'ppsx',
      'odt',
      'xls',
      'xlsx',
      'psd',
      'mp3',
      'm4a',
      'ogg',
      'wav',
      'mp4',
      'm4v',
      'mov',
      'wmv',
      'avi',
      'mpg',
      'ogv',
      '3gp',
      '3g2'
    ];

    if( !in_array( strtolower( $ext ), $valid_extensions ) )
      return false;

    return true;
  }

  /*
   * Sanitize input.
   */

  private function validate_input() {
    /*if( preg_match( '/([^\w\s\d\-_~,;:\[\]\(\).])|([\.]{2,})/', $image_tmp_name ) ) {
      header( http_response_code( 400 ) );
      exit;
    }*/
  }

  /*
   * Check file size.
   *
   * @param int $size
   * @return boolean
   */

  private function validate_file_size( $size ) {
    if( $size > wp_max_upload_size() )
      return false;

    return true;
  }

  /*
   * Constructor
   * -----------
   *
   * Process $_FILES and put in theme uploads directory.
   *
   * @param array $args
   */

  public function __construct( $args ) {
    try {
      $args = array_replace_recursive( [
        'uploads_dir' => '',
        'uploads_url' => '',
        'success' => false,
        'error' => false
      ], $args );

      extract( $args );

      /* Check for uploads directory and url */

      if( !$uploads_dir || !$uploads_url )
        throw new \Exception( 'Upload directory and/or url not specified' );

      if( !file_exists( $uploads_dir ) )
        mkdir( $uploads_dir, 0755 );

      /* Validate origin */

      /*if( !$this->validate_origin( $_SERVER['HTTP_HOST'] ) ) {
        throw new \Exception( 'Invalid origin' );
      } else {
        header( 'Access-Control-Allow-Origin: ' . $_SERVER['HTTP_HOST'] );
      }*/
      
      /* Normalize files */

      $files = $this->normalize_files( $_FILES['files'] );

      /* Store successfully uploaded files data */

      $data = [];

      array_walk( $files, function( $file, $i ) use ( &$data, $args ) {
        list( $type, $tmp_name, $size, $name, $ext ) = [
          $file['type'], 
          $file['tmp_name'], 
          $file['size'],
          pathinfo( $file['name'], PATHINFO_FILENAME ),
          strtolower( pathinfo( $file['name'], PATHINFO_EXTENSION ) )
        ];

        /* Check if uploaded via HTTP POST */

        if( is_uploaded_file( $tmp_name ) ) {

          /* More validations */

          if( !$this->validate_extension( $ext ) )
            throw new \Exception( 'Invalid extension' );

          if( !$this->validate_file_size( $size ) )
            throw new \Exception( 'File exceeds max upload size.' );

          /* Check if file exists ( append _copy if it does ) */

          if( file_exists( $args['uploads_dir'] . $name . '.' . $ext ) )
            $name .= '_copy';

          /* Set file path and url */

          $abs_path = $args['uploads_dir'] . $name . '.' . $ext;
          $url = $args['uploads_url'] . $name . '.' . $ext;

          /* Put file in uploads folder */

          $moved = move_uploaded_file( $tmp_name, $abs_path );

          if( $moved ) {
            $data[] = [ 
              'title' => $name, 
              'url' => $url,
              'mime_type' => $type,
              'size' => $size,
              'ext' => $ext
            ];
          } else {
            throw new \Exception( 'Error moving file' );
          }
        } else {
          throw new \Exception( 'Not uploaded via post' );
        }
      } );

      if( $success ) {
        if( is_callable( $success ) )
          call_user_func_array( $args['success'], [$data] );
      }
    } catch( \Exception $e ) {
      if( $error ) {
        if( is_callable( $error ) )
          call_user_func_array( $args['error'], [$e->getMessage()] );
      }
    }
  }

} // end File_Upload
