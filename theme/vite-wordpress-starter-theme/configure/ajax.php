<?php
/**
 * Theme file.
 *
 * @package Vite_Starter
 */

$starter_ajax_handlers = array();

foreach ( $starter_ajax_handlers as $starter_ajax_handler ) {
	require_once WFB_THEME_PATH . '/configure/ajax/' . $starter_ajax_handler;
}
unset( $starter_ajax_handlers, $starter_ajax_handler );
