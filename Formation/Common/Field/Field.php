<?php
/**
 * Render fields in front end and admin
 *
 * @package wp-theme-formation
 */

namespace Formation\Common\Field;

/**
 * Imports
 */

use Formation\Formation as FRM;
use Formation\Utils;
use Formation\Common\Field\File_Upload;
use function Formation\additional_script_data;

/**
 * Class
 */

class Field {

	/**
	 * Default arguments to render fields.
	 *
	 * @var array $default
	 */

	private static $default = [
		'render' => [
			'name'             => '',
			'fields'           => [],
			'data'             => '',
			'multi'            => false,
			'multi_col'        => false,
			'multi_item_class' => '',
			'copy'             => false,
			'hidden'           => false,
			'section_class'    => '',
		],
		'field'  => [
			'id'                => false,
			'name'              => false,
			'type'              => 'text',
			'label'             => false,
			'label_hidden'      => false,
			'label_class'       => '',
			'label_above'       => true,
			'attr'              => [],
			'field_class'       => '',
			'field_attr'        => [],
			'opt_button_class'  => '',
			'opt_button_attr'   => [],
			'opt_buttons_class' => '',
			'opt_buttons_attr'  => [],
			'class'             => '',
			'placeholder'       => '',
			'options'           => [],
			'hidden'            => false,
			'before'            => '',
			'after'             => '',
			'before_field'      => '',
			'after_field'       => '',
			'value'             => '',
			/* Hidden */
			'hidden_type_show'  => false,
			/* File */
			'file_type'         => 'file', // Or image
			'accept'            => '',
			'wp'                => false,
			/* Richtext */
			'rows'              => 4,
			'quicktags'         => false,
			'wpautop'           => false,
			'p_tags'            => true,
			'toolbar'           => 'bold,italic,separator,bullist,numlist,blockquote,separator,link',
		],
	];

	/**
	 * Multi buttons markup.
	 *
	 * @var array $multi_buttons
	 */

	public static $multi_buttons = [
		'add'    =>
			'<button type="button" class="o-multi__button" data-type="add" onclick="multi(this)">' .
				'<span class="u-v-h">Add Input</span>' .
				'<span class="dashicons dashicons-plus o-multi__icon"></span>' .
			'</button>',
		'remove' =>
			'<button type="button" class="o-multi__button" data-type="remove" onclick="multi(this)">' .
				'<span class="u-v-h">Remove Input</span>' .
				'<span class="dashicons dashicons-minus o-multi__icon"></span>' .
			'</button>',
	];

	/**
	 * Multi field copies to pass to front end.
	 *
	 * @var array $localize_data
	 */

	public static $localize_data = [
		'multi' => [],
		'files' => [],
		'links' => [],
	];

	/**
	 * Scripts to defer by handle.
	 *
	 * @var array $defer_script_handles
	 */

	public static $defer_script_handles = [];

	/**
	 * Get base name without keys or indexes.
	 *
	 * @param string $name
	 * @return string base name
	 */

	public static function get_top_level_name( $name ) {
		if ( strpos( $name, '[%i]' ) !== false ) {
			$name = explode( '[', $name )[0];
		}

		if ( strpos( $name, '[]' ) !== false ) {
			$name = explode( '[]', $name )[0];
		}

		if ( strpos( $name, '[' ) !== false ) {
			$name = explode( '[', $name )[0];
		}

		return $name;
	}

	/**
	 * Replace %i placeholder with actual index.
	 *
	 * @param string $name
	 * @param int $index
	 * @return string indexed name
	 */

	public static function index_name( $name, $index ) {
		return str_replace(
			array( '%i' ),
			array( $index ),
			$name
		);
	}

	/**
	 * Remove brackets from name to create id ( tinymce doesn't allow brackets in id ).
	 *
	 * @param string $id
	 * @return string
	 */

	public static function format_id( $id ) {
		$id = str_replace(
			array( '[', ']' ),
			array( '_' ),
			$id
		);

		return rtrim( $id, '_' );
	}

	/**
	 * Get value in data array from name ( lorem[%i][ipsum] )
	 *
	 * @param array $array
	 * @param string $key
	 * @return string/array value of $key in $array
	 */

