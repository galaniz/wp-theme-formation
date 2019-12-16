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
     * @return boolean|array
     */

    public static function get_mailchimp_list( $list_name = '' ) {
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
     * Format table data into labels and rows.
     *
     * @param string $label_key
     * @param string $rows_key
     * @param array $data
     * @return array
     */

    public static function format_table_data( $data = [], $label_key = 'label', $rows_key = 'data' ) {
        if( !$data )
            return [];

        $labels = [];
        $rows = [];

        foreach( $data as $i => $d ) {
            if( isset( $d['label'] ) )
                $labels[] = $d['label'];

            $dd = explode( "\n", $d['data'] );

            if( $i === 0 ) {
                foreach( $dd as $ddd ) {
                    $rows[] = [$ddd];
                }
            } else {
                foreach( $dd as $ii => $ddd )
                    $rows[$ii][] = $ddd;
            }
        }

        return [
            'labels' => $labels,
            'rows' => $rows
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

        if( !$rows )
            return '';

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
            'alt_trigger' => false,
            'trigger_class' => '',
            'content' => '',
            'button_text' => '',
            'x' => '&#10005;'
        ], $args );

        extract( $args );

        if( !$content )
            return '';

        $class = $class ? " $class" : "";
        $trigger_class = $trigger_class ? " $trigger_class" : "";
        $scale_transition = $scale_transition ? ' data-scale-transition="true"' : '';
        $alt_trigger = $alt_trigger ? ' data-alt-trigger=".js-trigger"' : '';

        return
            "<div class='o-modal$class'$scale_transition$alt_trigger>" .
                "<button class='o-modal__trigger$trigger_class' type='button'>" .
                    '<span class="o-button__text">' . $button_text . '</span>' .
                '</button>' .
                '<div class="o-modal__overlay"></div>' .
                '<div class="o-modal__window">' .
                    '<div class="o-modal__content">' .
                        ( $scale_transition ? '<div>' : '' ) .
                            $content .
                            '<button class="o-modal__close">' .
                                '<div class="u-visually-hidden">Close modal</div>' .
                                "<div class='o-modal__x'>$x</div>" .
                            '</button>' .
                        ( $scale_transition ? '</div>' : '' ) .
                    '</div>' .
                '</div>' .
            '</div>';
    }

    /*
     * Output for search form.
     *
     * @param array $args
     * @return string of html output
     */

    public static function render_search_form( $args = [] ) {
        $s = FRM::$sprites['Search'];

        $args = array_replace_recursive( [
            'field_class' => '',
            'input_class' => '',
            'button_class' => '',
            'icon' => [
                'class' => '',
                'w' => $s['w'],
                'h' => $s['h'],
                'id' => 'sprite-' . $s['id']
            ]
        ], $args );

        extract( $args );

        $field_class = 'o-field' . ( $field_class ? " $field_class" : '' );
        $input_class = 'o-field__input' . ( $input_class ? " $input_class" : '' );

        $unique_id = 'search-' . uniqid();

        return
            '<form role="search" method="get" class="o-field-container" action="' . esc_url( home_url( '/' ) ) . '">' .
                "<div class='$field_class'>" .
                    '<div class="u-position-relative">' .
                        "<label class='u-visually-hidden' for='$unique_id'>Search for:</label>" .
                        "<input type='search' id='$unique_id' class='$input_class' placeholder='Search' value='" . get_search_query() . "' name='s' />" .
                        "<button type='submit' class='$button_class'>" .
                            '<span class="u-visually-hidden">Submit search query</span>' .
                            "<svg class='" . $icon['class'] . "' width='" . $icon['w'] . "' height='" . $icon['h'] . "' viewBox='0 0 " . $icon['w'] . " " . $icon['h'] . "'>" .
                                "<use xlink:href='#" . $icon['id'] . "' />" .
                            '</svg>' .
                        '</button>' .
                    '</div>' .
                '</div>' .
            '</form>';
    }

    /*
     * Output for canvas workaround to scale svg in older browsers.
     *
     * @param string $svg_str
     * @return string of html output
     */

    public static function render_svg_scale_fix( $svg_str = '' ) {
        if( !$svg_str )
            return '';

        $view_box = explode( 'viewBox="', $svg_str );

        if( !isset( $view_box[1] ) )
            return '';

        $view_box = explode( '"', $view_box[1] )[0];
        $view_box = explode( ' ', $view_box );

        $w = (int) $view_box[2];
        $h = (int) $view_box[3];

        return "<canvas class='u-svg-scale-fix' width='$w' height='$h'></canvas>$svg_str";
    }

} // end Utils_Optional
