# WP HiDPI Images

WP HiDPI Images is an easy to use plugin that serves images which look good on both a normal screen and HiDPI displays. It generates and serves 2x versions of the image sizes defined by your theme or plugins. All of this is done behind the scenes, there is no settings to set or additional image size to select when inserting media into a post. 

2x images of non cropped sizes will be created from the original image. If you do not upload an image that is at least 2x the defined image sizes, it will not create a 2x version; the full sized image will be served instead of a 2x version. Cropped image sizes will always generate a 2x version based off of the original image even if the 2x version dimensions are larger than the original image. 

## FAQ

- If I deactivate the plugin, will my images still work?
- Yes. This plugin does not change any files, it only creates new ones and filters them in where appropriate.

## Usage

- Activate the plugin, it'll handle the rest

## Pre Existing Images

- Existing media will not have the 2x sizes automatically generated. To do this it is recommended to use [Regenerate Thumbnails](http://wordpress.org/plugins/regenerate-thumbnails/) plugin.
- Cropped images that have been inserted into the content and have not had their thumbnails regenerated will be replaced with the full version of the image, however the height and width restrictions will remain.
