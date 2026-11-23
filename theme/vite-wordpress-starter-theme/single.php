<?php
/**
 * Single blog post (Figma 3453:479): post-single layout and the «Актуальні теми» slider.
 * CPT singles have their own single-{post_type}.php templates (article-layout).
 *
 * @package Vite_Starter
 */

get_header();
?>

<main class="post-single">
	<?php
	while ( have_posts() ) :
		the_post();

		get_template_part( 'partials/parts/post-single' );

		$categories = get_the_category();

		get_template_part(
			'partials/parts/post-related',
			null,
			array(
				'exclude'     => get_the_ID(),
				'category_id' => ! empty( $categories ) ? (int) $categories[0]->term_id : 0,
				'title'       => __( 'Актуальні теми', 'vite-starter' ),
			)
		);

	endwhile;
	?>
</main>
<?php
get_footer();
