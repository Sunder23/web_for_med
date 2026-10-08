<?php
/**
 * Theme file.
 *
 * @package Vite_Starter
 */

/**
 * Registers the "cases" custom post type.
 *
 * @return void
 */
function starter_register_cases_cpt() {
	register_post_type(
		'cases',
		array(
			'labels'        => array(
				'name'          => __( 'Кейси', 'vite-starter' ),
				'singular_name' => __( 'Кейс', 'vite-starter' ),
				'add_new'       => __( 'Додати кейс', 'vite-starter' ),
				'add_new_item'  => __( 'Додати новий кейс', 'vite-starter' ),
				'edit_item'     => __( 'Редагувати кейс', 'vite-starter' ),
				'new_item'      => __( 'Новий кейс', 'vite-starter' ),
				'view_item'     => __( 'Переглянути кейс', 'vite-starter' ),
				'search_items'  => __( 'Шукати кейси', 'vite-starter' ),
				'not_found'     => __( 'Кейсів не знайдено', 'vite-starter' ),
				'all_items'     => __( 'Всі кейси', 'vite-starter' ),
				'menu_name'     => __( 'Кейси', 'vite-starter' ),
			),
			'public'        => true,
			'has_archive'   => true,
			'show_in_rest'  => true,
			'menu_icon'     => 'dashicons-portfolio',
			'menu_position' => 22,
			'supports'      => array( 'title', 'editor', 'thumbnail' ),
			'rewrite'       => array(
				'slug'       => 'cases',
				'with_front' => false,
			),
		)
	);
}
add_action( 'init', 'starter_register_cases_cpt' );
