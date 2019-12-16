# WP Theme Formation

Base class, utilities and added functionality for building WordPress themes.

## Variables

#### `public static $namespace`

Namespace for handles, option and meta names.  
_Type:_ `string`  
_Default:_ `'frm'`

#### `public static $src_path`

Path from vendor to src folder.  
_Type:_ `string`  
_Default:_ `'/vendor/alanizcreative/wp-theme-formation/src/'`

#### `public static $cpt`

Store post type names and data (can add custom key => value pairs).  
_Type:_ `associative array`  
_Default:_ `[]`  
_Example:_
```php
self::$cpt = [
	'custom_post_type' => [
		'slug' => 'custom_post_type_slug',
		'label' => 'Custom Post Type',
		'layout' => 'cards',
		/* reading settings*/
		'no_reading' => true, // exclude from settings
		'no_slug' => true, // do not save post page slug as custom post type slug,
		'ajax_posts_per_page' => true // add field to select number of posts to load with ajax
	]
];
```

#### `public static $pt_layout`

Store layouts by post type. Appends layouts from `[$cpt](#public-static-cpt)`.   
_Type:_ `associative array`  
_Default:_ `[]`

#### `public static $posts_per_page`

Default number of posts to display by type/post type.  
_Type:_ `associative array`  
_Default:_ `[]`

#### `public $editor_color_palette`

Editor color palette theme support arguments.   
_Type:_ `array`  
_Default:_ `[]`  
_Example:_
```php
$this->editor_color_palette[] = [
	'name' => 'Background Light',
	'slug' => 'background-light',
	'color' => '#FFFFFF'
];
```

#### `public $image_sizes`

Custom image sizes to register. Name => size.  
_Type:_ `associative array`  
_Default:_ `[]`

#### `public $nav_menus`

Nav menus to register. Slug => label.  
_Type:_ `associative array`  
_Default:_ `[]`

#### `public $editor_style_url`

Stylesheet url for admin editor styles.  
_Type:_ `string`  
_Default:_ `''`

#### `public $styles`

Stylesheets to register. See [wp_enqueue_style](https://developer.wordpress.org/reference/functions/wp_enqueue_style/).     
_Type:_ `array`  
_Default:_ `[]`  
_Example:_
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
_Type:_ `array`  
_Default:_ `[]`  
_Example:_
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
_Type:_ `string`  
_Default:_ `''`  

#### `public static $classes`

Optional classes to add to fields, labels, buttons, inputs and icons.   
_Type:_ `array`  
_Default:_
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
_Type:_ `array`  
_Default:_
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

## Global Functions

#### `additional_script_data( $name, $data, $admin, $head )`

Pass data to front end.  
_Parameters:_

|  | Type | Default | Description
|--|--|--|--|
| `$name` | `boolean` | `false` | Name of variable on front end.
| `$data` | `array` | `[]` | Data to pass to front end.
| `$admin` | `boolean` | `false` | Only apply to admin.
| `$head` | `boolean` | `false` | Outputs in footer otherwise in head.

#### `write_log( $log )`
Write to debug log.  
_Parameters:_
* `$log`  
Data to output in debug log.  
_Type:_ `string|array|object`  
_Default:_ `''`

## Utilities

### Getters

#### `public static function get_namespaced_str( $name )`

Prefix string with `[$namespace](#public-static-namespace)` only if not already prefixed.  
_Parameters:_
* `$name`  
_Type:_ `string`  
_Default:_ `''`  

_Returns:_ `string`

#### `public static function get_posts_per_page( $post_type )`

Get posts per page by post type.
_Parameters:_
* `$post_type`  
_Type:_ `string`  
_Default:_ `'post'`  

_Returns:_ `int`

#### `public static function get_first_cat( $id, $taxonomy )`

Get first category for post.  
_Parameters:_
* `$id`  
_Type:_ `int`  
_Default:_ `0`

* `$taxonomy`  
_Type:_ `string`  
_Default:_ `''`

_Returns:_ `boolean|array`
```php
[
	'category_name',
	'http://site.com/category/category_name'
]
```

#### `public static function get_id_early_admin()`

Get id early in admin.  
_Returns:_ `int`

#### `public static function get_id_outside_loop()`

Get current post id outside loop.  
_Returns:_ `int`

#### `public static function get_excerpt( $args )`

Get excerpt from post, page, any string.  
_Parameters:_
* `$args`  
_Type:_ `associative array`  
_Default:_ `[]`  
_Parameters:_
|  | Type | Default | Description
|--|--|--|--|
| `$content` | `string` | `''` |
| `$words` | `boolean` | `false` | Whether to trim by words.
| `$length` | `int` | `55` | In words or characters.
| `$post_id` | `int` | get_the_ID() | Only if no `$content`, defaults to
| `$post` | `string` | get_post( `$post_id` ) | Only if no `$content`, becomes `$content`

_Returns:_ `string`

#### `public static function get_next_posts_link()`

Get url of next posts page as fallback for ajax load more posts.  
_Returns:_ `string|boolean`

#### `public static function get_next_comments_link()`

Get url of next comments page as fallback for ajax load more comments.  
_Returns:_ `string`

### Render
### Optional
