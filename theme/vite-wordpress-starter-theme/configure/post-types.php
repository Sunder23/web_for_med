<?php
/**
 * Theme file.
 *
 * @package Vite_Starter
 */

$starter_post_types = array(
	'services.php',
	'directions.php',
	'cases.php',
);

foreach ( $starter_post_types as $starter_post_type ) {
	require_once WFB_THEME_PATH . '/configure/post-types/' . $starter_post_type;
}
unset( $starter_post_types, $starter_post_type );
