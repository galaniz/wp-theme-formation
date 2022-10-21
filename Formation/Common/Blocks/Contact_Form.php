<?php
/**
 * Contact form block
 *
 * @package wp-theme-formation
 */

namespace Formation\Common\Blocks;

/**
 * Imports
 */

use Formation\Formation as FRM;
use Formation\Common\Field\Field;
use Formation\Common\Blocks\Blocks;

/**
 * Class
 */

class Contact_Form {

	/**
	 * Variables
	 *
	 * Args for contact form and field blocks.
	 *
	 * @var array $blocks
	 * @see class Blocks for args breakdown.
	 */

	public static $blocks = [
		'contact-form'       => [
			'attr'             => [
				'id'             => ['type' => 'string'],
				'type'           => ['type' => 'string'],
				'email'          => ['type' => 'string'],
				'subject'        => ['type' => 'string'],
				'submit_label'   => ['type' => 'string'],
				'success_title'  => ['type' => 'string'],
				'success_text'   => ['type' => 'string'],
				'field_gap'      => ['type' => 'string'],
				'mailchimp_list' => ['type' => 'string'],
			],
			'default'          => [
				'id'             => '',
				'type'           => 'contact',
				'email'          => '',
				'subject'        => '',
				'submit_label'   => 'Send',
				'success_title'  => '',
				'success_text'   => '',
				'field_gap'      => '',
				'mailchimp_list' => '',
			],
			'provides_context' => [
				'contact-form/type' => 'type',
			],
			'render'           => [__CLASS__, 'render_contact_form'],
			'handle'           => 'contact_form',
			'script'           => 'contact-form/form.js',
		],
		'contact-form-group' => [
			'attr'             => [
				'legend'          => ['type' => 'string'],
				'required'        => ['type' => 'boolean'],
				'empty_message'   => ['type' => 'string'],
				'invalid_message' => ['type' => 'string'],
			],
			'default'          => [
				'legend'          => '',
				'required'        => false,
				'empty_message'   => '',
				'invalid_message' => '',
			],
			'provides_context' => [
				'contact-form-group/required'        => 'required',
				'contact-form-group/empty_message'   => 'empty_message',
				'contact-form-group/invalid_message' => 'invalid_message',
			],
			'uses_context'     => [
				'contact-form/type',
			],
			'render'           => [__CLASS__, 'render_contact_form_group'],
			'handle'           => 'contact_form_group',
			'script'           => 'contact-form/group.js',
		],
		'contact-form-field' => [
			'attr'         => [
				'id'                => ['type' => 'string'],
				'type'              => ['type' => 'string'],
				'name'              => ['type' => 'string'],
				'label'             => ['type' => 'string'],
				'required'          => ['type' => 'boolean'],
				'value'             => ['type' => 'string'],
				'options'           => ['type' => 'string'],
				'selected'          => ['type' => 'boolean'],
				'placeholder'       => ['type' => 'string'],
				'rows'              => ['type' => 'string'],
				'width'             => ['type' => 'string'],
				'classes'           => ['type' => 'string'],
				'empty_message'     => ['type' => 'string'],
				'invalid_message'   => ['type' => 'string'],
				'conditional'       => ['type' => 'string'],
				'mailchimp_consent' => ['type' => 'boolean'],
				'merge_field'       => ['type' => 'string'],
				'tag'               => ['type' => 'boolean'],
			],
			'default'      => [
				'id'                => '',
				'type'              => 'text',
				'name'              => '',
				'label'             => '',
				'required'          => false,
				'value'             => '',
				'options'           => '',
				'selected'          => false,
				'placeholder'       => '',
				'rows'              => '',
				'width'             => '',
				'classes'           => '',
				'empty_message'     => '',
				'invalid_message'   => '',
				'conditional'       => '',
				'mailchimp_consent' => false,
				'merge_field'       => '',
				'tag'               => false,
			],
			'uses_context' => [
				'contact-form/type',
				'contact-form-group/required',
				'contact-form-group/empty_message',
				'contact-form-group/invalid_message',
			],
			'render'       => [__CLASS__, 'render_contact_form_field'],
			'handle'       => 'contact_form_field',
			'script'       => 'contact-form/field.js',
		],
	];

	/**
	 * Constructor
	 */

	public function __construct() {
		/* Add blocks */

		add_action( 'init', [$this, 'register_blocks'] );
	}

	/**
	 * Pass blocks to Blocks class
	 */

