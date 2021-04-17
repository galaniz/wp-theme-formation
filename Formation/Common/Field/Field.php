<?php

/*
 * Render fields in front end and admin
 * ------------------------------------
 */

namespace Formation\Common\Field;

/*
 * Imports
 * -------
 */

use \Formation\Formation as FRM;
use \Formation\Utils;
use Formation\Common\Field\File_Upload;

use function Formation\additional_script_data;
use function Formation\write_log;

class Field {

 /*
	* Variables
	* ---------
	*
	* Default arguments to render fields.
	*
	* @var array $default
	*/

	private static $default = [
		'render' => [
			'name' => '',
			'fields' => [],
			'data' => '',
			'multi' => false,
			'multi_col' => false,
			'multi_item_class' => '',
			'copy' => false,
			'hidden' => false,
			'no_group' => false,
			'section_class' => ''
		],
		'field' => [
			'name' => false,
			'type' => 'text',
			'label' => false,
			'label_hidden' => false,
			'label_class' => '',
			'label_above' => true,
			'attr' => [],
			'field_class' => '',
			'field_attr' => [],
			'opt_button_class' => '',
			'opt_button_attr' => [],
			'opt_buttons_class' => '',
			'opt_buttons_attr' => [],
			'class' => '',
			'placeholder' => '',
			'options' => [],
			'hidden' => false,
			'before' => '',
			'after' => '',
			'before_field' => '',
			'after_field' => '',
			'value' => '',
			/* hidden */
			'hidden_type_show' => false,
			/* file */
			'file_type' => 'file', // or image
			'accept' => '',
			'wp' => false,
			/* richtext */
			'rows' => 4,
			'quicktags' => false,
			'wpautop' => false,
			'p_tags' => true,
			'toolbar' => 'bold,italic,separator,bullist,numlist,blockquote,separator,link'
		]
	];

 /*
	* Multi buttons markup.
	*
	* @var array $multi_buttons
	*/

	public static $multi_buttons = [
		'add' =>
			'<button type="button" class="o-multi__button" data-type="add" onclick="multi( this )">' .
				'<span class="u-v-h">Add Input</span>' .
				'<span class="dashicons dashicons-plus o-multi__icon"></span>' .
			'</button>',
		'remove' =>
			'<button type="button" class="o-multi__button" data-type="remove" onclick="multi( this )">' .
				'<span class="u-v-h">Remove Input</span>' .
				'<span class="dashicons dashicons-minus o-multi__icon"></span>' .
			'</button>'
	];

 /*
	* Multi field copies to pass to front end.
	*
	* @var array $localize_data
	*/

	public static $localize_data = [
		'multi' => [],
		'files' => [],
		'links' => []
	];

 /*
	* Helper methods
	* --------------
	*
	* Get base name without keys or indexes.
	*
	* @param string $name
	* @return string base name
	*/

	public static function get_top_level_name( $name ) {
		if( strpos( $name, '[%i]' ) !== false )
			$name = explode( '[', $name )[0];

		if( strpos( $name, '[]' ) !== false )
			$name = explode( '[]', $name )[0];

		if( strpos( $name, '[' ) !== false )
			$name = explode( '[', $name )[0];

		return $name;
	}

 /*
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

 /*
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

 /*
	* Get value in data array from name ( lorem[%i][ipsum] )
	*
	* @param array $array
	* @param string $key
	* @return string/array value of $key in $array
	*/

	public static function get_array_value( $array, $key ) {
		if( !$key ) return false;

		$key = str_replace(
			array( '[]', '[', ']' ),
			array( '', '.', '', ),
			$key
		);

		$key = explode( '.', $key );

		$value = $array;

		foreach( $key as $k ) {
			if( isset( $value[$k] ) ) {
				$value = $value[$k];
			} else {
				$value = false;
				break;
			}
		}

		return $value;
	}

 /*
	* Check if array is associative or indexed.
	*
	* source: http://thinkofdev.com/check-if-an-array-is-associative-or-sequentialindexed-in-php/
	*
	* @param array $arr
	* @return boolean
	*/

	public static function is_assoc( $arr ) {
		return array_keys( $arr ) !== range( 0, count( $arr ) - 1 );
	}

 /*
	* Recursively filter out required empty multi fields.
	*
	* @param array $array Contains multi fields value.
	* @param array $required Contains required keys.
	*/