	public static function get_array_value( $array, $key ) {
		if ( ! $key ) {
			return false;
		}

		$key = str_replace(
			array( '[]', '[', ']' ),
			array( '', '.', '' ),
			$key,
		);

		$key = explode( '.', $key );

		$value = $array;

		foreach ( $key as $k ) {
			if ( isset( $value[ $k ] ) ) {
				$value = $value[ $k ];
			} else {
				$value = false;
				break;
			}
		}

		return $value;
	}

	/**
	 * Check if array is associative or indexed.
	 *
	 * Source: http://thinkofdev.com/check-if-an-array-is-associative-or-sequentialindexed-in-php/
	 *
	 * @param array $arr
	 * @return boolean
	 */

	public static function is_assoc( $arr ) {
		return array_keys( $arr ) !== range( 0, count( $arr ) - 1 );
	}

	/**
	 * Recursively filter out required empty multi fields.
	 *
	 * @param array $array Contains multi fields value.
	 * @param array $required Contains required keys.
	 */

	public static function filter_multi_fields( &$array, $required = [] ) {
		if ( ! is_array( $array ) ) {
			return;
		}

		foreach ( $array as $k => &$v ) {
			if ( is_array( $v ) ) {
				$unset = false;

				/* Check if values required and if empty remove parent */

				foreach ( $v as $q => $r ) {
					if ( in_array( $q, $required, true ) && ! $r ) {
						$unset = true;
					}
				}

				if ( $unset ) {
					unset( $array[ $k ] );
				}

				self::filter_multi_fields( $v );

				continue;
			}
		}
	}

	/**
	 * Output sections of fields.
	 *
	 * @param array $args @see self::$default['render']
	 * @return string of markup
	 */

	public static function render( $args = [], &$output ) {
		/* Variables */

		$args           = array_replace_recursive( self::$default['render'], $args );
		$count          = 1;
		$top_level_name = '';
		$copy_output    = '';
		$pre            = FRM::$field_class_prefix;

		/* Destructure */

		[
			'name'             => $name,
			'fields'           => $fields,
			'data'             => $data,
			'multi'            => $multi,
			'multi_col'        => $multi_col,
			'multi_item_class' => $multi_item_class,
			'copy'             => $copy,
			'hidden'           => $hidden,
			'section_class'    => $section_class,
		] = $args;

		/* Single field passed in */

		if ( ! $fields ) {
				$fields = [$args];
		}

		/* Multi variables */

		if ( $name && $multi ) {
			$name = FRM::get_namespaced_str( $name );

			/* Get top level name in case an array */

			$top_level_name = self::get_top_level_name( $name );

			if ( is_array( $data ) && isset( $data[ $top_level_name ][0] ) ) {
				$count = count( $data[ $top_level_name ] );
			}
		}

		/* Start output */

		if ( is_admin() ) {
			if ( $top_level_name ) {
				$hide          = $hidden ? " style='display: none;'" : '';
				$col           = $multi_col ? ' data-col' : '';
				$section_class = esc_attr( $section_class ? " $section_class" : '' );

				$output .= "<div class='" . $pre . "-section$section_class' data-name='$top_level_name'$hide$col>";
			}
		}

		if ( $multi ) {
			$output .= '<div class="o-multi">';
		}

		/* Output fields */

		for ( $i = 0; $i < $count; $i++ ) {
			if ( $multi ) {
				$multi_item_class = esc_attr( $multi_item_class ? " $multi_item_class" : '' );

				$mi_start =
					"<div class='o-multi__item$multi_item_class' data-name='$top_level_name'>" .
						'<div class="o-multi__fields l-flex" data-wrap>';

				if ( 0 === $i ) {
					$copy_output .= $mi_start;
				}

				$output .= $mi_start;
			}

			foreach ( $fields as $f ) {
				if ( isset( $f['fields'] ) ) {
					if ( $f['fields'] ) {
						self::render( $f, $output );
						continue;
					}
				}

				self::render_field(
					$f,
					$output,
					$i,
					$data,
					false, // Copy
					$multi,
					$multi_col
				);

				if ( $multi && 0 === $i ) {
					self::render_field(
						$f,
						$copy_output,
						$i,
						$data,
						true, // Copy
						$multi,
						$multi_col
					);
				}
			}

			if ( $multi ) {
				$mi_end =
						'</div>' .
					'<div class="o-multi__buttons l-flex">' .
					self::$multi_buttons['add'];

				$output .= $mi_end;

				if ( $i > 0 ) {
					$output .= self::$multi_buttons['remove'];
				}

				if ( 0 === $i ) {
					$copy_output .= $mi_end . self::$multi_buttons['remove'];
				}

				$mi_end =
						'</div>' .
					'</div>';

				$output .= $mi_end;

				if ( 0 === $i ) {
					$copy_output .= $mi_end;
				}
			}
		}

		// End output

		if ( $multi ) {
			$output .= '</div>';

			if ( $copy_output ) {
				self::$localize_data['multi'][ $top_level_name ] = $copy_output;
			}
		}

		if ( is_admin() ) {
			$output .= '</div>';
		}

		additional_script_data( FRM::$namespace, self::$localize_data, true );

		return $output;
	}

