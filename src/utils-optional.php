<?php

/*
 * Utility methods
 * ---------------
 */

namespace Formation;

/*
 * Imports
 * -------
 */

use Formation\Formation as FRM; 

class Utils_Optional {

   /*
     * Get mailchimp list. 
     *
     * @param string $list_name
     * @return string boolean/array
     */

    public static function get_mailchimp_list( $list_name ) {
        // check for api
        if( !get_option( FRM::$namespace . '_mailchimp_api_key' ) )
            return false;

        // check for list
        $list_name = FRM::$namespace . "_$list_name";
        $list_id = get_option( $list_name . '_id' );

        if( !$list_id )
            return false;

        return [
            'id' => $list_id,
            'title' => get_option( $list_name . '_title' ),
            'submit_label' => get_option( $list_name . '_submit_label' ),
            'fields' => get_option( $list_name . '_fields' )
        ];
    }

} // end Utils_Optional
