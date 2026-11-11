<?php
/**
 * Theme file.
 *
 * @package Vite_Starter
 */

$starter_taxonomies = array(
	'case-deliverable.php',
	'case-technology.php',
	'case-status.php',
);

foreach ( $starter_taxonomies as $starter_taxonomy ) {
	require_once WFB_THEME_PATH . '/configure/taxonomies/' . $starter_taxonomy;
}
unset( $starter_taxonomies, $starter_taxonomy );
