<?php 

require_once ABSPATH.WPINC.'/class-wp-image-editor.php';
require_once ABSPATH.WPINC.'/class-wp-image-editor-gd.php';
require_once ABSPATH.WPINC.'/class-wp-image-editor-imagick.php';

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
				$image_2x = $this->_resize_2x($resized['width'], $resized['height'], $image);
				if (!is_wp_error($image_2x)) {
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

				imagedestroy($image);

				if (!is_wp_error($resized) && $resized) {
					unset($resized['path']);
					$metadata[$size] = $resized;
				}
			}

			$this->size = $orig_size;
		}

		// Full sized 2x image generation
		$image_2x = $this->_resize_2x($orig_size['width'], $orig_size['height'], $this->image);
		if (!is_wp_error($image_2x)) {
			$file_data_2x = $this->get_output_format();
			// Suffix, dest_path, extension so we generate a -@2x filename instead of 
			// just the height and width of the new file
			$filename_2x = $this->generate_filename(wphidpi_suffix_base(), null, $file_data_2x['1']);
			add_filter('jpeg_quality', 'wphidpi_jpeg_quality');
			$resized_2x = $this->_save($image_2x, $filename_2x);
			remove_filter('jpeg_quality', 'wphidpi_jpeg_quality');
			if (!is_wp_error($resized_2x) && $resized_2x) {
				unset($resized_2x['path']);
				$metadata[wphidpi_suffix_base()] = $resized_2x;
			}
			imagedestroy($image_2x);
		}

		return $metadata;
	}

	protected function _resize_2x($orig_w, $orig_h, $image) {
		$new_w = intval($orig_w) * 2;
		$new_h = intval($orig_h) * 2;

		
		$resized = wp_imagecreatetruecolor($new_w, $new_h);
		imagecopyresampled($resized, $image, 0, 0, 0, 0, $new_w, $new_h, $orig_w, $orig_h);

		if (is_resource($resized)) {
			$this->update_size($new_w, $new_h);
			return $resized;
		}

		return new WP_Error('image_2x_resize_error', __('Image 2x resize failed.'), $this->file);
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
