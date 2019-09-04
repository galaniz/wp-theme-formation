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
            'label' => 'SVG Logo',
            'section' => 'logo',
            'type' => 'file',
            'file_type' => 'image',
            'accept' => 'image/svg+xml'
        ],
        [
            'name' => 'footer_text',
            'label' => 'Footer Text',
            'section' => 'footer',
            'type' => 'richtext',
            'toolbar' => 'bold,italic,link',
            'p_tags' => false
        ]
    ];

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
            'fields' => []
        ], $args );

        extract( $args );

        /* Google recaptcha */

        if( $recaptcha ) {
            $this->sections[] = [
                'id' => 'recaptcha',
                'title' => 'Google Recaptcha'
            ];

            $this->fields[] = [
                'name' => 'recaptcha_site_key',
                'label' => 'Site Key',
                'section' => 'recaptcha'
            ];

            $this->fields[] = [
                'name' => 'recaptcha_secret_key',
                'label' => 'Secret Key',
                'section' => 'recaptcha'
            ];
        }

        /* Instagram */

        if( $instagram ) {
            $this->sections[] = [
                'id' => 'instagram',
                'title' => 'Instagram'
            ];
        }

        /* Mailchimp */

        if( $mailchimp_list_locations ) {
            $this->sections[] = [
                'id' => 'mailchimp',
                'title' => 'Mailchimp'
            ];

            $this->fields[] = [
                'name' => 'mailchimp_api_key',
                'section' => 'mailchimp',
                'label' => 'API Key'
            ];

            $this->fields[] = [
                'name' => 'mailchimp_data_center',
                'section' => 'mailchimp',
                'label' => 'Data Center',
            ];

            foreach( $mailchimp_list_locations as $name => $location ) {
                $cap_location = ucfirst( $location );
                $section_id = 'mailchimp_' . $location;
                $name_fields = $name . '_fields';

                $this->sections[] = [
                    'id' => $section_id,
                    'title' => "Mailchimp: $cap_location Form"
                ];

                $this->fields[] = [
                    'name' => $name . '_id',
                    'section' => $section_id,
                    'label' => 'List ID'
                ];

                $this->fields[] = [
                    'name' => $name . '_title',
                    'section' => $section_id,
                    'label' => 'Title'
                ];

                $this->fields[] = [
                    'name' => $name . '_submit_label',
                    'section' => $section_id,
                    'label' => 'Submit label'
                ];

                $this->fields[] = [
                    'name' => $name_fields,
                    'section' => $section_id,
                    'label' => 'Fields',
                    'multi' => true,
                    'on_save' => ['Select_Fields', 'filter'],
                    'fields' => Select_Fields::get( $name_fields )
                ];
            }
        }

        /* Additional fields */

        if( $fields )
            $this->fields = array_merge( $this->fields, $fields );

        /* Additional sections */

        if( $sections )
            $this->sections = array_merge( $this->sections, $sections );

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
        // add sections
        foreach( $this->sections as $section ) {
            add_settings_section( 
                $section['id'],
                $section['title'], 
                $section['callback'] ?? false, 
                $section['page'] ?? $this->page
            );
        }

        // add fields
        new Settings( $this->fields, $this->page );
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
            <form action="options.php" method="post">
                <?php
                    settings_fields( $this->page );
                    do_settings_sections( $this->page );
                    submit_button( 'Save Settings' );
                ?>
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
        }
    }

} // end Theme