	/**
	 * Output fields.
	 *
	 * @param array $args @see self::$default['field']
	 * @param string $output Append to it as loop in render method.
	 */

	public static function render_field( $args = [], &$output, $index = 0, $data = '', $copy = false, $multi = false, $multi_col = false ) {
		$args = array_replace_recursive( self::$default['field'], $args );

		/* Destructure */

		[
			'id'                => $id,
			'name'              => $name,
			'type'              => $type,
			'label'             => $label,
			'label_hidden'      => $label_hidden,
			'label_class'       => $label_class,
			'label_above'       => $label_above,
			'attr'              => $attr,
			'field_class'       => $field_class,
			'field_attr'        => $field_attr,
			'opt_button_class'  => $opt_button_class,
			'opt_button_attr'   => $opt_button_attr,
			'opt_buttons_class' => $opt_buttons_class,
			'opt_buttons_attr'  => $opt_buttons_attr,
			'class'             => $class,
			'placeholder'       => $placeholder,
			'options'           => $options,
			'hidden'            => $hidden,
			'before'            => $before,
			'after'             => $after,
			'before_field'      => $before_field,
			'after_field'       => $after_field,
			'value'             => $value,
			'hidden_type_show'  => $hidden_type_show,
			'file_type'         => $file_type,
			'accept'            => $accept,
			'wp'                => $wp,
			'rows'              => $rows,
			'quicktags'         => $quicktags,
			'wpautop'           => $wpautop,
			'p_tags'            => $p_tags,
			'toolbar'           => $toolbar,
		] = $args;

		/* Variables/attributes */

		$name = FRM::get_namespaced_str( $name );

		if ( $multi ) {
			$attr['data-name'] = $name;
			$attr['data-id']   = self::format_id( $name );
		}

		$name           = $multi && ! $copy ? self::index_name( $name, $index ) : $name;
		$id             = $id ? $id : self::format_id( $name );
		$pre            = FRM::$field_class_prefix;
		$checkbox_radio = 'checkbox' === $type || 'radio' === $type || 'radio-select' === $type || 'radio-text' === $type;
		$placeholder    = $placeholder ? 'placeholder="' . esc_attr( $placeholder ) . '"' : '';
		$classes        = esc_attr( $pre . '__' . $type . ' js-input' . ( $class ? " $class" : '' ) );
		$label_class    = esc_attr( $pre . '__label' . ( $label_class ? " $label_class" : '' ) );
		$label          = esc_html( $label );
		$label_text     = $label;

		if ( is_array( $data ) ) {
			$data_value = self::get_array_value( $data, $name );
		} else {
			$data_value = $data;
		}

		$val  = ! $value ? $data_value : $value;
		$req  = '';
		$attr = Utils::get_attr_as_str(
			$attr,
			function( $a, $v ) use ( &$req ) {
				if ( 'aria-required' === $a && 'true' === $v ) {
					$req = ' data-req';
				}
			}
		);

		$hidden = '100' === $hidden || ( $hidden && ! $val ) ? ' style="display: none;"' : '';

		if ( 'hidden' === $type && ! $hidden_type_show ) {
			$field_class .= ( $field_class ? ' ' : '' ) . 'u-v-h';
		}

		if ( 'radio-select' === $type || 'radio-text' === $type ) {
			$field_attr['role'] = 'group';
		}

		$field_attr = Utils::get_attr_as_str( $field_attr );

		/* Start output */

		$output .= $before_field;
		$output .= "<div class='" . $pre . '__field' . ( $field_class ? " $field_class" : '' ) . "' data-type='" . esc_attr( $type ) . "'$hidden$field_attr>";

		if ( $label && ! $label_hidden ) {
			if ( $checkbox_radio ) {
				$label = (
					"<label for='" . esc_attr( $id ) . "'$req>" .
						'<span class="' . $pre . '__control" data-type="' . $type . '"></span>' .
						"<span class='$label_class'>$label</span>" .
					'</label>'
				);
			} else {
				$label = (
					"<label id='" . uniqid() . "' class='$label_class' for='" . esc_attr( $id ) . "'$req>" .
						"<span>$label</span>" .
					'</label>'
				);
			}

			if ( $label_above ) {
				$output .= $label;
			}
		}

		$output .= $before;

		switch ( $type ) {
			case 'text':
			case 'email':
			case 'checkbox':
			case 'radio':
			case 'radio-select':
			case 'radio-text':
			case 'number':
			case 'tel':
			case 'hidden':
				if ( is_admin() ) {
					if ( 'checkbox' !== $type && 'number' !== $type ) {
						$classes .= ' regular-text';
					}

					if ( 'number' === $type ) {
						$classes .= ' small-text';
					}
				}

				if ( 'text' === $type || 'email' === $type ) {
					$classes .= ' ' . $pre . '__input';
				}

				$checked = '';
				$v       = $val;

				if ( $checkbox_radio ) {
					if ( (string) $data_value === (string) $value ) {
						$checked = 'checked';
					}

					$v = $value;
				}

				$t = $type;

				if ( 'radio-text' === $type || 'radio-select' === $type ) {
					$t = 'radio';
				}

				$output .= sprintf(
					'<input name="%1$s" id="%8$s" type="%2$s" value="%4$s" class="%5$s" %6$s %7$s %3$s>',
					esc_attr( $name ),
					esc_attr( $t ),
					$placeholder,
					esc_html( $v ),
					esc_attr( $classes ),
					$checked,
					$attr,
					esc_attr( $id )
				);

				break;
			case 'checkbox-group':
			case 'radio-group':
				$output .= self::render_opt_button(
					[
						'options'           => $options,
						'id'                => $name,
						'class'             => $class,
						'opt_button_class'  => $opt_button_class,
						'opt_buttons_class' => $opt_buttons_class,
						'opt_button_attr'   => Utils::get_attr_as_str( $opt_button_attr ),
						'opt_buttons_attr'  => Utils::get_attr_as_str( $opt_buttons_attr ),
						'type'              => 'checkbox-group' === $type ? 'checkbox' : 'radio',
						'value'             => $val,
						'attr'              => $attr,
					]
				);

				break;
			case 'file':
				$output .= self::render_asset(
					[
						'type'   => $file_type,
						'value'  => $val,
						'accept' => $accept,
						'name'   => $name,
						'id'     => $id,
						'class'  => 'o-asset--upload',
						'wp'     => $wp,
					]
				);

				break;
			case 'textarea':
				if ( $multi_col ) {
					$classes .= ' js-fit-content';
					$attr    .= ' oninput="textareaFitContent(this)"';
				}

				$output .= sprintf(
					'<textarea name="%1$s" id="%5$s" class="%2$s" %4$s>%3$s</textarea>',
					esc_attr( $name ),
					esc_attr( $classes ),
					esc_textarea( $val ),
					$attr,
					esc_attr( $id )
				);

				break;
			case 'richtext':
				ob_start();

				if ( ! $p_tags ) {
					add_filter(
						'tiny_mce_before_init',
						function( $init_settings ) {
							$init_settings['forced_root_block'] = false;
							return $init_settings;
						}
					);
				}

				wp_editor(
					html_entity_decode( $val ),
					$id,
					[
						'media_buttons' => false,
						'wpautop'       => $wpautop,
						'textarea_name' => $name,
						'textarea_rows' => $rows,
						'editor_class'  => $classes,
						'tinymce'       => [
							'toolbar1' => $toolbar,
							'toolbar2' => '',
							'toolbar3' => '',
							'toolbar4' => '',
						],
					]
				);

				$output .= ob_get_clean();

				break;
			case 'select':
				if ( $options ) {
					$opt = '';

					if ( ! self::is_assoc( $options ) ) {
						$o = [];

						foreach ( $options as $op ) {
							$o[ $op['value'] ] = $op['label'];
						}

						$options = $o;
					}

					foreach ( $options as $key => $opt_label ) {
						$selected = $key === $val ? ' selected' : '';

						$opt .= sprintf(
							'<option value="%s"%s>%s</option>',
							esc_html( $key ),
							$selected,
							esc_html( $opt_label )
						);
					}

					$output .= sprintf(
						'<select name="%1$s" id="%5$s" class="%3$s" %4$s>%2$s</select>',
						esc_attr( $name ),
						$opt,
						esc_attr( $classes ),
						$attr,
						esc_attr( $id )
					);
				}

				break;
			case 'link':
				self::$localize_data['links'][] = ['id' => $id];

				$output .= self::render_asset(
					[
						'upload' => false,
						'type'   => 'link',
						'value'  => $val,
						'name'   => $name,
						'id'     => $id,
						'class'  => 'o-asset--link',
						'wp'     => $wp,
					]
				);

				break;
		}

		$output .= $after;

		if ( $label && ! $label_hidden ) {
			if ( ! $label_above ) {
				$output .= $label;
			}
		}

		if ( 'radio-select' === $type && $options ) {
			$output .= self::render_field(
				[
					'id'      => uniqid(),
					'name'    => $name . '_select',
					'type'    => 'select',
					'options' => $options,
					'attr'    => [
						'aria-label' => $label_text,
					],
				],
				$output
			);
		}

		if ( 'radio-text' === $type ) {
			$output .= self::render_field(
				[
					'id'   => uniqid(),
					'name' => $name . '_text',
					'type' => 'text',
					'attr' => [
						'aria-label' => $label_text,
					],
				],
				$output
			);
		}

		$output .= '</div>';

		$output .= $after_field;
	}

