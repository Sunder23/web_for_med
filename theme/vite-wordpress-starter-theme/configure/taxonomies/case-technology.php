<?php
/**
 * Theme file.
 *
 * @package Vite_Starter
 */

/**
 * Registers the "case_technology" taxonomy (Technology in the case sidebar).
 *
 * Hierarchical only for the checkbox UI; no public term pages.
 *
 * @return void
 */
function starter_register_case_technology_taxonomy() {
	register_taxonomy(
		'case_technology',
		array( 'cases' ),
		array(
			'labels'             => array(
				'name'          => __( 'Технології', 'vite-starter' ),
				'singular_name' => __( 'Технологія', 'vite-starter' ),
				'menu_name'     => __( 'Технології', 'vite-starter' ),
				'all_items'     => __( 'Всі технології', 'vite-starter' ),
				'edit_item'     => __( 'Редагувати технологію', 'vite-starter' ),
				'add_new_item'  => __( 'Додати технологію', 'vite-starter' ),
				'new_item_name' => __( 'Назва технології', 'vite-starter' ),
				'search_items'  => __( 'Шукати технології', 'vite-starter' ),
				'not_found'     => __( 'Технологій не знайдено', 'vite-starter' ),
			),
			'description'        => __( 'Стек кейсу: WordPress, Gutenberg, ACF…', 'vite-starter' ),
			'hierarchical'       => true,
			'public'             => false,
			'publicly_queryable' => false,
			'rewrite'            => false,
			'show_ui'            => true,
			'show_in_rest'       => true,
			'show_admin_column'  => true,
		)
	);
}
add_action( 'init', 'starter_register_case_technology_taxonomy' );
