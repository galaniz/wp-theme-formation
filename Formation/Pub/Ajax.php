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
	 * Temporarily store plain email output.
	 *
	 * @var string $temp_output_plain
	 */

	public static $temp_output_plain = '';

	/**
	 * Temporarily store reply to email
	 *
	 * @var string $temp_reply_to
	 */

	public static $temp_reply_to = '';

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
	 * PHPMailer action callback.
	 *
	 * @param object $phpmailer
	 */

	public static function phpmailer_init( $phpmailer ) {
		/* phpcs:ignore */
		$phpmailer->AltBody = self::$temp_output_plain;
		$phpmailer->addReplyTo( self::$temp_reply_to );

		self::$temp_output_plain = '';
		self::$temp_reply_to     = '';

		remove_action( 'phpmailer_init', [get_called_class(), 'phpmailer_init'] );
	}

	/**
	 * Recurse through data to output plain and html email body.
	 *
	 * @param array $array
	 * @param string $output
	 * @param string $output_plain
	 * @param integer $depth
	 */

	protected static function recurse_email_html( $array = [], &$output = '', &$output_plain = '', $depth = 1 ) {
		foreach ( $array as $label => $value ) {
			$h = $depth + 1;

			if ( 1 === $depth ) {
				$output .= (
					'<tr>' .
						'<td style="padding: 16px 0; border-bottom: 2px solid #ccc;">'
				);
			}

			if ( $label ) {
				$output .= "<h$h style='font-family: sans-serif; color: #222; margin: 16px 0; line-height: 1.3em'>$label</h$h>";

				$output_plain .= "$label\n";
			}

			if ( is_array( $value ) ) {
				self::recurse_email_html( $value, $output, $output_plain, $depth + 1 );
			} else {
				$output .= "<p style='font-family: sans-serif; color: #222; margin: 16px 0; line-height: 1.5em;'>$value</p>";

				$output_plain .= wp_strip_all_tags( $value ) . "\n";
			}

			if ( 1 === $depth ) {
				$output .= (
						'</td>' .
					'</tr>'
				);

				$output_plain .= "\n";
			}
		}
	}

	/**
	 * Create nonces before form submission.
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
	 * @param string $priv_type
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
			} else {
				unset( $inputs[ $honeypot_name ] );
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
	 * @param string $id
	 * @param array $inputs
	 * @param array $post
	 * @param boolean $silent
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

		$email_hash = md5( strtolower( $email ) );
		$url        = "https://$data_center.api.mailchimp.com/3.0/lists/$list_id/members/$email_hash";

		/* Body */

		$body = [
			'email_address' => $email,
			'status_if_new' => 'pending',
		];

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
			if ( count( $tags ) > 0 ) {
				$response = wp_safe_remote_post(
					"$url/tags",
					[
						'method'  => 'POST',
						'headers' => [
							'Content-type'  => 'application/json',
							'Authorization' => "Bearer $key",
						],
						'body'    => wp_json_encode( ['tags' => $tags] ),
					]
				);
			}

			if ( $silent ) {
				return ['mailchimp_result' => 'Successfully subscribed.'];
			}

			return ['mailchimp_success' => 'Successfully subscribed.'];
		}
	}

	/**
	 * Process and send contact form.
	 *
	 * @param string $id
	 * @param array $inputs
	 * @param array $post
	 */

	protected static function send_contact_form( $id = '', $inputs = [], $post = [] ) {
		/* Meta option required */

		$meta = get_option( static::$namespace . '_form_' . $id, '' );

		if ( ! $meta ) {
			throw new \Exception( 'No meta' );
		}

		/* Email to send to required */

		$to_email = $meta['email'];

		if ( ! $to_email ) {
			throw new \Exception( 'No to email' );
		}

		/* Email meta */

		$subject   = $meta['subject'];
		$site_url  = home_url();
		$site_name = get_bloginfo( 'name' );
		$header    = "$site_name contact form submission";
		$footer    = "This email was sent from a contact form on $site_name ($site_url)";

		/* Types for sanitization */

		$input_types = [
			'text'     => 'text_field',
			'select'   => 'text_field',
			'textarea' => 'textarea_field',
			'email'    => 'email',
		];

		/* Email output */

		$output       = [];
		$output_html  = '';
		$output_plain = '';

		foreach ( $inputs as $name => $input ) {
			$input_type  = $input['type'];
			$input_label = $input['label'] ?? '';
			$input_value = $input['value'];

			/* Sanitize */

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

			/* Array to string */

			if ( is_array( $input_value ) ) {
				$input_value = implode( '<br>', $input_value );
			}

			/* Subject */

			if ( 'subject' === $name && $input_value ) {
				$subject .= ' - ' . $input_value;
				continue;
			}

			/* Reply to email */

			if ( 'email' === $input_type && $input_value ) {
				self::$temp_reply_to = $input_value;
				$input_value         = "<a href='mailto:$input_value'>$input_value</a>";
			}

			/* Legend */

			$legend = '';

			if ( isset( $input['legend'] ) ) {
				$legend = $input['legend'];

				if ( ! isset( $output[ $input['legend'] ] ) ) {
					$output[ $input['legend'] ] = [];
				}
			}

			/* Label */

			if ( ! isset( $output[ $input_label ] ) ) {
				if ( $legend ) {
					$output[ $input['legend'] ][ $input_label ] = [];
				} else {
					$output[ $input_label ] = [];
				}
			}

			/* Output value */

			$output_value = $input_value ? $input_value : '--';

			if ( $legend ) {
				$output[ $input['legend'] ][ $input_label ][] = $output_value;
			} else {
				$output[ $input_label ][] = $output_value;
			}
		}

		self::recurse_email_html( $output, $output_html, $output_plain );

		$output_html = (
			'<table width="100%" cellpadding="0" cellspacing="0" border="0">' .
				'<tr>' .
					'<td align="center" width="100%" style="padding: 0 16px 16px 16px;">' .
						'<table align="center" cellpadding="0" cellspacing="0" border="0" style="margin-right: auto; margin-left: auto; border-spacing: 0; max-width: 37.5em;">' .
							'<tr>' .
								'<td style="padding: 32px 0 0 0;">' .
									"<h1 style='font-family: sans-serif; color: #222; margin: 0; line-height: 1.3em;'>$header</h1>" .
								'</td>' .
							'</tr>' .
							'<tr>' .
								'<td>' .
									'<table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse: collapse;">' .
										$output_html .
										'<tr>' .
											'<td style="padding: 32px 0;">' .
												"<p style='font-family: sans-serif; color: #222; margin: 0; line-height: 1.5em;'>$footer</p>" .
											'</td>' .
										'</tr>' .
									'</table>' .
								'</td>' .
							'</tr>' .
						'</table>' .
					'</td>' .
				'</tr>' .
			'</table>'
		);

		$output_plain = (
			"$header\n\n" .
			$output_plain .
			$footer
		);

		self::$temp_output_plain = $output_plain;

		/* Subjext fallback */

		if ( ! $subject ) {
			$subject = "$site_name Contact Form";
		}

		/* Headers */

		$headers = [
			'MIME-Version: 1.0',
			'Content-Type: text/html; charset=UTF-8',
		];

		/* Send email */

		add_action( 'phpmailer_init', [get_called_class(), 'phpmailer_init'] );

		$result = wp_mail(
			$to_email,
			$subject,
			$output_html,
			$headers
		);

		if ( ! $result ) {
			throw new \Exception( 'Error sending form' );
		} else {
			return ['contact_success' => 'Form successully sent.'];
		}
	}

	/**
	 * Get more posts for posts and custom post types.
	 *
	 * @param array $post
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

}