	/**
	 * Output radio/checkbox buttons.
	 *
	 * @param array $args
	 * @return string of markup
	 */

	public static function render_opt_button( $args = [] ) {
		$args = array_merge(
			[
				'options'           => [],
				'id'                => FRM::$namespace . '_' . uniqid(),
				'type'              => 'radio',
				'value'             => '',
				'class'             => '',
				'opt_button_class'  => '',
				'opt_button_attr'   => '',
				'opt_buttons_class' => '',
				'opt_buttons_attr'  => '',
				'attr'              => '',
			],
			$args
		);

		/* Destructure */

		[
			'options'           => $options,
			'id'                => $id,
			'type'              => $type,
			'value'             => $value,
			'class'             => $class,
			'opt_button_class'  => $opt_button_class,
			'opt_button_attr'   => $opt_button_attr,
			'opt_buttons_class' => $opt_buttons_class,
			'opt_buttons_attr'  => $opt_buttons_attr,
			'attr'              => $attr,
		] = $args;

		/* Attributes */

		$class            = esc_attr( 'o-radio__input u-h-i js-input' . ( $class ? " $class" : '' ) );
		$opt_button_class = esc_attr( 'o-radio__field' . ( $opt_button_class ? " $opt_button_class" : '' ) );

		if ( $attr ) {
			$attr = " $attr";
		}

		if ( $opt_buttons_class ) {
			$opt_buttons_class = " $opt_buttons_class";
		}

		/* Output */

		$output = '';

		foreach ( $options as $index => $o ) {
			$operator = $o['operator'] ?? false;
			$operator = $operator ? ' data-operator="' . esc_attr( $operator ) . '"' : '';
			$o_label  = $o['label'];
			$o_value  = $o['value'];

			$checked = ( $value && $o_value === $value ) ? ' checked' : '';

			$o_id    = esc_attr( $id );
			$o_label = esc_html( $o['label'] );
			$o_value = esc_html( $o['value'] );
			$o_type  = esc_attr( $type );
			$o_for   = FRM::$namespace . '_' . uniqid();

			$output .=
				'<div class="o-radio__item" role="group">' .
					"<input class='$class' type='$o_type' id='$o_for' name='$o_id' value='$o_value'$checked$operator$attr>" .
					"<label for='$o_for' class='$opt_button_class'$opt_button_attr>" .
						"<span class='o-radio__label'>$o_label</span>" .
					'</label>' .
				'</div>';
		}

		return "<div class='o-radio$opt_buttons_class'$opt_buttons_attr>$output</div>";
	}

