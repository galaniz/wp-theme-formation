<?php

/*
 * Theme settings
 * --------------
 */

namespace Formation\Admin\Settings;

/*
 * Imports
 * -------
 */

use Formation\Formation as FRM;
use Formation\Utils_Optional;

use Formation\Common\Field\Field;
use Formation\Common\Field\Select_Fields;
use Formation\Admin\Settings\Settings;

use function Formation\write_log;
use function Formation\additional_script_data;

class Theme {

 /*
	* Variables
	* ---------
	*
	* Menu page.
	*
	* @var string $page
	*/

	private $page = 'theme';

 /*
	* Settings page hook. To filter when enqueing scripts.
	*
	* @var string $page_hook
	*/

	private $page_hook = '';

 /*
	* User capabilities to view / edit
	*
	* @var string $user_cap
	*/

	private $user_cap = 'manage_categories';

 /*
	* If business section include additional script
	*
	* @var boolean $business
	*/

	private $business = false;

 /*
	* Sections.
	*
	* @var array $sections {
	* 	@type string $id Accepts string.
	*		@type string $title Accepts string.
	*		@type string $callback Accepts function/boolean.
	*		@type string $page Accepts string.
	* }
	*/

	private $sections = [
		[
			'id' => 'logo',
			'title' => 'Logo'
		],
		[
			'id' => 'blocks',
			'title' => 'Blocks'
		],
		[
			'id' => 'footer',
			'title' => 'Footer'
		],
		[
			'id' => 'scripts',
			'title' => 'Scripts'
		]
	];

 /*
	* Fields.
	*
	* @var array $fields
	* @see class Field for args breakdown.
	*/

	private $fields = [
		[
			'name' => 'svg_logo',
			'label' => 'SVG',
			'type' => 'file',
			'file_type' => 'image',
			'accept' => 'image/svg+xml',
			'section' => 'logo',
			'tab' => 'General'
		],
		[
			'name' => 'logo',
			'label' => 'PNG',
			'type' => 'file',
			'file_type' => 'image',
			'accept' => 'image/png',
			'section' => 'logo',
			'wp' => true,
			'tab' => 'General'
		],
		[
			'name' => '_blocks',
			'label' => 'Reusable Blocks',
			'type' => 'hidden',
			'hidden_type_show' => true,
			'section' => 'blocks',
			'tab' => 'General',
			'after' => '<a class="button" href="/wp-admin/edit.php?post_type=wp_block">Manage All Reusable Blocks</a>'
		],
		[
			'name' => 'footer_text',
			'label' => 'Text',
			'type' => 'richtext',
			'toolbar' => 'bold,italic,link',
			'wpautop' => true,
			'section' => 'footer',
			'tab' => 'General'
		]
	];

 /*
	* Store nav from settings instance.
	*
	* @var string $tab_nav
	*/

	private $tab_nav = '';

 /*
	* Additional scripts and styles.
	*
	* @var function/null $scripts
	*/

	private $scripts = null;

 /*
	* Constructor
	* -----------
	*/

