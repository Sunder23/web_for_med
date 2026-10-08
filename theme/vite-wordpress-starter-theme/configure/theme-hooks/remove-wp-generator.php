<?php
/**
 * Theme file.
 *
 * @package Vite_Starter
 */

remove_action( 'wp_head', 'wp_generator' );

// Remove WP emoji.
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );
remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
remove_action( 'admin_print_styles', 'print_emoji_styles' );

/**
 * Deregisters wp-embed.js on the front end.
 *
 * @return void
 */
function starter_deregister_wp_embed() {
	wp_deregister_script( 'wp-embed' );
}
add_action( 'wp_footer', 'starter_deregister_wp_embed' );
