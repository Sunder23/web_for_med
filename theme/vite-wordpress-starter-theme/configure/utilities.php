<?php
/**
 * Theme file.
 *
 * @package Vite_Starter
 */

$starter_helpers = array(
	'get-static-dir.php',
	'get-array-value.php',
	'get-acf-field.php',
	'get-option.php',
	'is-block-content-view.php',
	'get-breadcrumb-items.php',
);

foreach ( $starter_helpers as $starter_helper ) {
	require_once WFB_THEME_PATH . '/configure/helpers/' . $starter_helper;
}
unset( $starter_helpers, $starter_helper );