	public function __construct( $args = [] ) {

		/* Default args */

		$args = array_replace_recursive( [
			'recaptcha' => false,
			'geocode' => false,
			'analytics' => false,
			'business' => false,
			'mailchimp_list_locations' => [],
			'sections' => [],
			'fields' => [],
			'scripts' => null
		], $args );

		extract( $args );

		/* Head and Footer Scripts */

		$this->fields[] = [
			'name' => 'scripts_head',
			'label' => 'Scripts in Head',
			'type' => 'textarea',
			'section' => 'scripts',
			'tab' => 'General',
			'attr' => [
				'rows' => 10,
				'data-full' => ''
			],
			'on_save' => function( $value ) {
				return $value;
			}
		];

		$this->fields[] = [
			'name' => 'scripts_footer',
			'label' => 'Scripts in Footer',
			'type' => 'textarea',
			'section' => 'scripts',
			'tab' => 'General',
			'attr' => [
				'rows' => 10,
				'data-full' => ''
			],
			'on_save' => function( $value ) {
				return $value;
			}
		];

		/* Google Recaptcha */

		if( $recaptcha ) {
			$this->sections[] = [
				'id' => 'google-recaptcha',
				'title' => 'Recaptcha'
			];

			$this->fields[] = [
				'name' => 'recaptcha_site_key',
				'label' => 'Site Key',
				'section' => 'google-recaptcha',
				'tab' => 'Google'
			];

			$this->fields[] = [
				'name' => 'recaptcha_secret_key',
				'label' => 'Secret Key',
				'section' => 'google-recaptcha',
				'tab' => 'Google'
			];
		}

		/* Google Geocode */

		if( $geocode ) {
			$this->sections[] = [
				'id' => 'google-geocode',
				'title' => 'Geocode'
			];

			$this->fields[] = [
				'name' => 'geocode_key',
				'label' => 'Geocode API Key',
				'section' => 'google-geocode',
				'tab' => 'Google'
			];
		}

		/* Google Analytics */

		if( $analytics ) {
			$this->sections[] = [
				'id' => 'google-analytics',
				'title' => 'Analytics'
			];

			$this->fields[] = [
				'name' => 'analytics_id',
				'label' => 'Analytics ID',
				'section' => 'google-analytics',
				'tab' => 'Google'
			];
		}

		/* Mailchimp */

		if( $mailchimp_list_locations ) {
			$this->sections[] = [
				'id' => 'mailchimp-credentials',
				'title' => 'Credentials'
			];

			$this->fields[] = [
				'name' => 'mailchimp_api_key',
				'label' => 'API Key',
				'section' => 'mailchimp-credentials',
				'tab' => 'Mailchimp'
			];

			foreach( $mailchimp_list_locations as $name => $location ) {
				$cap_location = ucfirst( $location );
				$section_id = 'mailchimp_' . $location;
				$name_fields = $name . '_fields';

				$f = Select_Fields::get( $name_fields );

				$f[] = [
					'type' => 'checkbox',
					'label' => 'Tag',
					'name' => $name_fields . '[%i][tag]',
					'value' => 1
				];

				$f[] = [
					'type' => 'checkbox',
					'label' => 'Merge Field',
					'name' => $name_fields . '[%i][merge_field]',
					'value' => 1
				];

				$this->sections[] = [
					'id' => $section_id,
					'title' => "$cap_location Form"
				];

				$this->fields[] = [
					'name' => $name . '_list_id',
					'label' => 'List ID',
					'section' => $section_id,
					'tab' => 'Mailchimp'
				];

				$this->fields[] = [
					'name' => $name . '_title',
					'label' => 'Title',
					'section' => $section_id,
					'tab' => 'Mailchimp'
				];

				$this->fields[] = [
					'name' => $name . '_text',
					'label' => 'Text',
					'type' => 'richtext',
					'toolbar' => 'bold,italic,link',
					'wpautop' => true,
					'section' => $section_id,
					'tab' => 'Mailchimp'
				];

				$this->fields[] = [
					'name' => $name . '_submit_label',
					'label' => 'Submit Label',
					'section' => $section_id,
					'tab' => 'Mailchimp'
				];

				$this->fields[] = [
					'name' => $name . '_success_message',
					'type' => 'richtext',
					'toolbar' => 'bold,italic,link',
					'wpautop' => true,
					'label' => 'Success Message',
					'section' => $section_id,
					'tab' => 'Mailchimp',
					'attr' => [
						'rows' => 5,
						'data-full' => ''
					]
				];

				$this->fields[] = [
					'name' => $name_fields,
					'label' => 'Fields',
					'label_hidden' => true,
					'multi' => true,
					'on_save' => function( $value ) {
						foreach( $value as &$v ) {
							$tag = $v['tag'] ?? false;
							$merge_field = $v['merge_field'] ?? false;

							if( $tag || $merge_field ) {
								if( !isset( $v['attr'] ) )
										$v['attr'] = [];

								if( $tag )
										$v['attr']['data-tag'] = 'true';

								if( $merge_field )
										$v['attr']['data-merge-field'] = 'true';
							}
						}

						return Select_Fields::filter( $value );
					},
					'fields' => $f,
					'section' => $section_id,
					'tab' => 'Mailchimp'
				];
			}
		}

		/* Business */

		if( $business ) {
			$this->business = true;

			$location_fields = [
				[
					'name' => 'location[%i][admin1_name]',
					'class' => 'js-admin1',
					'label' => 'Country',
					'attr' => ['onchange' => 'getAdmin1( event )']
				],
				[
					'type' => 'hidden',
					'name' => 'location[%i][admin1]'
				],
				[
					'type' => 'hidden',
					'name' => 'location[%i][admin1_id]'
				],
				[
					'label' => 'State/Province',
					'type' => 'select',
					'class' => 'js-admin3',
					'name' => 'location[%i][admin3_options]',
					'attr' => [
						'disabled' => 'true',
						'onchange' => 'setAdmin3Input( event )'
					],
					'options' => [
						[
							'label' => '— Select —',
							'value' => ''
						]
					]
				],
				[
					'type' => 'hidden',
					'name' => 'location[%i][admin3]'
				],
				[
					'type' => 'hidden',
					'name' => 'location[%i][admin3_name]'
				],
				[
					'name' => 'location[%i][city]',
					'label' => 'City'
				],
				[
					'name' => 'location[%i][line1]',
					'label' => 'Address Line 1'
				],
				[
					'name' => 'location[%i][line2]',
					'label' => 'Address Line 2',
					'optional' => true
				],
				[
					'name' => 'location[%i][postal_code]',
					'label' => 'Postal Code'
				],
				[
					'name' => 'location[%i][phone]',
					'label' => 'Phone Number'
				]
			];

			$hours_options = [];

			for( $h = 1; $h <= 24; $h++ ) {
				$hours_options[] = [
					'label' => $h < 10 ? "0$h" : $h,
					'value' => $h
				];
			} 

			$min_options = [
				'00' => '00',
				'15' => '15',
				'30' => '30',
				'45' => '45'
			];

			$hours_fields = [];
			$weekdays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

			foreach( $weekdays as $w ) {
				$w3 = substr( strtolower( $w ), 0, 3 ); 

				$hours_fields[] = [
					'name' => "hours[%i][$w3]",
					'label' => 'Day',
					'value' => $w,
					'attr' => ['readonly' => true],
					'before_field' => '<div class="o-multi__row o-toggle l-flex" data-wrap>'
				];

				$hours_fields[] = [
					'name' => "hours[%i][$w3" . "_open_hour]",
					'label' => 'Open Time',
					'type' => 'select',
					'field_class' => 'o-toggle__item',
					'options' => $hours_options,
					'before_field' => '<div class="l-flex">'
				];

				$hours_fields[] = [
					'name' => "hours[%i][$w3" . "_open_min]",
					'label' => 'Open Minute',
					'type' => 'select',
					'field_class' => 'o-toggle__item o-time',
					'options' => $min_options,
					'after_field' => '</div>'
				];

				$hours_fields[] = [
					'name' => "hours[%i][$w3" . "_close_hour]",
					'label' => 'Close Time',
					'type' => 'select',
					'field_class' => 'o-toggle__item',
					'options' => $hours_options,
					'before_field' => '<div class="l-flex">'
				];

				$hours_fields[] = [
					'name' => "hours[%i][$w3" . "_close_min]",
					'label' => 'Close Minute',
					'type' => 'select',
					'field_class' => 'o-toggle__item o-time',
					'options' => $min_options,
					'after_field' => '</div>'
				];

				$hours_fields[] = [
					'name' => "hours[%i][$w3" . "_closed]",
					'label' => 'Closed',
					'type' => 'checkbox',
					'value' => 1,
					'class' => 'o-toggle__trigger',
					'attr' => ['onchange' => 'toggleSiblings( event )'],
					'after_field' => '</div>'
				];
			}

			$this->sections[] = [
				'id' => 'business',
				'title' => 'Business Information'
			];

			$this->sections[] = [
				'id' => 'geonames',
				'title' => 'GeoNames'
			];

			$this->fields[] = [
				'name' => 'geonames_username',
				'label' => 'Username',
				'section' => 'geonames',
				'tab' => 'GeoNames',
				'after' => '<p class="u-helper">Used in Business tab to fetch countries and states</p>'
			];

			$this->fields[] = [
				'name' => 'location',
				'label' => 'Locations',
				'label_hidden' => true,
				'helper' => 'The first location is used as the main location in this theme.',
				'fields' => $location_fields,
				'multi' => true,
				'on_save' => function( $value ) {
					if( !is_array( $value ) )
						return $value;

					foreach( $value as $v ) {
						if( !isset( $v['line1'] ) || !isset( $v['city'] ) || !isset( $v['postal_code'] ) )
							continue;

						$location = Utils_Optional::format_location( $v );

						$lat_lng = FRM::get_lat_lng( $location );

						if( $lat_lng )
							$v['lat_lng'] = $lat_lng;
					} 

					return $value;
				},
				'section' => 'business',
				'tab' => 'Business'
			];

			$this->fields[] = [
				'name' => 'hours',
				'label' => 'Hours',
				'label_hidden' => true,
				'helper' => 'Order corresponds with order of locations.',
				'fields' => $hours_fields,
				'multi' => true,
				'multi_col' => true,
				'section' => 'business',
				'tab' => 'Business'
			];

			$this->fields[] = [
				'name' => 'email',
				'type' => 'email',
				'label' => 'Email',
				'section' => 'business',
				'tab' => 'Business'
			];
		}

		/* Additional fields */

		if( $fields )
			$this->fields = array_merge( $this->fields, $fields );

		/* Additional sections */

		if( $sections )
			$this->sections = array_merge( $this->sections, $sections );

		/* Uploads */

		if( file_exists( FRM::$uploads_dir ) ) {
			$uploads = $this->get_uploads();

			if( $uploads ) {
				$this->sections[] = [
					'id' => 'uploads',
					'title' => 'Theme Uploads'
				];

				$this->fields[] = [
					'name' => 'uploads_hidden',
					'type' => 'hidden',
					'hidden_type_show' => true,
					'value' => 1,
					'section' => 'uploads',
					'tab' => 'Uploads',
					'after' => $uploads
				];
			}
		}

		/* Addtional scripts */

		$this->scripts = $scripts;

		// add options page to settings
		add_action( 'admin_menu', [$this, 'menu'] );

		// register settings
		add_action( 'admin_init', [$this, 'setup'] );

		// enqueue scripts
		add_action( 'admin_enqueue_scripts', [$this, 'scripts'] );

		// file actions
		Field::file_actions();
	}