	public static function filter_multi_fields( &$array, $required = [] ) {
		if( !is_array( $array ) )
			return;

		foreach( $array as $k => &$v ) {
			if( is_array( $v ) ) {
				$unset = false;

				// check if values required and if empty remove parent
				foreach( $v as $q => $r ) {
					if( in_array( $q, $required ) && !$r )
						$unset = true;
				}

				if( $unset )
					unset( $array[$k] );

				self::filter_multi_fields( $v );

				continue;
			}
		}
	}

 /*
	* Render methods
	* --------------
	*
	* Output sections of fields.
	*
	* @param array $args @see self::$default['render']
	* @return string of markup.
	*/

	public static function render( $args = [], &$output ) {
		$args = array_replace_recursive( self::$default['render'], $args );
		extract( $args );

		$count = 1;
		$top_level_name = '';
		$pre = is_admin() ? 'o-field' : FRM::$classes['field_prefix'];

		// if single field passed in
		if( !$fields )
			$fields = [$args];

		if( $name && $multi ) {
			$name = FRM::get_namespaced_str( $name );

			// get top level name in case an array
			$top_level_name = Field::get_top_level_name( $name );

			if( is_array( $data ) && isset( $data[$top_level_name][0] ) )
				$count = count( $data[$top_level_name] );
		}

		if( is_admin() ) {
			if( $top_level_name ) {
				$hide = $hidden ? " style='display: none;'" : '';
				$col = $multi_col ? ' data-col' : '';
				$section_class = esc_attr( $section_class ? " $section_class" : '' );

				$output .= "<div class='" . $pre . "-section$section_class' data-name='$top_level_name'$hide$col>";
			}
		} else {
			if( !$no_group )
				$output .= '<div class="' . $pre . '-group l-100 l-flex" data-wrap data-align="center">';
		}

		if( isset( $args['label'] ) && !isset( $args['label_hidden'] ) && !$no_group  )
			$output .= '<div class="' . $pre . '-group__label">' . $args['label'] . '</div>';

		if( $multi )
			$output .= '<div class="o-multi">';

		$copy_output = '';

		for( $i = 0; $i < $count; $i++ ) {
			if( $multi ) {
				$multi_item_class = esc_attr( $multi_item_class ? " $multi_item_class" : '' );

				$mi_start =
					"<div class='o-multi__item$multi_item_class' data-name='$top_level_name'>" .
						'<div class="o-multi__fields l-flex" data-wrap>';

				if( $i === 0 )
					$copy_output .= $mi_start;

				$output .= $mi_start;
			}

			foreach( $fields as $f ) {
				if( isset( $f['fields'] ) ) {
					if( $f['fields'] ) {
						self::render( $f, $output );
						continue;
					}
				}

				self::render_field(
					$f,
					$output,
					$i,
					$data,
					false, // copy
					$multi,
					$multi_col
				);

				if( $multi && $i === 0 ) {
					self::render_field(
						$f,
						$copy_output,
						$i,
						$data,
						true, // copy
						$multi,
						$multi_col
					);
				}
			}

			if( $multi ) {
				$mi_end =
						'</div>' .
					'<div class="o-multi__buttons l-flex">' .
					self::$multi_buttons['add'];

				$output .= $mi_end;

				if( $i > 0 )
					$output .= self::$multi_buttons['remove'];

				if( $i === 0 )
					$copy_output .= $mi_end . self::$multi_buttons['remove'];

				$mi_end =
						'</div>' .
					'</div>';

				$output .= $mi_end;

				if( $i === 0 )
					$copy_output .= $mi_end;
			}
		}

		if( $multi ) {
			$output .= '</div>';

			if( $copy_output )
				self::$localize_data['multi'][$top_level_name] = $copy_output;
		}

		if( is_admin() ) {
			$output .= '</div>';
		} else {
			if( !$no_group )
				$output .= '</div>';
		}

		additional_script_data( FRM::$namespace, self::$localize_data, true );

		return $output;
	}

 /*
	* Output fields.
	*
	* @param array $args @see self::$default['field']
	* @param string $output Append to it as loop in render method.
	*/

