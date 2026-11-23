<?php
/**
 * Blog post archives: categories, tags, dates, authors.
 * CPT archives have their own archive-{post_type}.php templates.
 *
 * @package Vite_Starter
 */

get_header();

if ( is_category() || is_tag() || is_tax() ) {
	$archive_title = single_term_title( '', false );
} elseif ( is_author() ) {
	$archive_title = get_the_author_meta( 'display_name', (int) get_query_var( 'author' ) );
} else {
	$archive_title = wp_strip_all_tags( get_the_archive_title() );
}

get_template_part(
	'partials/parts/blog-archive',
	null,
	array(
		'title'       => $archive_title,
		'description' => get_the_archive_description(),
	)
);

get_footer();