	/**
	 * Output asset (files, links..).
	 *
	 * @param array $args
	 * @return string of markup
	 */

	public static function render_asset( $args = [] ) {
		$args = array_merge(
			[
				'upload'      => true,
				'wp'          => false,
				'class'       => '',
				'type'        => '',
				'value'       => '',
				'accept'      => '',
				'name'        => '',
				'id'          => '',
				'input_value' => '',
			],
			$args
		);

		/* Destructure */

		[
			'upload'      => $upload,
			'wp'          => $wp,
			'class'       => $class,
			'type'        => $type,
			'value'       => $value,
			'accept'      => $accept,
			'name'        => $name,
			'id'          => $id,
			'input_value' => $input_value,
		] = $args;

		/* Variables */

		$type      = strtolower( $type );
		$type_cap  = ucwords( $type );
		$exists    = $value ? true : false;
		$url       = '';
		$icon_text = '';
		$title     = basename( wp_parse_url( $value, PHP_URL_PATH ) );

		if ( $upload ) {
			self::$localize_data['files'][] = [
				'id'        => $id,
				'file_type' => $type,
			];

			if ( $wp ) { // Check if from wp media library
				if ( 'image' === $type ) {
					$input_value = $value;
					$wp_image    = FRM::get_image( (int) $value, 'medium' );

					if ( $wp_image ) {
						$title = basename( get_attached_file( (int) $value ) );
						$value = $wp_image['url'];
					} else {
						$exists = false;
						$value  = '';
					}
				}
			} else { // Check that file exists
				if ( ! file_exists( FRM::$uploads_dir . $title ) ) {
					$exists = false;
				}
			}
		}

		if ( 'image' === $type ) {
			$url = $value;
		}

		if ( 'file' === $type ) {
			$file_path_parts = pathinfo( $title );

			if ( isset( $file_path_parts['extension'] ) ) {
				$icon_text = $file_path_parts['extension'];
			}
		}

		if ( 'link' === $type ) {
			$title  = '';
			$target = '';

			$v = FRM::get_link( $value );

			if ( $v ) {
				$icon_text = $v['text'];
				$title     = $v['url'];
				$target    = $v['target'];
			} else {
				$exists = false;
			}
		}

		if ( ! $input_value ) {
			$input_value = $value;
		}

		$input_value = esc_html( $input_value );
		$accept      = esc_attr( $accept );
		$class       = esc_attr( 'o-asset' . ( $class ? " $class" : '' ) );

		/* Output */

		return (
			"<div class='$class'" . ( $wp && $upload ? " data-wp='true'" : '' ) . '>' .
				"<div class='o-asset__exists' style='display: " . ( $exists ? 'block' : 'none' ) . "'>" .
					"<div class='l-flex' data-align='center'>" .
						"<img class='o-asset__image' src='$url' alt='Asset preview'>" .
						"<div class='o-asset__icon'>$icon_text</div>" .
						(
							'link' === $type
							?
							"<span class='o-asset__target' style='display: none;'>$target</span>" .
							"<a class='o-asset__name' href='$title' target='_blank'>$title</a>"
							:
							"<span class='o-asset__name'>$title</span>"
						) .
						"<div class='o-asset__right l-flex'>" .
							(
								'link' === $type
								?
								"<button type='button' class='o-asset__edit'>" .
									"<span class='dashicons dashicons-edit'></span>" .
									"<span class='u-v-h'>Edit</span>" .
								'</button>'
								:
								''
							) .
						"<button type='button' class='o-asset__remove u-p-r'>" .
							"<span class='dashicons dashicons-no-alt'></span>" .
							"<span class='u-v-h'>Remove $type_cap</span>" .
							"<span class='o-asset__loader o-loader js-loader-remove' data-hide><span class='spinner is-active'></span></span>" .
						'</button>' .
					'</div>' .
				'</div>' .
			'</div>' .
			(
				$upload || 'link' === $type
				?
				"<div class='o-asset__no' style='display: " . ( $exists ? 'none' : 'block' ) . "'>" .
					"<p style='margin: 0'>" .
						"<label class='o-asset__select u-p-r" . ( is_admin() ? ' button add-media' : '' ) . "'>" .
							"<input type='" . ( $upload && ! $wp ? 'file' : 'button' ) . "' aria-label='Select $type_cap' class='u-h-i' accept='$accept'>" .
							"<span>Select $type_cap</span>" .
							"<span class='o-asset__loader o-loader js-loader-select' data-hide><span class='spinner is-active'></span></span>" .
						'</label>' .
					'</p>' .
				'</div>'
				:
				''
			) .
			"<input class='o-asset__input' name='$name' id='$id' type='hidden' value='$input_value'>" .
			'</div>'
		);
	}

