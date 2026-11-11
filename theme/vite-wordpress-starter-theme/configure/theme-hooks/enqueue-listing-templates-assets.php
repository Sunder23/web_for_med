<?php
/**
 * Theme file.
 *
 * @package Vite_Starter
 */

/**
 * Enqueues a per-template stylesheet (and optional script) on top of the global "main" bundle.
 *
 * @param string      $handle Style/script handle.
 * @param string      $style  scss entry file name under assets/src/scss.
 * @param string|null $script Optional js entry file name under assets/src/js.
 * @param array       $script_deps Optional script dependency handles.
 * @return void
 */
function starter_enqueue_template_assets( $handle, $style, $script = null, array $script_deps = array() ) {
	$style_handle = starter_vite_register_style( $handle, $style, array( 'main' ) );
	if ( $style_handle ) {
		wp_enqueue_style( $style_handle );
	}

	if ( $script ) {
		$script_handle = starter_vite_register_script( $handle, $script, $script_deps );
		if ( $script_handle ) {
			wp_enqueue_script( $script_handle );
		}
	}
}

/**
 * Enqueues assets for CPT archives and the blog home template.
 *
 * @return void
 */
function starter_enqueue_archive_assets() {
	if ( ( ! is_archive() && ! is_home() ) || is_post_type_archive( 'cases' ) ) {
		return;
	}

	starter_enqueue_template_assets( 'starter-archive', 'archive.scss' );
}
add_action( 'wp_enqueue_scripts', 'starter_enqueue_archive_assets', 110 );

/**
 * Enqueues assets for the cases archive (/cases/, vertical case slider).
 *
 * @return void
 */
function starter_enqueue_cases_archive_assets() {
	if ( ! is_post_type_archive( 'cases' ) ) {
		return;
	}

	starter_enqueue_template_assets( 'starter-cases-archive', 'cases-archive.scss', 'cases-archive.js' );
}
add_action( 'wp_enqueue_scripts', 'starter_enqueue_cases_archive_assets', 110 );

/**
 * Marks the cases archive on <body> (one-screen layout: footer pinned to the viewport bottom).
 *
 * @param string[] $classes Body classes.
 * @return string[] Modified body classes.
 */
function starter_cases_archive_body_class( $classes ) {
	if ( is_post_type_archive( 'cases' ) ) {
		$classes[] = 'is-cases-archive';
	}

	return $classes;
}
add_filter( 'body_class', 'starter_cases_archive_body_class' );

/**
 * Enqueues assets for single services / directions templates.
 *
 * @return void
 */
function starter_enqueue_single_cpt_assets() {
	if ( ! is_singular( array( 'services', 'directions' ) ) ) {
		return;
	}

	starter_enqueue_template_assets( 'starter-single-cpt', 'single-cpt.scss', 'single-cpt.js', array( 'jquery' ) );
}
add_action( 'wp_enqueue_scripts', 'starter_enqueue_single_cpt_assets', 110 );

/**
 * Enqueues assets for the single case template (layout, section counter).
 *
 * @return void
 */
function starter_enqueue_single_case_assets() {
	if ( ! is_singular( 'cases' ) ) {
		return;
	}

	starter_enqueue_template_assets( 'starter-single-case', 'single-case.scss', 'single-case.js' );
}
add_action( 'wp_enqueue_scripts', 'starter_enqueue_single_case_assets', 110 );

/**
 * Enqueues assets for the single blog post template.
 *
 * @return void
 */
function starter_enqueue_single_post_assets() {
	if ( ! is_singular( 'post' ) ) {
		return;
	}

	starter_enqueue_template_assets( 'starter-single-post', 'single-post.scss', 'single-post.js' );
}
add_action( 'wp_enqueue_scripts', 'starter_enqueue_single_post_assets', 110 );

/**
 * Enqueues assets for the 404 template.
 *
 * @return void
 */
function starter_enqueue_404_assets() {
	if ( ! is_404() ) {
		return;
	}

	starter_enqueue_template_assets( 'starter-error-404', 'error-404.scss' );
}
add_action( 'wp_enqueue_scripts', 'starter_enqueue_404_assets', 110 );