 /*
	* Add to settings menu
	* --------------------
	*/

	public function menu() {
		$page_hook = add_options_page(
			'Theme Settings',
			'Theme',
			$this->user_cap,
			$this->page,
			[$this, 'output']
		);

		$this->page_hook = $page_hook;
	}

 /*
	* Register and render fields
	* --------------------------
	*/

	public function setup() {
		// add fields
		$settings = new Settings( [
			'fields' => $this->fields,
			'sections' => $this->sections,
			'page' => $this->page,
			'tabs' => true
		] );

		// store nav
		$this->tab_nav = $settings->get_tab_nav();
	}

 /*
	* Settings page output
	* --------------------
	*/

	public function output() {
		// check user capabilities
		if( !current_user_can( $this->user_cap ) )
			return;

		// settings_errors(); ?>

		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<?php echo $this->tab_nav; ?>
			<form action="options.php" method="post"<?php echo $this->tab_nav ? ' style="padding-top: 30px;"' : ''; ?>>
				<div class="js-section">
				<?php
					settings_fields( $this->page );
					do_settings_sections( $this->page );
				?>
				</div>
				<?php submit_button( 'Save Settings' ); ?>
			</form>
		</div>

	<?php }

 /*
	* Enqueue scripts and styles
	* --------------------------
	*/

