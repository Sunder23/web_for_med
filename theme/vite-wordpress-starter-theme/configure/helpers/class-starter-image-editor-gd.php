<?php
/**
 * Theme file.
 *
 * @package Vite_Starter
 */

/**
 * GD image editor that converts palette (indexed-color) images to truecolor on load.
 *
 * GD's imagewebp() rejects palette images ("Palette image not supported by webp") and
 * writes an empty file, so 8-bit PNGs could not be converted to WebP when Imagick lacks
 * WebP support. Swapped in for WP_Image_Editor_GD via `wp_image_editors` (configure/optimize.php).
 */
class Starter_Image_Editor_GD extends WP_Image_Editor_GD {

	/**
	 * Loads the image and promotes palette images to truecolor, preserving alpha.
	 *
	 * @return true|WP_Error True if loaded successfully; WP_Error on failure.
	 */
	public function load() {
		$loaded = parent::load();

		if ( true === $loaded && ! imageistruecolor( $this->image ) ) {
			imagepalettetotruecolor( $this->image );
			imagealphablending( $this->image, false );
			imagesavealpha( $this->image, true );
		}

		return $loaded;
	}
}
