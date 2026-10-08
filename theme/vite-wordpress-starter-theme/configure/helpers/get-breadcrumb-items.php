<?php
/**
 * Theme file.
 *
 * @package Vite_Starter
 */

/**
 * Builds the breadcrumb trail for CPT singles/archives and blog pages.
 *
 * @return array[] List of items: [ 'label' => string, 'url' => string (optional) ]. Empty when no trail applies.
 */
function starter_get_breadcrumb_items() {
	if ( is_front_page() ) {
		return array();
	}

	$items = array(
		array(
			'label' => __( 'Головна', 'vite-starter' ),
			'url'   => home_url( '/' ),
		),
	);

	if ( is_singular( array( 'services', 'directions', 'cases' ) ) ) {
		$post_type_object = get_post_type_object( get_post_type() );
		$items[]          = array(
			'label' => $post_type_object->labels->name,
			'url'   => get_post_type_archive_link( get_post_type() ),
		);
		$items[]          = array( 'label' => get_the_title() );
	} elseif ( is_post_type_archive() ) {
		$post_type_object = get_queried_object();
		$items[]          = array( 'label' => $post_type_object->labels->name );
	} elseif ( is_singular( 'post' ) ) {
		$posts_page = (int) get_option( 'page_for_posts' );
		$items[]    = array(
			'label' => $posts_page ? get_the_title( $posts_page ) : __( 'Блог', 'vite-starter' ),
			'url'   => $posts_page ? get_permalink( $posts_page ) : home_url( '/blog/' ),
		);
		$items[]    = array( 'label' => get_the_title() );
	} elseif ( is_home() ) {
		$posts_page = (int) get_option( 'page_for_posts' );
		$items[]    = array( 'label' => $posts_page ? get_the_title( $posts_page ) : __( 'Блог', 'vite-starter' ) );
	} elseif ( is_singular() ) {
		$items[] = array( 'label' => get_the_title() );
	} else {
		return array();
	}

	return $items;
}