	public function register_blocks() {
		foreach ( self::$blocks as $name => $b ) {
			$b['frm']                = true;
			Blocks::$blocks[ $name ] = $b;
		}
	}

	/**
	 * Helpers
	 *
	 * Get associative array from string of key value pairs.
	 *
	 * @param string $str
	 * @param string $order of key value pairs.
	 * @return array
	 */

	public static function get_assoc_array_from_str( $str = '', $order = 'key:value', $indexed = false ) {
		if ( ! $str ) {
			return [];
		}

		$array = [];

		$a = explode( "\n", $str );

		if ( isset( $a[0] ) ) {
			foreach ( $a as $b ) {
				$c = explode( ' : ', $b );

				if ( isset( $c[0] ) && isset( $c[1] ) ) {
					$key   = $c[0];
					$value = $c[1];

					if ( $indexed ) {
						$array[] = [
							'label' => $key,
							'value' => $value,
						];
					} else {
						if ( 'key:value' === $order ) {
							$array[ $key ] = $value;
						} else {
							$array[ $value ] = $key;
						}
					}
				}
			}
		}

		return $array;
	}

	/**
	 * Render callbacks
	 *
	 * Output contact form.
	 *
	 * @param array $attributes
	 * @param string $content
	 * @return string of markup
	 */

	public static function render_contact_form( $attributes, $content = '', $block ) {
		$attr = array_replace_recursive( self::$blocks['contact-form']['default'], $attributes );

		[
			'id'             => $id,
			'type'           => $type,
			'email'          => $email,
			'subject'        => $subject,
			'submit_label'   => $submit_label,
			'success_title'  => $success_title,
			'success_text'   => $success_text,
			'field_gap'      => $field_gap,
			'mailchimp_list' => $mailchimp_list,
		] = $attr;

		if ( ! $email ) {
			$email = get_option( 'admin_email', '' );
		}

		if ( ! $subject ) {
			$subject = get_bloginfo( 'name' ) . ' Contact Form';
		}

		if ( ! $id ) {
			$id = uniqid();
		}

		if ( $id ) {
			/* Make sure $id not greater than 64 characters */

			$id = substr( $id, 0, 40 );

			$option_args = [];

			if ( 'contact' === $type || 'contact-mailchimp' === $type ) {
				$option_args['email']   = $email;
				$option_args['subject'] = $subject;
			}

			if ( 'mailchimp' === $type || 'contact-mailchimp' === $type ) {
				$option_args['mailchimp_list'] = $mailchimp_list;
			}

			update_option( FRM::$namespace . '_form_' . $id, $option_args );
		}

		$args = [
			'form_class'         => '',
			'form_attr'          => [],
			'form_id'            => $id,
			'form_data_type'     => $type,
			'fields'             => $content,
			'fields_class'       => '',
			'fields_attr'        => [],
			'button_field_class' => '',
			'button_class'       => '',
			'button_attr'        => [],
			'button_label'       => $submit_label,
			'error_message'      => [
				'primary'   => '',
				'secondary' => '',
			],
			'success_message'    => [
				'primary'   => $success_title,
				'secondary' => $success_text,
			],
		];

		$args = apply_filters( 'formation_contact_form_args', $args, $attr );

		/* Output */

		return FRM::render_form( $args );
	}

	public static function render_contact_form_group( $attributes, $content, $block ) {
		$attr = array_replace_recursive( self::$blocks['contact-form-group']['default'], $attributes );

		[
			'legend'          => $legend,
			'required'        => $required,
			'empty_message'   => $empty_message,
			'invalid_message' => $invalid_message,
		] = $attr;

		$legend_id = uniqid();

		/* Filter classes */

		$classes = [
			'container_class' => '',
			'fieldset_class'  => '',
			'fields_class'    => '',
		];

		$classes = apply_filters( 'formation_contact_form_group_classes', $classes, $attr, $block );

		[
			'container_class' => $container_class,
			'fieldset_class'  => $fieldset_class,
			'fields_class'    => $fields_class,
		] = $classes;

		if ( $container_class ) {
			$container_class = " class='$container_class'";
		}

		if ( $fieldset_class ) {
			$fieldset_class = " class='$fieldset_class'";
		}

		if ( $fields_class ) {
			$fields_class = " class='$fields_class'";
		}

		/* Required */

		$req_attr = '';

		if ( $required ) {
			$req_attr = ' data-required';
		}

		/* Output */

		return (
			"<div$container_class>" .
				"<fieldset$fieldset_class>" .
					"<legend id='$legend_id'$req_attr><span>$legend</span></legend>" .
					"<div$fields_class>" .
						$content .
					'</div>' .
				'</fieldset>' .
			'</div>'
		);
	}

