<?php
/**
 * Theme file.
 *
 * @package Vite_Starter
 */

if ( ! defined( 'WFB_THEME_PATH' ) ) {
	define( 'WFB_THEME_PATH', get_stylesheet_directory() );
}
if ( ! defined( 'WFB_THEME_URI' ) ) {
	define( 'WFB_THEME_URI', get_stylesheet_directory_uri() );
}
if ( ! defined( 'WFB_THEME_VERSION' ) ) {
	define( 'WFB_THEME_VERSION', '1.0.0' );
}

$starter_modules = array(
	'configure/post-types.php',
	'configure/taxonomies.php',
	'configure/theme-hooks.php',
	'configure/ajax.php',
	'configure/utilities.php',
	'configure/js-css.php',
	'configure/shortcodes.php',
	'configure/analytics.php',
	'configure/acf.php',
	'configure/acf-blocks.php',
	'configure/toc.php',
	'configure/optimize.php',
);

foreach ( $starter_modules as $starter_module ) {
	require_once __DIR__ . '/' . $starter_module;
}

if ( is_admin() ) {
	require_once __DIR__ . '/configure/admin.php';
}
unset( $starter_modules, $starter_module );
