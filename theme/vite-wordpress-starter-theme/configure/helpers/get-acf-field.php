<?php
/**
 * Theme file.
 *
 * @package Vite_Starter
 */

/**
 * Safe wrapper around ACF's get_field() that degrades when ACF is not active.
 *
 * @param string          $selector Field name or key.
 * @param int|string|bool $post_id Post ID, options page identifier, or false for the current post.
 * @param mixed           $fallback Value to return when ACF is not available.
 * @return mixed Field value, or the fallback value.
 */
function starter_get_field( $selector, $post_id = false, $fallback = null ) {
	if ( ! function_exists( 'get_field' ) ) {
		return $fallback;
	}

	return get_field( $selector, $post_id );
}
