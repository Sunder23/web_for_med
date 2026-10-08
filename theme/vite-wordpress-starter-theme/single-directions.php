<?php
/**
 * Single direction: shared article layout + FAQ, related directions and CTA.
 *
 * @package Vite_Starter
 */

get_header();
?>

<main class="single-cpt single-direction">
	<?php
	while ( have_posts() ) :
		the_post();

		$hero = (array) get_field( 'direction_hero' );

		get_template_part(
			'partials/parts/article-layout',
			null,
			array(
				'lead'   => $hero['description'] ?? '',
				'blurbs' => $hero['blurbs'] ?? array(),
			)
		);

		get_template_part( 'partials/parts/cpt-faq', null, (array) get_field( 'direction_faq' ) );

		get_template_part(
			'partials/parts/related-posts',
			null,
			array(
				'post_type' => 'directions',
				'exclude'   => get_the_ID(),
				'title'     => __( 'Схожі напрямки', 'vite-starter' ),
			)
		);

		get_template_part( 'partials/parts/cpt-cta', null, (array) get_field( 'direction_cta' ) );
	endwhile;
	?>
</main>
<?php
get_footer();
