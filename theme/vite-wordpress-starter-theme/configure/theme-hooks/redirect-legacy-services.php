<?php
/**
 * Theme file.
 *
 * @package Vite_Starter
 */

/**
 * Redirects the removed services CPT singles (`/services/<slug>/`) to the services page.
 *
 * The services CPT was replaced by a regular page at `/services/`; old singles that
 * still live in search indexes or internal links would otherwise end in a 404.
 *
 * @return void
 */
function starter_redirect_legacy_services() {
	if ( ! is_404() ) {
		return;
	}

	$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
	$path        = trim( (string) wp_parse_url( $request_uri, PHP_URL_PATH ), '/' );
	$home_path   = trim( (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH ), '/' );

	if ( '' !== $home_path && 0 === strpos( $path, $home_path . '/' ) ) {
		$path = substr( $path, strlen( $home_path ) + 1 );
	}

	if ( 0 !== strpos( $path, 'services/' ) ) {
		return;
	}

	error_log( 'WARN [legacy-services] redirecting /' . $path . '/ to /services/' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
	wp_safe_redirect( home_url( '/services/' ), 301 );
	exit;
}
// Priority 9: before redirect_canonical(), whose 404 slug guess would send /services/seo/ to a blog post.
add_action( 'template_redirect', 'starter_redirect_legacy_services', 9 );
