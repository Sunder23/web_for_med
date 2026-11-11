<?php
/**
 * Theme file.
 *
 * @package Vite_Starter
 */

/**
 * Builds the data for one case on the cases archive page.
 *
 * Fallbacks: title → post title, description → case_hero.subtitle,
 * cover → featured image, otherwise 0 (the template renders a placeholder).
 *
 * @param int|WP_Post $post Case post or ID.
 * @return array{id:int,url:string,title:string,description:string,cover_id:int}|null Item data, or null for an invalid post.
 */
function starter_get_case_archive_item( $post ) {
	$post = get_post( $post );

	if ( ! $post instanceof WP_Post ) {
		return null;
	}

	$archive = starter_get_field( 'case_archive', $post->ID, array() );
	$archive = is_array( $archive ) ? $archive : array();

	$title = trim( (string) starter_get_array_value( $archive, 'title', '' ) );
	if ( '' === $title ) {
		$title = get_the_title( $post );
	}

	$description = trim( (string) starter_get_array_value( $archive, 'description', '' ) );
	if ( '' === $description ) {
		$hero        = starter_get_field( 'case_hero', $post->ID, array() );
		$description = is_array( $hero ) ? trim( (string) starter_get_array_value( $hero, 'subtitle', '' ) ) : '';
	}

	$cover_id = (int) starter_get_array_value( $archive, 'cover', 0 );
	if ( ! $cover_id ) {
		$cover_id = (int) get_post_thumbnail_id( $post );
	}

	if ( ! $cover_id ) {
		error_log( 'WARN [cases-archive] no cover for case ' . $post->ID ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
	}

	return array(
		'id'          => (int) $post->ID,
		'url'         => (string) get_permalink( $post ),
		'title'       => $title,
		'description' => $description,
		'cover_id'    => $cover_id,
	);
}
