<?php
/**
 * Block styles for article content.
 * Case singles: a larger chapter heading, a conclusion paragraph and a gallery that
 * stacks its images on phones (assets/src/scss/components/_case-single.scss).
 * Blog posts: a CTA callout and a figure with one caption for its pictures
 * (assets/src/scss/components/_post-single.scss).
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

	// Pairs [ block name, style ] — one block may have several styles.
	$styles = array(
		array(
			'core/heading',
			array(
				'name'  => 'chapter',
				'label' => __( 'Заголовок розділу', 'vite-starter' ),
			),
		),
		array(
			'core/paragraph',
			array(
				'name'  => 'lead',
				'label' => __( 'Висновок', 'vite-starter' ),
			),
		),
		array(
			'core/gallery',
			array(
				'name'  => 'stack-mobile',
				'label' => __( 'Стовпчиком на телефоні', 'vite-starter' ),
			),
		),
		array(
			'core/group',
			array(
				'name'  => 'callout',
				'label' => __( 'Блок із закликом', 'vite-starter' ),
			),
		),
		array(
			'core/group',
			array(
				'name'  => 'figure',
				'label' => __( 'Ілюстрація з підписом', 'vite-starter' ),
			),
		),
	);

	foreach ( $styles as list( $block_name, $style ) ) {
		if ( ! register_block_style( $block_name, $style ) ) {
			error_log( 'WARN [block-styles] failed to register ' . $block_name . ' / ' . $style['name'] ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		}
	}
}

add_action( 'init', 'starter_register_block_styles' );
