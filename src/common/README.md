# Common

Commonly used classes that contain public and admin functionality.

## Field

### `class Field`

#### `public static render( $args, &$output )`

Output sections of fields and fields.

_Parameters:_

* `$args`  
_Type:_ `associative array`  
_Default:_ `[]`  
_Parameters:_

| Name | Type | Default | Description
|--|--|--|--|
| `name` | `string` | `''` | If multi, include top level name here.
| `fields` | `array` | `[]` | See [`render_field`](#user-content-public-static-function-render_field-args-output-index-data-copy-multi-multi_col-).
| `data` | `string/array` | `''` | Data to fill inputs with in [`render_field`](#user-content-public-static-function-render_field-args-output-index-data-copy-multi-multi_col-).
| `multi` | `boolean` | `false` | Add or remove multiple field(s).
| `hidden` | `boolean` | `false` | In admin and if true, set section to display none.
| `multi_col` | `boolean` | `false` | If multi, display as columns instead of rows.
| `no_group` | `boolean` | `false` | In frontend and if true, don't wrap in group div.

* `$output`  
_Type:_ `string`  

_Returns:_ `string`

#### `public static function render_field( $args, &$output, $index, $data, $copy $multi, $multi_col )`

Output fields. Works with [`render`](#user-content-public-static-render-args-output-).

_Parameters:_

* `$args`  
_Type:_ `associative array`  
_Default:_ `[]`  
_Parameters:_

| Name | Type | Default | Description
|--|--|--|--|
| `name` | `string` | `''` | Input name and id.
| `type` | `string` | `'text'` | Input type. Possible values: `text`<br>`email`<br>`checkbox`<br>`radio`<br>`number`<br>`hidden`<br>`checkbox_group`<br>`radio_group`<br>`textarea`<br>`select`<br>`file`(admin)<br>`richtext`(admin)<br>`link` (admin)
| `label` | `string/boolean` | `false` | Label text for input.
| `label_class` | `string` | `''` | Add class to label div.
| `label_hidden` | `boolean` | `false` | If true, label not included.
| `label_above` | `boolean` | `true` | If true, label added before input, otherwise added after.
| `placeholder` | `string` | `''` | Add placeholder to input.
| `class` | `string` | `''` | Add class to input.
| `field_class` | `string` | `''` | Add class to field.
| `attr` | `associative array` | `[]` | Add attributes to input.
| `options` | `associative array/array` | `[]` | Applies to types: `select`, `checkbox_group` and `radio_group`. Accepts an associative array of value => label pairs or an array of arrays.
| `hidden` | `string/boolean` | `false` | Set field to display none if `'100'` or `true` and  input contains no `value`.
| `before` | `string` | `''` | HTML content before input.
| `after` | `string` | `''` | HTML content after input.
| `value` | `string` | `''` | Value attribute content or selected value for `select`, `checkbox`, `radio`, `checkbox_group` or `radio_group`.
| `file_type` | `string` | `'file'` | Applies to file `type`. Also accepts `'image'` as value.
| `accept` | `string` | `''` | Applies to file `type`. File types accepted for upload.
| `wp` | `boolean` | `false` | Applies to file `type`. If true, upload from wp media library.
| `rows` | `int` | `4` | Applies to richtext `type`. See [wp_editor](https://developer.wordpress.org/reference/functions/wp_editor/).
| `quicktags` | `boolean` | `false` | Applies to richtext `type`. See [wp_editor](https://developer.wordpress.org/reference/functions/wp_editor/).
| `wpautop` | `boolean` | `false` | Applies to richtext `type`. See [wp_editor](https://developer.wordpress.org/reference/functions/wp_editor/).
| `p_tags` | `boolean` | `true` | Applies to richtext `type`. See [wp_editor](https://developer.wordpress.org/reference/functions/wp_editor/).
| `toolbar` | `string` | `'bold,italic,separator,bullist,numlist,blockquote,separator,link'` | Applies to richtext `type`. See [wp_editor](https://developer.wordpress.org/reference/functions/wp_editor/).

* `$output`  
_Type:_ `string`  

* `$index`  
_Type:_ `int`  
_Default:_ `0`

* `$data`  
_Type:_ `string|array`  
_Default:_ `''`

* `$copy`  
_Type:_ `boolean`  
_Default:_ `false`  
_Note:_ Passed from [`render`](#user-content-public-static-render-args-output-).

* `$multi`  
_Type:_ `boolean`  
_Default:_ `false`  
_Note:_ Passed from [`render`](#user-content-public-static-render-args-output-).

* `$multi_col`  
_Type:_ `boolean`  
_Default:_ `false`  
_Note:_ Passed from [`render`](#user-content-public-static-render-args-output-).

_Returns:_ `string`

#### `public static function filter_multi_fields( &$array, $required )`

Recursively filter out multi fields with empty required fields.

_Parameters:_

* `$array`  
Contains multi fields value.  
_Type:_ `array`  
_Required:_ true

* `$required`  
Contains required keys.  
_Type:_ `array`  
_Default:_ `[]`

#### `public static function scripts( $child )`

Enqueued scripts and styles. Call in [`admin_enqueue_scripts`](https://developer.wordpress.org/reference/hooks/admin_enqueue_scripts/).

_Parameters:_

* `$child`  
Specify if child theme.  
_Type:_ `boolean`  
_Default:_ false

#### `public static function file_actions()`

Ajax actions and callback to upload/remove files.

### `class File_Upload`

Handle file uploads outside of wp media library.

_Parameters:_

* `$args`  
_Type:_ `associative array`  
_Default:_ `[]`  
_Parameters:_

| Name | Type | Default | Description
|--|--|--|--|
| `uploads_dir` | `string` | `''` | Absolute path to directory.
| `uploads_url` | `string` | `''` | Url to directory.
| `success` | `boolean/function` | `false` | Callback function when file successfully uploaded. Array of data passed: title, url, mime_type, size and ext.
| `error` | `boolean/function` | `false` | Callback function when error uploading file. Error message passed.

### `class Select_Fields`

General fields to select for form generation.

#### `public static function get( $name )`

Get fields.

_Parameters:_

* `$name`  
Name to prefix fields.
_Type:_ `string`  
_Default:_ `''`   
_Required:_ true

_Returns:_ `array`

#### `public static function render( $fields, $group )`

Output fields. Uses [`Field::render`](#user-content-public-static-render-args-output-).

_Parameters:_

* `$fields`  
_Type:_ `array`  
_Default:_ `[]`   
_Required:_ true

* `$group`  
_Type:_ `boolean`  
_Default:_ `true`

_Returns:_ `string`  

#### `public static function filter( $value )`

Filter out if required fields `['type']` empty. Uses [`Field::filter_multi_fields`](#user-content-public-static-function-filter_multi_fields-array-required-).

_Parameters:_

* `$value`  
_Type:_ `array`  
_Default:_ `[]`   
_Required:_ true

_Returns:_ `$value`

#### `public static function scripts()`

Enqueued scripts. Call in [`admin_enqueue_scripts`](https://developer.wordpress.org/reference/hooks/admin_enqueue_scripts/).

## Blocks

### `class Blocks`

Register custom gutenberg blocks.

_Parameters:_

* `$args`  
_Type:_ `associative array`  
_Default:_ `[]`  

| Name | Type | Default | Description
|--|--|--|--|
| `folder_url` | `string` | `''` | Required. Url to folder containing block scripts.
| `extend_media` | `boolean` | `false` | If true, extends wp media block to add container div with option to specify width.

#### `public static $blocks`

Blocks to register. Append before instantiating.

_Type:_ `array`  
_Default:_ `[]`  
_Accepts:_ `associative array`

| Name | Type | Default | Description
|--|--|--|--|
| `attr` | `associative array` | `[]` | Attribute key and type.
| `default` | `associative array` | `[]` | Default values for attr.
| `parent` | `array` | `[]` | List of parent block names.
| `render` | `function` | `callable` | Callback function that returns output.
| `handle` | `string` | `''` | Required. Script handle name.
| `script` | `string` | `''` | Required. Script path relative to `folder_url`

```php
Blocks::$blocks['custom-block'] = [
  'attr' => [
  'id' => ['type' => 'int'],
  'title' => ['type' => 'string'],
  'text' => ['type' => 'string']
  ],
  'default' => [
  'id' => 0,
  'title' => '',
  'text' => ''
  ],
  'render' => [__CLASS__, 'render_custom_block'],
  'handle' => 'custom_block',
  'script' => 'custom-block.js'
];
```

### `class Contact_Form`

Contact form gutenberg block, registered with [`class Blocks`](#) and rendered with [`Field::render`](#user-content-public-static-render-args-output-).

```php
namespace WP_FRM\Blocks;

use Formation\Common\Blocks\Contact_Form;

class Custom_Blocks {
  public function __construct() {
  $contact_form_blocks = new Contact_Form();
  }
}
```
