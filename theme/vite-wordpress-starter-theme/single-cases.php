<?php
/**
 * Single case: shared article layout + FAQ, prev/next navigation and related cases.
 *
 * @package Vite_Starter
 */

get_header();
?>

<main class="single-cpt single-case">
	<?php
	while ( have_posts() ) :
		the_post();

		$hero = (array) get_field( 'case_hero' );

		get_template_part(
			'partials/parts/article-layout',
			null,
			array(
				'lead' => $hero['subtitle'] ?? '',
			)
		);

		get_template_part( 'partials/parts/cpt-faq', null, (array) get_field( 'case_faq' ) );

		get_template_part( 'partials/parts/post-nav' );

		get_template_part(
			'partials/parts/related-posts',
			null,
			array(
				'post_type' => 'cases',
				'exclude'   => get_the_ID(),
				'title'     => __( 'Схожі кейси', 'vite-starter' ),
			)
		);

	endwhile;
	?>
</main>
<?php
get_footer();
