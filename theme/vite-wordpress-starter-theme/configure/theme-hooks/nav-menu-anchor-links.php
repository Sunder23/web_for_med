<?php
/**
 * Theme file.
 *
 * Anchor items in nav menus:
 * - `#contacts` / `#contact-form` open the off-canvas contact drawer
 *   (`data-contact-open`, see assets/src/js/components/contactDrawer.js);
 * - other fragment-only links (`#cases`, …) target front-page sections, so off
 *   the front page they point at the home URL (`/#cases`).
 *
 * @package Vite_Starter
 */

/**
 * Rewrites anchor-only menu links.
 *
 * @param array $atts Link attributes.
 * @return array
 */
function starter_nav_menu_anchor_link_atts( $atts ) {
	$href = isset( $atts['href'] ) ? (string) $atts['href'] : '';

	if ( starter_is_contact_link( $href ) ) {
		// Non-empty: the nav walker drops attributes with an empty value.
		$atts['data-contact-open'] = 'true';
		return $atts;
	}

	if ( '' !== $href && '#' === $href[0] && strlen( $href ) > 1 && ! is_front_page() ) {
		$atts['href'] = home_url( '/' . $href );
	}

	return $atts;
}
add_filter( 'nav_menu_link_attributes', 'starter_nav_menu_anchor_link_atts', 20 );
