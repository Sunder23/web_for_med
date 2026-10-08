<?php
/**
 * Theme file.
 *
 * @package Vite_Starter
 */

/**
 * Adds SVG to the allowed upload mime types.
 *
 * @param array $mimes Allowed mime types.
 * @return array Modified list of allowed mime types.
 */
function starter_allow_svg_uploads( $mimes ) {
	$mimes['svg'] = 'image/svg+xml';

	return $mimes;
}
add_filter( 'upload_mimes', 'starter_allow_svg_uploads', 1 );
