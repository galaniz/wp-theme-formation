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

        if( $instagram ) {
            $this->sections[] = [
                'id' => 'insta-config',
                'title' => 'Configure'
            ];

            $return_uri = urlencode( admin_url( 'options-general.php?page=theme&tab=instagram' ) );
            $data_uri = urlencode( FRM::$src_url . 'admin/settings/insta.php' );

            $this->fields[] = [
                'name' => 'insta_access_token',
                'type' => 'hidden',
                'label' => 'Account',
                'section' => 'insta-config',
                'tab' => 'Instagram',
                'before' =>
                    '<div class="u-display-inline-block">' . 
                        '<a class="o-button button-secondary u-position-relative --lg" id="js-insta-auth" href="https://api.instagram.com/oauth/authorize?app_id=1988348524600914&redirect_uri=https%3A%2F%2Fwww.gracielaalaniz.com%2Finsta&scope=user_profile,user_media&response_type=code&return_uri=' . $return_uri . '" target="_blank">' .
                            "<span class='dashicons dashicons-instagram'></span>" .
                            "<span>Connect Account</span>" .
                            "<span class='o-loader'><span class='spinner is-active'></span></span>" .
                        '</a>' .
                    '</div>',
                'after' => '<input type="hidden" name="' . FRM::$namespace . '_insta_user_id">'
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

            if( $this->tab_nav ) {
                wp_enqueue_script(
                    FRM::$namespace . '-theme-settings-script',
                    FRM::$src_url . 'admin/assets/public/js/settings.js',
                    [],
                    NULL,
                    true
                );
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
