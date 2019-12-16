# WP Theme Formation

Base class, utilities and added functionality for building WordPress themes.
+ [Base](#base)
+ [Utilites](#utilities)
+ [Admin](#admin)
+ [Common](#common)
+ [Public](#public)

## Base

#### Variables

#### `public static $namespace`

Namespace for handles, option and meta names.  
*Type:* `string`  
*Default:* `'frm'`

#### `public static $src_path`

Path from vendor to src folder.  
*Type:* `string`  
*Default:* `'/vendor/alanizcreative/wp-theme-formation/src/'`

#### `public static $cpt`

Store post type names and data.  
*Type:* `associative array`  
*Default:* `[]`  
*Example:*
```php
self::$cpt = [
	'custom_post_type' => [
		'slug' => 'custom_post_type_slug',
		'label' => 'Custom Post Type',
		'layout' => 'cards',
		'no_reading' => true, // exclude from reading settings
		'no_slug' => true, // exclude from saving selected post page slug as custom post type slug,
		'ajax_posts_per_page' => true // add field in reading settings to select number of posts to load with ajax
	]
];
```

#### `public static $pt_layout`

Store layouts by post type. Appends layouts from `[$cpt](#public-static-cpt)`.  
*Type:* `associative array`  
*Default:* `[]`

#### `public static $posts_per_page`

Default number of posts to display by type/post type.  
*Type:* `associative array`  
*Default:* `[]`

#### `public $editor_color_palette`

Editor color palette theme support args.  
*Type:* `array`  
*Default:* `[]`  
*Example:*
```php
$this->editor_color_palette[] = [
	'name' => 'Background Light',
	'slug' => 'background-light',
	'color' => '#FFFFFF'
];
```

#### `public $image_sizes`

Custom image sizes to register. Name => size.  
*Type:* `associative array`  
*Default:* `[]`

#### `public $nav_menus`

Nav menus to register. Slug => label.  
*Type:* `associative array`  
*Default:* `[]`

#### `public $editor_style_url`

Stylesheet url for admin editor styles.  
*Type:* `string`  
*Default:* `''`

#### `public $styles`

Stylesheets to register. See [wp_enqueue_style](https://developer.wordpress.org/reference/functions/wp_enqueue_style/).  
*Type:* `array`  
*Default:* `[]`  
*Example:*
```php
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
```

#### `public $scripts`

Scripts to register. See [wp_enqueue_script](https://developer.wordpress.org/reference/functions/wp_enqueue_script/).  
*Type:* `array`  
*Default:* `[]`  
*Example:*
```php
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
```

#### `public static $loader_icon`

Markup for default loader icon.  
*Type:* `string`  
*Default:* `''`  

#### `public static $classes`

Optional classes to add to fields, labels, buttons...  
*Type:* `array`  
*Default:*
```php
self::$classes = [
	'field' => '',
	'button' => '',
	'label' => '',
	'input' => '',
	'icon' => ''
];
```

#### `public static $sprites`

Stores svg sprite meta. Svgs can be found in assets/svg.  
*Type:* `array`  
*Default:*
```php
self::$sprites = [
	'Icon' => [
		'id' => 'icon-id',
		'w' => 20,
		'h' => 20
	]
	...
];
```

#### Global Functions

#### `additional_script_data( $name, $data, $admin, $head )`

Pass data to front end.

| Variable | Type | Default | Description
|--|--|--|--|
| `$name` | `boolean` | `false` | Name of variable on front end.
| `$data` | `array` | `[]` | Data to pass to front end.
| `$admin` | `boolean` | `false` | Only apply to admin.
| `$head` | `boolean` | `false` | Outputs in footer otherwise in head.

#### `write_log( $log )`

Write to debug log.

| Variable | Type | Default | Description
|--|--|--|--|
| `$log` | `string|array|object` | `''` | Data to output in debug log.

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

`Class` that adds the following fields to WordPress' default reading settings page using base class variable `[$cpt](#public-static-cpt)`. Note: if `no_reading` is set, these fields are not added for that custom post type. Additional fields can be pushed to `Reading::$additional_fields` before instantiating class.

#### `{$cpt}_page`

*Type:* `int`  
Select page to display custom post type.  

#### `{$cpt}_posts_per_page`

*Type:* `int`  
Page displays selected number of posts.  

#### `{$cpt}_more_label`

*Type:* `string`  
More posts title.   

#### `{$cpt}_ajax_posts_per_page`

*Type:* `int`
How many posts to load with ajax. This field is added if `ajax_posts_per_page` is set in `[$cpt](#public-static-cpt)`.   

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
