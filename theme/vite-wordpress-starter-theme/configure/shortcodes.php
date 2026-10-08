<?php
/**
 * Theme file.
 *
 * @package Vite_Starter
 */

$starter_shortcodes = array();

foreach ( $starter_shortcodes as $starter_shortcode ) {
	require_once WFB_THEME_PATH . '/configure/shortcodes/' . $starter_shortcode;
}
unset( $starter_shortcodes, $starter_shortcode );
