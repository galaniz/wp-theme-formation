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
 * @param string $name Required.
 * @param array $data Required.
 * @param boolean $admin
 * @param boolean $head
 * @param boolean $action
 * @return void|string Script output.
 */

function additional_script_data( $name, $data = [], $admin = false, $head = false, $action = true ) {
	if ( ! $name || ! $data ) {
		return;
	}

	$name = esc_html( $name );
	$data = wp_json_encode( $data );
	$var  = 'data_' . uniqid();

	$output = (
		'<script type="text/javascript">' .
			'(function () {' .
				'function v(obj, k) {' .
					'return obj[k];' .
				'}' .
				"var $var = $data;" .
				"if (window.hasOwnProperty('$name')) {" .
					"Object.keys($var).forEach(function(key) {" .
						"window['$name'][key] = v($var, key);" .
					'});' .
				'} else {' .
					"window['$name'] = $var;" .
				'}' .
			'})();' .
		'</script>'
	);

	if ( ! $action ) {
		return $output;
	}

	$hook_name = $admin ? 'admin_print_footer_scripts' : 'wp_print_footer_scripts';

	if ( $head ) {
		$hook_name = $admin ? 'print_head_scripts' : 'wp_print_head_scripts';
	}

	add_action(
		$hook_name,
		function() use ( $name, $data ) {
			echo $output; // phpcs:ignore
		}
	);
}

/**
 * Write to debug log.
 *
 * @param array|object|string $log
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
