<?php
/**
 * Theme file.
 *
 * @package Vite_Starter
 */

/**
 * Registers the "directions" custom post type.
 *
 * @return void
 */
function starter_register_directions_cpt() {
	register_post_type(
		'directions',
		array(
			'labels'        => array(
				'name'          => __( 'Напрямки', 'vite-starter' ),
				'singular_name' => __( 'Напрямок', 'vite-starter' ),
				'add_new'       => __( 'Додати напрямок', 'vite-starter' ),
				'add_new_item'  => __( 'Додати новий напрямок', 'vite-starter' ),
				'edit_item'     => __( 'Редагувати напрямок', 'vite-starter' ),
				'new_item'      => __( 'Новий напрямок', 'vite-starter' ),
				'view_item'     => __( 'Переглянути напрямок', 'vite-starter' ),
				'search_items'  => __( 'Шукати напрямки', 'vite-starter' ),
				'not_found'     => __( 'Напрямків не знайдено', 'vite-starter' ),
				'all_items'     => __( 'Всі напрямки', 'vite-starter' ),
				'menu_name'     => __( 'Напрямки', 'vite-starter' ),
			),
			'public'        => true,
			'has_archive'   => true,
			'show_in_rest'  => true,
			'menu_icon'     => 'dashicons-heart',
			'menu_position' => 21,
			'supports'      => array( 'title', 'editor', 'thumbnail' ),
			'rewrite'       => array(
				'slug'       => 'directions',
				'with_front' => false,
			),
		)
	);
}
add_action( 'init', 'starter_register_directions_cpt' );
