<?php
/**
 * Settings field generation
 *
 * @package wp-theme-formation
 */

namespace Formation\Admin\Settings;

/**
 * Imports
 */

use Formation\Formation as FRM;
use Formation\Common\Field\Field;

/**
 * Class
 */

class Settings {

	/**
	 * Store fields.
	 *
	 * Note: section and on_save additional properties.
	 *
	 * @var array $fields
	 * @see \Formation\Common\Field for default properties.
	 */

	private $fields = [];

	/**
	 * Store sections.
	 *
	 * @var array $sections
	 */

	private $sections = [];

	/**
	 * Page to register setting.
	 *
	 * @var string $page
	 */

	private $page = '';

	/**
	 * Organize as tabs.
	 *
	 * @var boolean $tabs
	 */

	private $tabs = false;

	/**
	 * Tabs navigation html output.
	 *
	 * @var string $tab_nav
	 */

	private $tab_nav = '';

	/**
	 * Constructor
	 */

	public function __construct( $args = [] ) {
		$args = array_merge(
			[
				'fields'   => [],
				'sections' => [],
				'page'     => '',
				'tabs'     => false,
			],
			$args
		);

		[
			'fields'   => $fields,
			'sections' => $sections,
			'page'     => $page,
			'tabs'     => $tabs,
		] = $args;

		if ( ! $fields || ! $page ) {
			return;
		}

		$this->fields   = $fields;
		$this->sections = $sections;
		$this->page     = $page;
		$this->tabs     = $tabs;

		$this->setup();
	}

	/**
	 * Register and render fields
	 */

	public function setup() {
		$fields   = $this->fields;
		$sections = $this->sections;

		/* Check for tabs. Filter fields and sections by active tab. */

		$tabs        = [];
		$section_ids = [];

		if ( $this->tabs ) {
			$current_tab = filter_input( INPUT_GET, 'tab', FILTER_SANITIZE_STRING ) ?? false;

			/* Get tabs from fields and create nav */

			foreach ( $fields as $i => $field ) {
				$tab = $field['tab'] ?? '';

				if ( ! $tab ) {
					continue;
				}

				$formatted_tab = str_replace( ' ', '-', strtolower( $tab ) );

				if ( ! in_array( $tab, $tabs, true ) ) {
					if ( 0 === $i && ! $current_tab ) {
						$current_tab = $formatted_tab;
					}

					$tabs[ $tab ] = $formatted_tab;
				}
			}

			$this->tab_nav = '<nav class="nav-tab-wrapper wp-clearfix" aria-label="Secondary menu">';

			foreach ( $tabs as $t => $tt ) {
				$url    = admin_url( 'options-general.php?page=' . $this->page . '&tab=' . $tt );
				$active = '';
				$aria   = '';

				if ( $current_tab === $tt ) {
					$active = ' nav-tab-active';
					$aria   = " aria-current='page'";
				}

				$this->tab_nav .= "<a href='$url' class='nav-tab$active'$aria>" . $t . '</a>';
			}

			$this->tab_nav .= '</nav>';

			$fields = array_map(
				function( $v ) use ( $current_tab, $tabs, &$section_ids ) {
					$tab     = $v['tab'] ?? '';
					$section = $v['section'] ?? '';

					if ( ! $tab ) {
						return $v;
					}

					$formatted_tab = $tabs[ $tab ];

					if ( $current_tab === $formatted_tab ) {
						if ( $section ) {
							$section_ids[] = $section;
						}
					}

					return $v;
				},
				$fields
			);

			if ( ! $fields ) {
				$fields = $this->fields;
			}
		}

		/* Add sections */

		foreach ( $sections as $section ) {
			$section_id = $section['id'];
			$callback   = $section['callback'] ?? false;

			if ( $this->tabs ) {
				$callback = function() use ( $section_id, $section_ids ) {
					$hide = '';

					if ( ! in_array( $section_id, $section_ids, true ) ) {
						$hide = ' style="display: none;"';
					}

					/* phpcs:ignore */
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

		foreach ( $fields as $field ) {
			$name           = FRM::get_namespaced_str( $field['name'] );
			$top_level_name = Field::get_top_level_name( $name );
			$label          = $field['label'] ?? '';
			$type           = $field['type'] ?? 'text';
			$register_args  = [];

			if ( $label && isset( $field['helper'] ) ) {
				$label .= '<p class="u-helper">' . $field['helper'] . '</p>';
			}

			if ( ! isset( $field['fields'] ) ) {
				$field['label_hidden'] = true;
			}

			if ( isset( $field['on_save'] ) ) {
				if ( is_callable( $field['on_save'] ) ) {
					$register_args['sanitize_callback'] = $field['on_save'];
				}
			} else {
				if ( 'file' !== $type ) {
					if ( 'email' === $type ) {
						$register_args['sanitize_callback'] = function( $value ) {
							return sanitize_email( $value );
						};
					} else {
						$register_args['sanitize_callback'] = function( $value ) {
							return sanitize_text_field( $value );
						};
					}
				}
			}

			register_setting(
				$this->page,
				$top_level_name,
				$register_args
			);

			add_settings_field(
				$name,
				$label,
				function( $args ) use ( $top_level_name ) {
					$output = '';

					$args['data'] = [
						$top_level_name => get_option( $top_level_name, '' ),
					];

					Field::render( $args, $output );

					/* phpcs:ignore */
					echo $output;
				},
				$this->page,
				$field['section'],
				$field
			);
		}
	}

	/**
	 * Get tabs navigation
	 *
	 * @return string of output
	 */

	public function get_tab_nav() {
		return $this->tab_nav;
	}

} // End Settings
