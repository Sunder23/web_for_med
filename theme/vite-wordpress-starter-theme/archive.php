<?php
/**
 * Blog post archives: categories, tags, dates, authors.
 * CPT archives have their own archive-{post_type}.php templates.
 *
 * @package Vite_Starter
 */

get_header();

$archive_title = is_category() || is_tag() || is_tax()
	? single_term_title( '', false )
	: wp_strip_all_tags( get_the_archive_title() );

get_template_part(
	'partials/parts/blog-archive',
	null,
	array(
		'title'       => $archive_title,
		'description' => get_the_archive_description(),
	)
);

get_footer();
