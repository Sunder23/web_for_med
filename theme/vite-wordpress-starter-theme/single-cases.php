<?php
get_header();

$hero = get_field( 'case_hero' );
$faq  = get_field( 'case_faq' );
$cta  = get_field( 'case_cta' );
?>

<main class="single-cpt single-case">
	<section class="s-cpt-hero">
		<div class="s-cpt-hero__wrap l-wrap">
			<div class="s-cpt-hero__inner l-frame-x">
				<?php custom_theme_breadcrumbs(); ?>
				<h1 class="s-cpt-hero__title"><?php the_title(); ?></h1>
				<?php if ( ! empty( $hero['subtitle'] ) ) : ?>
					<p class="s-cpt-hero__subtitle"><?php echo esc_html( $hero['subtitle'] ); ?></p>
				<?php endif; ?>
			</div>
		</div>
	</section>

	<?php while ( have_posts() ) : the_post(); ?>
		<?php get_template_part( 'template-parts/content-with-toc' ); ?>
	<?php endwhile; ?>

	<?php get_template_part( 'template-parts/cpt-faq', null, (array) $faq ); ?>

	<?php get_template_part( 'template-parts/cpt-cta', null, (array) $cta ); ?>
</main>
<?php get_footer();