	public static function render_field( $args = [], &$output, $index = 0, $data = '', $copy = false, $multi = false, $multi_col = false ) {
		$args = array_replace_recursive( self::$default['field'], $args );
		
		extract( $args );

		$name = FRM::get_namespaced_str( $name );

		if( $multi ) {
			$attr['data-name'] = $name;
			$attr['data-id'] = self::format_id( $name );
		}

		$name = $multi && !$copy ? self::index_name( $name, $index ) : $name;

		$id = self::format_id( $name );
		$pre = is_admin() ? 'o-field' : FRM::$classes['field_prefix'];
		$classes = esc_attr( $pre . '__' . $type . ' js-input' . ( $class ? " $class" : '' ) );
		$placeholder = $placeholder ? 'placeholder="' . esc_attr( $placeholder ) . '"' : '';
		$checkbox_radio = $type === 'checkbox' || $type === 'radio';
		$label_class = esc_attr( $pre . "__label" . ( $label_class ? " $label_class" : '' ) );
		$label = esc_html( $label );

		if( !is_admin() ) {
			$classes .= esc_attr( FRM::$classes['input'] ? ' ' . FRM::$classes['input'] : '' );
			$field_class .= esc_attr( FRM::$classes['field'] ? ' ' . FRM::$classes['field'] : '' );
			$label_class .= esc_attr( FRM::$classes['label'] ? ' ' . FRM::$classes['label'] : '' );
		}

		$req = '';

		if( is_array( $data ) ) {
			$data_value = self::get_array_value( $data, $name );
		} else {
			$data_value = $data;
		}

		$val = !$value ? $data_value : $value;
		$attr = Utils::get_attr_as_str( $attr, function( $a, $v ) use ( &$req ) {
			if( $a == 'aria-required' && $v == 'true' )
				$req = ' data-req';
		} );

		$hidden = $hidden === '100' || ( $hidden && !$val ) ? ' style="display: none;"' : '';

		if( $type == 'hidden' && !$hidden_type_show )
			$field_class .= ( $field_class ? ' ' : '' ) . 'u-v-h';

		$field_attr = Utils::get_attr_as_str( $field_attr );

		$output .= $before_field;

		$output .=
			"<div class='" . $pre . ( $pre != 'o-field' ? '__field' : '' ) . ( $field_class ? " $field_class" : '' ) . "' data-type='$type'$hidden$field_attr>";

		if( $label && !$label_hidden ) {
			if( $type != 'checkbox-group' && $type != 'radio-group' )
				$output .= '<label>';

			$label = "<div class='$label_class'$req>$label</div>";

			if( $label_above )
				$output .= $label;
		}

		$output .= $before;

		switch( $type ) {
			case 'text':
			case 'email':
			case 'checkbox':
			case 'radio':
			case 'number':
			case 'hidden':
				if( is_admin() ) {
					if( $type !== 'checkbox' && $type !== 'number' )
						$classes .= ' regular-text';

					if( $type === 'number' )
						$classes .= ' small-text';
				}

				if( $type === 'text' || $type === 'email' )
					$classes .= ' ' . $pre .  '__input';

				$checked = '';
				$v = $val;

				if( $checkbox_radio ) {
					if( $data_value == $value )
						$checked = 'checked';

					$v = $value;
				}

				$output .= sprintf(
					'<input name="%1$s" id="%8$s" type="%2$s" value="%4$s" class="%5$s" %6$s %7$s %3$s>',
					$name,
					$type,
					$placeholder,
					esc_html( $v ),
					$classes,
					$checked,
					$attr,
					$id
				);

				if( $checkbox_radio )
					$output .= '<span class="' . $pre . '__control"></span>';

				break;
			case 'checkbox-group':
			case 'radio-group':
				$output .= self::render_opt_button( [
					'options' => $options,
					'id' => $name,
					'class' => $class,
					'opt_button_class' => $opt_button_class,
					'opt_buttons_class' => $opt_buttons_class,
					'opt_button_attr' => Utils::get_attr_as_str( $opt_button_attr ),
					'opt_buttons_attr' => Utils::get_attr_as_str( $opt_buttons_attr ),
					'type' => $type == 'checkbox-group' ? 'checkbox' : 'radio',
					'value' => $val,
					'attr' => $attr
				] );

				break;
			case 'file':
				$output .= self::render_asset( [
					'type' => $file_type,
					'value' => $val,
					'accept' => $accept,
					'name' => $name,
					'id' => $id,
					'class' => 'o-asset--upload',
					'wp' => $wp
				] );

				break;
			case 'textarea':
				if( $multi_col ) {
					$classes .= ' js-fit-content';
					$attr .= ' oninput="textareaFitContent( this )"';
				}

				$output .= sprintf(
					'<textarea name="%1$s" id="%5$s" class="%2$s" %4$s>%3$s</textarea>',
					$name,
					$classes,
					esc_textarea( $val ),
					$attr,
					$id
				);

				break;
			case 'richtext':
				ob_start();

				if( !$p_tags ) {
					add_filter( 'tiny_mce_before_init', function( $init_settings ) {
						$init_settings['forced_root_block'] = false;
						return $init_settings;
					} );
				}

				wp_editor( html_entity_decode( $val ), $id, [
					'media_buttons' => false,
					'wpautop' => $wpautop,
					'textarea_name' => $name,
					'textarea_rows' => $rows,
					'editor_class' => $classes,
					'tinymce' => [
						'toolbar1' => $toolbar,
						'toolbar2' => '',
						'toolbar3' => '',
						'toolbar4' => ''
					]
				] );

				$output .= ob_get_clean();

				break;
			case 'select':
				if( $options ) {
					$opt = '';

					if( !self::is_assoc( $options ) ) {
						$o = [];

						foreach( $options as $op )
							$o[$op['value']] = $op['label'];

						$options = $o;
					}

					foreach( $options as $key => $label ) {
						$selected = $key == $val ? ' selected' : '';

						$opt .= sprintf(
							'<option value="%s"%s>%s</option>',
							esc_html( $key ),
							$selected,
							$label
						);
					}

					$output .= sprintf(
						'<select name="%1$s" id="%5$s" class="%3$s" %4$s>%2$s</select>',
						$name,
						$opt,
						$classes,
						$attr,
						$id
					);
				}

				break;
			case 'link':
				self::$localize_data['links'][] = ['id' => $id];

				$output .= self::render_asset( [
					'upload' => false,
					'type' => 'link',
					'value' => $val,
					'name' => $name,
					'id' => $id,
					'class' => 'o-asset--link',
					'wp' => $wp
				] );

				break;
		}

		$output .= $after;

		if( $label && !$label_hidden ) {
			if( !$label_above )
				$output .= $label;

			if( $type != 'checkbox-group' && $type != 'radio-group' )
				$output .= '</label>';
		}

		$output .= '</div>';

		$output .= $after_field;
	}

