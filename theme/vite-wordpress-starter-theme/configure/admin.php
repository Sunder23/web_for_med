<?php
/**
 * Theme file.
 *
 * @package Vite_Starter
 */

/**
 * Hides the ACF field groups admin menu when the environment is production.
 *
 * @return void
 */
function starter_hide_acf_menu_in_production() {
	if ( 'production' !== wp_get_environment_type() ) {
		return;
	}

	remove_menu_page( 'edit.php?post_type=acf-field-group' );
}
add_action( 'admin_menu', 'starter_hide_acf_menu_in_production' );
