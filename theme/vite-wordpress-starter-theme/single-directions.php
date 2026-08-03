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
				<?php if ( ! empty( $hero['highlights'] ) ) : ?>
					<div class="s-cpt-hero__highlights">
						<?php foreach ( $hero['highlights'] as $highlight ) : ?>
							<div class="hero-highlight">
								<span class="hero-highlight__icon">&#10003;</span>
								<span class="hero-highlight__text"><?php echo esc_html( $highlight['text'] ); ?></span>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
		</div>

		<?php if ( ! empty( $hero['blurbs'] ) ) : ?>
			<div class="s-cpt-hero__wrap l-wrap">
				<div class="s-cpt-hero__inner l-frame-x">
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
				</div>
			</div>
		<?php endif; ?>
	</section>

	<?php
	$included      = get_field( 'direction_included' );
	$formats       = get_field( 'direction_formats' );
	$process       = get_field( 'direction_process' );
	$sections      = get_field( 'direction_sections' );
	$sidebar_who   = get_field( 'direction_sidebar_who' );
	$sidebar_needs = get_field( 'direction_sidebar_needs' );

	if ( WP_DEBUG ) {
		error_log( '[W4M single-directions] included=' . count( (array) ( $included['items'] ?? array() ) ) . ' formats=' . count( (array) ( $formats['items'] ?? array() ) ) . ' process=' . count( (array) ( $process['items'] ?? array() ) ) . ' sections=' . count( (array) $sections ) . ' post=' . get_the_ID() );
	}
	?>
	<section class="s-two-col">
		<div class="s-two-col__wrap l-wrap">
			<div class="s-two-col__inner l-frame-x">
				<div class="s-two-col__content">
					<?php if ( ! empty( $included['items'] ) ) : ?>
						<div class="content-section">
							<?php if ( ! empty( $included['title'] ) ) : ?>
								<h2 class="content-section__title section-title"><?php echo esc_html( $included['title'] ); ?></h2>
							<?php endif; ?>
							<ol class="svc-numbered">
								<?php foreach ( $included['items'] as $item ) : ?>
									<li class="svc-numbered__item">
										<h3 class="svc-numbered__title card-title"><?php echo esc_html( $item['title'] ); ?></h3>
										<div class="svc-numbered__text svc-prose"><?php echo wp_kses_post( $item['text'] ); ?></div>
									</li>
								<?php endforeach; ?>
							</ol>
						</div>
					<?php endif; ?>

					<?php if ( ! empty( $formats['items'] ) ) : ?>
						<div class="content-section">
							<?php if ( ! empty( $formats['title'] ) ) : ?>
								<h2 class="content-section__title section-title"><?php echo esc_html( $formats['title'] ); ?></h2>
							<?php endif; ?>
							<div class="info-card-grid">
								<?php foreach ( $formats['items'] as $card ) : ?>
									<div class="info-card">
										<div class="info-card__body">
											<?php if ( ! empty( $card['tag'] ) ) : ?>
												<span class="tag info-card__tag"><?php echo esc_html( $card['tag'] ); ?></span>
											<?php endif; ?>
											<h3 class="card-title"><?php echo esc_html( $card['title'] ); ?></h3>
											<div class="card-text svc-prose"><?php echo wp_kses_post( $card['text'] ); ?></div>
										</div>
									</div>
								<?php endforeach; ?>
							</div>
						</div>
					<?php endif; ?>

					<?php if ( ! empty( $process['items'] ) ) : ?>
						<div class="content-section">
							<?php if ( ! empty( $process['title'] ) ) : ?>
								<h2 class="content-section__title section-title"><?php echo esc_html( $process['title'] ); ?></h2>
							<?php endif; ?>
							<ol class="svc-numbered">
								<?php foreach ( $process['items'] as $item ) : ?>
									<li class="svc-numbered__item">
										<h3 class="svc-numbered__title card-title"><?php echo esc_html( $item['title'] ); ?></h3>
										<div class="svc-numbered__text svc-prose"><?php echo wp_kses_post( $item['text'] ); ?></div>
									</li>
								<?php endforeach; ?>
							</ol>
						</div>
					<?php endif; ?>

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
								<h3 class="tag"><?php echo esc_html( $sidebar_who['title'] ); ?></h3>
							<?php endif; ?>
							<div>
								<?php foreach ( $sidebar_who['items'] as $item ) : ?>
									<div class="icon-list__item">
										<span class="icon-list__icon icon-list__icon--check">&#10003;</span>
										<span><?php echo esc_html( $item['text'] ); ?></span>
									</div>
								<?php endforeach; ?>
							</div>
						</div>
					<?php endif; ?>
					<?php if ( ! empty( $sidebar_needs['items'] ) ) : ?>
						<div>
							<?php if ( ! empty( $sidebar_needs['title'] ) ) : ?>
								<h3 class="tag"><?php echo esc_html( $sidebar_needs['title'] ); ?></h3>
							<?php endif; ?>
							<div>
								<?php foreach ( $sidebar_needs['items'] as $item ) : ?>
									<div class="icon-list__item">
										<span class="icon-list__icon icon-list__icon--check">&#10003;</span>
										<span><?php echo esc_html( $item['text'] ); ?></span>
									</div>
								<?php endforeach; ?>
							</div>
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
