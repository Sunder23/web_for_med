<?php
/**
 * Theme file.
 *
 * Section: problems-solutions (Figma desktop 3287:1416 → 03_2 / 03_3 / 03_4,
 * mobile 3180:3596). Solution icons are 50px stroke icons from
 * static/images/home/solution-*.svg, attached to the ACF `image` sub-field.
 *
 * @package Vite_Starter
 */

if ( empty( $args ) || ! is_array( $args ) ) {
	return;
}
$problems    = isset( $args['problems'] ) && is_array( $args['problems'] ) ? $args['problems'] : array();
$solutions   = isset( $args['solutions'] ) && is_array( $args['solutions'] ) ? $args['solutions'] : array();
$banner_text = isset( $args['banner_text'] ) ? (string) $args['banner_text'] : '';

if ( ! $problems || ! $solutions ) {
	// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional WARN-level diagnostic (minimal logging policy).
	error_log( sprintf( 'WARN [section-problems-solutions] empty repeater: problems=%d solutions=%d', count( $problems ), count( $solutions ) ) );
}
?>
<div class="problems-solutions">
	<section class="problems">
		<div class="problems__wrap container">
			<div class="problems__inner">
				<?php foreach ( $problems as $item ) : ?>
					<div class="problem-card info-card">
						<?php if ( ! empty( $item['tag'] ) ) : ?>
							<span class="tag problem-card__tag"><?php echo esc_html( $item['tag'] ); ?></span>
						<?php endif; ?>
						<div class="problem-card__body info-card__body">
							<h3 class="problem-card__title card-title"><?php echo esc_html( $item['title'] ?? '' ); ?></h3>
							<p class="problem-card__text card-text"><?php echo esc_html( $item['text'] ?? '' ); ?></p>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<div class="problems-solutions__inner">
		<?php if ( '' !== $banner_text ) : ?>
			<section class="banner">
				<div class="banner__inner container">
					<h2 class="banner__text"><?php echo esc_html( $banner_text ); ?></h2>
				</div>
			</section>
		<?php endif; ?>

		<section class="solutions" id="about">
			<div class="solutions__wrap container">
				<div class="solutions__inner">
					<?php foreach ( $solutions as $item ) : ?>
						<div class="solution-card info-card">
							<?php if ( ! empty( $item['image'] ) ) : ?>
								<div class="solution-card__media">
									<?php
									echo wp_get_attachment_image(
										(int) $item['image'],
										'full',
										false,
										array(
											'class' => 'solution-card__icon',
											'alt'   => '',
										)
									);
									?>
								</div>
							<?php endif; ?>
							<div class="solution-card__body info-card__body">
								<h3 class="solution-card__title card-title"><?php echo esc_html( $item['title'] ?? '' ); ?></h3>
								<p class="solution-card__text card-text"><?php echo esc_html( $item['text'] ?? '' ); ?></p>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	</div>
</div>
