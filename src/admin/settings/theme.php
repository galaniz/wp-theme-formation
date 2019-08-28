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
use Formation\Common\Field; 
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
            'name' => 'logo',
            'label' => 'Logo',
            'section' => 'logo',
            'type' => 'file',
            'image' => true,
            'attr' => [
                'accept' => 'image/svg+xml,image/png'
            ]
        ],
        [
            'name' => 'footer_text',
            'label' => 'Footer Text',
            'section' => 'footer',
            'type' => 'richtext'
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
                'name' => 'recaptcha',
                'section' => 'recaptcha',
                'label' => 'Recaptcha',
                'fields' => [
                    [
                        'name' => 'recaptcha[0][site_key]',
                        'label' => 'Site Key',
                        'type' => 'text'
                    ],
                    [
                        'name' => 'recaptcha[0][secret_key]',
                        'label' => 'Secret Key',
                        'type' => 'text',
                        'on_save' => [$this, 'save_recaptcha']
                    ]
                ]
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
                [
                    'name' => 'mailchimp_api_key',
                    'section' => 'mailchimp',
                    'label' => 'API Key',
                ],
                [
                    'name' => 'mailchimp_data_center',
                    'section' => 'mailchimp',
                    'label' => 'Data Center',
                ]
            ];

            foreach( $mailchimp_list_locations as $name => $location ) {
                $this->fields[] = [
                    'name' => $name,
                    'section' => 'mailchimp',
                    'label' => 'List ID (displays in ' . $location . ')'
                ];
            }
        }

        // add options page to settings
        add_action( 'admin_menu', [$this, 'menu'] );

        // register settings
        add_action( 'admin_init', [$this, 'setup'] );

        // enqueue scripts
        add_action( 'admin_enqueue_scripts', [$this, 'scripts'] ); 
    }

   /*
    * Add to settings menu
    * --------------------
    */

    public function menu() {
        $page_hook = add_options_page( 
            'Theme Settings',
            'Theme Settings',
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
    * Field callbacks
    * ---------------
    */

    public function save_recaptcha( $value ) {
        Field::filter_multi_fields( $value, ['site_key', 'secret_key'] );

        return $value;
    }

   /*
    * Enqueue scripts and styles
    * --------------------------
    */

    public function scripts( $hook ) {
        if( $hook === $this->page_hook ) {
            Field::scripts();
        }
    }

} // end Theme
