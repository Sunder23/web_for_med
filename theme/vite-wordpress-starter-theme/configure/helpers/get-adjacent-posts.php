<?php
/**
 * Theme file.
 *
 * @package Vite_Starter
 */

/**
 * Oldest or newest published post of a type, other than the given one (loop-around target).
 *
 * @param string $post_type  Post type.
 * @param int    $exclude_id Current post ID.
 * @param string $order      ASC for the oldest, DESC for the newest.
 * @return WP_Post|null
 */
function starter_get_edge_post( $post_type, $exclude_id, $order ) {
	$found = get_posts(
		array(
			'post_type'      => $post_type,
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'orderby'        => 'date',
			'order'          => $order,
			'post__not_in'   => array( $exclude_id ),
			'no_found_rows'  => true,
		)
	);

	return $found ? $found[0] : null;
}

/**
 * Previous / next posts of the current loop post's type (by date), looping around at the ends.
 *
 * With two posts in total both directions point at the same post, so only "next" is kept.
 * Both are null when the post is the only one of its type.
 *
 * @return array{prev:WP_Post|null,next:WP_Post|null}
 */
function starter_get_adjacent_posts() {
	$post_type  = (string) get_post_type();
	$current_id = (int) get_the_ID();

	$prev_post = get_adjacent_post( false, '', true );
	$next_post = get_adjacent_post( false, '', false );

	if ( ! $prev_post instanceof WP_Post ) {
		$prev_post = starter_get_edge_post( $post_type, $current_id, 'DESC' );
	}
	if ( ! $next_post instanceof WP_Post ) {
		$next_post = starter_get_edge_post( $post_type, $current_id, 'ASC' );
	}

	if ( $prev_post && $next_post && $prev_post->ID === $next_post->ID ) {
		$prev_post = null;
	}

	return array(
		'prev' => $prev_post,
		'next' => $next_post,
	);
}
