<?php
/**
 * Single service: shared article layout + FAQ, prev/next navigation and related services.
 *
 * @package Vite_Starter
 */

get_header();
?>

<main class="single-cpt single-service">
	<?php
	while ( have_posts() ) :
		the_post();

		$hero = (array) get_field( 'service_hero' );

		get_template_part(
			'partials/parts/article-layout',
			null,
			array(
				'lead'    => $hero['subtitle'] ?? '',
				'note'    => $hero['text'] ?? '',
				'buttons' => $hero['buttons'] ?? array(),
				'blurbs'  => $hero['blurbs'] ?? array(),
			)
		);

		get_template_part( 'partials/parts/cpt-faq', null, (array) get_field( 'service_faq' ) );

		get_template_part( 'partials/parts/post-nav' );

		get_template_part(
			'partials/parts/related-posts',
			null,
			array(
				'post_type' => 'services',
				'exclude'   => get_the_ID(),
				'title'     => __( 'Схожі послуги', 'vite-starter' ),
			)
		);

	endwhile;
	?>
</main>
<?php
get_footer();
