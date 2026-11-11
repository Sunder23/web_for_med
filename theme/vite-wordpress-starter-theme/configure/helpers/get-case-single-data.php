<?php
/**
 * Theme file.
 *
 * @package Vite_Starter
 */

/**
 * Term names of a case taxonomy, sorted by name.
 *
 * @param WP_Post $post     Case post.
 * @param string  $taxonomy Taxonomy name.
 * @return string[] Term names (empty on error or when nothing is assigned).
 */
function starter_get_case_term_names( $post, $taxonomy ) {
	$terms = get_the_terms( $post, $taxonomy );

	if ( is_wp_error( $terms ) ) {
		error_log( 'WARN [case-single] terms error for case ' . $post->ID . ' (' . $taxonomy . '): ' . $terms->get_error_message() ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		return array();
	}

	if ( ! is_array( $terms ) ) {
		return array();
	}

	$names = wp_list_pluck( $terms, 'name' );
	natcasesort( $names );

	return array_values( $names );
}

/**
 * Builds the data for the single case page: /*FILE*\/ label, intro, image, sidebar details
 * and the number of sections for the "Section: NN/MM" counter.
 *
 * Fallbacks: client → post title, intro text → case_hero.subtitle, image → featured image.
 *
 * @param int|WP_Post $post Case post or ID.
 * @return array{client:string,intro_html:string,image_id:int,details:array<int,array{label:string,value:string,url:string}>,sections_total:int}|null Page data, or null for an invalid post.
 */
function starter_get_case_single_data( $post ) {
	$post = get_post( $post );

	if ( ! $post instanceof WP_Post ) {
		return null;
	}

	$intro   = starter_get_field( 'case_intro', $post->ID, array() );
	$intro   = is_array( $intro ) ? $intro : array();
	$details = starter_get_field( 'case_details', $post->ID, array() );
	$details = is_array( $details ) ? $details : array();

	$client = trim( (string) starter_get_array_value( $intro, 'client', '' ) );
	if ( '' === $client ) {
		$client = get_the_title( $post );
	}

	$intro_html = trim( (string) starter_get_array_value( $intro, 'text', '' ) );
	if ( '' === $intro_html ) {
		$hero     = starter_get_field( 'case_hero', $post->ID, array() );
		$subtitle = is_array( $hero ) ? trim( (string) starter_get_array_value( $hero, 'subtitle', '' ) ) : '';
		if ( '' !== $subtitle ) {
			$intro_html = wpautop( esc_html( $subtitle ) );
		}
	}
	if ( '' === $intro_html ) {
		error_log( 'WARN [case-single] empty intro for case ' . $post->ID ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
	}

	$image_id = (int) starter_get_array_value( $intro, 'image', 0 );
	if ( ! $image_id ) {
		$image_id = (int) get_post_thumbnail_id( $post );
	}
	if ( ! $image_id ) {
		error_log( 'WARN [case-single] no intro image for case ' . $post->ID ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
	}

	$statuses = starter_get_case_term_names( $post, 'case_status' );
	if ( count( $statuses ) > 1 ) {
		error_log( 'WARN [case-single] multiple statuses for case ' . $post->ID ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
	}

	$live_url   = trim( (string) starter_get_array_value( $details, 'live_url', '' ) );
	$live_label = trim( (string) starter_get_array_value( $details, 'live_label', '' ) );

	// Sidebar order as in the design; empty rows are dropped below.
	$rows = array(
		array( __( 'Deliverables', 'vite-starter' ), implode( ', ', starter_get_case_term_names( $post, 'case_deliverable' ) ), '' ),
		array( __( 'Technology', 'vite-starter' ), implode( ', ', starter_get_case_term_names( $post, 'case_technology' ) ), '' ),
		array( __( 'Duration', 'vite-starter' ), trim( (string) starter_get_array_value( $details, 'duration', '' ) ), '' ),
		array( __( 'Status', 'vite-starter' ), $statuses ? $statuses[0] : '', '' ),
		array( __( 'Live Project', 'vite-starter' ), $live_url ? ( $live_label ? $live_label : __( 'View Website', 'vite-starter' ) ) : '', $live_url ),
	);

	$details_rows = array();
	foreach ( $rows as $row ) {
		if ( '' === $row[1] ) {
			continue;
		}
		$details_rows[] = array(
			'label' => $row[0],
			'value' => $row[1],
			'url'   => $row[2],
		);
	}

	// Section 01 is the intro, then one section per h2 of the content (JS re-counts from the DOM).
	$sections_total = 1 + count( starter_collect_headings( (string) $post->post_content ) );

	return array(
		'client'         => $client,
		'intro_html'     => $intro_html,
		'image_id'       => $image_id,
		'details'        => $details_rows,
		'sections_total' => $sections_total,
	);
}
