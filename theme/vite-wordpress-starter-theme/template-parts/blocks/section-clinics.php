<?php
/**
 * Theme file.
 *
 * Section: clinics.
 *
 * @package Vite_Starter
 */

if ( empty( $args ) || ! is_array( $args ) ) {
	return;
}
$items = isset( $args['items'] ) && is_array( $args['items'] ) ? $args['items'] : array();
?>
<section class="clinics">
	<div class="clinics__wrap container">
		<div class="clinics__grid frame">
			<?php foreach ( $items as $item ) : ?>
				<div class="clinics__item">
					<?php echo wp_get_attachment_image( $item['icon'], 'full', '', array( 'class' => 'clinics__icon' ) ); ?>
					<span class="clinics__label"><?php echo esc_html( $item['title'] ); ?></span>
				</div>
			<?php endforeach; ?>
		</div>
		<div class="clinics__aside">
			<?php echo wp_get_attachment_image( $args['aside_icon'], 'full', '', array( 'class' => 'clinics__aside-icon' ) ); ?>
			<p class="clinics__aside-text"><?php echo esc_html( $args['aside_text'] ); ?></p>
		</div>
	</div>
</section>
