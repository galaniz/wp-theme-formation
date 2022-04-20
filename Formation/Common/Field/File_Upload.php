<?php
/**
 * Handle file upload
 *
 * @package wp-theme-formation
 */

namespace Formation\Common\Field;

/**
 * Imports
 */

use Formation\Formation as FRM;

/**
 * Class
 */

class File_Upload {

		/**
		 * Mime types and extensions.
		 */

		private $mime_types = [
			'image/jpeg'                    => 'jpg',
			'image/png'                     => 'png',
			'image/gif'                     => 'gif',
			'image/x-icon'                  => 'ico',
			'image/svg+xml'                 => 'svg',
			'image/svg'                     => 'svg',
			'application/pdf'               => 'pdf',
			'application/msword'            => 'doc',
			'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
			'application/vnd.ms-powerpoint' => 'ppt',
			'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx',
			'application/vnd.ms-excel'      => 'xls',
			'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
			'audio/mpeg'                    => 'mp3',
			'audio/mp4'                     => 'm4a',
			'audio/ogg'                     => 'ogg',
			'audio/x-wav'                   => 'wav',
			'video/mp4'                     => 'mp4',
			'video/quicktime'               => 'mov',
			'video/x-ms-wmv'                => 'wmv',
			'video/x-msvideo'               => 'avi',
			'video/mpeg'                    => 'mpg',
			'video/ogg'                     => 'ogv',
			'video/3gpp'                    => '3gp',
		];

		/**
		 * Make more secure array from _FILES.
		 *
		 * Source: https://dev.to/einlinuus/how-to-upload-files-with-php-correctly-and-securely-1kng
		 *
		 * @param array $files
		 * @return array
		 */

		private function normalize_files( $files ) {
				/* Normalize to indexed array of associative arrays */

				$_files       = [];
				$_files_count = count( $files['name'] );
				$_files_keys  = array_keys( $files );

				for ( $i = 0; $i < $_files_count; $i++ ) {
						foreach ( $_files_keys as $key ) {
								if ( 'tmp_name' === $key || 'name' === $key ) {
										$value = $files[ $key ][ $i ];

										if ( 'name' === $key ) {
												$value = wp_strip_all_tags( $value );
										}

										$_files[ $i ][ $key ] = $value;
								}
						}
				}

				/* Secure */

				foreach ( $_files as &$f ) {
						$file_path = $f['tmp_name'];
						$file_size = filesize( $file_path );
						$file_info = finfo_open( FILEINFO_MIME_TYPE );
						$file_type = finfo_file( $file_info, $file_path );

						$f['type'] = $file_type;
						$f['size'] = $file_size;
						$f['ext']  = $this->mime_types[ $file_type ] ?? '';
				}

				return $_files;
		}

		/**
		 * Verify file type.
		 *
		 * @param string $ext
		 * @return boolean
		 */

		private function validate_type( $type ) {
				if ( ! array_key_exists( $type, $this->mime_types ) ) {
						return false;
				}

				return true;
		}

		/**
		 * Check file size.
		 *
		 * @param int $size
		 * @return boolean
		 */

		private function validate_file_size( $size ) {
				if ( $size > wp_max_upload_size() ) {
						return false;
				}

				return true;
		}

		/**
		 * Process $_FILES and put in theme uploads directory.
		 *
		 * @param array $args
		 */

		public function __construct( $args ) {
				try {
						$args = array_replace_recursive(
								[
									'uploads_dir' => '',
									'uploads_url' => '',
									'success'     => false,
									'error'       => false,
								],
								$args
						);

						[
							'uploads_dir'      => $uploads_dir,
							'uploads_url'       => $uploads_url,
							'success'         => $success,
							'error'          => $error,
						] = $args;

						/* Check for uploads directory and url */

						if ( ! $uploads_dir || ! $uploads_url ) {
								throw new \Exception( 'Upload directory and/or url not specified' );
						}

						if ( ! file_exists( $uploads_dir ) ) {
								mkdir( $uploads_dir, 0755 );
						}

						/* Normalize files */

						$files = $this->normalize_files( $_FILES['files'] );

						/* Store successfully uploaded files data */

						$data = [];

						array_walk(
								$files,
								function( $file, $i ) use ( &$data, $args ) {
										[
											'type'     => $type,
											'tmp_name' => $tmp_name,
											'size'     => $size,
											'name'     => $name,
											'ext'      => $ext,
										] = $file;

										/* Check if uploaded via HTTP POST */

										if ( is_uploaded_file( $tmp_name ) ) {
												/* More validations */

												if ( ! $this->validate_type( $type ) ) {
														throw new \Exception( 'Invalid type' );
												}

												if ( ! $this->validate_file_size( $size ) ) {
														throw new \Exception( 'File exceeds max upload size.' );
												}

												/* Check if file exists (append _copy if it does) */

												if ( file_exists( $args['uploads_dir'] . $name ) ) {
														$name .= '_copy';
												}

												/* Set file path and url */

												$abs_path = $args['uploads_dir'] . $name;
												$url      = $args['uploads_url'] . $name;

												/* Put file in uploads folder */

												$moved = move_uploaded_file( $tmp_name, $abs_path );

												if ( $moved ) {
														$data[] = [
															'title'     => $name,
															'url'       => $url,
															'path'      => $abs_path,
															'mime_type' => $type,
															'size'      => $size,
															'ext'       => $ext,
														];
												} else {
														throw new \Exception( 'Error moving file' );
												}
										} else {
												throw new \Exception( 'Not uploaded via post' );
										}
								}
						); // end array_walk

						if ( $success ) {
								if ( is_callable( $success ) ) {
										call_user_func_array( $args['success'], [$data] );
								}
						}
				} catch ( \Exception $e ) {
						if ( $error ) {
								if ( is_callable( $error ) ) {
										call_user_func_array( $args['error'], [$e->getMessage()] );
								}
						}
				}
		}

} // End File_Upload
