<?php
/**
 * Additional utility methods
 *
 * @package wp-theme-formation
 */

namespace Formation;

/**
 * Imports
 */

use Formation\Formation as FRM;
use Formation\Utils;

/**
 * Class
 */

class Utils_Optional {

	/**
	 * Get media position class.
	 *
	 * @param string $pos
	 * @return string
	 */

	public static function get_media_pos_class( $pos = '', $id = 0 ) {
		if ( ! $pos ) {
			if ( $id ) {
				$pos = get_post_meta( $id, FRM::get_namespaced_str( 'media_pos' ), true );
			} else {
				return '';
			}
		}

		if ( ! $pos ) {
			return '';
		}

		return FRM::$media_pos_class_pre . $pos;
	}

	/**
	 * Format table data into labels and rows.
	 *
	 * @param string $label_key
	 * @param string $rows_key
	 * @param array $data
	 * @return array
	 */

	public static function format_table_data( $data = [], $label_key = 'label', $rows_key = 'data' ) {
		if ( ! $data ) {
			return [];
		}

		$labels = [];
		$rows   = [];

		foreach ( $data as $i => $d ) {
			if ( isset( $d['label'] ) ) {
				$labels[] = $d['label'];
			}

			$dd = explode( "\n", $d['data'] );

			if ( 0 === $i ) {
				foreach ( $dd as $ddd ) {
					$rows[] = [$ddd];
				}
			} else {
				foreach ( $dd as $ii => $ddd ) {
					$rows[ $ii ][] = $ddd;
				}
			}
		}

		return [
			'labels' => $labels,
			'rows'   => $rows,
		];
	}

	/**
	 * Format associative location array as string.
	 *
	 * @param array $location
	 * @return string
	 */

	public static function format_location( $location = [], $line_break = false, $include_admin1 = true ) {
		if ( ! $location ) {
			return '';
		}

		return (
			$location['line1'] .
			( isset( $location['line2'] ) ? ' ' . $location['line2'] : '' ) . ( $line_break ? '<br>' : ' ' ) .
			$location['city'] . ', ' . $location['admin3'] . ( $include_admin1 ? ', ' . $location['admin1_name'] : '' ) .
			( isset( $location['postal_code'] ) ? ' ' . $location['postal_code'] : '' )
		);
	}

	/**
	 * Format associative hours array as array of strings.
	 *
	 * @param array $hours
	 * @return array
	 */

	public static function format_hours( $hours = [], $days_sep = '–', $hours_sep = '–' ) {
		if ( ! $hours ) {
			return '';
		}

		$h = [
			'mon' => [
				'open'   => [$hours['mon_open_hour'], $hours['mon_open_min']],
				'close'  => [$hours['mon_close_hour'], $hours['mon_close_min']],
				'closed' => isset( $hours['mon_closed'] ) ? true : false,
				'full'   => 'Monday',
			],
			'tue' => [
				'open'   => [$hours['tue_open_hour'], $hours['tue_open_min']],
				'close'  => [$hours['tue_close_hour'], $hours['tue_close_min']],
				'closed' => isset( $hours['tue_closed'] ) ? true : false,
				'full'   => 'Tuesday',
			],
			'wed' => [
				'open'   => [$hours['wed_open_hour'], $hours['wed_open_min']],
				'close'  => [$hours['wed_close_hour'], $hours['wed_close_min']],
				'closed' => isset( $hours['wed_closed'] ) ? true : false,
				'full'   => 'Wednesday',
			],
			'thu' => [
				'open'   => [$hours['thu_open_hour'], $hours['thu_open_min']],
				'close'  => [$hours['thu_close_hour'], $hours['thu_close_min']],
				'closed' => isset( $hours['thu_closed'] ) ? true : false,
				'full'   => 'Thursday',
			],
			'fri' => [
				'open'   => [$hours['fri_open_hour'], $hours['fri_open_min']],
				'close'  => [$hours['fri_close_hour'], $hours['fri_close_min']],
				'closed' => isset( $hours['fri_closed'] ) ? true : false,
				'full'   => 'Friday',
			],
			'sat' => [
				'open'   => [$hours['sat_open_hour'], $hours['sat_open_min']],
				'close'  => [$hours['sat_close_hour'], $hours['sat_close_min']],
				'closed' => isset( $hours['sat_closed'] ) ? true : false,
				'full'   => 'Saturday',
			],
			'sun' => [
				'open'   => [$hours['sun_open_hour'], $hours['sun_open_min']],
				'close'  => [$hours['sun_close_hour'], $hours['sun_close_min']],
				'closed' => isset( $hours['sun_closed'] ) ? true : false,
				'full'   => 'Sunday',
			],
		];

		$h_org = [];

		foreach ( $h as $k => $v ) {
			$open_close = $v['open'][0] . ':' . $v['open'][1] . $hours_sep . $v['close'][0] . ':' . $v['close'][1];
			$closed     = $v['closed'] ? 'closed' : '';
			$key        = $closed ? $closed : $open_close;

			if ( ! array_key_exists( $key, $h_org ) ) {
				$h_org[ $key ] = [];
			}

			$h_org[ $key ][] = $h[ $k ]['full'];
		}

		$output = [];

		foreach ( $h_org as $k => $v ) {
			$days  = count( $v ) === 1 ? $v[0] : implode( $days_sep, [$v[0], $v[ count( $v ) - 1 ]] );
			$hours = 'Closed';

			if ( 'closed' !== $k ) {
				$open_close = explode( $hours_sep, $k );

				$open = explode( ':', $open_close[0] );
				$open = self::format_time( $open[0], $open[1] );

				$close = explode( ':', $open_close[1] );
				$close = self::format_time( $close[0], $close[1] );

				$hours = $open . $hours_sep . $close;
			}

			$output[] = [
				'days'  => $days,
				'hours' => $hours,
			];
		}

		return $output;
	}

