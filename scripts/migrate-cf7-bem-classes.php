<?php
/**
 * Rename legacy CSS classes inside Contact Form 7 form templates to the BEM
 * names used by the theme (see .ai-factory/plans/scss-mobile-first-bem.md):
 *
 *   class:btn / class:btn--*   -> class:button / class:button--*
 *   form-field-label           -> form-field__label
 *
 * CF7 keeps the template in two places: post_content and the `_form` meta
 * (the one the plugin actually renders) — both are updated. A form without
 * legacy classes is skipped, so re-running is safe.
 *
 * Args:
 *   (none)  dry run — logs what would change, writes nothing;
 *   apply   write the renamed templates.
 *
 * Run: docker compose -f docker/compose.yml run --rm wpcli wp eval-file /scripts/migrate-cf7-bem-classes.php [apply]
 * (On Windows/Git Bash, prefix with MSYS_NO_PATHCONV=1 so /scripts/... is not
 * mangled into a Windows path.)
 *
 * @package Vite_Starter
 */

if ( ! defined( 'WP_CLI' ) ) {
	echo "This script must be run via WP-CLI (wp eval-file).\n";
	exit( 1 );
}

$cf7_args  = isset( $args ) && is_array( $args ) ? $args : array();
$cf7_apply = in_array( 'apply', $cf7_args, true );

/**
 * Applies the legacy -> BEM class renames to a CF7 form template.
 *
 * @param string $template Form template.
 * @return string Renamed template.
 */
function starter_cf7_bem_rename( $template ) {
	$template = preg_replace( '/\bclass:btn(--[a-z0-9-]+)?\b/', 'class:button$1', $template );
	return str_replace( 'form-field-label', 'form-field__label', $template );
}

$cf7_forms = get_posts(
	array(
		'post_type'   => 'wpcf7_contact_form',
		'post_status' => 'any',
		'numberposts' => -1,
	)
);

if ( empty( $cf7_forms ) ) {
	WP_CLI::warning( '[cf7-bem] no Contact Form 7 forms found.' );
	return;
}

$cf7_changed = 0;

foreach ( $cf7_forms as $cf7_form ) {
	$cf7_meta     = (string) get_post_meta( $cf7_form->ID, '_form', true );
	$cf7_new_meta = starter_cf7_bem_rename( $cf7_meta );
	$cf7_new_body = starter_cf7_bem_rename( $cf7_form->post_content );

	if ( $cf7_new_meta === $cf7_meta && $cf7_new_body === $cf7_form->post_content ) {
		WP_CLI::log( sprintf( '[cf7-bem] #%d "%s": no legacy classes, skipped.', $cf7_form->ID, $cf7_form->post_title ) );
		continue;
	}

	++$cf7_changed;
	WP_CLI::log( sprintf( '[cf7-bem] #%d "%s": legacy classes found%s.', $cf7_form->ID, $cf7_form->post_title, $cf7_apply ? ', updating' : ' (dry run)' ) );

	if ( ! $cf7_apply ) {
		continue;
	}

	update_post_meta( $cf7_form->ID, '_form', wp_slash( $cf7_new_meta ) );

	$cf7_result = wp_update_post(
		array(
			'ID'           => $cf7_form->ID,
			'post_content' => wp_slash( $cf7_new_body ),
		),
		true
	);

	if ( is_wp_error( $cf7_result ) ) {
		WP_CLI::warning( sprintf( '[cf7-bem] #%d: post_content update failed: %s', $cf7_form->ID, $cf7_result->get_error_message() ) );
	}
}

WP_CLI::success( sprintf( '[cf7-bem] %d form(s) %s.', $cf7_changed, $cf7_apply ? 'updated' : 'would be updated' ) );
