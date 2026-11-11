<?php
/**
 * Theme file.
 *
 * @package Vite_Starter
 */

/**
 * Whether a link points at the contact form (`#contacts` / `#contact-form`).
 *
 * Such links open the off-canvas contact drawer (partials/parts/contact-drawer.php)
 * instead of scrolling the page.
 *
 * @param mixed $url Link URL (ACF link `url` or a plain string).
 * @return bool True when the URL fragment targets the contact form.
 */
function starter_is_contact_link( $url ) {
	if ( ! is_string( $url ) || '' === $url ) {
		return false;
	}

	$fragment = wp_parse_url( $url, PHP_URL_FRAGMENT );

	return in_array( $fragment, array( 'contacts', 'contact-form' ), true );
}

/**
 * Returns the ` data-contact-open` attribute for contact-form links, or an empty string.
 *
 * The output is a fixed literal, so it is safe to echo inside a tag.
 *
 * @param mixed $url Link URL.
 * @return string Attribute string (with a leading space) or ''.
 */
function starter_contact_trigger_attr( $url ) {
	return starter_is_contact_link( $url ) ? ' data-contact-open' : '';
}
