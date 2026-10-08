<?php
/**
 * Theme file.
 *
 * @package Vite_Starter
 */

/**
 * Registers the "services" custom post type.
 *
 * @return void
 */
function starter_register_services_cpt() {
	register_post_type(
		'services',
		array(
			'labels'        => array(
				'name'          => __( 'Послуги', 'vite-starter' ),
				'singular_name' => __( 'Послуга', 'vite-starter' ),
				'add_new'       => __( 'Додати послугу', 'vite-starter' ),
				'add_new_item'  => __( 'Додати нову послугу', 'vite-starter' ),
				'edit_item'     => __( 'Редагувати послугу', 'vite-starter' ),
				'new_item'      => __( 'Нова послуга', 'vite-starter' ),
				'view_item'     => __( 'Переглянути послугу', 'vite-starter' ),
				'search_items'  => __( 'Шукати послуги', 'vite-starter' ),
				'not_found'     => __( 'Послуг не знайдено', 'vite-starter' ),
				'all_items'     => __( 'Всі послуги', 'vite-starter' ),
				'menu_name'     => __( 'Послуги', 'vite-starter' ),
			),
			'public'        => true,
			'has_archive'   => true,
			'show_in_rest'  => true,
			'menu_icon'     => 'dashicons-hammer',
			'menu_position' => 20,
			'supports'      => array( 'title', 'editor', 'thumbnail' ),
			'rewrite'       => array(
				'slug'       => 'services',
				'with_front' => false,
			),
		)
	);
}
add_action( 'init', 'starter_register_services_cpt' );