 /*
	* Output radio / checkbox buttons
	*
	* @param array $args
	* @return string of markup
	*/

	public static function render_opt_button( $args = [] ) {
		$options = $args['options'] ?? [];
		$id = $args['id'] ?? FRM::$namespace . '_' . uniqid();
		$type = $args['type'] ?? 'radio';
		$value = $args['value'] ?? '';
		$class = $args['class'] ?? '';
		$opt_button_class = $args['opt_button_class'] ?? '';
		$opt_button_attr = $args['opt_button_attr'] ?? '';
		$opt_buttons_class = $args['opt_buttons_class'] ?? '';
		$opt_buttons_attr = $args['opt_buttons_attr'] ?? '';
		$attr = $args['attr'] ?? '';

		$class = esc_attr( 'o-radio__input u-h-i js-input' . ( $class ? " $class" : '' ) );
		$opt_button_class = esc_attr( 'o-radio__field' . ( $opt_button_class ? " $opt_button_class" : '' ) );

		if( $attr )
			$attr = " $attr";

		if( $opt_buttons_class )
			$opt_buttons_class = " $opt_buttons_class";

		$output = '';

		foreach( $options as $index => $o ) {
			$operator = $o['operator'] ?? false;
			$operator = $operator ? ' data-operator="' . $operator . '"' : '';

			$o_id = $id;
			$o_label = esc_html( $o['label'] );
			$o_value = esc_html( $o['value'] );

			$checked = ( $value && $o_value == $value ) ? ' checked' : '';

			$output .=
				'<div class="o-radio__item">' .
					'<label>' .
						"<input class='$class' type='$type' id='" . FRM::$namespace . '_' . uniqid() . "' name='$o_id' value='$o_value'$checked$operator$attr>" .
						"<div class='$opt_button_class'$opt_button_attr>" .
							"<div class='o-radio__label'>$o_label</div>" .
						'</div>' .
					'</label>' .
				'</div>';
		}

		return "<div class='o-radio$opt_buttons_class'$opt_buttons_attr>$output</div>";
	}

 /*
	* Output asset ( files, links.. ).
	*
	* @param array $args
	* @return string of markup
	*/