	/**
	 * Scripts and styles to enqueue.
	 */

	public static function scripts( $child = false ) {
		$path = FRM::$src_path . 'Common/assets/public/';
		$uri  = $child ? get_stylesheet_directory_uri() : get_template_directory_uri();

		wp_enqueue_style(
			FRM::$namespace . '-field-styles',
			$uri . $path . 'css/field.css',
			[],
			FRM::$script_ver
		);

		$upload_nonce_name = FRM::$namespace . '_upload_file_nonce';
		$remove_nonce_name = FRM::$namespace . '_remove_file_nonce';

		additional_script_data(
			FRM::$namespace,
			[
				$upload_nonce_name => wp_create_nonce( $upload_nonce_name ),
				$remove_nonce_name => wp_create_nonce( $remove_nonce_name ),
			],
			true
		);

		wp_enqueue_media();

		$handle = FRM::$namespace . '-field-script';

		static::$defer_script_handles[] = $handle;

		wp_enqueue_script(
			$handle,
			$uri . $path . 'js/field.js',
			[],
			FRM::$script_ver,
			true
		);

		add_filter(
			'script_loader_tag',
			function( $tag, $handle, $src ) {
				foreach ( self::$defer_script_handles as $value ) {
					if ( $value === $handle ) {
						$tag = str_replace( ' src', ' defer="defer" src', $tag );
					}
				}

				return $tag;
			},
			10,
			3
		);
	}

