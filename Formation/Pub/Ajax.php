<?php
/**
 * Actions and callbacks for ajax requests
 *
 * @package wp-theme-formation
 */

namespace Formation\Pub;

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
			$code = $e->getCode();

			if ( ! $code ) {
				$code = 500;
			}

			http_response_code( $code );

			echo esc_html( $e->getMessage() );

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

			/* Id required */

			$id = $_POST['id'] ?? false;

			if ( ! $id ) {
				throw new \Exception( 'No id' );
			}

			/* Input required */

			$inputs = $_POST['inputs'] ?? false;

			if ( ! $inputs ) {
				throw new \Exception( 'No inputs' );
			}

			/* Honeypot */

			$honeypot_name = static::$namespace . '_asi';

			if ( ! isset( $inputs[ $honeypot_name ] ) || ! empty( $inputs[ $honeypot_name ]['value'] ) ) {
				echo wp_json_encode( ['contact_success' => 'Success.'] );

				exit;
			}

			/* Type */

			$type = $_POST['type'] ?? 'contact';

			if ( 'contact' === $type ) {
				$echo = static::send_contact_form( $id, $inputs, $_POST );

				if ( $echo ) {
					echo wp_json_encode( $echo );
				}
			} elseif ( 'mailchimp' === $type ) {
				$echo = static::mailchimp_signup( $id, $inputs, $_POST );

				if ( $echo ) {
					echo wp_json_encode( $echo );
				}
			} elseif ( 'contact-mailchimp' === $type ) {
				$echo = static::send_contact_form( $id, $inputs, $_POST );

				/* Consent */

				$consent_name  = $_POST['mailchimp_consent_name'] ?? false;
				$consent_value = false;

				if ( $consent_name ) {
					$consent_value = $inputs[ $consent_name ]['value'] ?? false;
				}

				if ( $consent_value ) {
					$total_echo = [];

					if ( $echo ) {
						$total_echo = $echo;
					}

					$mc_echo = static::mailchimp_signup( $id, $inputs, $_POST, true );

					if ( $mc_echo ) {
						$total_echo['mailchimp_result'] = $mc_echo['mailchimp_result'];
					}

					echo wp_json_encode( $total_echo );
				} else {
					if ( $echo ) {
						echo wp_json_encode( $echo );
					}
				}
			}

			/* phpcs:ignore */
			/* elseif ( 'comment' === $type ) { static::send_comment( $priv_type ); } */

			exit;
		} catch ( \Exception $e ) {
			$code = $e->getCode();

			if ( ! $code ) {
				$code = 500;
			}

			http_response_code( $code );

			echo esc_html( $e->getMessage() );

			exit;
		}
	}

	/**
	 * Process mailchimp signup form.
	 *
	 * @var string $id
	 * @var array $inputs
	 * @var array $post
	 * @var boolean $silent
	 * @echo string if successful
	 */

	protected static function mailchimp_signup( $id = '', $inputs = [], $post = [], $silent = false ) {
		$meta = get_option( static::$namespace . '_form_' . $id, '' );

		if ( ! $meta ) {
			if ( $silent ) {
				exit;
			}

			throw new \Exception( 'No meta' );
		}

		$list_id = $meta['mailchimp_list'] ?? false;

		if ( ! $list_id ) {
			if ( $silent ) {
				exit;
			}

			throw new \Exception( 'No List ID' );
		}

		/* Inputs */

		$email        = '';
		$tags         = [];
		$merge_fields = [];

		$n = static::$namespace . '_';

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
					$email = rawurldecode( $input_value );
				}
			}

			$input_value = rawurldecode( $input_value );

			if ( isset( $input['tag'] ) ) {
				$tags[] = [
					'name'   => (string) $input_value,
					'status' => 'active',
				];
			}

			if ( isset( $input['merge_field'] ) ) {
				$merge_fields[ $input['merge_field'] ] = $input_value;
			}
		}

		if ( ! $email ) {
			if ( $silent ) {
				exit;
			}

			throw new \Exception( 'No email' );
		}

		$error = false;

		/* Credentials */

		$key = get_option( $n . 'mailchimp_api_key', '' );

		if ( ! $key ) {
			if ( $silent ) {
				exit;
			}

			throw new \Exception( 'No API key' );
		}

		/* Data center */

		$data_center_array = explode( '-', $key );
		$data_center       = isset( $data_center_array[1] ) ? $data_center_array[1] : '';

		if ( ! $data_center ) {
			if ( $silent ) {
				exit;
			}

			throw new \Exception( 'No data center' );
		}

		/* Url */

		$url = "https://$data_center.api.mailchimp.com/3.0/lists/$list_id/members/$email";

		/* Body */

		$body = [
			'email_address' => $email,
			'status_if_new' => 'pending',
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
				'method'  => 'PUT',
				'headers' => [
					'Content-type'  => 'application/json',
					'Authorization' => "Bearer $key",
				],
				'body'    => wp_json_encode( $body ),
			]
		);

		$code = isset( $response['response']['code'] ) ? $response['response']['code'] : 500;

		/* Error check */

		if ( is_wp_error( $response ) ) {
			$error = true;
		} else {
			if ( 200 !== $code ) {
				$error = true;
			}
		}

		if ( $error ) {
			$response_body = isset( $response['body'] ) ? json_decode( $response['body'] ) : '';
			$error_message = 'Error Mailchimp API';
			$error_detail  = '';

			if ( $response_body ) {
				$error_message = isset( $response_body->title ) ? 'Mailchimp ' . $response_body->title : '';
				$error_detail  = isset( $response_body->detail ) ? $response_body->detail : '';

				if ( $error_detail ) {
					$error_message .= ": $error_detail";
				}
			}

			if ( $silent ) {
				return ['mailchimp_result' => $error_message];
			}

			throw new \Exception( $error_message, $code );
		} else {
			if ( $silent ) {
				return ['mailchimp_result' => 'Successfully subscribed.'];
			}

			return ['mailchimp_success' => 'Successfully subscribed.'];
		}
	}

	/**
	 * Process and send contact form.
	 *
	 * @var string $id
	 * @var array $inputs
	 * @var array $post
	 * @echo string if successful
	 */

	protected static function send_contact_form( $id = '', $inputs = [], $post = [] ) {
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
				$legend_exists      = isset( $input['legend'] );

				if ( $input_label ) {
					$input_label_output = '<strong>' . $input_label . '</strong>: ';

					if ( 'textarea' === $input_type && $input_value ) {
						$input_label_output .= '<br>';
					}
				}

				if ( $legend_exists ) {
					$input_label_output = '<strong>' . $input['legend'] . '</strong>:' . ( $input_value ? '<br>' : '' ) . $input_label_output;
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
			return ['contact_success' => 'Form successully sent.'];
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

	public static function get_posts( $post = [] ) {
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
			$code = $e->getCode();

			if ( ! $code ) {
				$code = 500;
			}

			http_response_code( $code );

			echo esc_html( $e->getMessage() );

			exit;
		}
	}

} // End Ajax
