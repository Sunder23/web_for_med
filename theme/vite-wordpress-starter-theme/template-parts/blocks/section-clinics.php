<?php
/**
 * Theme file.
 *
 * Section: clinics (Figma desktop 3061:1458, mobile 3180:3534).
 *
 * @package Vite_Starter
 */

if ( empty( $args ) || ! is_array( $args ) ) {
	return;
}

$items = isset( $args['items'] ) && is_array( $args['items'] ) ? $args['items'] : array();

if ( ! $items ) {
	// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional WARN-level diagnostic (minimal logging policy).
	error_log( 'WARN [section-clinics] items are empty' );
}
?>
<section class="clinics">
	<div class="clinics__wrap container">
		<div class="clinics__grid frame">
			<?php foreach ( $items as $item ) : ?>
				<div class="clinics__item">
					<?php
					if ( ! empty( $item['icon'] ) ) {
						echo wp_get_attachment_image( (int) $item['icon'], 'full', false, array( 'class' => 'clinics__icon', 'alt' => '' ) );
					}
					?>
					<span class="clinics__label"><?php echo esc_html( $item['title'] ?? '' ); ?></span>
				</div>
			<?php endforeach; ?>
		</div>
		<?php if ( ! empty( $args['aside_text'] ) || ! empty( $args['aside_icon'] ) ) : ?>
			<div class="clinics__aside">
				<?php
				if ( ! empty( $args['aside_icon'] ) ) {
					echo wp_get_attachment_image( (int) $args['aside_icon'], 'full', false, array( 'class' => 'clinics__aside-icon', 'alt' => '' ) );
				}
				?>
				<p class="clinics__aside-text"><?php echo esc_html( $args['aside_text'] ?? '' ); ?></p>
			</div>
		<?php endif; ?>
	</div>
</section>
