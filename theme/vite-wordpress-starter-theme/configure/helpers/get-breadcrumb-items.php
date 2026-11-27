<?php
/**
 * Theme file.
 *
 * @package Vite_Starter
 */

/**
 * Builds the breadcrumb trail for CPT singles/archives and blog pages.
 *
 * @param array $labels Optional label overrides: `home` (first item), `blog` (posts page item).
 *                      The single post utility bar uses `home` / `blog` as in the design.
 * @return array[] List of items: [ 'label' => string, 'url' => string (optional) ]. Empty when no trail applies.
 */
function starter_get_breadcrumb_items( array $labels = array() ) {
	if ( is_front_page() ) {
		return array();
	}

	$posts_page = (int) get_option( 'page_for_posts' );
	$blog_label = ! empty( $labels['blog'] )
		? $labels['blog']
		: ( $posts_page ? get_the_title( $posts_page ) : __( 'Блог', 'vite-starter' ) );

	$items = array(
		array(
			'label' => ! empty( $labels['home'] ) ? $labels['home'] : __( 'Головна', 'vite-starter' ),
			'url'   => home_url( '/' ),
		),
	);

	if ( is_singular( array( 'directions', 'cases' ) ) ) {
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
		$items[] = array(
			'label' => $blog_label,
			'url'   => $posts_page ? get_permalink( $posts_page ) : home_url( '/blog/' ),
		);
		$items[] = array( 'label' => get_the_title() );
	} elseif ( is_home() ) {
		$items[] = array( 'label' => $blog_label );
	} elseif ( is_archive() ) {
		// Blog post archives (category, tag, date, author): Головна → Блог → archive.
		$items[] = array(
			'label' => $blog_label,
			'url'   => $posts_page ? get_permalink( $posts_page ) : home_url( '/blog/' ),
		);
		$items[] = array(
			'label' => is_category() || is_tag() || is_tax() ? single_term_title( '', false ) : wp_strip_all_tags( get_the_archive_title() ),
		);
	} elseif ( is_404() ) {
		$items[] = array( 'label' => __( 'Сторінку не знайдено', 'vite-starter' ) );
	} elseif ( is_singular() ) {
		$items[] = array( 'label' => get_the_title() );
	} else {
		return array();
	}

	return $items;
}
