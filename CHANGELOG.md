# Changelog

All notable changes to this project will be documented in this file.

## [2.0.1] - 2021-05-17

### Added
- CircleCI and Release It package to automate releases.
- Console message for blocks added `insert-block.js`.

### Changed
- Package and Webpack config updates.

## [2.0.3] - 2021-05-24

### Added
- Output head and footer scripts from `Theme`.

## [2.0.4] - 2021-06-29

### Changed
- `Ajax` `get_posts` make offset optional and include paged as possible param.

## [2.0.5] - 2021-10-04

### Added
- Script to restrict embed variations in gutenberg editor.
- Width and height to return array in `get_image` utility method.

### Changed
- Update front-end dependencies to latest version.
- Update webpack config and front-end asset structure from dependencies updates.

## [2.0.6] - 2021-10-25

### Changed
- Update to use `@use`/`@forward` instead of `@import`.

## [3.0.0] - 2022-04-19

### Changed
- Update all JS files to use JavaScript Standard Style.
- Update all SCSS files to use Stylelint Standard SCSS.
- Update all PHP files to use WordPress Coding Standards.
- Move `get_media_pos_class` utility method to `Utils_Optional`.

### Fixed
- `Field` checkboxes not reflecting saved value.
- `render_form` attributes causing front end issues.

## [3.0.1] - 2022-05-02

### Added
- Link attributes to `start_el` method in `Nav_Walker` class.

## [3.0.2] - 2022-06-02

### Changed
- A11y updates to `Field` class.
- Block dependencies static in `Blocks` class.
- Make `Formation` variables static for more flexible use.
- Update buttons in render methods to use spans instead of divs.
- Icon path optional in `render_social` method.
- Add option to run shortcode in `get_excerpt` method.

## [3.0.3] - 2022-07-01

### Changed
- `Reading` class fields conditionals.
- `Formation` callback methods static for minimal use cases.
- Update `get_excerpt` method in `Utils`.

## [3.0.4] - 2022-07-20

### Fixed
- `get_posts_per_page` utility method fix for `get_option` name.

## [3.0.5] - 2022-09-20

### Changed
- Remove all references to Google APIs.
- Formatting tabs to be cleaner.

## [3.0.6] - 2022-10-08

### Fixed
- `Settings` sanitization

### Changed
- Defer scripts instead of DOMContentLoaded and remove console messages
- Update CSS class names for newer version of Formation and remove unnecessary classes throughout
- `Theme` class make png logo and reusable blocks optional
- `Utils_Render` form method simplify and update some args

## [3.0.7] - 2022-10-14

### Removed
- `render_modal` and `render_table`.

### Added
- Uses and provides context to `Block` class
- `contact-mailchimp` option for `send_form` in `Ajax` class

### Changed
- Update `Contact_Form` for better a11y and flexibility
- Remove mailchimp list locations from `Theme` class and move to `Contact_Form`
- Render methods to have fewer hard coded classes
- Small organizational updates to `Field` class

## [3.0.8] - 2022-10-20

### Added
- theme-sample.json

### Changed
- Field gap options for `Contact_Form` optional
- wp.domReady replace window load listener
- More attributes for radio-text and radio-select types in `Field` class
- Conditional option for fields in `Contact_Form`
- Tags for mailchimp form string type
- Replace some classes with attributes in `Field` class
- Simplify required labels and legends

## [3.0.9] - 2022-10-25

### Fixed
- `is_external_url` host check

### Changed
- withSelect callback clean up

## [3.0.10] - 2022-10-31

### Changed
- Add ability to set autocomplete tokens for fields in `Contact_Form`

## [3.0.11] - 2022-10-31

### Added
- Size attribute to fields in `Contact_Form` and `Field`

### Changed
- Span for required icon in `Contact Form` legend and `Field` labels

## [3.0.12] - 2022-11-08

### Changed
- `render_form` move error summary to top
- `Contact_Form` required visually hidden text for fieldset

## [3.0.13] - 2022-11-11

### Added
- Page excerpts in `Formation`

## [3.0.14] - 2022-11-12

### Changed
- Replace urldecode with rawurldecode, more flexible codes in response and detailed Mailchimp error response in `Ajax`

## [3.0.15] - 2022-11-13

### Changed
- `Ajax` return contact and mailchimp results for more flexible output

## [3.0.16] - 2022-12-08

### Fixed
- HTTP codes in `Field` and `Ajax`
- Tags fix Mailchimp in `Ajax`

### Changed
- More robust plain and html email output for `send_contact_form` in `Ajax`

### Removed
- Size attribute in `Field` and `Contact_Form`

### Added
- Data attribute for radio-tet and radio-select inputs in `Field`

## [3.0.17] - 2022-12-15

### Fixed
- Reply to header in `send_contact_form` in `Ajax`

## [3.0.18] - 2022-12-16

### Fixed
- Theme head script output 

## [3.0.19] - 2022-12-20

### Fixed
- Required variable error for output paramater in `Field`

## [3.0.20] - 2023-01-18

### Fixed
- Required parameter fixes in various methods

### Changed
- Search form label
