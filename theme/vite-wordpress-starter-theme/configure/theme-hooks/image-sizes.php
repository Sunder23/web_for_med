<?php
/**
 * Theme file.
 *
 * @package Vite_Starter
 */

/**
 * Removes unused core image sizes.
 *
 * @return void
 */
function starter_remove_image_sizes() {
	remove_image_size( '1536x1536' );
	remove_image_size( '2048x2048' );
}
add_action( 'after_setup_theme', 'starter_remove_image_sizes' );

/**
 * Stops WordPress generating the default intermediate sizes to avoid overloading the server.
 *
 * @param array $sizes Intermediate image sizes.
 * @return array Filtered image sizes.
 */
function starter_remove_default_image_sizes( $sizes ) {
	unset( $sizes['large'], $sizes['medium'], $sizes['medium_large'] );

	return $sizes;
}
add_filter( 'intermediate_image_sizes_advanced', 'starter_remove_default_image_sizes' );

// Disable the "scaled" big image size.
add_filter( 'big_image_size_threshold', '__return_false' );
