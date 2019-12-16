# WP Theme Formation

Base class, utilities and added functionality for building WordPress themes. It's split by category:
+ [Admin](#admin)
+ [Common](#common)
+ [Public](#public)

## Base

#### Variables

| Name | Type | Default | Static | Description |
|--|--|--|--|--|
| `$namespace` | `string` | `'frm'` | ✓ | Namespace for handles, option and meta names. |
| `$src_path` | `string` | `'/vendor/alanizcreative/wp-theme-formation/src/'` | ✓ | Path from vendor to src. | 
| `$src_url` | `string` | `get_template_directory_uri() . self::$src_path` | ✓ | Url to src. | 
| `$cpt`{#cpt} | `associative array` | `[]` | ✓ | Store post type names and data (can add custom key => pair values). ```php
	self::$cpt['custom_post_type'] = [
		'slug' => 'custom_post_type_slug',
		'label' => 'Custom Post Type',
		'layout' => 'cards',
		'no_reading' => true, // exclude from reading settings
		'no_slug' => true, // exclude from saving selected post page slug as custom post type slug,
		'ajax_posts_per_page' => true // add field in reading settings to select number of posts to load with ajax
	];
 ```|

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