<?php
/**
 * Select fields to output form
 *
 * @package wp-theme-formation
 */

namespace Formation\Common\Field;

/**
 * Imports
 */

use Formation\Formation as FRM;
use Formation\Common\Field\Field;

/**
 * Class
 */

class Select_Fields {

	/**
	 * Get fields.
	 *
	 * @param string $name
	 * @return array
	 */

	public static function get( $name = '' ) {
		if ( ! $name ) {
			return [];
		}

		return [
			[
				'name'  => $name . '[%i][name]',
				'label' => 'Name',
			],
			[
				'name'  => $name . '[%i][label]',
				'label' => 'Label',
			],
			[
				'type'    => 'select',
				'name'    => $name . '[%i][type]',
				'attr'    => [
					'onchange' => 'showHiddenFields( event )',
				],
				'label'   => 'Type',
				'options' => [
					'text'     => 'Text',
					'checkbox' => 'Checkbox',
					'radio'    => 'Radio',
					'email'    => 'Email',
					'select'   => 'Select',
					'textarea' => 'Text Area',
					'hidden'   => 'Hidden',
				],
			],
			[
				'type'   => 'textarea',
				'name'   => $name . '[%i][options]',
				'label'  => 'Options (value : label)',
				'hidden' => true,
				'attr'   => [
					'rows' => 5,
					'cols' => 40,
				],
			],
			[
				'type'  => 'text',
				'name'  => $name . '[%i][value]',
				'label' => 'Value',
			],
			[
				'type'  => 'checkbox',
				'label' => 'Required',
				'name'  => $name . '[%i][required]',
				'value' => 1,
			],
		];
	}

	/**
	 * Output fields.
	 *
	 * @param array $fields
	 * @return string of markup
	 */

	public static function render( $fields = [] ) {
		if ( ! $fields ) {
			return '';
		}

		$output = '';

		if ( ! isset( $fields[0] ) ) {
			$fields = [$fields];
		}

		$id = uniqid();

		$fields = array_map(
			function( $v ) use ( $id ) {
				if ( ! isset( $v['name'] ) ) {
					$v['name'] = FRM::$namespace . '_' . str_replace( ' ', '_', strtolower( $v['label'] ) );
				}

				$v['name'] .= "_$id";

				$type    = $v['type'];
				$options = $v['options'] ?? '';

				if ( ! isset( $v['attr'] ) ) {
					$v['attr'] = [];
				}

				if ( $options ) {
					$options     = explode( "\n", $options );
					$options_arr = [];
					$ff          = [];

					foreach ( $options as $o ) {
						$o_arr                    = explode( ' : ', $o );
						$options_arr[ $o_arr[0] ] = $o_arr[1];

						if ( 'checkbox' === $type || 'radio' === $type ) {
							$ff[] = [
								'name'        => FRM::$namespace . '_' . str_replace( ' ', '_', strtolower( $o_arr[1] ) ),
								'type'        => $type,
								'label'       => $o_arr[1],
								'value'       => $o_arr[0],
								'label_first' => false,
							];
						}
					}

					$v['options'] = $options_arr;

					if ( 'checkbox' === $type || 'radio' === $type ) {
						$v = [
							'fields' => $ff,
							'label'  => $v['label'] ?? '',
						];
					}
				}

				if ( isset( $v['required'] ) ) {
					$v['attr']['aria-required'] = 'true';
				}

				return $v;
			},
			$fields
		);

		Field::render(
			[
				'fields' => $fields,
			],
			$output
		);

		return $output;
	}

	/**
	 * Filter value by required fields.
	 *
	 * @param array $value
	 * @return array
	 */

	public static function filter( $value ) {
		Field::filter_multi_fields( $value, ['type'] );
		return $value;
	}

	/**
	 * Scripts to enqueue.
	 */

	public static function scripts() {
		$path   = FRM::$src_path . 'Common/assets/public/';
		$handle = FRM::$namespace . '-select-fields-script';

		wp_enqueue_script(
			$handle,
			get_template_directory_uri() . $path . 'js/select-fields.js',
			[],
			FRM::$script_ver,
			true
		);
	}

} // End Select_Fields
