<?php
/**
 * Theme file.
 *
 * Section: process (Figma desktop 3061:1660, mobile 3025:365).
 * The pulsing header icon was dropped in the redesign (п.11); step icons are
 * 50px stroke icons from static/images/home/process-*.svg, attached to the
 * ACF `image` sub-field (п.13).
 *
 * @package Vite_Starter
 */

if ( empty( $args ) || ! is_array( $args ) ) {
	return;
}
$items = isset( $args['items'] ) && is_array( $args['items'] ) ? $args['items'] : array();

if ( ! $items ) {
	// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional WARN-level diagnostic (minimal logging policy).
	error_log( 'WARN [section-process] items are empty' );
}
?>
<section class="process process--top" id="process">
	<div class="process__wrap container">
		<div class="process__header frame">
			<h2 class="section-title process__title" data-aos="fade-in" data-aos-duration="600"><?php echo esc_html( $args['title'] ?? '' ); ?></h2>
		</div>
	</div>
</section>
<section class="process process--bottom">
	<div class="process__wrap container">
		<div class="process__steps">
			<?php foreach ( $items as $item ) : ?>
				<div class="process-step info-card">
					<?php if ( ! empty( $item['image'] ) ) : ?>
						<div class="process-step__media">
							<?php
							echo wp_get_attachment_image(
								(int) $item['image'],
								'full',
								false,
								array(
									'class' => 'process-step__icon',
									'alt'   => '',
								)
							);
							?>
						</div>
					<?php endif; ?>
					<div class="process-step__body info-card__body">
						<?php if ( ! empty( $item['tag'] ) ) : ?>
							<span class="tag process-step__tag"><?php echo esc_html( $item['tag'] ); ?></span>
						<?php endif; ?>
						<h3 class="process-step__title card-title"><?php echo esc_html( $item['title'] ?? '' ); ?></h3>
						<p class="process-step__text card-text"><?php echo esc_html( $item['text'] ?? '' ); ?></p>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
