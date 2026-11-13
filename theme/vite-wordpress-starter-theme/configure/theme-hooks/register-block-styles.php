<?php
/**
 * Block styles for article content (case singles): a larger chapter heading, a
 * conclusion paragraph and a gallery that stacks its images on phones. Styles live
 * in assets/src/scss/components/_case-single.scss (`is-style-*`).
 *
 * @package Vite_Starter
 */

/**
 * Registers the theme block styles.
 *
 * @return void
 */
function starter_register_block_styles() {
	if ( ! function_exists( 'register_block_style' ) ) {
		error_log( 'WARN [block-styles] register_block_style() is unavailable' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		return;
	}

	$styles = array(
		'core/heading'   => array(
			'name'  => 'chapter',
			'label' => __( 'Заголовок розділу', 'vite-starter' ),
		),
		'core/paragraph' => array(
			'name'  => 'lead',
			'label' => __( 'Висновок', 'vite-starter' ),
		),
		'core/gallery'   => array(
			'name'  => 'stack-mobile',
			'label' => __( 'Стовпчиком на телефоні', 'vite-starter' ),
		),
	);

	foreach ( $styles as $block_name => $style ) {
		if ( ! register_block_style( $block_name, $style ) ) {
			error_log( 'WARN [block-styles] failed to register ' . $block_name . ' / ' . $style['name'] ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		}
	}
}

add_action( 'init', 'starter_register_block_styles' );
