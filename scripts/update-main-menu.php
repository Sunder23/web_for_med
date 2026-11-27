<?php
/**
 * Short main menu "Головне меню" (slug `main-menu-short`): Про нас / Послуги / Кейси / Блог.
 *
 * Items:
 *   Про нас → the `about` page;
 *   Послуги → the `services` page;
 *   Кейси   → the `cases` post type archive (`/cases/`);
 *   Блог    → the posts page (`page_for_posts`); WordPress marks it
 *             `current_page_parent` on single posts and post archives.
 *
 * Items are matched by target (page ID / URL), so re-running never duplicates them;
 * their order is restored to the list above (`menu_order`).
 * The former «Контакти» item (custom link `#contacts`) is removed: the contact
 * drawer opens from the header button (`data-contact-open`).
 *
 * The menu is looked up by slug, then by the `menu-main` location (the slug may
 * have been edited in the admin), and only created when neither exists.
 * The menu is assigned to the `menu-main` (header) and `menu-footer` locations.
 * Other locations and the previous "Main Menu" are left as they are, so the old
 * menu can be put back in Appearance → Menus. Re-running changes nothing.
 *
 * Args:
 *   (none)  dry run — logs what would change, writes nothing;
 *   apply   create the menu / items, remove the obsolete ones and assign the locations.
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
$mm_menu = wp_get_nav_menu_object( $mm_slug );
if ( ! $mm_menu ) {
	// The slug may have been changed in the admin: fall back to the menu in the header location.
	$mm_assigned = (array) get_theme_mod( 'nav_menu_locations', array() );
	$mm_menu     = ! empty( $mm_assigned['menu-main'] ) ? wp_get_nav_menu_object( (int) $mm_assigned['menu-main'] ) : false;
}
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
$mm_about    = get_page_by_path( 'about' );
$mm_services = get_page_by_path( 'services' );
$mm_blog     = (int) get_option( 'page_for_posts' );
$mm_items    = array(
	array(
		'title' => 'Про нас',
		'page'  => $mm_about instanceof WP_Post ? (int) $mm_about->ID : 0,
	),
	array(
		'title' => 'Послуги',
		'page'  => $mm_services instanceof WP_Post ? (int) $mm_services->ID : 0,
	),
	array(
		'title'   => 'Кейси',
		'archive' => 'cases',
	),
	array(
		'title' => 'Блог',
		'page'  => $mm_blog,
	),
);

// Custom links that are no longer part of the menu.
$mm_obsolete_urls = array( '#contacts' );

$mm_existing = $mm_menu_id ? (array) wp_get_nav_menu_items( $mm_menu_id ) : array();
$mm_order    = array(); // Menu item ID => wanted menu_order.

foreach ( $mm_items as $mm_position => $mm_item ) {
	if ( isset( $mm_item['page'] ) && ! $mm_item['page'] ) {
		WP_CLI::warning( "[main-menu] item \"{$mm_item['title']}\": page not found, skipped" );
		continue;
	}

	$mm_found = null;
	foreach ( $mm_existing as $mm_old ) {
		if ( isset( $mm_item['page'] ) ) {
			$mm_same = 'post_type' === $mm_old->type && (int) $mm_old->object_id === $mm_item['page'];
		} elseif ( isset( $mm_item['archive'] ) ) {
			$mm_same = 'post_type_archive' === $mm_old->type && $mm_old->object === $mm_item['archive'];
		} else {
			$mm_same = 'custom' === $mm_old->type && $mm_old->url === $mm_item['url'];
		}
		if ( $mm_same ) {
			$mm_found = $mm_old;
			break;
		}
	}

	if ( $mm_found ) {
		WP_CLI::log( "[main-menu] item \"{$mm_item['title']}\": exists (#{$mm_found->ID})" );
		$mm_order[ (int) $mm_found->ID ] = $mm_position + 1;
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
	} elseif ( isset( $mm_item['archive'] ) ) {
		$mm_data['menu-item-type']   = 'post_type_archive';
		$mm_data['menu-item-object'] = $mm_item['archive'];
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
	$mm_order[ (int) $mm_item_id ] = $mm_position + 1;
}

// 2a. Obsolete items.
foreach ( $mm_existing as $mm_old ) {
	if ( 'custom' !== $mm_old->type || ! in_array( $mm_old->url, $mm_obsolete_urls, true ) ) {
		continue;
	}
	++$mm_changes;
	if ( ! $mm_apply ) {
		WP_CLI::log( "[main-menu] item \"{$mm_old->title}\" ({$mm_old->url}, #{$mm_old->ID}): would be removed" );
		continue;
	}
	if ( wp_delete_post( (int) $mm_old->ID, true ) ) {
		WP_CLI::log( "[main-menu] item \"{$mm_old->title}\" ({$mm_old->url}, #{$mm_old->ID}): removed" );
	} else {
		WP_CLI::warning( "[main-menu] item #{$mm_old->ID}: cannot be removed" );
	}
}

// 2b. Order: a new item may land after the old ones, so put every item back in place.
foreach ( $mm_order as $mm_item_id => $mm_menu_order ) {
	if ( (int) get_post_field( 'menu_order', $mm_item_id ) === $mm_menu_order ) {
		continue;
	}
	++$mm_changes;
	WP_CLI::log( "[main-menu] item #{$mm_item_id}: position {$mm_menu_order}" . ( $mm_apply ? '' : ' (would be set)' ) );
	if ( $mm_apply ) {
		wp_update_post(
			array(
				'ID'         => $mm_item_id,
				'menu_order' => $mm_menu_order,
			)
		);
	}
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