	public static function render_asset( $args = [] ) {
		$args = array_merge( [
			'upload' => true,
			'wp' => false,
			'class' => '',
			'type' => '',
			'value' => '',
			'accept' => '',
			'name' => '',
			'id' => '',
			'input_value' => ''
		], $args );

		extract( $args );

		$type = strtolower( $type );
		$type_cap = ucwords( $type );
		$exists = $value ? true : false;

		$url = '';
		$icon_text = '';
		$title = basename( parse_url( $value, PHP_URL_PATH ) );

		if( $upload ) {
			self::$localize_data['files'][] = [
				'id' => $id,
				'file_type' => $type
			];

			// check if from wp media library
			if( $wp ) {
				if( $type == 'image' ) {
					$input_value = $value;

					$wp_image = FRM::get_image( (int) $value, 'medium' );

					if( $wp_image ) {
						$title = basename( get_attached_file( (int) $value ) );
						$value = $wp_image['url'];
					} else {
						$exists = false;
						$value = '';
					}
				}
			} else {
				// check that file exists
				if( !file_exists( FRM::$uploads_dir . $title ) )
					$exists = false;
			}
		}

		if( $type == 'image' )
			$url = $value;

		if( $type == 'file' ) {
			$file_path_parts = pathinfo( $title );

			if( isset( $file_path_parts['extension'] ) )
				$icon_text = $file_path_parts['extension'];
		}

		if( $type == 'link' ) {
			$title = '';
			$target = '';

			$v = FRM::get_link( $value );

			if( $v ) {
				$icon_text = $v['text'];
				$title = $v['url'];
				$target = $v['target'];
			} else {
				$exists = false;
			}
		}

		if( !$input_value )
			$input_value = $value;

		$input_value = esc_html( $input_value );
		$accept = esc_attr( $accept );

		$class = esc_attr( 'o-asset' . ( $class ? " $class" : '' ) );

		return
			"<div class='$class'" . ( $wp && $upload ? " data-wp='true'" : '' ) . ">" .
				"<div class='o-asset__exists' style='display: " . ( $exists ? "block" : "none" ) . "'>" .
					"<div class='l-flex' data-align='center'>" .
						"<img class='o-asset__image' src='$url' alt='Asset preview'>" .
						"<div class='o-asset__icon'>$icon_text</div>" .
						(
							$type == 'link'
							?
							"<span class='o-asset__target' style='display: none;'>$target</span>" .
							"<a class='o-asset__name' href='$title' target='_blank'>$title</a>"
							:
							"<span class='o-asset__name'>$title</span>"
						) .
						"<div class='o-asset__right l-flex'>" .
							(
								$type == 'link'
								?
								"<button type='button' class='o-asset__edit'>" .
									"<span class='dashicons dashicons-edit'></span>" .
									"<span class='u-v-h'>Edit</span>" .
								"</button>"
								:
								''
							) .
							"<button type='button' class='o-asset__remove u-p-r'>" .
								"<span class='dashicons dashicons-no-alt'></span>" .
								"<span class='u-v-h'>Remove $type_cap</span>" .
								"<span class='o-asset__loader o-loader js-loader-remove' data-hide><span class='spinner is-active'></span></span>" .
							"</button>" .
						"</div>" .
					"</div>" .
				"</div>" .
				(
					$upload || $type == 'link'
					?
					"<div class='o-asset__no' style='display: " . ( $exists ? "none" : "block" ) . "'>" .
						"<p style='margin: 0'>" .
							"<label class='o-asset__select u-p-r" . ( is_admin() ? ' button add-media' : '' ) . "'>" .
								"<input type='" . ( $upload && !$wp ? 'file' : 'button' ) . "' aria-label='Select $type_cap' class='u-h-i' accept='$accept'>" .
								"<span>Select $type_cap</span>" .
								"<span class='o-asset__loader o-loader js-loader-select' data-hide><span class='spinner is-active'></span></span>" .
							"</label>" .
						"</p>" .
					"</div>"
					:
					''
				) .
				"<input class='o-asset__input' name='$name' id='$id' type='hidden' value='$input_value'>" .
			"</div>";
	}

 /*
	* Output listbox ( substitute for select ).
	*
	* @param array $args {
	*		@type string $options Accepts array {
	*			@type string $value Accepts string.
	*			@type string $label Accepts string.
	*			@type string $selected Accepts boolean.
	*		}
	*		@type string $id Accepts string.
	*		@type string $list_class Accepts string.
	* }
	* @return string of markup
	*/

