# WP Theme Formation

Base class, utilities and added functionality for building WordPress themes. It's split by category:
+ [Admin](#admin)
+ [Common](#common)
+ [Public](#public)

## Base

#### Variables
<table>
	<thead>
		<tr>
			<th>Name</th>
			<th>Type</th>
			<th>Default</th>
			<th>Static</th>
			<th>Description</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td><code>$namespace</code></td>
			<td><code>string</code></td>
			<td><code>'frm'</code></td>
			<td>✓</td>
			<td>Namespace for handles, option and meta names.</td>
		</tr>
		<tr>
			<td><code>$src_path</code></td>
			<td><code>string</code></td>
			<td><code>'/vendor/alanizcreative/wp-theme-formation/src/'</code></td>
			<td>✓</td>
			<td>Path from vendor to source.</td>
		</tr>
		<tr>
			<td><code>$src_url</code></td>
			<td><code>string</code></td>
			<td><code>get_template_directory_uri() . self::$src_path</code></td>
			<td>✓</td>
			<td>Url to source.</td>
		</tr>
		<tr id="cpt">
			<td><code>$cpt</code></td>
			<td><code>associative array</code></td>
			<td><code>[]</code></td>
			<td>✓</td>
			<td>
				Store post type names and data (can add custom key => pair values).
				<pre lang="php">
					self::$cpt['custom_post_type'] = [
						'slug' => 'custom_post_type_slug',
						'label' => 'Custom Post Type',
						'layout' => 'cards',
						'no_reading' => true, // exclude from reading settings
						'no_slug' => true, // exclude from saving selected post page slug as custom post type slug,
						'ajax_posts_per_page' => true // add field in reading settings to select number of posts to load with ajax
					];
				</pre>
			</td>
		</tr>
		<tr>
			<td><code>$pt_layout</code></td>
			<td><code>associative array</code></td>
			<td><code>[]</code></td>
			<td>✓</td>
			<td>Store layouts for post types. Appends layouts from <a href="#cpt">$cpt</a>.</td>
		</tr>
		<tr>
			<td><code>$posts_per_page</code></td>
			<td><code>associative array</code></td>
			<td><code>[]</code></td>
			<td>✓</td>
			<td>Default number of posts to display by type / post type</td>
		</tr>
		<tr>
			<td><code>$editor_color_palette</code></td>
			<td><code>array</code></td>
			<td><code>[]</code></td>
			<td></td>
			<td>
				Editor color palette theme support args.
				<pre lang="php">
					$this->editor_color_palette[] = [
						'name' => 'Background Light',
						'slug' => 'background-light',
						'color' => '#FFFFFF'
					];
				</pre>
			</td>
		</tr>
		<tr>
			<td><code>$image_sizes</code></td>
			<td><code>associative array</code></td>
			<td><code>[]</code></td>
			<td></td>
			<td>Custom image sizes to register. Name => size.</td>
		</tr>
		<tr>
			<td><code>$nav_menus</code></td>
			<td><code>associative array</code></td>
			<td><code>[]</code></td>
			<td></td>
			<td>Nav menus to register. Slug => label.</td>
		</tr>
		<tr>
			<td><code>$editor_style_url</code></td>
			<td><code>string</code></td>
			<td><code>''</code></td>
			<td></td>
			<td>Stylesheet url for admin editor styles.</td>
		</tr>
		<tr>
			<td><code>$styles</code></td>
			<td><code>array</code></td>
			<td><code>[]</code></td>
			<td></td>
			<td>
				Stylesheets to register. See <a href="https://developer.wordpress.org/reference/functions/wp_enqueue_style/">wp_enqueue_style</a>.
				<pre lang="php">
					$this->styles = [
						[
							'handle' => 'fonts',
							'url' => 'https://fonts.googleapis.com/css?family=Roboto'
						],
						[
							'handle' => 'main',
							'url' => get_stylesheet_uri(),
							'dep' => ['fonts'],
							'ver' => 1.0 // string|bool|null
						]
					];
				</pre>
			</td>
		</tr>
		<tr>
			<td><code>$scripts</code></td>
			<td><code>array</code></td>
			<td><code>[]</code></td>
			<td></td>
			<td>
				Scripts to register. See <a href="https://developer.wordpress.org/reference/functions/wp_enqueue_script/">wp_enqueue_script</a>.
				<pre lang="php">
					$this->scripts = [
						[
							'handle' => 'scripts',
							'url' => get_template_directory_uri() . '/assets/public/js/main.js',
							'dep' => ['jquery'],
							'ver' => 1.0, // string|bool|null
							'footer' => true,
							'defer' => true,
							'data' => [ // wp_localize_script
								'post_type' => 'post'
							]
						]
					];
				</pre>
			</td>
		</tr>
		<tr>
			<td><code>$loader_icon</code></td>
			<td><code>string</code></td>
			<td><code>''</code></td>
			<td>✓</td>
			<td>Markup for default loader icon.</td>
		</tr>
		<tr>
			<td><code>$classes</code></td>
			<td><code>array</code></td>
			<td>
				<pre lang="php">
					self::$classes = [
				        'field' => '',
				        'button' => '',
				        'label' => '',
				        'input' => '',
				        'icon' => ''
				    ];
				</pre>
			</td>
			<td>✓</td>
			<td>Optional classes to add to fields, labels, buttons...</td>
		</tr>
		<tr>
			<td><code>$sprites</code></td>
			<td><code>array</code></td>
			<td>
				<pre lang="php">
					self::$sprites = [
						'Icon' => [
							'id' => 'icon-id',
							'w' => 20,
							'h' => 20
						]
					]
				</pre>
			</td>
			<td>✓</td>
			<td>Stores svg sprite meta. Svgs can be found in assets/svg.</td>
		</tr>
	</tbody>
</table>

#### Global Functions

##### `additional_script_data( $name = false, $data = [], $admin = false, $head = false )`
##### `write_log( $log )`

#### Utilities

##### Getters
##### Render
##### Optional

## Admin

### Settings
`Class` that uses [WordPress' Settings API](https://developer.wordpress.org/plugins/settings/using-settings-api/) to build out settings pages. 

#### Options
| Name | Type | Default | Required | Description
|--|--|--|--|--|
| `page` | `string` | `''` | ✓ | Page to register settings.
| `fields` | `array` | `[]` | ✓ | See [field](#field). Includes section and tab options. 
| `sections` | `array` | `[]` |  | Add section to page. Provide id and title.
| `tabs` | `boolean` | `false` |  | Organize as tabs. 
#### Example
```php
use Formation\Admin\Settings\Settings;

$settings = new Settings( [
	'page' => 'Business Information',
    'fields' => [
        [
            'name' => 'address',
            'label' => 'Address',
            'section' => 'location'
        ],
        [
            'name' => 'city',
            'label' => 'City',
            'section' => 'location'
        ],
        [
            'name' => 'postal_code',
            'label' => 'Postal Code',
            'section' => 'location'
        ]
	],
    'sections' => [
	    [
		    'id' => 'location',
		    'title' => 'Location'
	    ]
    ]
] );
```

### Reading
`Class` that adds the following fields to WordPress' default reading settings page using base class variable `[$cpt](#cpt)`. Note: if `no_reading` is set, these fields are not added for that custom post type. 

| Name | Type | Description
|--|--|--|
| `{$cpt}_page` | `int` | Select page to display custom post type.  
| `{$cpt}_posts_per_page` | `int` | Page displays selected number of posts. 
| `{$cpt}_more_label` | `string` | More posts title.  
| `{$cpt}_ajax_posts_per_page` | `int` | How many posts to load with ajax. This field is added if `ajax_posts_per_page` is set in `[$cpt](#cpt)`. 

Additional fields can be pushed to`Reading::$additional_fields` before instantiating class.

### Theme
`Class` that adds a new settings page with fields for commonly used theme items. It includes default fields for svg and png logos as well as footer text.

#### Options
| Name | Type | Default | Description
|--|--|--|--|
| `recaptcha` | `boolean` | `false` | Include Google Recaptcha tab and fields.  
| `mailchimp_list_locations` | `associative array` | `[]` | Include Mailchimp tab and fields. Name => location.  
| `sections` | `array` | `[]` | See [settings](#settings).
| `fields` | `array` | `[]` | See [settings](#settings). 
| `scripts` | `function|null` | `null` | Pass custom scripts to page.

## Common

## Public