	/**
	 * Format hour and min in 12 hour time.
	 *
	 * @param string $hours
	 * @param string $min
	 * @return string
	 */

	public static function format_time( $hour, $min ) {
		$hour = (int) $hour;
		$am   = 'am';

		if ( $hour > 12 ) {
			$am    = 'pm';
			$hour -= 12;
		}

		return "$hour:$min$am";
	}

	/**
	 * Output logo.
	 *
	 * @param string $class
	 * @param boolean $old_browser_compat
	 * @return string html
	 */

	public static function render_logo( $class = '', $old_browser_compat = false ) {
		$n      = FRM::$namespace;
		$svg    = get_option( $n . '_svg_logo_meta', false );
		$img    = $n . '_logo';
		$output = '';

		if ( $svg ) {
			$meta   = explode( '|', $svg );
			$width  = (int) $meta[0];
			$height = (int) $meta[1];
			$path   = $meta[2];

			if ( $old_browser_compat && $width && $height ) {
				$output .= "<div style='padding-top:" . ( ( $height / $width ) * 100 ) . "%'></div>";
			}

			/* phpcs:ignore */
			$output .= file_get_contents( $path ); // Ignore: local path
		} else {
			$id = get_option( $img, 0 );

			if ( ! $id ) {
				return '';
			}

			$image = Utils::get_image( $id, 'large' );

			if ( ! $image ) {
				return '';
			}

			$src    = esc_url( $image['url'] );
			$alt    = esc_attr( $image['alt'] );
			$srcset = esc_attr( $image['srcset'] );
			$sizes  = esc_attr( $image['sizes'] );
			$class  = esc_attr( $class );

			$output = "<img class='$class' src='$src' alt='$alt' srcset='$srcset' sizes='$sizes'>";
		}

		return $output;
	}

	/**
	 * Output for responsive tables.
	 *
	 * @param array $args
	 * @return string html
	 */

	public static function render_table( $args = [] ) {
		$args = array_merge(
			[
				'labels'      => [],
				'label_class' => '',
				'rows'        => [],
				'row_class'   => '',
				'row'         => false,
				'class'       => '',
				'attr'        => [],
			],
			$args
		);

		/* Destructure */

		[
			'labels'      => $labels,
			'label_class' => $label_class,
			'rows'        => $rows,
			'row_class'   => $row_class,
			'row'         => $row,
			'class'       => $class,
			'attr'        => $attr,
		] = $args;

		/* Required */

		if ( ! $rows ) {
			return;
		}

		/* Escape */

		$class       = esc_attr( $class );
		$row_class   = esc_attr( $row_class );
		$label_class = esc_attr( $label_class );

		if ( $class ) {
			$class = " class='$class'";
		}

		if ( $row_class ) {
			$row_class = " class='$row_class'";
		}

		if ( $label_class ) {
			$label_class = " class='$label_class'";
		}

		/* Attributes */

		$attr = Utils::get_attr_as_str( $attr );

		if ( $attr ) {
			$attr = " $attr";
		}

		/* Output */

		$output = ! $row ? "<table role='table'$class$attr>" : '';

		if ( $labels && ! $row ) {
			$lr = '';

			foreach ( $labels as $l ) {
				$lr .= "<th role='columnheader'>" . esc_html( $l ) . '</th>';
			}

			$output .=
				"<thead role='rowgroup'>" .
					"<tr role='row'$row_class>" .
						$lr .
					'</tr>' .
				'</thead>';
		}

		if ( $rows ) {
			$output .= ! $row ? "<tbody role='rowgroup'>" : '';

			foreach ( $rows as $r ) {
				$output .= "<tr role='row'$row_class>";

				foreach ( $r as $i => $d ) {
					$data_label = $labels[ $i ] ? ' data-label="' . esc_attr( $labels[ $i ] ) . '"' . $label_class : '';
					$output    .= "<td role='cell'$data_label><div>" . esc_html( $d ) . '</div></td>';
				}

				$output .= '</tr>';
			}

			$output .= ! $row ? '</tbody>' : '';
		}

		$output .= ! $row ? '</table>' : '';

		return $output;
	}

