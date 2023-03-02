<?php
/**
 * Global functions and variables
 *
 * @package wp-theme-formation
 */

namespace Formation;

/**
 * Pass data to front end not passed with localize script.
 *
 * @param array $args {
 *  @type string $name Required.
 *  @type array $data Required.
 *  @type boolean $admin
 *  @type boolean $head
 *  @type boolean $action
 * }
 * @return void|string Script output.
 */

function additional_script_data( $args = [] ) {
	$args = array_merge(
		[
			'name'   => '',
			'data'   => [],
			'admin'  => false,
			'head'   => false,
			'action' => true,
		],
		$args
	);

	/* Destructure */

	[
		'name'   => $name,
		'data'   => $data,
		'admin'  => $admin,
		'head'   => $head,
		'action' => $action,
	] = $args;

	/* Name and data required */

	if ( ! $name || ! $data ) {
		return;
	}

	/* Script output */

	$name = esc_html( $name );
	$data = wp_json_encode( $data );
	$var  = 'data_' . uniqid();

	$output = (
		'<script type="text/javascript" defer>' .
			'(function () {' .
				'function v(obj, k) {' .
					'return obj[k];' .
				'}' .
				"var $var = $data;" .
				"if (Object.getOwnPropertyDescriptor(window, '$name')) {" .
					"Object.keys($var).forEach(function(key) {" .
						"window['$name'][key] = v($var, key);" .
					'});' .
				'} else {' .
					"window['$name'] = $data;" .
				'}' .
			'})();' .
		'</script>'
	);

	if ( ! $action ) {
		return $output;
	}

	/* Action */

	$hook_name = $admin ? 'admin_print_footer_scripts' : 'wp_print_footer_scripts';

	if ( $head ) {
		$hook_name = $admin ? 'admin_head' : 'wp_head';
	}

	add_action(
		$hook_name,
		function() use ( $output ) {
			echo $output; // phpcs:ignore
		}
	);
}

/**
 * Write to debug log.
 *
 * @param array|object|string $log
 * @return void
 */

function write_to_log( $log = '' ) {
	/* phpcs:disable */
	if ( is_array( $log ) || is_object( $log ) ) {
		error_log( print_r( $log, true ) );
	} else {
		error_log( $log );
	}
	/* phpcs:enable */
}
