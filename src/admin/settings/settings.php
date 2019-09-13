<?php

/*
 * Settings field generation
 * -------------------------
 */

namespace Formation\Admin\Settings;

/*
 * Imports
 * -------
 */

use Formation\Formation as FRM;  
use \Formation\Common\Field\Field;
use function \Formation\write_log;

class Settings {

   /*
    * Variables
    * ---------
    *
    * Store fields.
    *
    * @var array $fields
    * @see \Formation\Common\Field for default properties.
    * 
    * Note: section and on_save additional properties.
    */

   	private $fields = [];

   /*
    * Store sections.
    *
    * @var array $sections
    */

   	private $sections = [];

   /*
    * Page to register setting.
    *
    * @var string $page
    */

  	private $page = '';

   /*
    * Organize as tabs.
    *
    * @var boolean $tabs
    */

  	private $tabs = false;

   /*
    * Tabs navigation html output.
    *
    * @var string $tab_nav
    */

  	private $tab_nav = '';

   /*
    * Constructor
    * -----------
    */

	public function __construct( $args = [] ) {
		$args = array_merge( [
			'fields' => [],
			'sections' => [],
			'page' => '',
			'tabs' => false
		], $args );

		extract( $args );

		if( !$fields || !$page )
			return;

		$this->fields = $fields;
		$this->sections = $sections;
		$this->page = $page;
		$this->tabs = $tabs;

		$this->setup();
	}

   /*
    * Register and render fields
    * --------------------------
    */

	public function setup() {
		$fields = $this->fields;
		$sections = $this->sections;

		/* Check for tabs. Filter fields and sections by active tab. */

		$tabs = [];
		$section_ids = [];

		if( $this->tabs ) {
			$current_tab = filter_input( INPUT_GET, 'tab', FILTER_SANITIZE_STRING ) ?? false;

			/* Get tabs from fields and create nav */

			foreach( $fields as $i => $field ) {
				$tab = $field['tab'] ?? '';

				if( !$tab )
					continue;

				$formatted_tab = str_replace( ' ', '-', strtolower( $tab ) );

				if( !in_array( $tab, $tabs ) ) {
					if( $i === 0 && !$current_tab )
						$current_tab = $formatted_tab;

					$tabs[$tab] = $formatted_tab;
				} 
			}

			$this->tab_nav = '<nav class="nav-tab-wrapper wp-clearfix" aria-label="Secondary menu">';

			foreach( $tabs as $t => $tt ) {
				$url = admin_url( 'options-general.php?page=' . $this->page . '&tab=' . $tt );
				$active = '';
				$aria = '';

				if( $current_tab === $tt ) {
					$active = " nav-tab-active";
					$aria = " aria-current='page'";
				}

				$this->tab_nav .= "<a href='$url' class='nav-tab$active'$aria>" . $t . "</a>";
			}

			$this->tab_nav .= '</nav>';

			$fields = array_map( function( $v ) use ( $current_tab, $tabs, &$section_ids ) {
				$tab = $v['tab'] ?? '';
				$section = $v['section'] ?? '';

				if( !$tab )
					return $v;

				$formatted_tab = $tabs[$tab];

				if( $current_tab === $formatted_tab )
					if( $section )
						$section_ids[] = $section;

				return $v;
			}, $fields );

			if( !$fields )
				$fields = $this->fields;
		}

		/* Add sections */

        foreach( $sections as $section ) {
        	$section_id = $section['id'];
        	$callback = $section['callback'] ?? false;

        	if( $this->tabs ) {
        		$callback = function() use ( $section_id, $section_ids ) {
        			$hide = '';

        			if( !in_array( $section_id, $section_ids ) ) 
        				$hide = ' style="display: none;">';

        			echo "</div><div class='js-section'$hide>";
        		};
        	}

            add_settings_section( 
                $section_id,
                $section['title'], 
                $callback,
                $section['page'] ?? $this->page
            );
        }

        /* Add fields */

	    foreach( $fields as $field ) {
	    	$name = FRM::get_namespaced_str( $field['name'] );
	    	$top_level_name = Field::get_top_level_name( $name );
	    	$register_args = [];

	    	if( !isset( $field['fields'] ) )
	    		$field['label_hidden'] = true;

	    	if( isset( $field['on_save'] ) ) {
	    		if( is_callable( $field['on_save'] ) ) {
	    			$register_args['sanitize_callback'] = $field['on_save'];
	    		}
	    	}

	    	register_setting( 
	    		$this->page, 
	    		$top_level_name, 
	    		$register_args 
		    );

	        add_settings_field( 
	        	$name, 
	        	$field['label'] ?? '', 
	        	function( $args ) use ( $top_level_name ) { // $args = $field
	        		$output = '';

	        		$args['data'] = [
	        			$top_level_name => get_option( $top_level_name, '' )
	        		];
	        		
	        		Field::render( $args, $output );

	        		echo $output;
	        	}, 
	        	$this->page, 
	        	$field['section'], 
	        	$field 
	        );
	    }
	}

   /*
    * Get tabs navigation
    * -------------------
    *
    * @return string of output
    */

   	public function get_tab_nav() {
   		return $this->tab_nav;
   	}

} // end Settings
