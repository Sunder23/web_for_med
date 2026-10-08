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
<section class="s-clinics">
	<div class="s-clinics__wrap l-wrap">
		<div class="clinics-grid">
			<?php foreach ($items as $item) : ?>
				<div class="clinics-grid__item">
					<?php echo wp_get_attachment_image($item['icon'], 'full', '', ['class' => 'clinics-grid__icon']); ?>
					<span><?php echo esc_html($item['title']); ?></span>
				</div>
			<?php endforeach; ?>
		</div>
		<div class="clinics-aside">
			<?php echo wp_get_attachment_image($args['aside_icon'], 'full', '', ['class' => 'clinics-aside__icon']); ?>
			<p class="clinics-aside__text"><?php echo esc_html($args['aside_text']); ?></p>
		</div>
	</div>
</section>
