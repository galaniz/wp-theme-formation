<?php

/*
 * Render fields in frontend and admin
 * -----------------------------------
 */

namespace FG\Common;

class Field {

   /*
    * Variables
    * ---------
    */
   
    // default arguments
    private static $default = [
        'render' => [
            'fields' => [],
            'index' => 0,
            'data' => [],
            'value' => false,
            'multi' => false,
            'copy' => false
        ],
        'field' => [
            'name' => false,
            'type' => 'text',
            'label' => false,
            'label_hidden' => false,
            'attr' => [],
            'field_class' => '',
            'class' => '',
            'placeholder' => '',
            'options' => [],
            'hidden' => false,
            'before' => '',
            'after' => ''
        ]
    ];

    // multi buttons markup
    public static $multi_buttons = [
        'add' => 
            '<button type="button" class="o-multi__button --add" data-type="add">' .
                '<span class="u-visually-hidden">Add Input</span>' .
                '<span class="dashicons dashicons-plus"></span>' .
            '</button>',
        'remove' => 
            '<button type="button" class="o-multi__button --remove" data-type="remove">' .
                '<span class="u-visually-hidden">Remove Input</span>' .
                '<span class="dashicons dashicons-minus"></span>' .
            '</button>'
    ];

   /*
    * Helper methods
    * --------------
    */

    // for getting saved value(s)
    public static function get_top_level_name( $name ) {
        if( strpos( $name, '[%i]' ) !== false )
            $name = explode( '[', $name )[0];

        if( strpos( $name, '[]' ) !== false )
            $name = explode( '[]', $name )[0];

        if( strpos( $name, '[' ) !== false )
            $name = explode( '[', $name )[0];

        return $name;
    }

    // replace %i with actual index
    public static function index_name( $name, $index ) {
        return str_replace( 
            array( '%i' ),
            array( $index ),
            $name 
        );
    }

    // get value in data array from name ( eg. lorem[%i][ipsum] )
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
    * Render methods
    * --------------
    */

    // render field markup
    public static function render( $args, &$output ) {
        $args = array_replace_recursive( self::$default['render'], $args );
        extract( $args );

        if( $multi ) {
            $output .= 
                '<div class="o-multi__item">' .
                    '<div class="o-multi__fields l-flex --wrap">';
        }

        foreach( $fields as $f ) {
            $f = array_replace_recursive( self::$default['field'], $f );

            $name = $multi && !$copy ? self::index_name( $f['name'], $index ) : $f['name'];
            $type = $f['type'];
            $classes = 'o-field__' . $type . ' js-input ' . $f['class'];
            $placeholder = '';
            $req = '';
            $attr = [];

            if( is_array( $value ) ) {
                $val = self::get_array_value( $data, $name );
            } else {
                $val = $value;
            }

            if( count( $f['attr'] ) > 0 ) {
                foreach( $f['attr'] as $a => $v ) {
                    $attr[] = $a . '="' . $v . '"';

                    if( $a == 'aria-required' && $v == 'true' )
                       $req = ' --req'; 
                }
            }

            $attr = implode( ' ', $attr );

            $hidden = $f['hidden'] && !$val ? 'style="display: none;"' : '';

            $output .= 
                "<div class='o-field" . ( $f['field_class'] ? ' ' . $f['field_class'] : '' ) . "' $hidden>";

            if( $f['label'] && !$f['label_hidden'] )
                $output .= "<label><div class='o-field__label$req'>" . $f['label'] . "</div>";

            if( $f['placeholder'] )
                $placeholder = 'placeholder="' . $f['placeholder'] . '"';

            $output .= $f['before'];

            // check which type of field
            switch( $type ) {
                case 'text':
                case 'email':
                case 'checkbox':
                case 'radio':
                case 'number':
                    if( $type !== 'checkbox' && $type !== 'number' )
                        $classes .= ' regular-text';

                    if( $type === 'number' )
                        $classes .= ' small-text';

                    if( $type === 'text' || $type === 'email' )
                        $classes .= ' o-field__input';
                    
                    $checked = '';
                    $v = $val;

                    if( $type === 'checkbox' || $type === 'radio' ) {
                        if( $val == $f['value'] ) 
                            $checked = 'checked';

                        $v = $f['value'];
                    }

                    $output .= sprintf( 
                        '<input name="%1$s" id="%1$s" type="%2$s" value="%4$s" class="%5$s" %6$s %7$s %3$s>', 
                        $name, 
                        $type, 
                        $placeholder, 
                        $v,
                        $classes,
                        $checked,
                        $attr
                    );

                    break;
                case 'textarea':
                    $output .= sprintf(
                        '<textarea name="%1$s" id="%1$s" class="%2$s" %4$s>%3$s</textarea>', 
                        $name, 
                        $classes,
                        $val,
                        $attr
                    );

                    break;
                case 'select': 
                    if( count( $f['options'] ) > 0 ) {
                        $opt = '';

                        foreach( $f['options'] as $key => $label ) {
                            $selected = $key == $val ? 'selected' : '';

                            $opt .= sprintf( 
                                '<option value="%s" %s>%s</option>', 
                                $key, 
                                $selected, 
                                $label 
                            );
                        }

                        $output .= sprintf(
                            '<select name="%1$s" id="%1$s" class="%3$s" %4$s>%2$s</select>', 
                            $name, 
                            $opt,
                            $classes,
                            $attr
                        );
                    }

                    break;
            }

            $output .= $f['after'];

            if( $f['label'] && !$f['label_hidden'] )
                $output .= '</label>';

            $output .= '</div>';
        }

        if( $multi ) {
            $output .= 
                '</div>' .
                '<div class="o-multi__buttons l-flex">' . 
                    self::$multi_buttons['add'];

            if( $index > 0 || $copy )
                $output .= self::$multi_buttons['remove'];

            $output .= 
                    '</div>' . 
                '</div>';
        }
    }

    // render listbox ( substitute for select )
    public static function render_listbox( $args = [] ) {
        $options = $args['options'] ?? [];
        $id = $args['id'] ?? 'fg_' . uniqid();

        $list_id = $id . '_list';
        $list_class = $args['list_class'] ?? '';

        // classes for list
        $list_classes = 'o-listbox__list js-input';

        if( $list_class )
            $list_classes .= " $list_class";

        if( count( $options ) == 0 )
            return '';

        $selected_index_label = '';
        $selected_index_id = '';
        $selected_index = 0;
        $options_output = '';

        foreach( $options as $index => $o ) {
            $v = $o['value'];
            $l = $o['label'];
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

        $caret = 
            '<svg class="o-listbox__caret u-flex-shrink-0" width="15" height="9" viewBox="0 0 15 9">' .
                '<use xlink:href="#sprite-caret" />' .
            '</svg>';

        return 
            '<div class="o-listbox">' .
                "<button class='o-listbox__btn l-flex --align-center --justify' type='button' aria-haspopup='listbox' aria-labelledby='$id' id='$id'><div class='o-listbox__text u-flex-shrink-0'>$selected_index_label</div>$caret</button>" . 
                '<div class="o-listbox__container">' .
                    "<ul class='$list_classes' id='$list_id' tabindex='-1' role='listbox' aria-labelledby='$id' aria-activedescendant='$selected_index_id'>" . 
                        $options_output . 
                    '</ul>' .
                '</div>' .
            '</div>';
    }

} // end Field
