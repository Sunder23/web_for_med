<?php
get_header();

$hero          = get_field( 'service_hero' );
$audience      = get_field( 'service_audience' );
$triggers      = get_field( 'service_triggers' );
$included      = get_field( 'service_included' );
$stages        = get_field( 'service_stages' );
$strategy      = get_field( 'service_strategy' );
$examples      = get_field( 'service_examples' );
$formats       = get_field( 'service_formats' );
$faq           = get_field( 'service_faq' );
$sections      = get_field( 'service_sections' );
$sidebar_who   = get_field( 'service_sidebar_who' );
$sidebar_needs = get_field( 'service_sidebar_needs' );
$cta           = get_field( 'service_cta' );

// Pages with content sections use the two-column layout (e.g. «Аналітика і моніторинг»).
$is_two_column = ! empty( $sections );
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
				<?php if ( $is_two_column && ! empty( $hero['blurbs'] ) ) : ?>
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

	<?php if ( $is_two_column ) : ?>

		<?php
		get_template_part( 'template-parts/two-column-page', null, array(
			'sections'      => $sections,
			'sidebar_who'   => $sidebar_who,
			'sidebar_needs' => $sidebar_needs,
		) );
		?>

	<?php else : ?>

		<?php if ( ! empty( $audience['title'] ) || ! empty( $audience['cards'] ) ) : ?>
			<section class="s-svc s-svc--audience">
				<div class="s-svc__wrap l-wrap">
					<div class="s-svc__inner l-frame-x">
						<?php if ( ! empty( $audience['title'] ) ) : ?>
							<h2 class="s-svc__title section-title"><?php echo esc_html( $audience['title'] ); ?></h2>
						<?php endif; ?>
						<?php if ( ! empty( $audience['intro'] ) ) : ?>
							<p class="s-svc__intro"><?php echo esc_html( $audience['intro'] ); ?></p>
						<?php endif; ?>
						<?php if ( ! empty( $audience['cards'] ) ) : ?>
							<div class="svc-cards">
								<?php foreach ( $audience['cards'] as $card ) : ?>
									<div class="svc-card info-card">
										<div class="info-card__body">
											<h3 class="svc-card__title card-title"><?php echo esc_html( $card['title'] ); ?></h3>
											<p class="svc-card__text card-text"><?php echo esc_html( $card['text'] ); ?></p>
										</div>
									</div>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>
						<?php if ( ! empty( $audience['notice']['title'] ) || ! empty( $audience['notice']['text'] ) ) : ?>
							<div class="svc-notice">
								<?php if ( ! empty( $audience['notice']['title'] ) ) : ?>
									<h2 class="svc-notice__title section-title"><?php echo esc_html( $audience['notice']['title'] ); ?></h2>
								<?php endif; ?>
								<div class="svc-notice__text svc-prose"><?php echo wp_kses_post( $audience['notice']['text'] ); ?></div>
							</div>
						<?php endif; ?>
					</div>
				</div>
			</section>
		<?php endif; ?>

		<?php if ( ! empty( $triggers['title'] ) || ! empty( $triggers['items'] ) ) : ?>
			<section class="s-svc s-svc--triggers">
				<div class="s-svc__wrap l-wrap">
					<div class="s-svc__inner l-frame-x">
						<?php if ( ! empty( $triggers['title'] ) ) : ?>
							<h2 class="s-svc__title section-title"><?php echo esc_html( $triggers['title'] ); ?></h2>
						<?php endif; ?>
						<?php if ( ! empty( $triggers['intro'] ) ) : ?>
							<p class="s-svc__intro"><?php echo esc_html( $triggers['intro'] ); ?></p>
						<?php endif; ?>
						<?php if ( ! empty( $triggers['items'] ) ) : ?>
							<ul class="svc-list">
								<?php foreach ( $triggers['items'] as $item ) : ?>
									<li class="svc-list__item"><?php echo esc_html( $item['text'] ); ?></li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>
					</div>
				</div>
			</section>
		<?php endif; ?>

		<?php if ( ! empty( $included['title'] ) || ! empty( $included['items'] ) ) : ?>
			<section class="s-svc s-svc--included">
				<div class="s-svc__wrap l-wrap">
					<div class="s-svc__inner l-frame-x">
						<?php if ( ! empty( $included['title'] ) ) : ?>
							<h2 class="s-svc__title section-title"><?php echo esc_html( $included['title'] ); ?></h2>
						<?php endif; ?>
						<?php if ( ! empty( $included['items'] ) ) : ?>
							<ol class="svc-numbered">
								<?php foreach ( $included['items'] as $item ) : ?>
									<li class="svc-numbered__item">
										<h3 class="svc-numbered__title card-title"><?php echo esc_html( $item['title'] ); ?></h3>
										<div class="svc-numbered__text svc-prose"><?php echo wp_kses_post( $item['text'] ); ?></div>
									</li>
								<?php endforeach; ?>
							</ol>
						<?php endif; ?>
					</div>
				</div>
			</section>
		<?php endif; ?>

		<?php if ( ! empty( $stages['title'] ) || ! empty( $stages['items'] ) ) : ?>
			<section class="s-svc s-svc--stages">
				<div class="s-svc__wrap l-wrap">
					<div class="s-svc__inner l-frame-x">
						<?php if ( ! empty( $stages['title'] ) ) : ?>
							<h2 class="s-svc__title section-title"><?php echo esc_html( $stages['title'] ); ?></h2>
						<?php endif; ?>
						<?php if ( ! empty( $stages['items'] ) ) : ?>
							<div class="svc-stages">
								<?php foreach ( $stages['items'] as $key => $item ) : ?>
									<div class="svc-stage info-card">
										<span class="tag c-tag"><?php echo esc_html( sprintf( '%02d', $key + 1 ) ); ?></span>
										<div class="info-card__body">
											<h3 class="svc-stage__title card-title"><?php echo esc_html( $item['title'] ); ?></h3>
											<div class="svc-stage__text card-text svc-prose"><?php echo wp_kses_post( $item['text'] ); ?></div>
										</div>
									</div>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>
					</div>
				</div>
			</section>
		<?php endif; ?>

		<?php if ( ! empty( $strategy['title'] ) || ! empty( $strategy['text'] ) ) : ?>
			<section class="s-svc s-svc--strategy">
				<div class="s-svc__wrap l-wrap">
					<div class="s-svc__inner l-frame-x">
						<?php if ( ! empty( $strategy['title'] ) ) : ?>
							<h2 class="s-svc__title section-title"><?php echo esc_html( $strategy['title'] ); ?></h2>
						<?php endif; ?>
						<div class="s-svc__prose svc-prose"><?php echo wp_kses_post( $strategy['text'] ); ?></div>
					</div>
				</div>
			</section>
		<?php endif; ?>

		<?php if ( ! empty( $examples['title'] ) || ! empty( $examples['items'] ) ) : ?>
			<section class="s-svc s-svc--examples">
				<div class="s-svc__wrap l-wrap">
					<div class="s-svc__inner l-frame-x">
						<?php if ( ! empty( $examples['title'] ) ) : ?>
							<h2 class="s-svc__title section-title"><?php echo esc_html( $examples['title'] ); ?></h2>
						<?php endif; ?>
						<?php if ( ! empty( $examples['items'] ) ) : ?>
							<div class="svc-cards svc-cards--examples">
								<?php foreach ( $examples['items'] as $item ) : ?>
									<div class="svc-card info-card">
										<div class="info-card__body">
											<h3 class="svc-card__title card-title"><?php echo esc_html( $item['title'] ); ?></h3>
											<p class="svc-card__text card-text"><?php echo esc_html( $item['text'] ); ?></p>
										</div>
									</div>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>
						<?php if ( ! empty( $examples['button_label'] ) ) : ?>
							<div class="s-svc__actions">
								<a href="<?php echo esc_url( ! empty( $examples['button_url'] ) ? $examples['button_url'] : '#' ); ?>" class="btn btn--secondary"><?php echo esc_html( $examples['button_label'] ); ?></a>
							</div>
						<?php endif; ?>
					</div>
				</div>
			</section>
		<?php endif; ?>

		<?php if ( ! empty( $formats['title'] ) || ! empty( $formats['items'] ) ) : ?>
			<section class="s-svc s-svc--formats">
				<div class="s-svc__wrap l-wrap">
					<div class="s-svc__inner l-frame-x">
						<?php if ( ! empty( $formats['title'] ) ) : ?>
							<h2 class="s-svc__title section-title"><?php echo esc_html( $formats['title'] ); ?></h2>
						<?php endif; ?>
						<?php if ( ! empty( $formats['intro'] ) ) : ?>
							<p class="s-svc__intro"><?php echo esc_html( $formats['intro'] ); ?></p>
						<?php endif; ?>
						<?php if ( ! empty( $formats['items'] ) ) : ?>
							<div class="svc-cards svc-cards--formats">
								<?php foreach ( $formats['items'] as $item ) : ?>
									<div class="svc-card info-card">
										<div class="info-card__body">
											<h3 class="svc-card__title card-title"><?php echo esc_html( $item['title'] ); ?></h3>
											<div class="svc-card__text card-text svc-prose"><?php echo wp_kses_post( $item['text'] ); ?></div>
										</div>
									</div>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>
					</div>
				</div>
			</section>
		<?php endif; ?>

		<?php if ( ! empty( $faq['items'] ) ) : ?>
			<section class="s-svc s-svc--faq">
				<div class="s-svc__wrap l-wrap">
					<div class="s-svc__inner l-frame-x">
						<?php if ( ! empty( $faq['title'] ) ) : ?>
							<h2 class="s-svc__title section-title"><?php echo esc_html( $faq['title'] ); ?></h2>
						<?php endif; ?>
						<div class="faq" data-faq>
							<?php foreach ( $faq['items'] as $key => $item ) : ?>
								<div class="faq__item">
									<button class="faq__question" type="button" aria-expanded="false" aria-controls="faq-answer-<?php echo esc_attr( (string) $key ); ?>">
										<span><?php echo esc_html( $item['question'] ); ?></span>
										<svg class="faq__chevron" width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
											<path d="M2 5.5L8 11.5L14 5.5" stroke="currentColor" stroke-width="2" />
										</svg>
									</button>
									<div class="faq__answer" id="faq-answer-<?php echo esc_attr( (string) $key ); ?>">
										<div class="faq__answer-inner svc-prose"><?php echo wp_kses_post( $item['answer'] ); ?></div>
									</div>
								</div>
							<?php endforeach; ?>
						</div>
					</div>
				</div>
			</section>
		<?php endif; ?>

	<?php endif; ?>

	<?php get_template_part( 'template-parts/cpt-cta', null, (array) $cta ); ?>
</main>
<?php get_footer();
