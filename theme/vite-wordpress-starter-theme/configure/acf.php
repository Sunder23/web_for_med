<?php
/**
 * Theme file.
 *
 * @package Vite_Starter
 */

/**
 * Sets the directory where ACF field group JSON is saved.
 *
 * @return string Path to the acf-json directory.
 */
function starter_acf_json_save_point() {
	return WFB_THEME_PATH . '/configure/acf/acf-json';
}
add_filter( 'acf/settings/save_json', 'starter_acf_json_save_point' );

/**
 * Adds the theme's acf-json directory to the ACF JSON load points.
 *
 * @param array $paths Existing ACF JSON load paths.
 * @return array Modified list of ACF JSON load paths.
 */
function starter_acf_json_load_point( $paths ) {
	unset( $paths[0] );

	$paths[] = WFB_THEME_PATH . '/configure/acf/acf-json';

	return $paths;
}
add_filter( 'acf/settings/load_json', 'starter_acf_json_load_point' );
