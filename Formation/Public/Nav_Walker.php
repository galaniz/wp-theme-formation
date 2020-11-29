<?php 

/*
 * Customize wp nav html output
 * ----------------------------
 */

namespace Formation\Public;

class Nav_Walker extends \Walker_Nav_Menu {

  /* Variables */

  public $ul_class;
  public $li_class;
  public $li_attr;
  public $a_class;
  public $a_attr;
  public $before_output;
  public $after_output;
  public $before_link_output;
  public $before_link_text_output;
  public $after_link_text_output;

  /* Constructor */
  
  public function __construct( $args ) {
    $this->ul_class = $args['ul_class'] ?? '';
    $this->li_class = $args['li_class'] ?? '';
    $this->li_attr = $args['li_attr'] ?? '';
    $this->a_class = $args['a_class'] ?? '';
    $this->a_attr = $args['a_attr'] ?? '';
    $this->before_output = $args['before_output'] ?? false;
    $this->after_output = $args['after_output'] ?? false;
    $this->before_link_output = $args['before_link_output'] ?? false;
    $this->before_link_text_output = $args['before_link_text_output'] ?? false;
    $this->after_link_text_output = $args['after_link_text_output'] ?? false;
  }

  /* Output ul element */

  public function start_lvl( &$output, $depth = 0, $args = [] ) {
    $classes = ['sub-menu'];

    if( $this->ul_class )
      $classes[] = $this->ul_class;

    $class_names = join( ' ', apply_filters( 'nav_menu_submenu_css_class', $classes, $args, $depth ) );
    $class_names = $class_names ? ' class="' . esc_attr( $class_names ) . '"' : '';

    $output .= "<ul$class_names data-depth='$depth'>";
  }

  /* Output li element */

  public function start_el( &$output, $item, $depth = 0, $args = [], $id = 0 ) {
    $title = $item->title;
    $permalink = $item->url;
    $id = $item->ID;

    if( is_callable( $this->before_output ) )
      call_user_func_array( $this->before_output, [&$this, &$output, $depth, $args, $item] );

    $classes = empty( $item->classes ) ? [] : (array) $item->classes;
    $classes[] = 'menu-item-' . $item->ID;
    $classes[] = $this->li_class;

    $class_names = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item, $args, $depth ) );

    $output .= "<li class='$class_names'" . $this->li_attr . " data-depth='$depth'>";

    if( is_callable( $this->before_link_output ) )
      call_user_func_array( $this->before_link_output, [&$this, &$output, $depth, $args, $item] );

    $output .= "<a class='" . $this->a_class . "' " . $this->a_attr . "href='$permalink' data-depth='$depth'>";

    if( is_callable( $this->before_link_text_output ) )
      call_user_func_array( $this->before_link_text_output, [&$this, &$output, $depth, $args, $item] );

    $output .= $title; 

    if( is_callable( $this->after_link_text_output ) )
      call_user_func_array( $this->after_link_text_output, [&$this, &$output, $depth, $args, $item] );

    $output .= "</a>";

    if( is_callable( $this->after_output ) )
      call_user_func_array( $this->after_output, [&$this, &$output, $depth, $args, $item] );
  }

} // end Nav_Walker
