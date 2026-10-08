<?php

add_filter('wpcf7_autop_or_not', '__return_false');

/**
 * Singular views whose main body is rendered by the_content() with Gutenberg blocks.
 * Core block assets (wp-block-library, layout support) must stay enabled here.
 */
function custom_theme_is_block_content_view() {
	return is_singular( array( 'post', 'services', 'directions', 'cases' ) );
}

/**
 * Render breadcrumbs for CPT singles/archives and blog pages.
 * Echoes nothing on the front page.
 */
function custom_theme_breadcrumbs() {
	if ( is_front_page() ) {
		return;
	}

	$items = array(
		array( 'label' => 'Головна', 'url' => home_url( '/' ) ),
	);

	if ( is_singular( array( 'services', 'directions', 'cases' ) ) ) {
		$post_type_object = get_post_type_object( get_post_type() );
		$items[] = array(
			'label' => $post_type_object->labels->name,
			'url'   => get_post_type_archive_link( get_post_type() ),
		);
		$items[] = array( 'label' => get_the_title() );
	} elseif ( is_post_type_archive() ) {
		$post_type_object = get_queried_object();
		$items[] = array( 'label' => $post_type_object->labels->name );
	} elseif ( is_singular( 'post' ) ) {
		$posts_page = (int) get_option( 'page_for_posts' );
		$items[] = array(
			'label' => $posts_page ? get_the_title( $posts_page ) : 'Блог',
			'url'   => $posts_page ? get_permalink( $posts_page ) : home_url( '/blog/' ),
		);
		$items[] = array( 'label' => get_the_title() );
	} elseif ( is_home() ) {
		$posts_page = (int) get_option( 'page_for_posts' );
		$items[] = array( 'label' => $posts_page ? get_the_title( $posts_page ) : 'Блог' );
	} elseif ( is_singular() ) {
		$items[] = array( 'label' => get_the_title() );
	} else {
		return;
	}

	echo '<nav class="breadcrumbs" aria-label="' . esc_attr__( 'Хлібні крихти', 'textdomaintomodify' ) . '"><ol class="breadcrumbs__list">';
	foreach ( $items as $item ) {
		echo '<li class="breadcrumbs__item">';
		if ( ! empty( $item['url'] ) ) {
			echo '<a class="breadcrumbs__link" href="' . esc_url( $item['url'] ) . '">' . esc_html( $item['label'] ) . '</a>';
		} else {
			echo '<span class="breadcrumbs__current" aria-current="page">' . esc_html( $item['label'] ) . '</span>';
		}
		echo '</li>';
	}
	echo '</ol></nav>';
}
