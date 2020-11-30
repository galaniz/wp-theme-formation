# Admin

Admin settings generation and pages.

## `class Settings`

Uses [WordPress' Settings API](https://developer.wordpress.org/plugins/settings/using-settings-api/) to build out settings pages.

_Parameters:_

* `$args`  
_Type:_ `associative array`  
_Default:_ `[]`  
_Parameters:_

| Name | Type | Default | Required | Description
|--|--|--|--|--|
| `page` | `string` | `''` | ✓ | Page to register settings.
| `fields` | `array` | `[]` | ✓ | See [class Field](/src/common/#user-content-class-field). Includes section and tab options.
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
## `class Reading`

Adds the following fields to WordPress' default reading settings page using formation class variable [`$cpt`](/src/#user-content-public-static-cpt). Note: if `no_reading` is set, these fields are not added for that custom post type. Additional fields can be pushed to `Reading::$additional_fields` before instantiating class.

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

How many posts to load with ajax. This field is added if `ajax_posts_per_page` is set in [`$cpt`](/src/#user-content-public-static-cpt).  
*Type:* `int`

## `class Theme`

Adds a new settings page with fields for commonly used theme items. It includes default fields for uploading svg and png logos as well as a textbox for footer text.

_Parameters:_

* `$args`  
_Type:_ `associative array`  
_Default:_ `[]`  
_Parameters:_

| Name | Type | Default | Description
|--|--|--|--|
| `recaptcha` | `boolean` | `false` | Include Google Recaptcha tab and fields.  
| `mailchimp_list_locations` | `associative array` | `[]` | Include Mailchimp tab and fields. name `str` => location `str`  
| `sections` | `array` | `[]` | See [settings](#settings).
| `fields` | `array` | `[]` | See [settings](#settings).
| `scripts` | `function/null` | `null` | Pass custom scripts to page.
