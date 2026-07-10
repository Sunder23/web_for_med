<?php
/**
 * Two-column content layout: left content sections + right sidebar.
 * Used by single-directions.php and the two-column variant of single-services.php.
 *
 * @param array $args {
 *     @type array $sections      Rows of [title, content].
 *     @type array $sidebar_who   Group [title, items[]].
 *     @type array $sidebar_needs Group [title, items[]].
 * }
 */

$sections      = ! empty( $args['sections'] ) ? $args['sections'] : array();
$sidebar_who   = ! empty( $args['sidebar_who'] ) ? $args['sidebar_who'] : array();
$sidebar_needs = ! empty( $args['sidebar_needs'] ) ? $args['sidebar_needs'] : array();
?>
<section class="s-two-col">
	<div class="s-two-col__wrap l-wrap">
		<div class="s-two-col__inner l-frame-x">
			<div class="s-two-col__content">
				<?php foreach ( $sections as $section ) : ?>
					<section class="content-section">
						<?php if ( ! empty( $section['title'] ) ) : ?>
							<h2 class="content-section__title section-title"><?php echo esc_html( $section['title'] ); ?></h2>
						<?php endif; ?>
						<div class="content-section__body svc-prose"><?php echo wp_kses_post( $section['content'] ); ?></div>
					</section>
				<?php endforeach; ?>
			</div>
			<aside class="s-two-col__sidebar">
				<?php foreach ( array( $sidebar_who, $sidebar_needs ) as $block ) : ?>
					<?php if ( empty( $block['title'] ) && empty( $block['items'] ) ) { continue; } ?>
					<div class="sidebar-card info-card">
						<div class="info-card__body">
							<?php if ( ! empty( $block['title'] ) ) : ?>
								<h3 class="sidebar-card__title card-title"><?php echo esc_html( $block['title'] ); ?></h3>
							<?php endif; ?>
							<?php if ( ! empty( $block['items'] ) ) : ?>
								<ul class="sidebar-card__list">
									<?php foreach ( $block['items'] as $item ) : ?>
										<li class="sidebar-card__item card-text"><?php echo esc_html( $item['text'] ); ?></li>
									<?php endforeach; ?>
								</ul>
							<?php endif; ?>
						</div>
					</div>
				<?php endforeach; ?>
			</aside>
		</div>
	</div>
</section>
