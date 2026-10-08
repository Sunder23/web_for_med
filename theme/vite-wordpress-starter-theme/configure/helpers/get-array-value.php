<?php
/**
 * Theme file.
 *
 * @package Vite_Starter
 */

/**
 * Retrieves an array value by key, returning a fallback when the key is absent.
 *
 * @param array      $source_array Array to read from.
 * @param int|string $key          Key to look up.
 * @param mixed      $fallback     Value to return when the key does not exist.
 * @return mixed Value at the given key, or the fallback value.
 */
function starter_get_array_value( $source_array, $key, $fallback = false ) {
	return isset( $source_array[ $key ] ) ? $source_array[ $key ] : $fallback;
}
