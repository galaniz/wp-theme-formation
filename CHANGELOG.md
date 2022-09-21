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
