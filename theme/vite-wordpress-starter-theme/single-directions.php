<?php
get_header();

$hero = get_field( 'direction_hero' );
$faq  = get_field( 'direction_faq' );
$cta  = get_field( 'direction_cta' );
?>

<main class="single-cpt single-direction">
	<section class="s-cpt-hero">
		<div class="s-cpt-hero__wrap l-wrap">
			<div class="s-cpt-hero__inner l-frame-x">
				<?php custom_theme_breadcrumbs(); ?>
				<h1 class="s-cpt-hero__title"><?php the_title(); ?></h1>
				<?php if ( ! empty( $hero['description'] ) ) : ?>
					<p class="s-cpt-hero__subtitle"><?php echo esc_html( $hero['description'] ); ?></p>
				<?php endif; ?>
				<?php if ( ! empty( $hero['blurbs'] ) ) : ?>
					<div class="s-cpt-hero__blurbs">
						<?php foreach ( $hero['blurbs'] as $blurb ) : ?>
							<div class="hero-blurb info-card">
								<div class="info-card__body">
									<h3 class="hero-blurb__title card-title"><?php echo esc_html( $blurb['title'] ); ?></h3>
									<p class="hero-blurb__text card-text"><?php echo esc_html( $blurb['text'] ); ?></p>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
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