	public static function render_listbox( $args = [] ) {
		$options = $args['options'] ?? [];
		$id = $args['id'] ?? FRM::$namespace . '_' . uniqid();

		$list_id = esc_attr( $id . '_list' );
		$list_class = esc_attr( $args['list_class'] ?? '' );

		// classes for list
		$list_classes = 'o-listbox__list js-input';

		if( $list_class )
			$list_classes .= " $list_class";

		$list_classes = esc_attr( $list_classes );

		if( count( $options ) == 0 )
			return '';

		$selected_index_label = '';
		$selected_index_id = '';
		$selected_index = 0;
		$options_output = '';

		foreach( $options as $index => $o ) {
			$v = esc_html( $o['value'] );
			$l = esc_html( $o['label'] );
			$s = $o['selected'] ?? false;

			$o_id = $id . '_' . $v;
			$selected = '';

			if( $s ) {
				$selected_index = $index;
				$selected_index_id = $o_id;
				$selected_index_label = $l;
				$selected = ' aria-selected="true"';
			}

			$options_output .= "<li class='o-listbox__item' id='$o_id' data-value='$v' role='option'$selected>$l</li>";
		}

		return
			'<div class="o-listbox">' .
				"<button class='o-listbox__btn l-flex' data-align='center' data-justify='def' type='button' aria-haspopup='listbox' aria-labelledby='$id' id='$id'>" .
					"<div class='o-listbox__text'>$selected_index_label</div>" .
					'<div class="o-listbox__caret"></div>' .
				"</button>" .
				'<div class="o-listbox__container">' .
					"<ul class='$list_classes' id='$list_id' tabindex='-1' role='listbox' aria-labelledby='$id' aria-activedescendant='$selected_index_id'>" .
						$options_output .
					'</ul>' .
				'</div>' .
			'</div>';
	}

 /*
	* Scripts and styles to enqueue.
	*/

	public static function scripts( $child = false ) {
		$path = FRM::$src_path . 'Common/assets/public/';
		$uri = $child ? get_stylesheet_directory_uri() : get_template_directory_uri();

		wp_enqueue_style(
			FRM::$namespace . '-field-styles',
			$uri . $path . 'css/field.css'
		);

		$upload_nonce_name = FRM::$namespace . '_upload_file_nonce';
		$remove_nonce_name = FRM::$namespace . '_remove_file_nonce';

		additional_script_data( FRM::$namespace, [
			$upload_nonce_name => wp_create_nonce( $upload_nonce_name ),
			$remove_nonce_name => wp_create_nonce( $remove_nonce_name )
		], true );

		wp_enqueue_media();

		wp_enqueue_script(
			FRM::$namespace . '-field-script',
			$uri . $path . 'js/field.js',
			[],
			NULL,
			true
		);
	}

 /*
	* Ajax action and callback to upload files.
	*/

	public static function file_actions() {
		add_action( 'wp_ajax_upload_file', function() {
			try {
				// check upload nonce
				if( !check_ajax_referer( FRM::$namespace . '_upload_file_nonce', FRM::$namespace . '_upload_file_nonce', false ) )
					throw new \Exception( 'Not allowed' );

				new File_Upload( [
					'uploads_dir' => FRM::$uploads_dir,
					'uploads_url' => FRM::$uploads_url,
					'success' => function( $data ) {
						echo json_encode( $data );
					},
					'error' => function( $err ) {
						throw new \Exception( $err );
					}
				] );

				exit;
			} catch( \Exception $e ) {
				echo $e->getMessage();
				header( http_response_code( 500 ) );
				exit;
			}
		} );

		add_action( 'wp_ajax_remove_file', function() {
			try {
				// check upload nonce
				if( !check_ajax_referer( FRM::$namespace . '_remove_file_nonce', FRM::$namespace . '_remove_file_nonce', false ) )
					throw new \Exception( 'Not allowed' );

				if( !isset( $_POST['file_path'] ) )
					throw new \Exception( 'No file path' );

				unlink( $_POST['file_path'] );

				echo json_encode( 'File successfully deleted.' );

				exit;
			} catch( \Exception $e ) {
				echo $e->getMessage();
				header( http_response_code( 500 ) );
				exit;
			}
		} );
	}

} // end Field
