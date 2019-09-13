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
use Formation\Common\Field\Field; 
use Formation\Common\Field\Select_Fields; 
use Formation\Admin\Settings\Settings; 

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
    * Settings page hook. To filter when enqueing scripts.
    *
    * @var string $page_hook
    */

    private $user_cap = 'manage_categories';

   /*
    * Sections.
    *
    * @var array $sections {
    *       @type string $id Accepts string. 
    *       @type string $title Accepts string.
    *       @type string $callback Accepts function/boolean.
    *       @type string $page Accepts string.
    * }
    */

    private $sections = [
        [
            'id' => 'logo',
            'title' => 'Logo'
        ],
        [
            'id' => 'footer',
            'title' => 'Footer'
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
            'name' => 'footer_text',
            'label' => 'Text',
            'type' => 'richtext',
            'toolbar' => 'bold,italic,link',
            'p_tags' => false,
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
            'instagram' => false,
            'mailchimp_list_locations' => [],
            'sections' => [],
            'fields' => [],
            'scripts' => null
        ], $args );

        extract( $args );

        /* Google recaptcha */

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

        /* Instagram */

        /*if( $instagram ) {
            $this->sections[] = [
                'id' => 'instagram',
                'title' => 'Instagram'
            ];
        }*/

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

                $this->sections[] = [
                    'id' => $section_id,
                    'title' => "$cap_location Form"
                ];

                $this->fields[] = [
                    'name' => $name . '_id',
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
                    'name' => $name . '_submit_label',
                    'label' => 'Submit Label',
                    'section' => $section_id,
                    'tab' => 'Mailchimp'
                ];

                $this->fields[] = [
                    'name' => $name_fields,
                    'label' => 'Fields',
                    'label_hidden' => true,
                    'multi' => true,
                    'on_save' => ['Select_Fields', 'filter'],
                    'fields' => Select_Fields::get( $name_fields ),
                    'section' => $section_id,
                    'tab' => 'Mailchimp'
                ];
            }
        }

        /* Additional fields */

        if( $fields )
            $this->fields = array_merge( $this->fields, $fields );

        /* Additional sections */

        if( $sections )
            $this->sections = array_merge( $this->sections, $sections );

        /* Addtional scripts */

        $this->scripts = $scripts;

        // add options page to settings
        add_action( 'admin_menu', [$this, 'menu'] );

        // register settings
        add_action( 'admin_init', [$this, 'setup'] );

        // enqueue scripts
        add_action( 'admin_enqueue_scripts', [$this, 'scripts'] ); 

        // file upload
        Field::file_upload_action();
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

        // settings_errors();

        ?>

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
        }
    }

} // end Theme
