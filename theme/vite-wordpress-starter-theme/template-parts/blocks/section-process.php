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
<section class="s-process s-process--top" id="process">
	<div class="s-process__wrap l-wrap">
		<div class="s-process__header l-frame-x">
			<div class="pulse-icon">
				<div class="icon-wrap">

				</div>
				<div class="elements">
					<div class="circle circle-outer"></div>
					<div class="circle circle-inner"></div>
					<div class="pulse pulse-1"></div>
					<div class="pulse pulse-2"></div>
					<div class="pulse pulse-3"></div>
				</div>
			</div>

			<h2 class="section-title" data-aos="fade-in" data-aos-duration="600"><?php echo esc_html( $args['title'] ); ?></h2>
		</div>

	</div>
</section>
<section class="s-process s-process--bottom">
	<div class="s-process__wrap l-wrap">
		<div class="s-process__steps ">
			<?php foreach ( $items as $item ) : ?>
				<div class="process-step info-card">
					<?php if ( ! empty( $item['image'] ) ) : ?>
						<div class="process-step--image">
							<?php echo wp_get_attachment_image( $item['image'], array( 200, 173 ), '', array( 'class' => 'process-step--image__item' ) ); ?>
						</div>
					<?php endif; ?>
					<div class="process-step__top info-card__body">
						<span class="tag c-tag"><?php echo esc_html( $item['tag'] ); ?></span>
						<h3 class="process-step__title card-title"><?php echo esc_html( $item['title'] ); ?></h3>
						<p class="process-step__text card-text"><?php echo esc_html( $item['text'] ); ?></p>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>

</section>
