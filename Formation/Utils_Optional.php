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
use Formation\Utils;

class Utils_Optional {

 /*
  * Get mailchimp list by location
  *
  * @param string $location
  * @return boolean|array
  */

  public static function get_mailchimp_list( $location = '' ) {
    // check for api
    if( !get_option( FRM::$namespace . '_mailchimp_api_key', '' ) )
      return false;

    // check for list
    $name = FRM::$namespace . "_$location";
    $list_id = get_option( $name . '_list_id', false );

    if( !$list_id )
      return false;

    return [
      'id' => $list_id,
      'title' => get_option( $name . '_title', 'Sign up' ),
      'text' => get_option( $name . '_text', '' ),
      'submit_label' => get_option( $name . '_submit_label', 'Submit' ),
      'success_message' => get_option( $name . '_success_message', '' ),
      'fields' => get_option( $name . '_fields', false )
    ];
  }

 /*
  * Fetch logo.
  *
  * @return string
  */

  public static function get_logo( $class = '', $old_browser_compat = false ) {
    $n = FRM::$namespace;

    $svg_key = $n . '_svg_logo';
    $key = $n . '_logo';

    $svg_url = get_option( $svg_key, '' );

    if( $svg_url ) {
      $site_url = get_site_url() . '/';
      $svg_path = str_replace( $site_url, ABSPATH, $svg_url );

      if( $svg_path == $svg_url ) {
        $site_url = get_site_url( '', 'http' ) . '/';
        $svg_path = str_replace( $site_url, ABSPATH, $svg_url );
      }

      $s = file_get_contents( $svg_path );

      if( $old_browser_compat )
        $s = self::render_svg_scale_fix( $s );

      return $s;
    } else {
      $id = get_option( $key, 0 );

      if( !$id )
        return '';

      $image = Utils::get_image( $id, 'large' );

      if( !$image )
        return '';

      $src = $image['url'];
      $alt = $image['alt'];
      $srcset = $image['srcset'];
      $sizes = $image['sizes'];
      $class = $class ? " class='$class'" : '';

      return "<img$class src='$src' alt='$alt' srcset='$srcset' sizes='$sizes'>";
    }

    return '';
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
          '<div>' . $button_text . '</div>' .
        '</button>' .
        '<div class="o-modal__overlay"></div>' .
        '<div class="o-modal__window">' .
          '<div class="o-modal__content">' .
            ( $scale_transition ? '<div>' : '' ) .
              $content .
              '<button class="o-modal__close">' .
                '<div class="u-v-h">Close modal</div>' .
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
    $args = array_replace_recursive( [
      'form_class' => '',
      'field_class' => '',
      'input_class' => '',
      'button_class' => '',
      'icon_class' => ''
    ], $args );

    extract( $args );

    $unique_id = 'search-' . uniqid();

    $field_class = $field_class ? " $field_class" : '';
    $input_class = $input_class ? " $input_class" : '';
    $form_class = $form_class ? " class='$form_class'" : ''; 
    $button_class = $button_class ? " class='$button_class'" : ''; 
    $icon_class = $icon_class ? " class='$icon_class'" : ''; 

    return
      "<form$form_class role='search' method='get' action='" . esc_url( home_url( '/' ) ) . "'>" .
        "<div class='o-field$field_class'>" .
          '<div class="u-p-r">' .
            "<label class='u-v-h' for='$unique_id'>Search for:</label>" .
            "<input class='o-field__input$input_class' type='search' id='$unique_id' placeholder='Search' value='" . get_search_query() . "' name='s' />" .
            "<button$button_class type='submit'>" .
              '<span class="u-v-h">Submit search query</span>' .
              "<div$icon_class></div>" .
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

    return "<canvas class='u-svg-sc' width='$w' height='$h'></canvas>$svg_str";
  }

 /*
  * Output associative location array as string.
  *
  * @param array $location
  * @return string
  */

  public static function format_location( $location = [], $line_break = false, $include_admin1 = true ) {
    if( !$location )
      return '';

    return
      $location['line1'] .
      ( isset( $location['line2'] ) ? ' ' . $location['line2'] : '' ) . ( $line_break ? '<br>' : ' ' ) .
      $location['city'] . ', ' . $location['admin3'] . ( $include_admin1 ? ', ' . $location['admin1_name'] : '' ) .
      ( isset( $location['postal_code'] ) ? ' ' . $location['postal_code'] : '' ); 
  }

