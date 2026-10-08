<?php
/**
 * Theme file.
 *
 * @package Vite_Starter
 */

/**
 * Registers all ACF blocks found under configure/acf/acf-blocks.
 *
 * Each block's stylesheet (assets/src/scss/block-{slug}.scss) is registered under
 * the handle "starter-block-{slug}", which block.json references via "style".
 *
 * @return void
 */
function starter_register_acf_blocks() {
	if ( ! function_exists( 'register_block_type' ) ) {
		return;
	}

	$block_dirs = glob( WFB_THEME_PATH . '/configure/acf/acf-blocks/*', GLOB_ONLYDIR );

	foreach ( $block_dirs as $block_dir ) {
		if ( ! file_exists( $block_dir . '/block.json' ) ) {
			continue;
		}

		$block_slug = basename( $block_dir );

		if ( ! starter_vite_register_style( 'starter-block-' . $block_slug, 'block-' . $block_slug . '.scss' ) && ( VITE_BUILD || VITE_DEV ) ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional WARN-level diagnostic (minimal logging policy).
			error_log( 'WARN [acf-blocks] style entry missing for ' . $block_slug );
		}

		register_block_type( $block_dir );
	}
}
add_action( 'init', 'starter_register_acf_blocks' );
