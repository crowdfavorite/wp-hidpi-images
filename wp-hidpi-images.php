<?php
/*
Plugin Name: WP HiDPI Images
Plugin URI: http://crowdfavorite.com/wordpress/plugins/ 
Description: Create and insert high resolution images to support high DPI displays.
Version: 0.1
Author: Crowd Favorite
Author URI: http://crowdfavorite.com
*/

function wphidpi_image_editors($editors) {
	require_once('hidpi-image-editors.php');
	// Selection occurs based on a number of requirements but tests sequentially
	array_unshift($editors, 'WPHiDPI_Image_Editor_GD'); // @TODO WPHiDPI_Image_Editor_Imagick
	return $editors;
}
add_filter('wp_image_editors', 'wphidpi_image_editors');

function wphidpi_jpeg_quality($quality) {
	// 0-100 scale
	return 30;
}

// Insertion magic, will also work for backend and various get functions 
function wphidpi_image_downsize($out, $id, $size) {
	
	remove_filter('image_downsize', 'wphidpi_image_downsize', 10, 3);
	if (is_array($size)) {
		foreach ($size as &$component) {
			$component = intval($component) * 2;	
		}		
	}
	// Full treated differently
	else if (strtolower($size) == 'full') {
		$size = wphidpi_suffix_base();
	}
	else {	
		$size = $size.wphidpi_suffix();
	}
	$downsize = image_downsize($id, $size);

	// If downsize isn't false  and this is an intermediate
	if ($downsize && $downsize[3]) {
		$downsize[1] = intval($downsize[1]) / 2; 
		$downsize[2] = intval($downsize[2]) / 2;
		return $downsize; 
	}
 	return false;
}
add_filter('image_downsize', 'wphidpi_image_downsize', 10, 3);

function wphidpi_suffix() {
	return '-'.wphidpi_suffix_base();
}

function wphidpi_suffix_base() {
	return '@2x';
}

// Cleanup after ourselves on image delete
function wphidpi_delete_image($path) {
	$path_bits = explode('.', $path);
	$path_2x = '';
	$length = count($path_bits);
	$uploadpath = wp_upload_dir();
	foreach ($path_bits as $key => $bit) {
		if ($length - 1 == $key) {
			$path_2x .= wphidpi_suffix().'.'.$bit;		
			break;
		}	
		else if ($length - 2 == $key) {
			$path_2x .= $bit;
		}
		else {
			$path_2x .= $bit.'.';
		}
	}

	if ($path_2x) {
		// Original file
		if (file_exists($path_2x)) {
			@unlink($path_2x);
		}
		// Intermediate sizes
		else if (file_exists(path_join($uploadpath['basedir'], $path_2x))) {
			@unlink(path_join($uploadpath['basedir'], $path_2x));
		}
	}
	// This is a filter not an action, return the original path
	return $path;
}
add_filter('wp_delete_file', 'wphidpi_delete_image');
