# WP HiDPI Images

WP HiDPI Images is an easy to use plugin that serves images which look good on both a normal screen and HiDPI displays. It generates and serves 2x versions of the image sizes defined by your theme or plugins, provided that the uploaded image is at least that size. All of this is done behind the scenes, there is no settings to set or additional image size to select when inserting media into a post. 

Cropped image sizes will also generate a 2x version, though at a slightly lower quality than a non cropped image size. 

## Usage
- Activate the plugin, it'll handle the rest

## Pre Existing Images

- Existing media will not have the 2x sizes automatically generated. To do this it is recommended to use [Regenerate Thumbnails](http://wordpress.org/plugins/regenerate-thumbnails/) plugin.

- Cropped images that have been inserted into the content and not had their thumbnails regenerated will be replaced with the full version of the image, however the height and width restrictions will remain.