<?php
/**
 * Theme file.
 *
 * @package Vite_Starter
 */

/**
 * Reads an ACF options-page field, cached per request.
 *
 * @param string $selector Field name or key.
 * @param mixed  $fallback Value to return when the field is unavailable.
 * @return mixed Field value, or the fallback value.
 */
function starter_get_option( $selector, $fallback = null ) {
	static $cache = array();

	if ( array_key_exists( $selector, $cache ) ) {
		return $cache[ $selector ];
	}

	$value              = starter_get_field( $selector, 'options', $fallback );
	$cache[ $selector ] = $value;

	return $value;
}
