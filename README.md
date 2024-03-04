# WP BookWidgets: Integrate BookWidgets widgets in your WordPress site

## Description

Integrate [BookWidgets](https://www.bookwidgets.com) widgets in your WordPress
site. Quickly embed or link to widgets by shortcode.

Use one of the shortcodes in your page or posts to link or embed widgets.

### `[bw_link]`: Link to a widget

Use the link of the widget from the "Share" dialog in BookWidgets:

    Have a look at [bw_link url="https://www.bookwidgets.com/play/ThIsIsa-LiNk123?teacher_id=123456"]my widget[/bw_link]

If you enabled sharing by shortcode for the widget, you can also link
using the shortcode of the widget:

    Have a look at [bw_link code="ABCDE"]my widget[/bw_link]

### `[bw_embed]`: Embed a widget

Use the link of the widget from the "Share" dialog in BookWidgets:

    [bw_embed url="https://www.bookwidgets.com/play/ThIsIsa-LiNk123?teacher_id=123456"]

If you enabled sharing by shortcode for the widget, you can also link
using the shortcode of the widget:

    [bw_embed code="ABCDE"]

Supported parameters:

- `code`: Widget shortcode
- `width`: Width of the widget frame
- `height`: Height of the widget frame
- `allowfullscreen`: set this to `1` to add a fullscreen button in the bottom right corner


## Installation

1. Upload the plugin files to the `/wp-content/plugins/wp-bookwidgets` directory.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Use the Settings→BookWidgets screen to configure the plugin

## Changelog

### 0.9

- Add support for `url` parameter to `bw_embed` and `bw_link`

### 0.8

- Fixed bug with missing / badly displayed content following widget when using LTI
- Fixed layout issues

### 0.3

- Added a `allowfullscreen` option to `bw_embed`

### 0.1

- Initial release

