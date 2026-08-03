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

	<?php
	$facts   = get_field( 'case_facts' );
	$sections = get_field( 'case_sections' );
	$results  = get_field( 'case_results' );

	if ( WP_DEBUG ) {
		error_log( '[W4M single-cases] facts=' . ( empty( $facts ) ? 'no' : 'yes' ) . ' metrics=' . ( empty( $results['metrics'] ) ? 'no' : 'yes' ) . ' post=' . get_the_ID() );
	}
	?>

	<?php if ( ! empty( $facts ) ) : ?>
		<section class="s-case-facts">
			<div class="s-case-facts__wrap l-wrap">
				<div class="s-case-facts__inner l-frame-x">
					<div class="case-facts">
						<?php foreach ( $facts as $fact ) : ?>
							<div class="case-facts__item">
								<span class="case-facts__label tag c-tag"><?php echo esc_html( $fact['label'] ); ?></span>
								<p class="case-facts__value"><?php echo esc_html( $fact['value'] ); ?></p>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( ! empty( $sections ) ) : ?>
		<section class="s-case-content">
			<div class="s-case-content__wrap l-wrap">
				<div class="s-case-content__inner l-frame-x">
					<?php foreach ( $sections as $section ) : ?>
						<div class="content-section">
							<?php if ( ! empty( $section['title'] ) ) : ?>
								<h2 class="content-section__title section-title"><?php echo esc_html( $section['title'] ); ?></h2>
							<?php endif; ?>
							<div class="content-section__body svc-prose"><?php echo wp_kses_post( $section['content'] ); ?></div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( ! empty( $results ) && ( ! empty( $results['title'] ) || ! empty( $results['metrics'] ) || ! empty( $results['text'] ) ) ) : ?>
		<section class="s-case-results">
			<div class="s-case-results__wrap l-wrap">
				<div class="s-case-results__inner l-frame-x">
					<?php if ( ! empty( $results['title'] ) ) : ?>
						<h2 class="s-case-results__title section-title"><?php echo esc_html( $results['title'] ); ?></h2>
					<?php endif; ?>
					<?php if ( ! empty( $results['metrics'] ) ) : ?>
						<div class="case-metrics">
							<?php foreach ( $results['metrics'] as $metric ) : ?>
								<div class="case-metric">
									<p class="case-metric__value"><?php echo esc_html( $metric['value'] ); ?></p>
									<p class="case-metric__label card-text"><?php echo esc_html( $metric['label'] ); ?></p>
								</div>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
					<?php if ( ! empty( $results['text'] ) ) : ?>
						<div class="s-case-results__text svc-prose"><?php echo wp_kses_post( $results['text'] ); ?></div>
					<?php endif; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php get_template_part( 'template-parts/cpt-faq', null, (array) $faq ); ?>

	<?php get_template_part( 'template-parts/cpt-cta', null, (array) $cta ); ?>
</main>
<?php get_footer();
