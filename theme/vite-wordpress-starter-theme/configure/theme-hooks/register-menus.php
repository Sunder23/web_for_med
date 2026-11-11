<?php
/**
 * Theme file.
 *
 * @package Vite_Starter
 */

/**
 * Registers the theme's navigation menus.
 *
 * @return void
 */
function starter_register_menus() {
	register_nav_menus(
		array(
			'menu-main'   => __( 'Main menu', 'vite-starter' ),
			// Optional; footer.php falls back to the top level of menu-main.
			'menu-footer' => __( 'Footer menu', 'vite-starter' ),
		)
	);
}
add_action( 'init', 'starter_register_menus' );
