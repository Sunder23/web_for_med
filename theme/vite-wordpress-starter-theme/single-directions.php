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

	<?php
	$sections      = get_field( 'direction_sections' );
	$sidebar_who   = get_field( 'direction_sidebar_who' );
	$sidebar_needs = get_field( 'direction_sidebar_needs' );

	if ( WP_DEBUG ) {
		error_log( '[W4M single-directions] sections=' . count( (array) $sections ) . ' post=' . get_the_ID() );
	}
	?>
	<section class="s-two-col">
		<div class="s-two-col__wrap l-wrap">
			<div class="s-two-col__inner l-frame-x">
				<div class="s-two-col__content">
					<?php if ( ! empty( $sections ) ) : ?>
						<?php foreach ( $sections as $section ) : ?>
							<div class="content-section">
								<?php if ( ! empty( $section['title'] ) ) : ?>
									<h2 class="content-section__title section-title"><?php echo esc_html( $section['title'] ); ?></h2>
								<?php endif; ?>
								<div class="svc-prose"><?php echo wp_kses_post( $section['content'] ); ?></div>
							</div>
						<?php endforeach; ?>
					<?php endif; ?>
				</div>
				<aside class="s-two-col__sidebar">
					<?php if ( ! empty( $sidebar_who['items'] ) ) : ?>
						<div>
							<?php if ( ! empty( $sidebar_who['title'] ) ) : ?>
								<h3 class="card-title"><?php echo esc_html( $sidebar_who['title'] ); ?></h3>
							<?php endif; ?>
							<ul class="svc-list">
								<?php foreach ( $sidebar_who['items'] as $item ) : ?>
									<li class="svc-list__item"><?php echo esc_html( $item['text'] ); ?></li>
								<?php endforeach; ?>
							</ul>
						</div>
					<?php endif; ?>
					<?php if ( ! empty( $sidebar_needs['items'] ) ) : ?>
						<div>
							<?php if ( ! empty( $sidebar_needs['title'] ) ) : ?>
								<h3 class="card-title"><?php echo esc_html( $sidebar_needs['title'] ); ?></h3>
							<?php endif; ?>
							<ul class="svc-list">
								<?php foreach ( $sidebar_needs['items'] as $item ) : ?>
									<li class="svc-list__item"><?php echo esc_html( $item['text'] ); ?></li>
								<?php endforeach; ?>
							</ul>
						</div>
					<?php endif; ?>
				</aside>
			</div>
		</div>
	</section>

	<?php get_template_part( 'template-parts/cpt-faq', null, (array) $faq ); ?>

	<?php get_template_part( 'template-parts/cpt-cta', null, (array) $cta ); ?>
</main>
<?php get_footer();
