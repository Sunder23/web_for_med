<?php
/**
 * Assigns random placeholder featured images to CPT entries and blog posts.
 *
 * Temporary filler until real covers are ready. Placeholder attachments are looked up by
 * their file path (IDs differ between environments). Every assigned thumbnail is marked with
 * the `_w4m_placeholder_thumb` postmeta, so `reset` removes only placeholders and never a
 * real cover set later.
 *
 * Run:
 *   wp eval-file /scripts/assign-placeholder-thumbnails.php [dry-run] [force] [reset] [types=services,cases]
 *
 *   dry-run — print what would change, write nothing
 *   force   — also replace existing thumbnails (default: posts with a thumbnail are skipped)
 *   reset   — remove placeholder thumbnails (only posts marked with _w4m_placeholder_thumb)
 *   types=  — comma-separated subset of post types (default: services,directions,cases,post)
 *
 * Add --debug to see the resolved attachments and query args.
 *
 * @package Vite_Starter
 */

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	echo "This script must be run via: wp eval-file scripts/assign-placeholder-thumbnails.php [dry-run] [force] [reset]\n";
	exit( 1 );
}

global $wpdb;

$w4m_cli_args   = isset( $args ) ? (array) $args : array();
$dry_run        = in_array( 'dry-run', $w4m_cli_args, true );
$force          = in_array( 'force', $w4m_cli_args, true );
$reset          = in_array( 'reset', $w4m_cli_args, true );
$w4m_post_types = array( 'services', 'directions', 'cases', 'post' );
$marker_key     = '_w4m_placeholder_thumb';

foreach ( $w4m_cli_args as $w4m_arg ) {
	if ( 0 === strpos( $w4m_arg, 'types=' ) ) {
		$w4m_post_types = array_values( array_filter( array_map( 'sanitize_key', explode( ',', substr( $w4m_arg, 6 ) ) ) ) );
	}
}

$unknown_types = array_diff( $w4m_post_types, get_post_types() );
if ( $unknown_types ) {
	WP_CLI::error( 'Unknown post type(s): ' . implode( ', ', $unknown_types ) );
}

$mode_prefix = $dry_run ? '[dry-run] ' : '';

// Reset: remove marked placeholders only.
if ( $reset ) {
	$marked = get_posts(
		array(
			'post_type'      => $w4m_post_types,
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'meta_key'       => $marker_key, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
		)
	);

	WP_CLI::log( sprintf( '%sRESET: %d post(s) with placeholder thumbnails.', $mode_prefix, count( $marked ) ) );

	foreach ( $marked as $w4m_post_id ) {
		WP_CLI::log( sprintf( '  [%s] #%d "%s" → thumbnail removed', get_post_type( $w4m_post_id ), $w4m_post_id, get_the_title( $w4m_post_id ) ) );
		if ( ! $dry_run ) {
			delete_post_thumbnail( $w4m_post_id );
			delete_post_meta( $w4m_post_id, $marker_key );
		}
	}

	WP_CLI::success( sprintf( '%sRemoved %d placeholder thumbnail(s).', $mode_prefix, count( $marked ) ) );
	return;
}

// Resolve placeholder attachments by file path.
$placeholder_files = array(
	'2026/10/web_site_development.webp',
	'2026/10/seo_medicine_web_site.webp',
	'2026/05/hero_image.webp',
);

$attachments = array();
foreach ( $placeholder_files as $file ) {
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery
	$attachment_id = (int) $wpdb->get_var(
		$wpdb->prepare(
			"SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_wp_attached_file' AND meta_value = %s LIMIT 1",
			$file
		)
	);

	if ( ! $attachment_id || 'attachment' !== get_post_type( $attachment_id ) ) {
		WP_CLI::warning( "Placeholder not found in media library: {$file}" );
		continue;
	}

	$attachments[ $attachment_id ] = $file;
	WP_CLI::debug( "placeholder #{$attachment_id} => {$file}", 'placeholder' );
}

if ( ! $attachments ) {
	WP_CLI::error( 'No placeholder attachments found — upload them to the media library first.' );
}

$attachment_ids = array_keys( $attachments );
$can_vary       = count( $attachment_ids ) > 1;
$stats          = array();

foreach ( $w4m_post_types as $w4m_post_type ) {
	$query_args = array(
		'post_type'      => $w4m_post_type,
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'orderby'        => 'ID',
		'order'          => 'ASC',
	);
	WP_CLI::debug( "query: {$w4m_post_type} " . wp_json_encode( $query_args ), 'placeholder' );

	$post_ids                = get_posts( $query_args );
	$stats[ $w4m_post_type ] = array(
		'assigned' => 0,
		'skipped'  => 0,
		'failed'   => 0,
	);
	$previous_id             = 0;

	WP_CLI::log( sprintf( '%s%s: %d published post(s).', $mode_prefix, $w4m_post_type, count( $post_ids ) ) );

	foreach ( $post_ids as $w4m_post_id ) {
		$w4m_title    = get_the_title( $w4m_post_id );
		$current_id   = (int) get_post_thumbnail_id( $w4m_post_id );
		$is_marked    = (bool) get_post_meta( $w4m_post_id, $marker_key, true );
		$has_real_img = $current_id && ! $is_marked;

		if ( $current_id && ! $force ) {
			WP_CLI::log( sprintf( '  [%s] #%d "%s" skip: has thumbnail #%d', $w4m_post_type, $w4m_post_id, $w4m_title, $current_id ) );
			++$stats[ $w4m_post_type ]['skipped'];
			$previous_id = $current_id;
			continue;
		}

		// Avoid the same image on two consecutive posts of one type.
		do {
			$attachment_id = $attachment_ids[ array_rand( $attachment_ids ) ];
		} while ( $can_vary && $attachment_id === $previous_id );
		$previous_id = $attachment_id;

		WP_CLI::log(
			sprintf(
				'  [%s] #%d "%s" → attachment #%d (%s)%s',
				$w4m_post_type,
				$w4m_post_id,
				$w4m_title,
				$attachment_id,
				$attachments[ $attachment_id ],
				$has_real_img ? " [replaces #{$current_id}]" : ''
			)
		);

		if ( $dry_run ) {
			++$stats[ $w4m_post_type ]['assigned'];
			continue;
		}

		// set_post_thumbnail() returns false when the value is unchanged, so check the result instead.
		set_post_thumbnail( $w4m_post_id, $attachment_id );
		if ( (int) get_post_thumbnail_id( $w4m_post_id ) !== $attachment_id ) {
			WP_CLI::warning( "#{$w4m_post_id}: failed to set thumbnail #{$attachment_id}." );
			++$stats[ $w4m_post_type ]['failed'];
			continue;
		}

		update_post_meta( $w4m_post_id, $marker_key, 1 );
		++$stats[ $w4m_post_type ]['assigned'];
	}
}

$summary = array();
foreach ( $stats as $w4m_post_type => $counts ) {
	$summary[] = sprintf( '%s: %d assigned, %d skipped, %d failed', $w4m_post_type, $counts['assigned'], $counts['skipped'], $counts['failed'] );
}

WP_CLI::success( $mode_prefix . implode( '; ', $summary ) );
