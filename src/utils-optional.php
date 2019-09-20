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

    /*
     * Output for responsive tables.
     *
     * @param array $args
     * @return string of html output
     */

    public static function render_table( $args = [] ) {
        $args = array_merge(
            [
                'labels' => [],
                'rows' => [],
                'class' => '',
                'row_class' => '',
                'row' => false,
                'attr' => []
            ],
            $args
        );

        extract( $args );

        $class = $class ? " $class" : '';
        $row_class = $row_class ? " $row_class" : '';

        if( $attr ) {
            $attr_formatted = [];

            foreach( $attr as $a => $v )
                $attr_formatted[] = $a . '="' . $v . '"';

            $attr = ' ' . implode( ' ', $attr_formatted );
        } else {
            $attr = '';
        }

        $output = !$row ? "<table class='o-table$class'$attr>" : '';

        if( $labels && !$row ) {
            $lr = '';

            foreach( $labels as $l )
                $lr .= "<th>$l</th>";

            $output .= 
                '<thead>' .
                    "<tr class='o-table__row$row_class'>" .
                        $lr .
                    '</tr>' . 
                '</thead>';
        }

        if( $rows ) {
            $output .= !$row ? '<tbody>' : '';

            foreach( $rows as $r ) {
                $output .= "<tr class='o-table__row$row_class'>";

                foreach( $r as $i => $d ) {
                    $data_label = $labels[$i] ? ' data-label="' . $labels[$i] . '" class="o-table__label"' : '';
                    
                    $output .= "<td$data_label><div>" . $d . '</div></td>';
                }

                $output .= '</tr>';
            }

            $output .= !$row ? '</tbody>' : '';
        }

        $output .= !$row ? '</table>' : '';

        return $output;
    }

    /*
     * Output for modals.
     *
     * @param array $args
     * @return string of html output
     */

    public static function render_modal( $args = [] ) {
        $args = array_merge( [
            'class' => '',
            'scale_transition' => false,
            'alt_trigger' => '',
            'trigger_class' => '',
            'content' => '',
            'button_text' => '',
            'x' => '&#10005;'
        ], $args );

        extract( $args );

        if( !$content )
            return '';

        $class = $class ? " $class" : ""; 
        $scale_transition = $scale_transition ? ' data-scale-transition="true"' : '';
        $alt_trigger = $alt_trigger ? ' data-alt-trigger=".js-trigger"' : '';

        return 
            "<div class='o-modal$class'$scale_transition$alt_trigger>" .
                '<div class="l-flex --align-center">' .
                    "<button class='o-modal__trigger$trigger_class' type='button'>" .
                        '<span class="o-button__text">' . $button_text . '</span>' .
                    '</button>' .
                    '<div class="o-modal__window">' .
                        '<div class="o-modal__content">' .
                            '<div>' .
                                $content .
                                '<button class="o-modal__close">' .
                                    '<div class="u-visually-hidden">Close modal</div>' .
                                    "<div class='o-modal__x'>$x</div>" .
                                '</button>' .
                            '</div>' .
                        '</div>' .
                    '</div>' .
                    '<div class="o-modal__overlay"></div>' .
                '</div>' .
            '</div>';
    }

} // end Utils_Optional
