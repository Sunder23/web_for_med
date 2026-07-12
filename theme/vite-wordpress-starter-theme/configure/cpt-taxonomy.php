<?php

// Custom Post types & Taxonomies here

function custom_theme_register_cpts() {

	register_post_type( 'services', array(
		'labels' => array(
			'name'          => 'Послуги',
			'singular_name' => 'Послуга',
			'add_new'       => 'Додати послугу',
			'add_new_item'  => 'Додати нову послугу',
			'edit_item'     => 'Редагувати послугу',
			'new_item'      => 'Нова послуга',
			'view_item'     => 'Переглянути послугу',
			'search_items'  => 'Шукати послуги',
			'not_found'     => 'Послуг не знайдено',
			'all_items'     => 'Всі послуги',
			'menu_name'     => 'Послуги',
		),
		'public'       => true,
		'has_archive'  => true,
		'show_in_rest' => true,
		'menu_icon'    => 'dashicons-hammer',
		'menu_position'=> 20,
		'supports'     => array( 'title', 'editor', 'thumbnail' ),
		'rewrite'      => array( 'slug' => 'services', 'with_front' => false ),
	) );

	register_post_type( 'directions', array(
		'labels' => array(
			'name'          => 'Напрямки',
			'singular_name' => 'Напрямок',
			'add_new'       => 'Додати напрямок',
			'add_new_item'  => 'Додати новий напрямок',
			'edit_item'     => 'Редагувати напрямок',
			'new_item'      => 'Новий напрямок',
			'view_item'     => 'Переглянути напрямок',
			'search_items'  => 'Шукати напрямки',
			'not_found'     => 'Напрямків не знайдено',
			'all_items'     => 'Всі напрямки',
			'menu_name'     => 'Напрямки',
		),
		'public'       => true,
		'has_archive'  => true,
		'show_in_rest' => true,
		'menu_icon'    => 'dashicons-heart',
		'menu_position'=> 21,
		'supports'     => array( 'title', 'editor', 'thumbnail' ),
		'rewrite'      => array( 'slug' => 'directions', 'with_front' => false ),
	) );

	register_post_type( 'cases', array(
		'labels' => array(
			'name'          => 'Кейси',
			'singular_name' => 'Кейс',
			'add_new'       => 'Додати кейс',
			'add_new_item'  => 'Додати новий кейс',
			'edit_item'     => 'Редагувати кейс',
			'new_item'      => 'Новий кейс',
			'view_item'     => 'Переглянути кейс',
			'search_items'  => 'Шукати кейси',
			'not_found'     => 'Кейсів не знайдено',
			'all_items'     => 'Всі кейси',
			'menu_name'     => 'Кейси',
		),
		'public'       => true,
		'has_archive'  => true,
		'show_in_rest' => true,
		'menu_icon'    => 'dashicons-portfolio',
		'menu_position'=> 22,
		'supports'     => array( 'title', 'editor', 'thumbnail' ),
		'rewrite'      => array( 'slug' => 'cases', 'with_front' => false ),
	) );
}
add_action( 'init', 'custom_theme_register_cpts' );
