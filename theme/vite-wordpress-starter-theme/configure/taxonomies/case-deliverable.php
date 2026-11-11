<?php
/**
 * Theme file.
 *
 * @package Vite_Starter
 */

/**
 * Registers the "case_deliverable" taxonomy (Deliverables in the case sidebar).
 *
 * Hierarchical only for the checkbox UI; no public term pages.
 *
 * @return void
 */
function starter_register_case_deliverable_taxonomy() {
	register_taxonomy(
		'case_deliverable',
		array( 'cases' ),
		array(
			'labels'             => array(
				'name'          => __( 'Роботи', 'vite-starter' ),
				'singular_name' => __( 'Робота', 'vite-starter' ),
				'menu_name'     => __( 'Роботи (Deliverables)', 'vite-starter' ),
				'all_items'     => __( 'Всі роботи', 'vite-starter' ),
				'edit_item'     => __( 'Редагувати роботу', 'vite-starter' ),
				'add_new_item'  => __( 'Додати роботу', 'vite-starter' ),
				'new_item_name' => __( 'Назва роботи', 'vite-starter' ),
				'search_items'  => __( 'Шукати роботи', 'vite-starter' ),
				'not_found'     => __( 'Робіт не знайдено', 'vite-starter' ),
			),
			'description'        => __( 'Що зроблено в кейсі: UX/UI Design, Web Development, Content Architecture…', 'vite-starter' ),
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
add_action( 'init', 'starter_register_case_deliverable_taxonomy' );
