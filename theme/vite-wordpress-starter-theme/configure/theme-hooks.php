<?php
/**
 * Theme file.
 *
 * @package Vite_Starter
 */

$starter_theme_hooks = array(
	'register-menus.php',
	'nav-menu-bem-classes.php',
	'theme-support.php',
	'image-sizes.php',
	'allow-svg-uploads.php',
	'remove-wp-generator.php',
	'disable-auto-update-emails.php',
	'deprioritize-yoast-metabox.php',
	'disable-autoparagraph-wrapping-cf7.php',
	'enqueue-listing-templates-assets.php',
);

foreach ( $starter_theme_hooks as $starter_theme_hook ) {
	require_once WFB_THEME_PATH . '/configure/theme-hooks/' . $starter_theme_hook;
}
unset( $starter_theme_hooks, $starter_theme_hook );
