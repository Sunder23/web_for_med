<?php
/**
 * Theme file.
 *
 * @package Vite_Starter
 */

/**
 * Registers the "case_status" taxonomy (Status in the case sidebar, one term per case).
 *
 * Hierarchical only for the checkbox UI; no public term pages.
 *
 * @return void
 */
function starter_register_case_status_taxonomy() {
	register_taxonomy(
		'case_status',
		array( 'cases' ),
		array(
			'labels'             => array(
				'name'          => __( 'Статуси', 'vite-starter' ),
				'singular_name' => __( 'Статус', 'vite-starter' ),
				'menu_name'     => __( 'Статуси', 'vite-starter' ),
				'all_items'     => __( 'Всі статуси', 'vite-starter' ),
				'edit_item'     => __( 'Редагувати статус', 'vite-starter' ),
				'add_new_item'  => __( 'Додати статус', 'vite-starter' ),
				'new_item_name' => __( 'Назва статусу', 'vite-starter' ),
				'search_items'  => __( 'Шукати статуси', 'vite-starter' ),
				'not_found'     => __( 'Статусів не знайдено', 'vite-starter' ),
			),
			'description'        => __( 'Оберіть один статус: Completed, In progress…', 'vite-starter' ),
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
add_action( 'init', 'starter_register_case_status_taxonomy' );
