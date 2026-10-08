<?php
/**
 * Theme file.
 *
 * BEM classes for wp_nav_menu() output. A menu opts in with a custom
 * `bem_block` argument (e.g. 'bem_block' => 'nav'); its markup then gets
 * `{block}__menu`, `{block}__item`, `{block}__item--parent`,
 * `{block}__item--current`, `{block}__link` and `{block}__submenu`.
 * Core classes (`menu-item`, `current-menu-item`, …) are kept so plugins
 * relying on them keep working; theme styles target only the BEM classes.
 *
 * @package Vite_Starter
 */

/**
 * Returns the BEM block a wp_nav_menu() call opted into, or an empty string.
 *
 * @param stdClass|array $args wp_nav_menu() arguments.
 * @return string Sanitized block name.
 */
function starter_get_nav_menu_bem_block( $args ) {
	$args = (object) $args;

	return ! empty( $args->bem_block ) ? sanitize_html_class( $args->bem_block ) : '';
}

/**
 * Sets the `{block}__menu` class on the menu <ul>.
 *
 * @param array $args wp_nav_menu() arguments.
 * @return array
 */
function starter_nav_menu_bem_menu_class( $args ) {
	$block = starter_get_nav_menu_bem_block( $args );
	if ( '' !== $block ) {
		$args['menu_class'] = $block . '__menu';
	}

	return $args;
}
add_filter( 'wp_nav_menu_args', 'starter_nav_menu_bem_menu_class' );

/**
 * Adds `{block}__item` (+ `--parent` / `--current`) to menu <li> elements.
 *
 * @param string[] $classes   Item classes.
 * @param WP_Post  $menu_item Menu item.
 * @param stdClass $args      wp_nav_menu() arguments.
 * @return string[]
 */
function starter_nav_menu_bem_item_class( $classes, $menu_item, $args ) {
	$block = starter_get_nav_menu_bem_block( $args );
	if ( '' === $block ) {
		return $classes;
	}

	$classes[] = $block . '__item';
	if ( in_array( 'menu-item-has-children', $classes, true ) ) {
		$classes[] = $block . '__item--parent';
	}
	if ( in_array( 'current-menu-item', $classes, true ) ) {
		$classes[] = $block . '__item--current';
	}

	return $classes;
}
add_filter( 'nav_menu_css_class', 'starter_nav_menu_bem_item_class', 10, 3 );

/**
 * Adds `{block}__link` to menu <a> elements.
 *
 * @param array    $atts      Link attributes.
 * @param WP_Post  $menu_item Menu item.
 * @param stdClass $args      wp_nav_menu() arguments.
 * @return array
 */
function starter_nav_menu_bem_link_class( $atts, $menu_item, $args ) {
	$block = starter_get_nav_menu_bem_block( $args );
	if ( '' !== $block ) {
		$atts['class'] = trim( ( isset( $atts['class'] ) ? $atts['class'] . ' ' : '' ) . $block . '__link' );
	}

	return $atts;
}
add_filter( 'nav_menu_link_attributes', 'starter_nav_menu_bem_link_class', 10, 3 );

/**
 * Adds `{block}__submenu` to nested menu <ul> elements.
 *
 * @param string[] $classes Submenu classes.
 * @param stdClass $args    wp_nav_menu() arguments.
 * @return string[]
 */
function starter_nav_menu_bem_submenu_class( $classes, $args ) {
	$block = starter_get_nav_menu_bem_block( $args );
	if ( '' !== $block ) {
		$classes[] = $block . '__submenu';
	}

	return $classes;
}
add_filter( 'nav_menu_submenu_css_class', 'starter_nav_menu_bem_submenu_class', 10, 2 );
