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

}
