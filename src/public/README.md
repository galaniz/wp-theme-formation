# Public

Classes/methods used on the frontend.

### `trait Ajax`

Actions and callbacks for ajax requests. Inserted into [`class Formation`](https://github.com/galaniz/wp-theme-formation/tree/master/src/admin/#user-content-class-formation).

#### `public static function create_nonce()`

Create nonces before form submission. Workaround for caching issues.

_Post:_

* `nonce_name`  
_Type:_ `string`  
_Required:_ true  

_Echo:_ `string` JSON containing nonce.

#### `public static function send_form( $priv_type )`

Validate nonces and recaptcha for contact and comment forms. Recaptcha API keys required from [`Theme`](https://github.com/galaniz/wp-theme-formation/tree/master/src/admin/#user-content-class-theme) settings.

_Parameters:_

* `$priv_type`  
Differentiate between logged in and non-logged in users. Possible values `'priv'` or `'nopriv'`.     
_Type:_ `string`  
_Default:_ `'nopriv'`  

_Post:_

* `nonce`  
_Type:_ `string`  
_Required:_ true  

* `nonce_name`  
_Type:_ `string`  
_Required:_ true

* `recaptcha`  
_Type:_ `string`  
_Required:_ true

* `type`  
_Type:_ `string`  
_Default:_ `'contact'`  
_Note:_ Possible values: `'contact'` `'mailchimp'` `'comment'`

#### `protected static function send_contact_form()`

Process and send contact form.

_Post:_

* `id`  
_Type:_ `string`  
_Required:_ true  
_Note:_ Must be part of option name `self::$namespace . '_form_' . $id` in order to fetch metadata like email and subject.

* `inputs`  
_Type:_ `array`  
_Required:_ true

_Echo:_ `string` JSON with success message.

#### `protected static function mailchimp_signup()`

Process mailchimp signup form. Mailchimp API key required from [`Theme`](https://github.com/galaniz/wp-theme-formation/tree/master/src/admin/#user-content-class-theme) settings.

_Post:_

* `location`  
_Type:_ `string`  
_Required:_ true  
_Note:_ Must be part of option name `self::$namespace . '_mailchimp_list_' . $location` in order to fetch list id.

* `inputs`  
_Type:_ `array`  
_Required:_ true

_Echo:_ `string` JSON with success message.

#### `public static function get_posts()`

Get more posts for posts and custom post types.

_Post:_

* `offset`  
_Type:_ `int`  
_Default:_ `0`

* `type`  
_Type:_ `string`  
_Default:_ `'post'`

* `posts_per_page`  
_Type:_ `int`  
_Default:_ `0`  
_Required:_ true

* `query_args_static`  
_Type:_ `array`  

* `query_args`  
_Type:_ `array`

* `filters`  
_Type:_ `array`   

_Echo:_ `string` of HTML or JSON

### `class Nav_Walker`

Customize nav html output. Extends WordPress [`Walker_Nav_Menu`](https://developer.wordpress.org/reference/classes/walker_nav_menu/) class.

_Parameters:_

* `$args`  
_Type:_ `associative array`  
_Default:_ `[]`  
_Note:_ Variables passed to callback functions: `&$this`, `&$output`, `$depth`, `$args`, `$item`.

| Name | Type | Default | Description
|--|--|--|--|
| `ul_class` | `string` | `''` |
| `li_class` | `string` | `''` |
| `li_attr` | `string` | `''` |
| `a_class` | `string` | `''` |
| `a_attr` | `string` | `''` |
| `before_output` | `function/boolean` | `false` | Called before list item output.
| `after_output` | `function/boolean` | `false` | Called after link item output.
| `before_link_output` | `function/boolean` | `false` | Called before link item output.
| `before_link_text_output` | `function/boolean` | `false` | Called before link item text output.
| `after_link_text_output` | `function/boolean` | `false` | Called after link item text output.
