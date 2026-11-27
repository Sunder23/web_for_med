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
 * Builds the data for the single case page: /*CASE*\/ label, intro, image, sidebar details
 * and the number of sections for the "Section: NN/MM" counter.
 *
 * Fallbacks: project_name → client → post title, intro text → case_hero.subtitle,
 * image → featured image → theme placeholder (only when the case has content).
 *
 * @param int|WP_Post $post Case post or ID.
 * @return array{client:string,project_name:string,intro_html:string,image_id:int,placeholder:array{url:string,width:int,height:int}|null,details:array<int,array{key:string,label:string,value:string,url:string}>,sections_total:int}|null Page data, or null for an invalid post.
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

	// Utility bar label /*CASE: …*/: project name → client → post title.
	$project_name = trim( (string) starter_get_array_value( $intro, 'project_name', '' ) );
	if ( '' === $project_name ) {
		$project_name = $client;
		error_log( 'WARN [case-single] no project_name for case ' . $post->ID . ', using client/title' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
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
	// No intro image and no featured image: the theme placeholder (Figma 3155:3274),
	// but only for a case that has something to show next to it.
	$placeholder = null;
	if ( ! $image_id ) {
		$has_content = '' !== $intro_html || '' !== trim( (string) $post->post_content );
		if ( $has_content ) {
			$placeholder = starter_get_case_placeholder_image();
		}
		error_log( 'WARN [case-single] no intro image for case ' . $post->ID . ( $placeholder ? ', using placeholder' : ', no content either' ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
	}

	$statuses = starter_get_case_term_names( $post, 'case_status' );
	if ( count( $statuses ) > 1 ) {
		error_log( 'WARN [case-single] multiple statuses for case ' . $post->ID ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
	}

	$live_url   = trim( (string) starter_get_array_value( $details, 'live_url', '' ) );
	$live_label = trim( (string) starter_get_array_value( $details, 'live_label', '' ) );

	// Sidebar order as in the design; empty rows are dropped below. The key becomes a
	// BEM modifier (the status row is hidden on phones, Figma 3124:1416).
	$rows = array(
		'deliverables' => array( __( 'Deliverables', 'vite-starter' ), implode( ', ', starter_get_case_term_names( $post, 'case_deliverable' ) ), '' ),
		'technology'   => array( __( 'Technology', 'vite-starter' ), implode( ', ', starter_get_case_term_names( $post, 'case_technology' ) ), '' ),
		'duration'     => array( __( 'Duration', 'vite-starter' ), trim( (string) starter_get_array_value( $details, 'duration', '' ) ), '' ),
		'status'       => array( __( 'Status', 'vite-starter' ), $statuses ? $statuses[0] : '', '' ),
		'live'         => array( __( 'Live Project', 'vite-starter' ), $live_url ? ( $live_label ? $live_label : __( 'View Website', 'vite-starter' ) ) : '', $live_url ),
	);

	$details_rows = array();
	foreach ( $rows as $row_key => $row ) {
		if ( '' === $row[1] ) {
			continue;
		}
		$details_rows[] = array(
			'key'   => $row_key,
			'label' => $row[0],
			'value' => $row[1],
			'url'   => $row[2],
		);
	}

	// Section 01 is the intro, then one section per h2 of the content (JS re-counts from the DOM).
	$sections_total = 1 + count( starter_collect_headings( (string) $post->post_content ) );

	return array(
		'client'         => $client,
		'project_name'   => $project_name,
		'intro_html'     => $intro_html,
		'image_id'       => $image_id,
		'placeholder'    => $placeholder,
		'details'        => $details_rows,
		'sections_total' => $sections_total,
	);
}
