<?php
/**
 * Theme file.
 *
 * Optional front-end optimizations. Each flag can be overridden in wp-config.php.
 *
 * @package Vite_Starter
 */

if ( ! defined( 'STARTER_STRIP_BLOCK_STYLES' ) ) {
	define( 'STARTER_STRIP_BLOCK_STYLES', false );
}

if ( STARTER_STRIP_BLOCK_STYLES ) {
	add_action(
		'wp_enqueue_scripts',
		function () {
			wp_dequeue_style( 'wp-block-library' );
			wp_dequeue_style( 'wp-block-library-theme' );
			wp_dequeue_style( 'wc-block-style' );
			wp_dequeue_style( 'global-styles' );
			wp_dequeue_style( 'classic-theme-styles' );
		},
		100
	);

	add_action(
		'after_setup_theme',
		function () {
			remove_action( 'wp_enqueue_scripts', 'wp_enqueue_global_styles' );
			remove_action( 'wp_body_open', 'wp_global_styles_render_svg_filters' );
			remove_action( 'wp_footer', 'wp_enqueue_global_styles', 1 );
			remove_filter( 'render_block', 'wp_render_duotone_support' );
			remove_filter( 'render_block', 'wp_restore_group_inner_container' );
			remove_filter( 'render_block', 'wp_render_layout_support_flag' );
		}
	);
}

if ( ! defined( 'STARTER_REMOVE_JQUERY_MIGRATE' ) ) {
	define( 'STARTER_REMOVE_JQUERY_MIGRATE', true );
}

if ( STARTER_REMOVE_JQUERY_MIGRATE ) {
	add_action(
		'wp_default_scripts',
		function ( $scripts ) {
			if ( is_admin() || empty( $scripts->registered['jquery'] ) ) {
				return;
			}
			$scripts->registered['jquery']->deps = array_diff(
				$scripts->registered['jquery']->deps,
				array( 'jquery-migrate' )
			);
		}
	);
}