	/**
	 * Output for modal.
	 *
	 * @param array $args
	 * @return string html
	 */

	public static function render_modal( $args = [] ) {
		$args = array_merge(
			[
				'class'         => '',
				'attr'          => [],
				'trigger'       => '',
				'dialog_class'  => '',
				'dialog_attr'   => [],
				'overlay_class' => '',
				'content'       => [],
				'content_class' => '',
				'close_class'   => '',
				'icon_path'     => '',
				'a11y_class'    => '',
			],
			$args
		);

		/* Destructure */

		[
			'class'         => $class,
			'attr'          => $attr,
			'trigger'       => $trigger,
			'dialog_class'  => $dialog_class,
			'dialog_attr'   => $dialog_attr,
			'overlay_class' => $overlay_class,
			'content'       => $content,
			'content_class' => $content_class,
			'close_class'   => $close_class,
			'icon_path'     => $icon_path,
			'a11y_class'    => $a11y_class,
		] = $args;

		/* Escape */

		$class         = esc_attr( $class );
		$dialog_class  = esc_attr( $dialog_class );
		$overlay_class = esc_attr( $overlay_class );
		$content_class = esc_attr( $content_class );
		$close_class   = esc_attr( $close_class );
		$a11y_class    = esc_attr( $a11y_class );

		if ( $class ) {
			$class = " class='$class'";
		}

		if ( $dialog_class ) {
			$dialog_class = " class='$dialog_class'";
		}

		if ( $overlay_class ) {
			$overlay_class = " class='$overlay_class'";
		}

		if ( $content_class ) {
			$content_class = " class='$content_class'";
		}

		if ( $close_class ) {
			$close_class = " class='$close_class'";
		}

		if ( $a11y_class ) {
			$close_class = " class='$a11y_class'";
		}

		/* Attributes */

		$attr        = Utils::get_attr_as_str( $attr );
		$dialog_attr = Utils::get_attr_as_str( $dialog_attr );

		if ( $attr ) {
			$attr = " $attr";
		}

		if ( $dialog_attr ) {
			$dialog_attr = " $dialog_attr";
		}

		/* Output */

		return (
			"<div$class$attr>" .
				$trigger .
				"<div role='dialog'$dialog_class$dialog_attr>" .
					"<div$overlay_class></div>" .
					"<div$content_class>" .
						'<div>' .
							$content .
							"<button$close_class>" .
								"<span$a11y_class>Close modal</span>" .
								/* phpcs:ignore */
								file_get_contents( $icon_path ) . // Ignore: local path
							'</button>' .
						'</div>' .
					'</div>' .
				'</div>' .
			'</div>'
		);
	}

	/**
	 * Output for search form.
	 *
	 * @param array $args
	 * @return string html
	 */

	public static function render_form_search( $args = [] ) {
		$args = array_merge(
			[
				'form_class'   => '',
				'field_class'  => '',
				'input_class'  => '',
				'button_class' => '',
				'icon_class'   => '',
				'icon_path'    => '',
				'a11y_class'   => '',
			],
			$args
		);

		/* Destructure */

		[
			'form_class'   => $form_class,
			'field_class'  => $field_class,
			'input_class'  => $input_class,
			'button_class' => $button_class,
			'icon_class'   => $icon_class,
			'icon_path'    => $icon_path,
			'a11y_class'   => $a11y_class,
		] = $args;

		/* Escape */

		$form_class   = esc_attr( $form_class );
		$field_class  = esc_attr( $field_class );
		$input_class  = esc_attr( $input_class );
		$button_class = esc_attr( $button_class );
		$icon_class   = esc_attr( $icon_class );
		$action       = esc_url( home_url( '/' ) );
		$query        = esc_attr( get_search_query() );

		if ( $form_class ) {
			$form_class = " class='$form_class'";
		}

		if ( $field_class ) {
			$field_class = " class='$field_class'";
		}

		if ( $input_class ) {
			$input_class = " class='$input_class'";
		}

		if ( $button_class ) {
			$button_class = " class='$button_class'";
		}

		if ( $icon_class ) {
			$icon_class = " class='$icon_class'";
		}

		if ( $a11y_class ) {
			$a11y_class = " class='$a11y_class'";
		}

		/* Label ID */

		$unique_id = 'search-' . uniqid();

		return (
			"<form$form_class role='search' method='get' action='$action'>" .
				"<div$field_class>" .
					"<label$a11y_class for='$unique_id'>Search for: </label>" .
					"<input$input_class type='search' id='$unique_id' placeholder='Search' value='$query' name='s' />" .
					"<button$button_class type='submit'>" .
						"<span$a11y_class>Submit search query</span>" .
						"<span$icon_class>" .
							/* phpcs:ignore */
							( $icon_path ? file_get_contents( $icon_path ) : '' ) . // Ignore: local path
						'</span>' .
					'</button>' .
				'</div>' .
			'</form>'
		);
	}

} // End Utils_Optional
