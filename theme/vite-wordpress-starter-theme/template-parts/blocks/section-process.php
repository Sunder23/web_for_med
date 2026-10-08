<?php
/**
 * Theme file.
 *
 * Section: process.
 *
 * @package Vite_Starter
 */

if ( empty( $args ) || ! is_array( $args ) ) {
	return;
}
$items = isset( $args['items'] ) && is_array( $args['items'] ) ? $args['items'] : array();
?>
<section class="process process--top" id="process">
	<div class="process__wrap container">
		<div class="process__header frame">
			<div class="pulse-icon">
				<div class="pulse-icon__core">

				</div>
				<div class="pulse-icon__rings">
					<div class="pulse-icon__circle pulse-icon__circle--outer"></div>
					<div class="pulse-icon__circle pulse-icon__circle--inner"></div>
					<div class="pulse-icon__pulse pulse-icon__pulse--1"></div>
					<div class="pulse-icon__pulse pulse-icon__pulse--2"></div>
					<div class="pulse-icon__pulse pulse-icon__pulse--3"></div>
				</div>
			</div>

			<h2 class="section-title process__title" data-aos="fade-in" data-aos-duration="600"><?php echo esc_html( $args['title'] ); ?></h2>
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
							<?php echo wp_get_attachment_image( $item['image'], array( 200, 173 ), '', array( 'class' => 'process-step__image' ) ); ?>
						</div>
					<?php endif; ?>
					<div class="process-step__top info-card__body">
						<span class="tag"><?php echo esc_html( $item['tag'] ); ?></span>
						<h3 class="process-step__title card-title"><?php echo esc_html( $item['title'] ); ?></h3>
						<p class="process-step__text card-text"><?php echo esc_html( $item['text'] ); ?></p>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>

</section>
