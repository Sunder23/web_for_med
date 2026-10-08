<?php
/**
 * Theme file.
 *
 * @package Vite_Starter
 */

$starter_taxonomies = array();

foreach ( $starter_taxonomies as $starter_taxonomy ) {
	require_once WFB_THEME_PATH . '/configure/taxonomies/' . $starter_taxonomy;
}
unset( $starter_taxonomies, $starter_taxonomy );
