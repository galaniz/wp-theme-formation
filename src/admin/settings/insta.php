<?php

/*
 * Process instagram data
 * ----------------------
 */

namespace Formation\Admin\Settings;


/*
 * Imports
 * -------
 */

use Formation\Formation as FRM; 

function set_error_option( $error = true ) {
	$opt_name = FRM::$namespace . '_insta_error';

	if( $error ) {
		update_option( $opt_name, 1 );
	} else {
		delete_option( $opt_name );
	}
}

try {
	$status = http_response_code();

	if( $status == 200 ) {
		$access_token = $_POST['access_token'] ?? '';
		$user_id = $_POST['user_id'] ?? '';

		if( !$access_token || !$user_id ) {
			set_error_option();
		} else {
			$n = FRM::$namespace;

			update_option( $n . '_insta_access_token', $access_token );
			update_option( $n . '_insta_user_id', $user_id );
			
			set_error_option( false );
		}
	} else {
		set_error_option();
	}
} catch( \Exception $e ) { 
	set_error_option();
}
