<?php
/**
 * Posts page (blog home).
 *
 * @package Vite_Starter
 */

get_header();

$posts_page = (int) get_option( 'page_for_posts' );

get_template_part(
	'partials/parts/blog-archive',
	null,
	array(
		'title' => $posts_page ? get_the_title( $posts_page ) : __( 'Блог', 'vite-starter' ),
	)
);

get_footer();
