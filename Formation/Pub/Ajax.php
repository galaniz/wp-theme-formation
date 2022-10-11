<?php
/**
 * Actions and callbacks for ajax requests
 *
 * @package wp-theme-formation
 */

namespace Formation\Pub;

/**
 * Imports
 */

use Formation\Formation as FRM;

/**
 * Trait
 */

trait Ajax {

	/**
	 * Setup actions.
	 *
	 * @uses add_action() to add ajax actions.
	 */

	public static function ajax_actions() {
		add_action(
			'wp_ajax_nopriv_create_nonce',
			function() {
				static::create_nonce();
			}
		);

		add_action(
			'wp_ajax_create_nonce',
			function() {
				static::create_nonce();
			}
		);

		add_action(
			'wp_ajax_nopriv_send_form',
			function() {
				static::send_form( 'nopriv' );
			}
		);

		add_action(
			'wp_ajax_send_form',
			function() {
				static::send_form( 'priv' );
			}
		);

		add_action(
			'wp_ajax_nopriv_get_posts',
			function() {
				/* phpcs:ignore */
				static::get_posts( $_POST );
			}
		);

		add_action(
			'wp_ajax_get_posts',
			function() {
				/* phpcs:ignore */
				static::get_posts( $_POST );
			}
		);
	}

	/**
	 * Create nonces before form submission.
	 *
	 * @pass string $nonce_name Required.
	 * @echo string json containing nonce.
	 */

	public static function create_nonce() {
		try {
			/* phpcs:ignore */
			if ( ! isset( $_POST['nonce_name'] ) ) {
				throw new \Exception( 'No nonce name' );
			}

			echo wp_json_encode(
				[
					/* phpcs:ignore */
					'nonce' => wp_create_nonce( esc_html( $_POST['nonce_name'] ) ),
				]
			);

			exit;
		} catch ( \Exception $e ) {
			echo esc_html( $e->getMessage() );
			header( http_response_code( 500 ) );
			exit;
		}
	}

	/**
	 * Validate nonces for contact and comment forms.
	 *
	 * @pass string $nonce Required.
	 * @pass string $nonce_name Required.
	 */

	public static function send_form( $priv_type = 'nopriv' ) {
		try {
			if ( ! isset( $_POST['nonce'] ) ) {
				throw new \Exception( 'Forbidden' );
			}

			if ( ! wp_verify_nonce( $_POST['nonce'], $_POST['nonce_name'] ) ) {
				throw new \Exception( 'Forbidden' );
			}

			/* Honey Pot */

			/* Type */

			$type = $_POST['type'] ?? 'contact';

			if ( 'contact' === $type ) {
				static::send_contact_form( $_POST );
			} elseif ( 'mailchimp' === $type ) {
				static::mailchimp_signup( $_POST );
			}

			/* phpcs:ignore */
			/* elseif ( 'comment' === $type ) { static::send_comment( $priv_type ); } */

			exit;
		} catch ( \Exception $e ) {
			echo esc_html( $e->getMessage() );
			header( http_response_code( 500 ) );
			exit;
		}
	}

	/**
	 * Process mailchimp signup form.
	 *
	 * @pass string $id Required.
	 * @pass array $inputs Required.
	 * @echo string if successfully sent
	 */

