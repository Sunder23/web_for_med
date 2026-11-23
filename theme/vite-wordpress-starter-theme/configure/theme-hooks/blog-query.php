<?php
/**
 * Theme file.
 *
 * @package Vite_Starter
 */

/**
 * Six posts per page on the blog listing (posts page and post archives: category,
 * tag, author, date) — two rows of the three-column grid; «Показати ще» loads the next page.
 *
 * @param WP_Query $query Current query.
 * @return void
 */
function starter_blog_query( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}

	if ( ! $query->is_home() && ! $query->is_category() && ! $query->is_tag() && ! $query->is_author() && ! $query->is_date() ) {
		return;
	}

	$query->set( 'posts_per_page', 6 );
}
add_action( 'pre_get_posts', 'starter_blog_query' );
