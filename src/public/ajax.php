<?php

/*
 * Actions and callbacks for ajax requests
 * ---------------------------------------
 */

namespace Formation\Pub;

use \Formation\Formation as FRM; 
use function \Formation\write_log;

trait Ajax {

   /*
	* Setup actions.
	*
	* @uses add_action() to add ajax actions.
	*/

	public static function ajax_actions() {
		add_action( 'wp_ajax_nopriv_create_nonce', function() {
			static::create_nonce();
		} );

		add_action( 'wp_ajax_create_nonce', function() {
			static::create_nonce();
		} );

		add_action( 'wp_ajax_nopriv_send_form', function() {
			static::send_form( 'nopriv' );
		} );

		add_action( 'wp_ajax_send_form', function() {
			static::send_form( 'priv' );
		} );

		add_action( 'wp_ajax_nopriv_get_posts', function() {
			static::get_posts();
		} );

		add_action( 'wp_ajax_get_posts', function() {
			static::get_posts();
		} );
    }

    /*
     * Create nonces before form submission.
     *
     * @pass string $nonce_name Required.
     * @echo string json containing nonce.
     */

    public static function create_nonce() {
    	try {
    		if( !isset( $_POST['nonce_name'] ) ) 
    			throw new \Exception( 'No nonce name' ); 

    		echo json_encode( [
    			'nonce' => wp_create_nonce( $_POST['nonce_name'] )
    		] );

			exit;
    	} catch( \Exception $e ) { 
    		echo $e->getMessage();
    		header( http_response_code( 500 ) );
			exit;
    	}
    }

    /*
     * Validate nonces and recaptcha for contact and comment forms.
     *
     * Note: recaptcha api keys required.
     *
     * @pass string $nonce Required.
     * @pass string $nonce_name Required.
     * @pass string $recaptcha Required.
     */

    public static function send_form( $priv_type ) {
    	try {
    		if( !isset( $_POST['nonce'] ) ) 
    			throw new \Exception( 'Forbidden' ); 

    		if( !wp_verify_nonce( $_POST['nonce'], $_POST['nonce_name'] ) )
				throw new \Exception( 'Forbidden' );

    		// check recaptcha token exists
    		if( !isset( $_POST['recaptcha'] ) ) 
    			throw new \Exception( 'Forbidden' ); 

    		$recaptcha = $_POST['recaptcha'];
			$recaptcha_secret_key = get_option( static::$namespace . '_recaptcha_secret_key', '' );

    		// check recaptcha api keys exist
    		if( !$recaptcha_secret_key )
    			throw new \Exception( 'Forbidden' ); 

			// verify recaptcha token
			$recaptcha_url = "https://www.google.com/recaptcha/api/siteverify?secret=$recaptcha_secret_key&response=$recaptcha";
			$recaptcha_response = wp_remote_get( $recaptcha_url );

			if( isset( $recaptcha_response['body'] ) ) {
				$recaptcha_response_result = json_decode( $recaptcha_response['body'], true );
				$recaptcha_response_success = $recaptcha_response_result['success'];
				$recaptcha_response_score = $recaptcha_response_result['score'];
			} else {
				$recaptcha_response_success = false;
				$recaptcha_response_score = 0;
			}

			if( !$recaptcha_response_result || $recaptcha_response_score < 0.8 )
				throw new \Exception( 'Recaptcha invalid / score too low' );

			$type = $_POST['type'] ?? 'contact';

			if( $type == 'contact' ) {
				static::send_contact_form();
			} else if( $type == 'comment' ) {
				// static::send_comment( $priv_type );
			}

			exit;
    	} catch( \Exception $e ) { 
    		echo $e->getMessage();
    		header( http_response_code( 500 ) );
			exit;
    	}
    }

    /*
     * Process and send contact form.
     *
     * @pass string $id Required.
     * @pass array $inputs
     * @echo string if successfully sent
     */
	