	public static function render_contact_form_field( $attributes, $content, $block ) {
		$attributes = array_replace_recursive( self::$blocks['contact-form-field']['default'], $attributes );

		[
			'id'                => $id,
			'type'              => $type,
			'name'              => $name,
			'label'             => $label,
			'required'          => $required,
			'value'             => $value,
			'options'           => $options,
			'selected'          => $selected,
			'placeholder'       => $placeholder,
			'rows'              => $rows,
			'width'             => $width,
			'classes'           => $classes,
			'empty_message'     => $empty_message,
			'invalid_message'   => $invalid_message,
			'conditional'       => $conditional,
			'mailchimp_consent' => $mailchimp_consent,
			'merge_field'       => $merge_field,
			'tag'               => $tag,
		] = $attributes;

		$output = '';
		$attr   = [];
		$prefix = FRM::$namespace . '_';

		/* Field args */

		$field = [
			'type'        => $type,
			'name'        => FRM::$namespace . '_' . $name,
			'label'       => $label,
			'value'       => $value,
			'placeholder' => $placeholder,
			'class'       => $classes,
		];

		if ( $id ) {
			$field['id'] = $id;
		}

		$group_required = $block->context[ FRM::$namespace . '/contact-form-group/required' ] ?? false;

		if ( 'radio' === $type || 'radio-group' === $type || 'radio-select' === $type || 'radio-text' === $type || 'checkbox' === $type || 'checkbox-group' === $type ) {
			$field['label_first'] = false;

			if ( $group_required ) {
				$group_empty   = $block->context[ FRM::$namespace . '/contact-form-group/empty_message' ] ?? false;
				$group_invalid = $block->context[ FRM::$namespace . '/contact-form-group/invalid_message' ] ?? false;

				$attr['data-aria-required'] = 'true';

				if ( $group_empty ) {
					$attr['data-empty-message'] = $group_empty;
				}

				if ( $group_invalid ) {
					$attr['data-invalid-message'] = $group_invalid;
				}
			}
		} else {
			if ( $group_required ) {
				$attr['aria-required'] = 'true';
			}
		}

		/* Attributes */

		if ( $required ) {
			$attr['aria-required'] = 'true';
		}

		if ( $rows && 'textarea' === $type ) {
			$attr['rows'] = $rows;
		}

		if ( $empty_message ) {
			$attr['data-empty-message'] = $empty_message;
		}

		if ( $invalid_message ) {
			$attr['data-invalid-message'] = $invalid_message;
		}

		if ( $conditional ) {
			$field['field_attr'] = [
				'style'        => 'display:none',
				'data-display' => $conditional,
			];

			$field['field_class'] = 'js-conditional';
		}

		/* Mailchimp attributes */

		$form_type = $block->context[ FRM::$namespace . '/contact-form/type' ] ?? false;

		if ( 'mailchimp' === $form_type || 'contact-mailchimp' === $form_type ) {
			if ( $mailchimp_consent ) {
				$attr['data-mailchimp-consent'] = 'true';
			}

			if ( $merge_field ) {
				$attr['data-merge-field'] = $merge_field;
			}

			if ( $tag ) {
				$attr['data-tag'] = 'true';
			}
		}

		$field['attr'] = $attr;

		/* Options */

		if ( $options ) {
			$order   = 'select' === $type ? 'value:key' : 'key:value';
			$indexed = 'select' === $type ? false : true;

			$options = self::get_assoc_array_from_str( $options, $order, $indexed );

			$field['options'] = $options;
		}

		/* Filter args */

		$field = apply_filters( 'formation_contact_form_field_args', $field, $attributes, $block );

		/* Output */

		$render_args = [
			'fields' => ( isset( $field[0] ) ? $field : [$field] ),
		];

		if ( 'radio' === $type || 'radio-select' === $type || 'radio-text' === $type || 'checkbox' === $type ) {
			$render_args['data'] = $selected ? $value : '';
		}

		if ( $value && ( 'radio-group' === $type || 'checkbox-group' === $type || 'select' === $type ) ) {
			$render_args['data'] = $value;
		}

		Field::render( $render_args, $output );

		return $output;
	}

} // End Contact_Form
