<?php
/**
 * Removes the content of the dropped `services` CPT so `/services/` can become a page.
 *
 * The post type itself is no longer registered by the theme; this script deletes what is
 * left in the database:
 *   - every post with `post_type = services` (any status, with its meta, via wp_delete_post);
 *   - nav menu items pointing to a services single or the services archive
 *     (`_menu_item_object = services`);
 * then flushes the rewrite rules.
 *
 * Run it before update-services-page.php (that script refuses to create the page while
 * services posts still exist).
 *
 * Args:
 *   (none)  dry run — logs what would be deleted, writes nothing;
 *   apply   delete the posts / menu items and flush rewrite rules.
 *
 * Run: docker compose -f docker/compose.yml run --rm wpcli wp eval-file /scripts/remove-services-cpt.php [apply]
 * (On Windows/Git Bash, prefix with MSYS_NO_PATHCONV=1.)
 *
 * @package Vite_Starter
 */

if ( ! defined( 'WP_CLI' ) ) {
	echo "This script must be run via WP-CLI (wp eval-file).\n";
	exit( 1 );
}

global $wpdb;

$rs_args    = isset( $args ) && is_array( $args ) ? $args : array();
$rs_apply   = in_array( 'apply', $rs_args, true );
$rs_deleted = 0;
$rs_failed  = 0;

WP_CLI::log( '[remove-services] ' . ( $rs_apply ? 'apply' : 'dry run' ) );

// 1. Posts. Queried directly: the post type is no longer registered.
$rs_posts = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
	$wpdb->prepare( "SELECT ID, post_name, post_status FROM {$wpdb->posts} WHERE post_type = %s ORDER BY ID", 'services' )
);

if ( ! $rs_posts ) {
	WP_CLI::log( '[remove-services] no services posts found' );
}

foreach ( (array) $rs_posts as $rs_post ) {
	$rs_label = "#{$rs_post->ID} {$rs_post->post_name} ({$rs_post->post_status})";
	if ( ! $rs_apply ) {
		WP_CLI::log( "[remove-services] post {$rs_label}: would be deleted" );
		continue;
	}
	if ( wp_delete_post( (int) $rs_post->ID, true ) ) {
		++$rs_deleted;
		WP_CLI::log( "[remove-services] post {$rs_label}: deleted" );
	} else {
		++$rs_failed;
		WP_CLI::warning( "[remove-services] post {$rs_label}: delete failed" );
	}
}

// 2. Menu items pointing to services singles / the services archive.
$rs_menu_items = $wpdb->get_col( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
	$wpdb->prepare(
		"SELECT p.ID FROM {$wpdb->posts} p
		INNER JOIN {$wpdb->postmeta} m ON m.post_id = p.ID AND m.meta_key = '_menu_item_object'
		WHERE p.post_type = 'nav_menu_item' AND m.meta_value = %s",
		'services'
	)
);

if ( ! $rs_menu_items ) {
	WP_CLI::log( '[remove-services] no services menu items found' );
}

foreach ( (array) $rs_menu_items as $rs_item_id ) {
	$rs_label = '#' . $rs_item_id . ' "' . get_the_title( (int) $rs_item_id ) . '"';
	if ( ! $rs_apply ) {
		WP_CLI::log( "[remove-services] menu item {$rs_label}: would be deleted" );
		continue;
	}
	if ( wp_delete_post( (int) $rs_item_id, true ) ) {
		++$rs_deleted;
		WP_CLI::log( "[remove-services] menu item {$rs_label}: deleted" );
	} else {
		++$rs_failed;
		WP_CLI::warning( "[remove-services] menu item {$rs_label}: delete failed" );
	}
}

if ( ! $rs_apply ) {
	WP_CLI::success( '[remove-services] dry run: ' . ( count( (array) $rs_posts ) + count( (array) $rs_menu_items ) ) . ' object(s) would be deleted; run with `apply`' );
	return;
}

flush_rewrite_rules();
WP_CLI::log( '[remove-services] rewrite rules flushed' );

if ( $rs_failed ) {
	WP_CLI::warning( "[remove-services] deleted {$rs_deleted}, failed {$rs_failed}" );
	return;
}

WP_CLI::success( "[remove-services] deleted {$rs_deleted} object(s)" );