	public function scripts( $hook ) {
		if( $hook === $this->page_hook ) {
			Field::scripts();
			Select_Fields::scripts();

			if( is_callable( $this->scripts ) )
				call_user_func( $this->scripts );

			$uri = FRM::$src_url . 'Admin/assets/public';

			wp_enqueue_style(
				FRM::$namespace . '-settings-styles',
				$uri . '/css/settings.css'
			);

			wp_enqueue_script(
				FRM::$namespace . '-theme-settings-script',
				$uri . '/js/settings.js',
				[],
				NULL,
				true
			);

			if( $this->tab_nav ) {
				wp_enqueue_script(
					FRM::$namespace . '-theme-settings-tab-nav-script',
					$uri . '/js/tab-nav.js',
					[],
					NULL,
					true
				);
			}

			if( $this->business ) {
				wp_enqueue_script(
					FRM::$namespace . '-theme-settings-business-script',
					$uri . '/js/business.js',
					[],
					NULL,
					true
				);

				additional_script_data( FRM::$namespace, [
					'geonames_un' => get_option( FRM::$namespace . '_geonames_username' ) 
				], true );
			}
		}
	}

	public function get_uploads() {
		$dir = scandir( FRM::$uploads_dir );
		$output = '';

		if( $dir )
			$dir = array_diff( $dir, ['..', '.'] );

		if( count( $dir ) >= 1 ) {
			foreach( $dir as $i => $file_name ) {
				$file_type = mime_content_type( FRM::$uploads_dir . $file_name );
				$name = FRM::$namespace . '_theme_upload_' . $i;

				$output .=
					'<div class="o-asset-row">' .
							Field::render_asset( [
								'upload' => false,
								'name' => $name,
								'id' => $name,
								'type' => strpos( $file_type, 'image' ) !== false ? 'image' : 'file',
								'value' => FRM::$uploads_url . $file_name,
								'class' => 'o-asset--remove',
								'input_value' => FRM::$uploads_dir . $file_name
							] ) .
					'</div>';
			}
		}

		return $output;
	}

} // end Theme
