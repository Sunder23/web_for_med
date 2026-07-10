<?php
/**
 * Set up the main navigation menu and blog page. Idempotent — run via:
 *   wp eval-file /scripts/import/setup-menu.php
 *
 * Structure: Головна / Послуги▾ (4 service pages) / Напрямки роботи / Кейси / Блог.
 * Also creates the «Блог» page, assigns it as page_for_posts, and enables
 * pretty permalinks when the structure is unset (required for the EN slugs
 * /services/, /directions/, /cases/, /blog/).
 *
 * Builds a dedicated "Main Menu" and assigns it to the menu-main location;
 * any previously assigned menu is left intact but unassigned. If "Main Menu"
 * already has items, they are left untouched (no duplicates).
 */

require_once __DIR__ . '/lib/common.php';

$w4m_errors  = array();
$w4m_actions = array();

// --- Pretty permalinks ------------------------------------------------------

if ( ! get_option( 'permalink_structure' ) ) {
	update_option( 'permalink_structure', '/%postname%/' );
	$w4m_actions[] = 'permalink_structure set to /%postname%/';
}

// --- Blog page + page_for_posts -------------------------------------------

$w4m_blog_created = false;
$w4m_blog_id      = w4m_import_find_or_create_post(
	'blog',
	'page',
	array( 'post_title' => 'Блог' ),
	$w4m_blog_created
);

if ( is_wp_error( $w4m_blog_id ) ) {
	$w4m_errors[] = 'Blog page: ' . $w4m_blog_id->get_error_message();
} else {
	if ( $w4m_blog_created ) {
		$w4m_actions[] = 'blog page created';
	}

	if ( (int) get_option( 'page_for_posts' ) !== (int) $w4m_blog_id ) {
		update_option( 'page_for_posts', $w4m_blog_id );
		$w4m_actions[] = 'page_for_posts set';
	}

	if ( 'page' !== get_option( 'show_on_front' ) && (int) get_option( 'page_on_front' ) > 0 ) {
		update_option( 'show_on_front', 'page' );
		$w4m_actions[] = 'show_on_front set to page';
	}
}

// --- Main menu -------------------------------------------------------------

$w4m_location = 'menu-main';
$w4m_menu     = wp_get_nav_menu_object( 'Main Menu' );
$w4m_menu_id  = $w4m_menu ? (int) $w4m_menu->term_id : 0;

if ( ! $w4m_menu_id ) {
	$w4m_menu_id = wp_create_nav_menu( 'Main Menu' );

	if ( is_wp_error( $w4m_menu_id ) ) {
		$w4m_errors[] = 'Menu: ' . $w4m_menu_id->get_error_message();
		$w4m_menu_id  = 0;
	} else {
		$w4m_actions[] = 'menu created';
	}
}

if ( $w4m_menu_id ) {
	$w4m_locations = get_nav_menu_locations();

	if ( ! isset( $w4m_locations[ $w4m_location ] ) || (int) $w4m_locations[ $w4m_location ] !== $w4m_menu_id ) {
		$w4m_theme_locations                  = get_theme_mod( 'nav_menu_locations' );
		$w4m_theme_locations                  = is_array( $w4m_theme_locations ) ? $w4m_theme_locations : array();
		$w4m_theme_locations[ $w4m_location ] = $w4m_menu_id;
		set_theme_mod( 'nav_menu_locations', $w4m_theme_locations );
		$w4m_actions[] = "menu assigned to {$w4m_location}";
	}
}

/**
 * Add one nav menu item, collecting errors.
 *
 * @param int   $menu_id
 * @param array $args   wp_update_nav_menu_item() menu-item args.
 * @param array $errors Collected error messages (by reference).
 * @return int Item ID, or 0 on failure.
 */
function w4m_import_add_menu_item( $menu_id, array $args, array &$errors ) {
	$args['menu-item-status'] = 'publish';

	$item_id = wp_update_nav_menu_item( $menu_id, 0, $args );

	if ( is_wp_error( $item_id ) ) {
		$errors[] = 'Menu item "' . ( isset( $args['menu-item-title'] ) ? $args['menu-item-title'] : '?' ) . '": ' . $item_id->get_error_message();
		return 0;
	}

	return (int) $item_id;
}

if ( $w4m_menu_id ) {
	$w4m_existing_items = wp_get_nav_menu_items( $w4m_menu_id );

	if ( ! empty( $w4m_existing_items ) ) {
		$w4m_actions[] = 'menu already has ' . count( $w4m_existing_items ) . ' item(s), items left untouched';
	} else {
		w4m_import_add_menu_item(
			$w4m_menu_id,
			array(
				'menu-item-title' => 'Головна',
				'menu-item-url'   => home_url( '/' ),
				'menu-item-type'  => 'custom',
			),
			$w4m_errors
		);

		$w4m_services_item = w4m_import_add_menu_item(
			$w4m_menu_id,
			array(
				'menu-item-title' => 'Послуги',
				'menu-item-url'   => home_url( '/services/' ),
				'menu-item-type'  => 'custom',
			),
			$w4m_errors
		);

		$w4m_service_links = array(
			'web-development' => 'Веб розробка',
			'seo'             => 'SEO',
			'ppc'             => 'Контекстна реклама',
			'analytics'       => 'Аналітика і моніторинг',
		);

		foreach ( $w4m_service_links as $w4m_slug => $w4m_label ) {
			$w4m_service = get_page_by_path( $w4m_slug, OBJECT, 'services' );

			if ( ! $w4m_service instanceof WP_Post ) {
				$w4m_errors[] = "Menu: service \"{$w4m_slug}\" not found — run import-services.php first.";
				continue;
			}

			w4m_import_add_menu_item(
				$w4m_menu_id,
				array(
					'menu-item-title'     => $w4m_label,
					'menu-item-type'      => 'post_type',
					'menu-item-object'    => 'services',
					'menu-item-object-id' => $w4m_service->ID,
					'menu-item-parent-id' => $w4m_services_item,
				),
				$w4m_errors
			);
		}

		w4m_import_add_menu_item(
			$w4m_menu_id,
			array(
				'menu-item-title' => 'Напрямки роботи',
				'menu-item-url'   => home_url( '/directions/' ),
				'menu-item-type'  => 'custom',
			),
			$w4m_errors
		);

		w4m_import_add_menu_item(
			$w4m_menu_id,
			array(
				'menu-item-title' => 'Кейси',
				'menu-item-url'   => home_url( '/cases/' ),
				'menu-item-type'  => 'custom',
			),
			$w4m_errors
		);

		if ( ! is_wp_error( $w4m_blog_id ) ) {
			w4m_import_add_menu_item(
				$w4m_menu_id,
				array(
					'menu-item-title'     => 'Блог',
					'menu-item-type'      => 'post_type',
					'menu-item-object'    => 'page',
					'menu-item-object-id' => $w4m_blog_id,
				),
				$w4m_errors
			);
		}

		$w4m_actions[] = 'menu items created';
	}
}

foreach ( $w4m_errors as $w4m_message ) {
	WP_CLI::warning( $w4m_message );
}

WP_CLI::success( sprintf(
	'Menu setup: %s; %d error(s).',
	$w4m_actions ? implode( ', ', $w4m_actions ) : 'nothing to do',
	count( $w4m_errors )
) );