	protected static function send_contact_form() {
    	// id with array of information ( email, subject... )
    	$id = $_POST['id'];

    	if( !$id )
    		throw new \Exception( 'No id' );

    	$meta = get_option( static::$namespace . '_form_' . $id, '' );

    	if( !$meta ) 
    		throw new \Exception( 'No meta' );

    	$to_email = $meta['email'];
		
		if( !$to_email )
			throw new \Exception( 'No to email' );

		$subject = $meta['subject'];
		$inputs = $_POST['inputs'];

		$site_url = home_url();
		$site_name = get_bloginfo( 'name' );
		$output = '';

		$input_types = [
			'text' => 'text_field',
			'select' => 'text_field',
			'textarea' => 'textarea_field',
			'email' => 'email'
		];

		foreach( $inputs as $name => $input ) {
			$input_type = $input['type'];
			$input_label = $input['label'] ?? '';
			$input_value = $input['value'];

			if( $input_type ) {
				if( isset( $input_types[$input_type] ) ) {
					$sanitize_type = 'sanitize_' . $input_types[$input_type];

					if( function_exists( $sanitize_type ) )
						$input_value = $sanitize_type( $input_value );
				} 

				if( $input_type === 'textarea' )
					$input_value = nl2br( $input_value );
			}

			if( is_array( $input_value ) ) 
				$input_value = implode( '<br>', $input_value );

			if( $input_label == 'subject' && $input_value ) {
				$subject .= " - " . $input_value;
				continue;
			}

			// make email name equal to sender name
			if( $input_label == 'name' && $input_value ) {
				add_filter( 'wp_mail_from_name', function( $name ) use ( $input_value ) {
					return $input_value;
				} );
			}

			if( $input_type == 'email' ) {
				$output .= "<strong>$input_label</strong>: " . ( $input_value ? "<a href='mailto:$input_value'>$input_value</a>" : '' ) . '<br>';

				if( $input_value ) {
					// make email from equal to sender email
					add_filter( 'wp_mail_from', function( $email ) use ( $input_value ) {
						return $input_value;
					} );
				}
			} else {
				$input_label_output = '';
				$email_label_exists = isset( $input['email_label'] );

				if( $input_label ) {
					$input_label_output = '<strong>' . $input_label . '</strong>: ';

					if( $input_type === 'textarea' && $input_value )
						$input_label_output .= '<br>';
				}

				if( $email_label_exists )
					$input_label_output = '<strong>' . $input['email_label'] . '</strong>:' . ( $input_value ? '<br>' : '' );

				if( $input_value ) {
					$output .= $input_label_output . $input_value . '<br>';
				} else {
					if( $input_label_output )
						$output .= $input_label_output . $input_value . '<br>';
				}
			}
		}

		$output .= "<br>This email was sent from a contact form on $site_name ($site_url)";

		// allow html
		add_filter( 'wp_mail_content_type', function() {
			return 'text/html';
		} );

		if( !$subject )
			$subject = "$site_name Contact Form";

		// send email
		$result = wp_mail( $to_email, $subject, "<p style='margin:0;font-family:sans-serif;'>$output</p>" );

		if( !$result ) {
			throw new \Exception( 'Error sending form' ); 
		} else {
			echo json_encode( ['success' => 'Form successully sent.'] );
		}

		// reset content-type to avoid conflicts
		// remove_filter( 'wp_mail_content_type', 'wpdocs_set_html_mail_content_type' );
    }

    /*
     * Get more posts for posts and custom post types.
     *
     * @pass int $offset
     * @pass string $type
     * @pass int $posts_per_page Required. 
     * @pass array $query_args
     * @pass array $filters
     * @echo string json containing output
     */

    public static function get_posts() {
    	try {
	    	$offset = (int) $_POST['offset'];
	    	$type = $_POST['type'];
	    	$posts_per_page = (int) $_POST['ppp'];

	    	if( !$posts_per_page ) 
	    		throw new \Exception( 'No limit' ); 

	    	$args = [
	    		'offset' => $offset,
	    		'post_type' => $type,
	    		'post_status' => 'publish',
	    		'posts_per_page' => $posts_per_page
	    	];

	    	write_log($_POST['query_args']);
	    	write_log($_POST['filters']);

	    	if( isset( $_POST['query_args'] ) && isset( $_POST['filters'] ) ) {
	    		$filters = $_POST['filters'];
	    		$query_args = $_POST['query_args'];
	    		$processed_args = [];

	    		foreach( $filters as $id => $arr ) {
	    			if( isset( $query_args[$id] ) ) {
	    				$add_to_query_args = true;

	    				// replace placeholders like %value with value
						array_walk_recursive(
							$query_args[$id],
							function( &$v ) use ( $arr, &$add_to_query_args ) {
								$actual_v = $arr['value'];

								if( $actual_v == 'null' )
									$add_to_query_args = false;

								if( $v == '%value' )
									$v = $actual_v;

								if( $v == '%value:int' )
									$v = (int) $actual_v;

								if( $v == '%operator' && isset( $arr['operator'] ) )
									$v = $arr['operator'];
							}
						);

						if( $add_to_query_args )
							$processed_args = array_replace_recursive( $query_args[$id], $processed_args );
	    			}		
	    		}

				$args = array_merge_recursive( $processed_args, $args );

				write_log('ARGS');
				write_log( $processed_args );
				write_log( $args );
	    	}

	    	$output = static::render_ajax_posts( $args );

	    	if( is_string( $output ) ) 
	    		echo $output;

	    	if( is_array( $output ) )
	    		echo json_encode( $output );

	    	exit;
    	} catch( \Exception $e ) {
    		echo $e->getMessage();
    		header( http_response_code( 500 ) );
			exit;
    	}
    }

} // end Ajax