	protected static function mailchimp_signup( $post ) {
		$id     = $post['id'] ?? false;
		$inputs = $post['inputs'] ?? false;

		if ( ! $id ) {
			throw new \Exception( 'No id' );
		}

		if ( ! $inputs ) {
			throw new \Exception( 'No inputs' );
		}

		$meta = get_option( static::$namespace . '_form_' . $id, '' );

		if ( ! $meta ) {
			throw new \Exception( 'No meta' );
		}

		$list_id = $meta['mailchimp_list'] ?? false;

		if ( ! $list_id ) {
			throw new \Exception( 'No List ID' );
		}

		/* Inputs */

		$email        = '';
		$tags         = [];
		$merge_fields = [];

		$n = self::$namespace . '_';

		foreach ( $inputs as $name => $input ) {
			$input_type  = $input['type'];
			$input_label = $input['label'] ?? '';
			$input_value = $input['value'];

			if ( $input_type ) {
				if ( isset( $input_types[ $input_type ] ) ) {
					$sanitize_type = 'sanitize_' . $input_types[ $input_type ];

					if ( function_exists( $sanitize_type ) ) {
						$input_value = $sanitize_type( $input_value );
					}
				}

				if ( 'textarea' === $input_type ) {
					$input_value = nl2br( $input_value );
				}

				if ( 'email' === $input_type ) {
					$email = urldecode( $input_value );
				}
			}

			$input_value = urldecode( $input_value );

			if ( isset( $input['tag'] ) ) {
				$tags[] = $input_value;
			}

			if ( isset( $input['merge_field'] ) ) {
				$merge_fields[ strtoupper( str_replace( $n, '', $name ) ) ] = $input_value;
			}
		}

		if ( ! $email ) {
			throw new \Exception( 'No email' );
		}

		$error = false;

		/* Credentials */

		$key = get_option( $n . 'mailchimp_api_key', '' );

		if ( ! $key ) {
			throw new \Exception( 'No API key' );
		}

		$data_center = explode( '-', $key )[1];

		/* Url */

		$url = "https://$data_center.api.mailchimp.com/3.0/lists/$list_id/members/";

		/* Body */

		$body = [
			'email_address' => $email,
			'status'        => 'pending',
		];

		if ( count( $tags ) > 0 ) {
			$body['tags'] = $tags;
		}

		if ( count( $merge_fields ) > 0 ) {
			$body['merge_fields'] = $merge_fields;
		}

		/* Post */

		$response = wp_safe_remote_post(
			$url,
			[
				'headers' => [
					'Content-type'  => 'application/json',
					'Authorization' => "Bearer $key",
				],
				'body'    => wp_json_encode( $body ),
			]
		);

		if ( is_wp_error( $response ) ) {
			$error = true;
		} else {
			$code = isset( $response['response']['code'] ) ? $response['response']['code'] : 500;

			if ( 200 !== $code ) {
				$error = true;
			}
		}

		if ( $error ) {
			throw new \Exception( 'Error Mailchimp API' );
		} else {
			echo wp_json_encode( ['success' => 'Successfully subscribed.'] );
		}
	}

	/**
	 * Process and send contact form.
	 *
	 * @pass string $id Required.
	 * @pass array $inputs
	 * @echo string if successfully sent
	 */

	protected static function send_contact_form( $post ) {
		$id     = $post['id'] ?? false; // Id to get array of information (email, subject...)
		$inputs = $post['inputs'] ?? false;

		if ( ! $id ) {
			throw new \Exception( 'No id' );
		}

		if ( ! $inputs ) {
			throw new \Exception( 'No inputs' );
		}

		$meta = get_option( static::$namespace . '_form_' . $id, '' );

		if ( ! $meta ) {
			throw new \Exception( 'No meta' );
		}

		$to_email = $meta['email'];

		if ( ! $to_email ) {
			throw new \Exception( 'No to email' );
		}

		$subject   = $meta['subject'];
		$site_url  = home_url();
		$site_name = get_bloginfo( 'name' );
		$output    = '';

		$input_types = [
			'text'     => 'text_field',
			'select'   => 'text_field',
			'textarea' => 'textarea_field',
			'email'    => 'email',
		];

		foreach ( $inputs as $name => $input ) {
			$input_type  = $input['type'];
			$input_label = $input['label'] ?? '';
			$input_value = $input['value'];

			if ( $input_type ) {
				if ( isset( $input_types[ $input_type ] ) ) {
					$sanitize_type = 'sanitize_' . $input_types[ $input_type ];

					if ( function_exists( $sanitize_type ) ) {
						$input_value = $sanitize_type( $input_value );
					}
				}

				if ( 'textarea' === $input_type ) {
					$input_value = nl2br( $input_value );
				}
			}

			if ( is_array( $input_value ) ) {
				$input_value = implode( '<br>', $input_value );
			}

			if ( 'subject' === $input_label && $input_value ) {
				$subject .= ' - ' . $input_value;
				continue;
			}

			/* Make email name equal to sender name */

			if ( 'name' === $input_label && $input_value ) {
				add_filter(
					'wp_mail_from_name',
					function( $name ) use ( $input_value ) {
						return $input_value;
					}
				);
			}

			if ( 'email' === $input_type ) {
				$output .= "<strong>$input_label</strong>: " . ( $input_value ? "<a href='mailto:$input_value'>$input_value</a>" : '' ) . '<br>';

				/* Make email from equal to sender email */

				if ( $input_value ) {
					add_filter(
						'wp_mail_from',
						function( $email ) use ( $input_value ) {
								return $input_value;
						}
					);
				}
			} else {
				$input_label_output = '';
				$email_label_exists = isset( $input['email_label'] );

				if ( $input_label ) {
					$input_label_output = '<strong>' . $input_label . '</strong>: ';

					if ( 'textarea' === $input_type && $input_value ) {
						$input_label_output .= '<br>';
					}
				}

				if ( $email_label_exists ) {
					$input_label_output = '<strong>' . $input['email_label'] . '</strong>:' . ( $input_value ? '<br>' : '' );
				}

				if ( $input_value ) {
					$output .= $input_label_output . $input_value . '<br>';
				} else {
					if ( $input_label_output ) {
						$output .= $input_label_output . $input_value . '<br>';
					}
				}
			}
		}

		$output .= "<br>This email was sent from a contact form on $site_name ($site_url)";

		/* Allow html */

		add_filter(
			'wp_mail_content_type',
			function() {
				return 'text/html';
			}
		);

		if ( ! $subject ) {
			$subject = "$site_name Contact Form";
		}

		/* Send email */

		$result = wp_mail( $to_email, $subject, "<p style='margin:0;font-family:sans-serif;'>$output</p>" );

		if ( ! $result ) {
			throw new \Exception( 'Error sending form' );
		} else {
			echo wp_json_encode( ['success' => 'Form successully sent.'] );
		}

		// Reset content-type to avoid conflicts
		// remove_filter( 'wp_mail_content_type', 'wpdocs_set_html_mail_content_type' );
	}

