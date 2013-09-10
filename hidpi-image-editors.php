<?php

require_once ABSPATH.WPINC.'/class-wp-image-editor.php';
require_once ABSPATH.WPINC.'/class-wp-image-editor-gd.php';
require_once ABSPATH.WPINC.'/class-wp-image-editor-imagick.php';

// @TODO Implement 2x image create for Imagick class
Class WPHiDPI_Image_Editor_Imagick extends WP_Image_Editor_Imagick {
	function multi_resize($sizes) {
		$metadata = array();
		$orig_size = $this->size;
		$orig_image = $this->image->getImage();

		foreach ($sizes as $size => $size_data) {
			if (!$this->image)
				$this->image = $orig_image->getImage();

			if (!(isset($size_data['width']) && isset($size_data['height'])))
				continue;

			if (!isset($size_data['crop'])) {
				$size_data['crop'] = false;
			}

			$resize_result = $this->resize($size_data['width'], $size_data['height'], $size_data['crop']);

			if (!is_wp_error($resize_result)) {

				$resized = $this->_save($this->image);
				// NEED TO RESIZE AGAIN HERE

				$this->image->clear();
				$this->image->destroy();
				$this->image = null;

				if (!is_wp_error($resized) && $resized) {
					unset($resized['path']);
					$metadata[$size] = $resized;
				}
			}

			$this->size = $orig_size;
		}

		$this->image = $orig_image;

		return $metadata;
	}
}

Class WPHiDPI_Image_Editor_GD extends WP_Image_Editor_GD {
	function multi_resize($sizes) {
		$metadata = array();
		$orig_size = $this->size;

		foreach ($sizes as $size => $size_data) {
			if (!(isset($size_data['width']) && isset($size_data['height']))) {
				continue;
			}

			if (!isset($size_data['crop'])) {
				$size_data['crop'] = false;
			}

			$image = $this->_resize($size_data['width'], $size_data['height'], $size_data['crop']);

			if (!is_wp_error($image)) {
				$resized = $this->_save($image);

			// @2x generation
				$image_2x = null;
				$old_size = $this->size;
				// Gets used in _save
				$this->size = $orig_size;

				if ($size_data['crop']) {
					$image_2x = $this->_resize_2x($resized, true);
				}
				// Not a crop, ensure that original is at least 2x of the size created
				else if ($orig_size['width'] >= (2 * $resized['width']) && $orig_size['height'] >= (2 * $resized['height'])) {
					$image_2x = $this->_resize_2x($resized, false);
				}


				if (!empty($image_2x) && !is_wp_error($image_2x)) {
					$file_data_2x = $this->get_output_format();
					// Suffix, dest_path, extension so we generate a -@2x filename instead of
					// just the height and width of the new file
					$filename_2x = $this->generate_filename($this->get_2x_suffix(), null, $file_data_2x['1']);
					add_filter('jpeg_quality', 'wphidpi_jpeg_quality');
					$resized_2x = $this->_save($image_2x, $filename_2x);
					remove_filter('jpeg_quality', 'wphidpi_jpeg_quality');
					if (!is_wp_error($resized_2x) && $resized_2x) {
						unset($resized_2x['path']);
						$metadata[$size.wphidpi_suffix()] = $resized_2x;
					}
					imagedestroy($image_2x);
				}

				// Set this back to the original resized image size
				$this->size = $old_size;

			// End @2x generation

				imagedestroy($image);

				if (!is_wp_error($resized) && $resized) {
					unset($resized['path']);
					$metadata[$size] = $resized;
				}
			}

			$this->size = $orig_size;
		}

		return $metadata;
	}

	// Basically same as _resize but multiplies dst_w and dst_h by 2
	protected function _resize_2x($new_size, $crop) {
		$dims = image_resize_dimensions($this->size['width'], $this->size['height'], $new_size['width'], $new_size['height'], $crop);
		if (!$dims) {
			return new WP_Error('error_getting_dimensions', __('Image resize failed.', 'wphidpi'), $this->file);
		}
		list($dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h) = $dims;

		// Unique to _resize_2x
		$dst_w *= 2;
		$dst_h *= 2;

		$resized = wp_imagecreatetruecolor($dst_w, $dst_h);
		imagecopyresampled($resized, $this->image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);

		if (is_resource($resized)) {
			$this->update_size($dst_w, $dst_h);
			return $resized;
		}

		return new WP_Error('image_resize_error', __('Image resize failed.', 'wphidpi'), $this->file);
	}

	public function get_2x_suffix() {
		if (!$this->get_size()) {
			return false;
		}

		$width = intval($this->size['width']) / 2;
		$height = intval($this->size['height']) / 2;
		return "{$width}x{$height}".wphidpi_suffix();
	}
}
