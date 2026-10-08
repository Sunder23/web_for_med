<?php
/**
 * Theme file.
 *
 * @package Vite_Starter
 */

/**
 * Registers core theme support features and loads the text domain.
 *
 * @return void
 */
function starter_setup() {
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'html5', array( 'script', 'style' ) );

	// Block editor: opt in to editor styles and load our stylesheet
	// (centered content container + prose styles matching the front end).
	add_theme_support( 'editor-styles' );
	add_editor_style( 'style-editor.css' );

	load_theme_textdomain( 'vite-starter', WFB_THEME_PATH . '/languages' );
}
add_action( 'after_setup_theme', 'starter_setup' );
