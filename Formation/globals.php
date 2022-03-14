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
 * @param string/boolean $name Required.
 * @param array $data Required.
 * @param boolean $admin
 */

function additional_script_data( $name = false, $data = [], $admin = false, $head = false ) {
		$action = $admin ? 'admin_print_footer_scripts' : 'wp_print_footer_scripts';

		if ( $head ) {
				$action = $admin ? 'admin_head' : 'wp_head';
		}

		add_action(
				$action,
				function() use ( $name, $data ) {
						if ( ! $name || ! $data ) {
								return;
						}

						$name = esc_html( $name );
						$var  = 'data_' . uniqid(); ?>

						<script type="text/javascript">
						(function () {
							<?php /* phpcs:disable */ ?>
							var <?php echo $var; ?> = <?php echo wp_json_encode( $data ); ?>;

							if( window.hasOwnProperty( '<?php echo $name; ?>' ) ) {
								/* Merge existing object with new data */

								for( var key in <?php echo $var; ?> ) {
									window['<?php echo $name; ?>'][key] = <?php echo $var; ?>[key];
								}
							} else {
								window['<?php echo $name; ?>'] = <?php echo $var; ?>;
							}
							<?php /* phpcs:enable */ ?>
						})();
						</script>
						<?php
				}
		);
}

/**
 * Write to debug log.
 *
 * @param array/object/string $log
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
