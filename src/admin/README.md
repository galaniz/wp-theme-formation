# Admin

Admin settings generation and pages.

## Settings

`Class` that uses [WordPress' Settings API](https://developer.wordpress.org/plugins/settings/using-settings-api/) to build out settings pages.

### Options

| Name | Type | Default | Required | Description
|--|--|--|--|--|
| `page` | `string` | `''` | ✓ | Page to register settings.
| `fields` | `array` | `[]` | ✓ | See [field class](#). Includes section and tab options.
| `sections` | `array` | `[]` |  | Add section to page. Provide id `str` and title `str` as associative array.
| `tabs` | `boolean` | `false` |  | Organize as tabs.

### Example

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
## Reading

`Class` that adds the following fields to WordPress' default reading settings page using base class variable [`$cpt`](https://github.com/galaniz/wp-theme-formation#user-content-public-static-cpt). Note: if `no_reading` is set, these fields are not added for that custom post type. Additional fields can be pushed to `Reading::$additional_fields` before instantiating class.

#### `{$cpt}_page`

Select page to display custom post type.   
*Type:* `int`   

#### `{$cpt}_posts_per_page`

Page displays selected number of posts.  
*Type:* `int`   

#### `{$cpt}_more_label`

More posts title.  
*Type:* `string`    

#### `{$cpt}_ajax_posts_per_page`

How many posts to load with ajax. This field is added if `ajax_posts_per_page` is set in [`$cpt`](https://github.com/galaniz/wp-theme-formation#user-content-public-static-cpt).  
*Type:* `int`

## Theme

`Class` that adds a new settings page with fields for commonly used theme items. It includes default fields for svg and png logos as well as footer text.

### Options

| Name | Type | Default | Description
|--|--|--|--|
| `recaptcha` | `boolean` | `false` | Include Google Recaptcha tab and fields.  
| `mailchimp_list_locations` | `associative array` | `[]` | Include Mailchimp tab and fields. name `str` => location `str`  
| `sections` | `array` | `[]` | See [settings](#settings).
| `fields` | `array` | `[]` | See [settings](#settings).
| `scripts` | `function/null` | `null` | Pass custom scripts to page.
