<?php
/**
 * Theme file.
 *
 * @package Vite_Starter
 */

/**
 * Shows every published case on /cases/, ordered manually (menu_order) and then by date.
 *
 * @param WP_Query $query Current query.
 * @return void
 */
function starter_cases_archive_query( $query ) {
	if ( is_admin() || ! $query->is_main_query() || ! $query->is_post_type_archive( 'cases' ) ) {
		return;
	}

	$query->set( 'posts_per_page', -1 );
	$query->set(
		'orderby',
		array(
			'menu_order' => 'ASC',
			'date'       => 'DESC',
		)
	);
	$query->set( 'no_found_rows', true );
}
add_action( 'pre_get_posts', 'starter_cases_archive_query' );
