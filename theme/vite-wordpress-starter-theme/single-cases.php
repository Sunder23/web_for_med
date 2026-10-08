<?php
/**
 * Single case: shared article layout + FAQ, related cases and CTA.
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

		get_template_part(
			'partials/parts/related-posts',
			null,
			array(
				'post_type' => 'cases',
				'exclude'   => get_the_ID(),
				'title'     => __( 'Схожі кейси', 'vite-starter' ),
			)
		);

		get_template_part( 'partials/parts/cpt-cta', null, (array) get_field( 'case_cta' ) );
	endwhile;
	?>
</main>
<?php
get_footer();
