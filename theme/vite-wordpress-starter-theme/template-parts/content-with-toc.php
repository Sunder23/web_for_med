<?php

/**
 * Content + TOC two-column layout: block-editor content on the left,
 * sticky table of contents (auto-built from h2/h3 headings) with optional
 * who/needs info cards below it on the right.
 *
 * Used by all CPT singles and the blog single. Evolved from two-column-page.php.
 *
 * @param array $args {
 *     @type array $sidebar_who   Group [title, items[]].
 *     @type array $sidebar_needs Group [title, items[]].
 * }
 */

$sidebar_who   = ! empty($args['sidebar_who']) ? $args['sidebar_who'] : array();
$sidebar_needs = ! empty($args['sidebar_needs']) ? $args['sidebar_needs'] : array();

$toc = custom_theme_get_toc();
?>
<section class="s-two-col s-content-toc">
	<div class="s-two-col__wrap l-wrap">
		<div class="s-two-col__inner l-frame-x">
			<div class="s-two-col__content s-content-toc__content">
				<div class="entry-content svc-prose"><?php the_content(); ?></div>
			</div>
			<?php if (!wp_is_mobile()) : ?>
				<aside class="s-two-col__sidebar s-content-toc__sidebar">
					<div class="s-content-toc__sticky">
						<?php if (! empty($toc)) : ?>
							<nav class="toc" data-toc aria-label="<?php esc_attr_e('Зміст сторінки', 'textdomaintomodify'); ?>">
								<h2 class="toc__title card-title"><?php esc_html_e('Зміст', 'textdomaintomodify'); ?></h2>
								<ul class="toc__list">
									<?php foreach ($toc as $heading) : ?>
										<li class="toc__item toc__item--h<?php echo esc_attr((string) $heading['level']); ?>">
											<a class="toc__link" href="#<?php echo esc_attr($heading['id']); ?>"><?php echo esc_html($heading['title']); ?></a>
										</li>
									<?php endforeach; ?>
								</ul>
							</nav>
						<?php endif; ?>
						<?php foreach (array($sidebar_who, $sidebar_needs) as $block) : ?>
							<?php if (empty($block['title']) && empty($block['items'])) {
								continue;
							} ?>
							<div class="sidebar-card info-card">
								<div class="info-card__body">
									<?php if (! empty($block['title'])) : ?>
										<h3 class="sidebar-card__title card-title"><?php echo esc_html($block['title']); ?></h3>
									<?php endif; ?>
									<?php if (! empty($block['items'])) : ?>
										<ul class="sidebar-card__list">
											<?php foreach ($block['items'] as $item) : ?>
												<li class="sidebar-card__item card-text"><?php echo esc_html($item['text']); ?></li>
											<?php endforeach; ?>
										</ul>
									<?php endif; ?>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				</aside>
			<?php endif; ?>
		</div>
	</div>
</section>