<?php
get_header();

$hero          = get_field( 'service_hero' );
$faq           = get_field( 'service_faq' );
$sidebar_who   = get_field( 'service_sidebar_who' );
$sidebar_needs = get_field( 'service_sidebar_needs' );
$cta           = get_field( 'service_cta' );
?>

<main class="single-cpt single-service">
	<section class="s-cpt-hero">
		<div class="s-cpt-hero__wrap l-wrap">
			<div class="s-cpt-hero__inner l-frame-x">
				<?php custom_theme_breadcrumbs(); ?>
				<h1 class="s-cpt-hero__title"><?php the_title(); ?></h1>
				<?php if ( ! empty( $hero['subtitle'] ) ) : ?>
					<p class="s-cpt-hero__subtitle"><?php echo esc_html( $hero['subtitle'] ); ?></p>
				<?php endif; ?>
				<?php if ( ! empty( $hero['buttons'] ) ) : ?>
					<div class="s-cpt-hero__actions">
						<?php foreach ( $hero['buttons'] as $key => $button ) : ?>
							<a href="<?php echo esc_url( ! empty( $button['url'] ) ? $button['url'] : '#' ); ?>" class="btn <?php echo 0 === $key ? 'btn--primary' : 'btn--secondary'; ?>"><?php echo esc_html( $button['label'] ); ?></a>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
				<?php if ( ! empty( $hero['text'] ) ) : ?>
					<p class="s-cpt-hero__text"><?php echo esc_html( $hero['text'] ); ?></p>
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
		<?php
		get_template_part( 'template-parts/content-with-toc', null, array(
			'sidebar_who'   => $sidebar_who,
			'sidebar_needs' => $sidebar_needs,
		) );
		?>
	<?php endwhile; ?>

	<?php get_template_part( 'template-parts/cpt-faq', null, (array) $faq ); ?>

	<?php get_template_part( 'template-parts/cpt-cta', null, (array) $cta ); ?>
</main>
<?php get_footer();