 /*
  * Output associative hours array as array of string.
  *
  * @param array $hours
  * @return array
  */

  public static function format_hours( $hours = [], $days_sep = '–', $hours_sep = '–' ) {
    if( !$hours )
      return '';

    $h = [
      'mon' => [
        'open' => [$hours['mon_open_hour'], $hours['mon_open_min']],
        'close' => [$hours['mon_close_hour'], $hours['mon_close_min']],
        'closed' => isset( $hours['mon_closed'] ) ? true : false,
        'full' => 'Monday'
      ],
      'tue' => [
        'open' => [$hours['tue_open_hour'], $hours['tue_open_min']],
        'close' => [$hours['tue_close_hour'], $hours['tue_close_min']],
        'closed' => isset( $hours['tue_closed'] ) ? true : false,
        'full' => 'Tuesday'
      ],
      'wed' => [
        'open' => [$hours['wed_open_hour'], $hours['wed_open_min']],
        'close' => [$hours['wed_close_hour'], $hours['wed_close_min']],
        'closed' => isset( $hours['wed_closed'] ) ? true : false,
        'full' => 'Wednesday'
      ],
      'thu' => [
        'open' => [$hours['thu_open_hour'], $hours['thu_open_min']],
        'close' => [$hours['thu_close_hour'], $hours['thu_close_min']],
        'closed' => isset( $hours['thu_closed'] ) ? true : false,
        'full' => 'Thursday'
      ],
      'fri' => [
        'open' => [$hours['fri_open_hour'], $hours['fri_open_min']],
        'close' => [$hours['fri_close_hour'], $hours['fri_close_min']],
        'closed' => isset( $hours['fri_closed'] ) ? true : false,
        'full' => 'Friday'
      ],
      'sat' => [
        'open' => [$hours['sat_open_hour'], $hours['sat_open_min']],
        'close' => [$hours['sat_close_hour'], $hours['sat_close_min']],
        'closed' => isset( $hours['sat_closed'] ) ? true : false,
        'full' => 'Saturday'
      ],
      'sun' => [
        'open' => [$hours['sun_open_hour'], $hours['sun_open_min']],
        'close' => [$hours['sun_close_hour'], $hours['sun_close_min']],
        'closed' => isset( $hours['sun_closed'] ) ? true : false,
        'full' => 'Sunday'
      ]
    ];

    $h_org = [];

    foreach( $h as $k => $v ) {
      $open_close = $v['open'][0] . ':' . $v['open'][1] . $hours_sep . $v['close'][0] . ':' . $v['close'][1];
      $closed = $v['closed'] ? 'closed' : '';
      $key = $closed ? $closed : $open_close;

      if( !array_key_exists( $key, $h_org ) )
        $h_org[$key] = [];

      $h_org[$key][] = $h[$k]['full'];
    } 

    $output = [];

    foreach( $h_org as $k => $v ) {
      $days = count( $v ) == 1 ? $v[0] : implode( $days_sep, [$v[0], $v[count( $v ) - 1]] );
      $hours = 'Closed';

      if( $k !== 'closed' ) {
        $open_close = explode( $hours_sep, $k );

        $open = explode( ':', $open_close[0] );
        $open = self::format_time( $open[0], $open[1] );

        $close = explode( ':', $open_close[1] );
        $close = self::format_time( $close[0], $close[1] );

        $hours = $open . $hours_sep . $close;
      }

      $output[] = [
        'days' => $days,
        'hours' => $hours
      ];
    }

    return $output;
  }

 /*
  * Output hour and min in 12 hours time.
  *
  * @param string $hours
  * @param string $min
  * @return string
  */

  public static function format_time( $hour, $min ) {
    $hour = ( int ) $hour;
    $am = 'am';

    if( $hour > 12 ) {
      $am = 'pm';
      $hour -= 12;
    } 

    return "$hour:$min$am";
  }

} // end Utils_Optional
