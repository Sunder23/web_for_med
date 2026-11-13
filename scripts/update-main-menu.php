<?php
/**
 * Short main menu "Головне меню" (slug `main-menu-short`): Про нас / Кейси / Контакти.
 *
 * Items:
 *   Про нас  → the `about` page;
 *   Кейси    → `#cases` (front-page section; off the front page the theme
 *              rewrites it to `/#cases`, see configure/theme-hooks/nav-menu-anchor-links.php);
 *   Контакти → `#contacts` (opens the contact drawer).
 *
 * The menu is assigned to the `menu-main` (header) and `menu-footer` locations.
 * Other locations and the previous "Main Menu" are left as they are, so the old
 * menu can be put back in Appearance → Menus. Re-running changes nothing.
 *
 * Args:
 *   (none)  dry run — logs what would change, writes nothing;
 *   apply   create the menu / items and assign the locations.
 *
 * Run: docker compose -f docker/compose.yml run --rm wpcli wp eval-file /scripts/update-main-menu.php [apply]
 * (On Windows/Git Bash, prefix with MSYS_NO_PATHCONV=1.)
 *
 * @package Vite_Starter
 */

if ( ! defined( 'WP_CLI' ) ) {
	echo "This script must be run via WP-CLI (wp eval-file).\n";
	exit( 1 );
}

$mm_args    = isset( $args ) && is_array( $args ) ? $args : array();
$mm_apply   = in_array( 'apply', $mm_args, true );
$mm_changes = 0;
$mm_slug    = 'main-menu-short';

WP_CLI::log( '[main-menu] ' . ( $mm_apply ? 'apply' : 'dry run' ) );

// 1. Menu.
$mm_menu    = wp_get_nav_menu_object( $mm_slug );
$mm_menu_id = $mm_menu ? (int) $mm_menu->term_id : 0;

if ( $mm_menu_id ) {
	WP_CLI::log( "[main-menu] menu \"{$mm_menu->name}\" #{$mm_menu_id}: exists" );
} elseif ( $mm_apply ) {
	$mm_created = wp_create_nav_menu( 'Головне меню' );
	if ( is_wp_error( $mm_created ) ) {
		WP_CLI::error( '[main-menu] cannot create menu: ' . $mm_created->get_error_message() );
	}
	$mm_menu_id = (int) $mm_created;
	wp_update_term( $mm_menu_id, 'nav_menu', array( 'slug' => $mm_slug ) );
	++$mm_changes;
	WP_CLI::log( "[main-menu] menu \"Головне меню\" #{$mm_menu_id}: created" );
} else {
	++$mm_changes;
	WP_CLI::log( '[main-menu] menu "Головне меню": would be created' );
}

// 2. Items, in order.
$mm_about = get_page_by_path( 'about' );
$mm_items = array(
	array(
		'title' => 'Про нас',
		'page'  => $mm_about instanceof WP_Post ? (int) $mm_about->ID : 0,
	),
	array(
		'title' => 'Кейси',
		'url'   => '#cases',
	),
	array(
		'title' => 'Контакти',
		'url'   => '#contacts',
	),
);

$mm_existing = $mm_menu_id ? (array) wp_get_nav_menu_items( $mm_menu_id ) : array();

foreach ( $mm_items as $mm_position => $mm_item ) {
	if ( isset( $mm_item['page'] ) && ! $mm_item['page'] ) {
		WP_CLI::warning( "[main-menu] item \"{$mm_item['title']}\": page `about` not found, skipped" );
		continue;
	}

	$mm_found = null;
	foreach ( $mm_existing as $mm_old ) {
		$mm_same = isset( $mm_item['page'] )
			? ( 'post_type' === $mm_old->type && (int) $mm_old->object_id === $mm_item['page'] )
			: ( 'custom' === $mm_old->type && $mm_old->url === $mm_item['url'] );
		if ( $mm_same ) {
			$mm_found = $mm_old;
			break;
		}
	}

	if ( $mm_found ) {
		WP_CLI::log( "[main-menu] item \"{$mm_item['title']}\": exists (#{$mm_found->ID})" );
		continue;
	}

	++$mm_changes;
	if ( ! $mm_apply ) {
		WP_CLI::log( "[main-menu] item \"{$mm_item['title']}\": would be created" );
		continue;
	}

	$mm_data = array(
		'menu-item-title'    => $mm_item['title'],
		'menu-item-status'   => 'publish',
		'menu-item-position' => $mm_position + 1,
	);
	if ( isset( $mm_item['page'] ) ) {
		$mm_data['menu-item-type']      = 'post_type';
		$mm_data['menu-item-object']    = 'page';
		$mm_data['menu-item-object-id'] = $mm_item['page'];
	} else {
		$mm_data['menu-item-type'] = 'custom';
		$mm_data['menu-item-url']  = $mm_item['url'];
	}

	$mm_item_id = wp_update_nav_menu_item( $mm_menu_id, 0, $mm_data );
	if ( is_wp_error( $mm_item_id ) ) {
		WP_CLI::warning( "[main-menu] item \"{$mm_item['title']}\": " . $mm_item_id->get_error_message() );
		continue;
	}
	WP_CLI::log( "[main-menu] item \"{$mm_item['title']}\": created (#{$mm_item_id})" );
}

// 3. Locations.
$mm_locations = (array) get_theme_mod( 'nav_menu_locations', array() );
$mm_updated   = $mm_locations;

foreach ( array( 'menu-main', 'menu-footer' ) as $mm_location ) {
	$mm_current = isset( $mm_locations[ $mm_location ] ) ? (int) $mm_locations[ $mm_location ] : 0;

	if ( $mm_menu_id && $mm_current === $mm_menu_id ) {
		WP_CLI::log( "[main-menu] location {$mm_location}: already assigned" );
		continue;
	}

	++$mm_changes;
	WP_CLI::log( "[main-menu] location {$mm_location}: " . ( $mm_apply ? 'assigned' : 'would be assigned' ) . " (was #{$mm_current})" );
	if ( $mm_apply ) {
		$mm_updated[ $mm_location ] = $mm_menu_id;
	}
}

if ( $mm_apply && $mm_updated !== $mm_locations ) {
	set_theme_mod( 'nav_menu_locations', $mm_updated );
}

if ( ! $mm_changes ) {
	WP_CLI::success( '[main-menu] nothing to change' );
} else {
	WP_CLI::success( sprintf( '[main-menu] %d change(s) %s', $mm_changes, $mm_apply ? 'applied' : 'pending (run with apply)' ) );
}
