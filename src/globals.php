<?php

/*
 * Global functions and variables
 * ------------------------------
 */

namespace Foundation;

/*
 * Pass data to front end not passed with localize script.
 *
 * @param string/boolean $name Required.
 * @param array $data Required.
 * @param boolean $admin
 */

function additional_script_data( $name = false, $data = [], $admin = false ) {
	$action = $admin ? 'admin_print_footer_scripts' : 'wp_print_footer_scripts';

	add_action( $action, 
		function() use ( $name, $data ) { 
			if( !$name || !$data ) return; ?>

			<script type="text/javascript">
	            var additionalData = <?php echo json_encode( $data ); ?>;
	        	
	            if( window.<?php echo $name; ?> ) {
	        		// merge existing object with new data
	                for( var key in additionalData ) {
	                    if( additionalData.hasOwnProperty( key ) ) 
	                        <?php echo $name; ?>[key] = additionalData[key];
	                }
	        	} else {
	        		<?php echo $name; ?> = additionalData;
	        	}
	        </script>

		<?php }
    );
}

/*
 * Write to debug log.
 *
 * @param array/object/string $log 
 */

function write_log( $log )  {
    if( is_array( $log ) || is_object( $log ) ) {
        error_log( print_r( $log, true ) );
    } else {
        error_log( $log );
    }
}