	/**
	 * Get more posts for posts and custom post types.
	 *
	 * @pass string $type
	 * @pass int $posts_per_page Required.
	 * @pass array $query_args_static
	 * @pass array $query_args
	 * @pass array $filters
	 * @echo string json containing output
	 */

	public static function get_posts( $post ) {
		try {
			$type           = $post['type'] ?? 'post';
			$posts_per_page = (int) $post['ppp'] ?? 0;

			if ( ! $posts_per_page ) {
				throw new \Exception( 'No limit' );
			}

			$args = [
				'post_type'      => $type,
				'post_status'    => 'publish',
				'posts_per_page' => $posts_per_page,
			];

			if ( isset( $post['offset'] ) ) {
				$args['offset'] = (int) $post['offset'];
			}

			if ( isset( $post['paged'] ) ) {
				$args['paged'] = (int) $post['paged'];
			}

			if ( isset( $post['query_args_static'] ) ) {
				$query_args_static = $post['query_args_static'];

				if ( $query_args_static && is_array( $query_args_static ) ) {
					$args = array_replace_recursive( $query_args_static, $args );
				}
			}

			if ( isset( $post['query_args'] ) && isset( $post['filters'] ) ) {
				$filters        = $post['filters'];
				$query_args     = $post['query_args'];
				$processed_args = [];

				foreach ( $filters as $id => $arr ) {
					if ( isset( $query_args[ $id ] ) ) {
						$add_to_query_args = true;

						/* Replace placeholders like %value with value */

						array_walk_recursive(
							$query_args[ $id ],
							function( &$v ) use ( $arr, &$add_to_query_args ) {
								$actual_v = $arr['value'];

								if ( 'null' === $actual_v ) {
									$add_to_query_args = false;
								}

								if ( '%value' === $v ) {
									$v = $actual_v;
								}

								if ( '%value:int' === $v ) {
									$v = (int) $actual_v;
								}

								if ( '%operator' === $v && isset( $arr['operator'] ) ) {
									$v = $arr['operator'];
								}
							}
						);

						if ( $add_to_query_args ) {
							$processed_args = array_merge_recursive( $query_args[ $id ], $processed_args );
						}
					}
				}

				$args = array_merge_recursive( $processed_args, $args );
			}

			$output = static::render_ajax_posts( $args );

			if ( is_string( $output ) ) {
				/* phpcs:ignore */
				echo $output;
			}

			if ( is_array( $output ) ) {
				echo wp_json_encode( $output );
			}

			exit;
		} catch ( \Exception $e ) {
			echo esc_html( $e->getMessage() );
			header( http_response_code( 500 ) );
			exit;
		}
	}

} // End Ajax
