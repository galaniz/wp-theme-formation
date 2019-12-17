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
| `fields` | `array` | `[]` | See [`render_field`](#).
| `data` | `string/array` | `''` | Data to fill inputs with in [`render_field`](#).
| `multi` | `boolean` | `false` | Add or remove multiple field(s).
| `hidden` | `boolean` | `false` | In admin and if true, set section to display none.
| `multi_col` | `boolean` | `false` | If multi, display as columns instead of rows.
| `no_group` | `boolean` | `false` | In frontend and if true, don't wrap in group div.

* `$output`  
_Type:_ `string`  

_Returns:_ `string`

#### `public static function render_field( $args, &$output, $index, $data, $copy $multi, $multi_col )`

Output fields. Works with [`render`](#).

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
_Note:_ Passed from [`render`](#).

* `$multi`  
_Type:_ `boolean`  
_Default:_ `false`  
_Note:_ Passed from [`render`](#).

* `$multi_col`  
_Type:_ `boolean`  
_Default:_ `false`  
_Note:_ Passed from [`render`](#).

_Returns:_ `string`




### `class File_Upload`
### `class Select_Fields`

## Blocks
