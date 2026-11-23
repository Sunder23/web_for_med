<?php
/**
 * Posts page (blog home). The page itself keeps the title «Блог» (menu, breadcrumbs);
 * the listing heading comes from the design (Figma 3446:656).
 *
 * @package Vite_Starter
 */

get_header();

get_template_part(
	'partials/parts/blog-archive',
	null,
	array(
		'title' => __( 'Досвід, помилки та несподівані відкриття', 'vite-starter' ),
	)
);

get_footer();