	/**
	 * Ajax action and callback to upload files.
	 */

	public static function file_actions() {
		add_action(
			'wp_ajax_upload_file',
			function() {
				try {
					/* Check upload nonce */

					if ( ! check_ajax_referer( FRM::$namespace . '_upload_file_nonce', FRM::$namespace . '_upload_file_nonce', false ) ) {
						throw new \Exception( 'Not allowed' );
					}

					new File_Upload(
						[
							'uploads_dir' => FRM::$uploads_dir,
							'uploads_url' => FRM::$uploads_url,
							'success'     => function( $data ) {
								echo wp_json_encode( $data );
							},
							'error'       => function( $err ) {
								throw new \Exception( $err );
							},
						]
					);

					exit;
				} catch ( \Exception $e ) {
					echo esc_html( $e->getMessage() );
					header( http_response_code( 500 ) );
					exit;
				}
			}
		);

		add_action(
			'wp_ajax_remove_file',
			function() {
				try {
					/* Check upload nonce */

					if ( ! check_ajax_referer( FRM::$namespace . '_remove_file_nonce', FRM::$namespace . '_remove_file_nonce', false ) ) {
						throw new \Exception( 'Not allowed' );
					}

					if ( ! isset( $_POST['file_path'] ) ) {
						throw new \Exception( 'No file path' );
					}

					unlink( $_POST['file_path'] );

					echo wp_json_encode( 'File successfully deleted.' );

					exit;
				} catch ( \Exception $e ) {
					echo esc_html( $e->getMessage() );
					header( http_response_code( 500 ) );
					exit;
				}
			}
		);
	}

} // End Field
