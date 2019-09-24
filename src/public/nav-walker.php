<?php 

/*
 * Customize wp nav html output
 * ----------------------------
 */

namespace Formation\Pub;

class Nav_Walker extends \Walker_Nav_Menu {

    /* Variables */

    public $li_class;
    public $li_attr;
    public $a_class;
    public $a_attr;
    public $before_output;
    public $after_output;

    /* Constructor */
    
    public function __construct( $args ) {
        $this->li_class = $args['li_class'] ?? '';
        $this->li_attr = $args['li_attr'] ?? '';
        $this->a_class = $args['a_class'] ?? '';
        $this->a_attr = $args['a_attr'] ?? '';
        $this->before_output = $args['before_output'] ?? false;
        $this->after_output = $args['after_output'] ?? false;
    }

    /* Output li element */

    public function start_el( &$output, $item, $depth = 0, $args = [], $id = 0 ) {
        $title = $item->title;
        $permalink = $item->url;
        $id = $item->ID;

        if( is_callable( $this->before_output ) )
            call_user_func_array( $this->before_output, [&$this, &$output] );

        $classes = empty( $item->classes ) ? [] : (array) $item->classes;
        $classes[] = 'menu-item-' . $item->ID;
        $classes[] = $this->li_class;

        $class_names = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item, $args, $depth ) );

        $output .= 
            "<li class='$class_names'" . $this->li_attr . ">" .
                "<a class='" . $this->a_class . "' " . $this->a_attr . "href='$permalink'>$title</a>";

        if( is_callable( $this->after_output ) )
            call_user_func_array( $this->after_output, [&$this, &$output] );
    }

} // end Nav_Walker
