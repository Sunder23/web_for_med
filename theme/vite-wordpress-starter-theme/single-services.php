<?php
get_header();

$hero = get_field('service_hero');
$faq  = get_field('service_faq');
$cta  = get_field('service_cta');
?>

<main class="single-cpt single-service">
	<section class="s-cpt-hero">
		<div class="s-cpt-hero__wrap l-wrap">
			<div class="s-cpt-hero__inner l-frame-x">
				<?php custom_theme_breadcrumbs(); ?>
				<h1 class="s-cpt-hero__title"><?php the_title(); ?></h1>
				<?php if (! empty($hero['subtitle'])) : ?>
					<p class="s-cpt-hero__subtitle"><?php echo esc_html($hero['subtitle']); ?></p>
				<?php endif; ?>
				<?php if (! empty($hero['buttons'])) : ?>
					<div class="s-cpt-hero__actions">
						<?php foreach ($hero['buttons'] as $key => $button) : ?>
							<a href="<?php echo esc_url(! empty($button['url']) ? $button['url'] : '#'); ?>" class="btn <?php echo 0 === $key ? 'btn--primary' : 'btn--secondary'; ?>"><?php echo esc_html($button['label']); ?></a>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
				<?php if (! empty($hero['text'])) : ?>
					<p class="s-cpt-hero__text"><?php echo esc_html($hero['text']); ?></p>
				<?php endif; ?>
			</div>
		</div>

		<?php if (! empty($hero['blurbs'])) : ?>
			<div class="s-cpt-hero__wrap l-wrap">
				<div class="s-cpt-hero__inner l-frame-x">
					<div class="s-cpt-hero__blurbs">
						<?php foreach ($hero['blurbs'] as $blurb) : ?>
							<div class="hero-blurb info-card">
								<div class="info-card__body">
									<h3 class="hero-blurb__title card-title"><?php echo esc_html($blurb['title']); ?></h3>
									<p class="hero-blurb__text card-text"><?php echo esc_html($blurb['text']); ?></p>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
		<?php endif; ?>

	</section>

	<?php
	$layout = get_field('service_layout');
	if (empty($layout)) {
		$layout = 'linear';
	}

	if (WP_DEBUG) {
		error_log('[W4M single-services] layout=' . $layout . ' post=' . get_the_ID());
	}
	?>

	<?php if ('linear' === $layout) : ?>
		<?php
		$audience = get_field('service_audience');
		$triggers = get_field('service_triggers');
		$included = get_field('service_included');
		$stages   = get_field('service_stages');
		$strategy = get_field('service_strategy');
		$examples = get_field('service_examples');
		$formats  = get_field('service_formats');
		?>

		<?php if (! empty($audience['cards'])) : ?>
			<?php if (WP_DEBUG) { error_log('[W4M single-services] rendering section: audience post=' . get_the_ID()); } ?>
			<section class="s-svc s-svc--audience">
				<div class="s-svc__wrap l-wrap">
					<div class="s-svc__inner l-frame-x">
						<?php if (! empty($audience['title'])) : ?>
							<h2 class="s-svc__title section-title"><?php echo esc_html($audience['title']); ?></h2>
						<?php endif; ?>
						<?php if (! empty($audience['intro'])) : ?>
							<p class="s-svc__intro"><?php echo esc_html($audience['intro']); ?></p>
						<?php endif; ?>
						<div class="svc-cards">
							<?php foreach ($audience['cards'] as $card) : ?>
								<div class="svc-card">
									<h3 class="svc-card__title card-title"><?php echo esc_html($card['title']); ?></h3>
									<p class="svc-card__text card-text"><?php echo esc_html($card['text']); ?></p>
								</div>
							<?php endforeach; ?>
						</div>
						<?php if (! empty($audience['notice']) && (! empty($audience['notice']['title']) || ! empty($audience['notice']['text']))) : ?>
							<div class="svc-notice">
								<?php if (! empty($audience['notice']['title'])) : ?>
									<h3 class="svc-notice__title section-title"><?php echo esc_html($audience['notice']['title']); ?></h3>
								<?php endif; ?>
								<?php if (! empty($audience['notice']['text'])) : ?>
									<div class="svc-notice__text svc-prose"><?php echo wp_kses_post($audience['notice']['text']); ?></div>
								<?php endif; ?>
							</div>
						<?php endif; ?>
					</div>
				</div>
			</section>
		<?php endif; ?>

		<?php if (! empty($triggers['items'])) : ?>
			<?php if (WP_DEBUG) { error_log('[W4M single-services] rendering section: triggers post=' . get_the_ID()); } ?>
			<section class="s-svc s-svc--triggers">
				<div class="s-svc__wrap l-wrap">
					<div class="s-svc__inner l-frame-x">
						<?php if (! empty($triggers['title'])) : ?>
							<h2 class="s-svc__title section-title"><?php echo esc_html($triggers['title']); ?></h2>
						<?php endif; ?>
						<?php if (! empty($triggers['intro'])) : ?>
							<p class="s-svc__intro"><?php echo esc_html($triggers['intro']); ?></p>
						<?php endif; ?>
						<ul class="svc-list">
							<?php foreach ($triggers['items'] as $item) : ?>
								<li class="svc-list__item"><?php echo esc_html($item['text']); ?></li>
							<?php endforeach; ?>
						</ul>
					</div>
				</div>
			</section>
		<?php endif; ?>

		<?php if (! empty($included['items'])) : ?>
			<?php if (WP_DEBUG) { error_log('[W4M single-services] rendering section: included post=' . get_the_ID()); } ?>
			<section class="s-svc s-svc--included">
				<div class="s-svc__wrap l-wrap">
					<div class="s-svc__inner l-frame-x">
						<?php if (! empty($included['title'])) : ?>
							<h2 class="s-svc__title section-title"><?php echo esc_html($included['title']); ?></h2>
						<?php endif; ?>
						<ol class="svc-numbered">
							<?php foreach ($included['items'] as $item) : ?>
								<li class="svc-numbered__item">
									<h3 class="svc-numbered__title card-title"><?php echo esc_html($item['title']); ?></h3>
									<div class="svc-numbered__text svc-prose"><?php echo wp_kses_post($item['text']); ?></div>
								</li>
							<?php endforeach; ?>
						</ol>
					</div>
				</div>
			</section>
		<?php endif; ?>

		<?php if (! empty($stages['items'])) : ?>
			<?php if (WP_DEBUG) { error_log('[W4M single-services] rendering section: stages post=' . get_the_ID()); } ?>
			<section class="s-svc s-svc--stages">
				<div class="s-svc__wrap l-wrap">
					<div class="s-svc__inner l-frame-x">
						<?php if (! empty($stages['title'])) : ?>
							<h2 class="s-svc__title section-title"><?php echo esc_html($stages['title']); ?></h2>
						<?php endif; ?>
						<div class="svc-stages">
							<?php foreach ($stages['items'] as $item) : ?>
								<div class="svc-stage">
									<h3 class="svc-stage__title card-title"><?php echo esc_html($item['title']); ?></h3>
									<div class="svc-stage__text svc-prose"><?php echo wp_kses_post($item['text']); ?></div>
								</div>
							<?php endforeach; ?>
						</div>
					</div>
				</div>
			</section>
		<?php endif; ?>

		<?php if (! empty($strategy['text']) || ! empty($strategy['title'])) : ?>
			<?php if (WP_DEBUG) { error_log('[W4M single-services] rendering section: strategy post=' . get_the_ID()); } ?>
			<section class="s-svc s-svc--strategy">
				<div class="s-svc__wrap l-wrap">
					<div class="s-svc__inner l-frame-x">
						<?php if (! empty($strategy['title'])) : ?>
							<h2 class="s-svc__title section-title"><?php echo esc_html($strategy['title']); ?></h2>
						<?php endif; ?>
						<?php if (! empty($strategy['text'])) : ?>
							<div class="svc-prose"><?php echo wp_kses_post($strategy['text']); ?></div>
						<?php endif; ?>
					</div>
				</div>
			</section>
		<?php endif; ?>

		<?php if (! empty($examples['items'])) : ?>
			<?php if (WP_DEBUG) { error_log('[W4M single-services] rendering section: examples post=' . get_the_ID()); } ?>
			<section class="s-svc s-svc--examples">
				<div class="s-svc__wrap l-wrap">
					<div class="s-svc__inner l-frame-x">
						<?php if (! empty($examples['title'])) : ?>
							<h2 class="s-svc__title section-title"><?php echo esc_html($examples['title']); ?></h2>
						<?php endif; ?>
						<div class="svc-cards">
							<?php foreach ($examples['items'] as $item) : ?>
								<div class="svc-card">
									<h3 class="svc-card__title card-title"><?php echo esc_html($item['title']); ?></h3>
									<p class="svc-card__text card-text"><?php echo esc_html($item['text']); ?></p>
								</div>
							<?php endforeach; ?>
						</div>
						<?php if (! empty($examples['button_label'])) : ?>
							<a href="<?php echo esc_url(! empty($examples['button_url']) ? $examples['button_url'] : '#'); ?>" class="btn btn--secondary"><?php echo esc_html($examples['button_label']); ?></a>
						<?php endif; ?>
					</div>
				</div>
			</section>
		<?php endif; ?>

		<?php if (! empty($formats['items'])) : ?>
			<?php if (WP_DEBUG) { error_log('[W4M single-services] rendering section: formats post=' . get_the_ID()); } ?>
			<section class="s-svc s-svc--formats">
				<div class="s-svc__wrap l-wrap">
					<div class="s-svc__inner l-frame-x">
						<?php if (! empty($formats['title'])) : ?>
							<h2 class="s-svc__title section-title"><?php echo esc_html($formats['title']); ?></h2>
						<?php endif; ?>
						<?php if (! empty($formats['intro'])) : ?>
							<p class="s-svc__intro"><?php echo esc_html($formats['intro']); ?></p>
						<?php endif; ?>
						<div class="svc-cards">
							<?php foreach ($formats['items'] as $item) : ?>
								<div class="svc-card">
									<h3 class="svc-card__title card-title"><?php echo esc_html($item['title']); ?></h3>
									<div class="svc-card__text card-text svc-prose"><?php echo wp_kses_post($item['text']); ?></div>
								</div>
							<?php endforeach; ?>
						</div>
					</div>
				</div>
			</section>
		<?php endif; ?>

	<?php else : ?>
		<?php
		$sections      = get_field('service_sections');
		$sidebar_who   = get_field('service_sidebar_who');
		$sidebar_needs = get_field('service_sidebar_needs');

		if (WP_DEBUG) {
			error_log('[W4M single-services] rendering section: two-col post=' . get_the_ID());
		}
		?>
		<section class="s-two-col">
			<div class="s-two-col__wrap l-wrap">
				<div class="s-two-col__inner l-frame-x">
					<div class="s-two-col__content">
						<?php if (! empty($sections)) : ?>
							<?php foreach ($sections as $section) : ?>
								<div class="content-section">
									<?php if (! empty($section['title'])) : ?>
										<h2 class="content-section__title section-title"><?php echo esc_html($section['title']); ?></h2>
									<?php endif; ?>
									<div class="svc-prose"><?php echo wp_kses_post($section['content']); ?></div>
								</div>
							<?php endforeach; ?>
						<?php endif; ?>
					</div>
					<aside class="s-two-col__sidebar">
						<?php if (! empty($sidebar_who['items'])) : ?>
							<div>
								<?php if (! empty($sidebar_who['title'])) : ?>
									<h3 class="card-title"><?php echo esc_html($sidebar_who['title']); ?></h3>
								<?php endif; ?>
								<ul class="svc-list">
									<?php foreach ($sidebar_who['items'] as $item) : ?>
										<li class="svc-list__item"><?php echo esc_html($item['text']); ?></li>
									<?php endforeach; ?>
								</ul>
							</div>
						<?php endif; ?>
						<?php if (! empty($sidebar_needs['items'])) : ?>
							<div>
								<?php if (! empty($sidebar_needs['title'])) : ?>
									<h3 class="card-title"><?php echo esc_html($sidebar_needs['title']); ?></h3>
								<?php endif; ?>
								<ul class="svc-list">
									<?php foreach ($sidebar_needs['items'] as $item) : ?>
										<li class="svc-list__item"><?php echo esc_html($item['text']); ?></li>
									<?php endforeach; ?>
								</ul>
							</div>
						<?php endif; ?>
					</aside>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php get_template_part('template-parts/cpt-faq', null, (array) $faq); ?>

	<?php get_template_part('template-parts/cpt-cta', null, (array) $cta); ?>
</main>
<?php get_footer();
