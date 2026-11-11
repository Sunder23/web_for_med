<?php
/**
 * Theme file.
 *
 * Section: services (Figma desktop 3061:1551, mobile 3025:314).
 * The decorative header icon was dropped in the redesign (п.11).
 *
 * @package Vite_Starter
 */

if ( empty( $args ) || ! is_array( $args ) ) {
	return;
}
$items = isset( $args['items'] ) && is_array( $args['items'] ) ? array_values( $args['items'] ) : array();

if ( ! $items ) {
	// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional WARN-level diagnostic (minimal logging policy).
	error_log( 'WARN [section-services] items are empty' );
}
?>
<section class="services" id="services">
	<div class="services__wrap container">
		<div class="services__header frame">
			<h2 class="section-title services__title" data-aos="fade-in" data-aos-duration="600"><?php echo esc_html( $args['title'] ?? '' ); ?></h2>
		</div>
		<div class="services__body frame">
			<?php if ( ! empty( $args['image'] ) ) : ?>
				<div class="services__image glitch-image">
					<?php echo wp_get_attachment_image( (int) $args['image'], 'full', false, array( 'class' => 'services__photo glitch-image__layer' ) ); ?>
				</div>
			<?php endif; ?>
			<ol class="services-list">
				<?php foreach ( $items as $key => $item ) : ?>
					<li
						class="services-list__item icon-list__item<?php echo 0 === $key ? ' services-list__item--active' : ''; ?>"
						data-service-target="<?php echo esc_attr( (string) $key ); ?>">
						<?php
						if ( ! empty( $item['icon'] ) ) {
							echo wp_get_attachment_image(
								(int) $item['icon'],
								'full',
								false,
								array(
									'class' => 'services-list__icon icon-list__icon',
									'alt'   => '',
								)
							);
						}
						?>
						<?php if ( ! empty( $item['text'] ) ) : ?>
							<div class="services-list__content">
								<p class="services-list__title"><span class="services-list__number"><?php echo absint( $key + 1 ); ?>.</span> <?php echo esc_html( $item['title'] ?? '' ); ?></p>
								<p class="services-list__desc"><?php echo esc_html( $item['text'] ); ?></p>
							</div>
						<?php else : ?>
							<p class="services-list__title"><span class="services-list__number"><?php echo absint( $key + 1 ); ?>.</span> <?php echo esc_html( $item['title'] ?? '' ); ?></p>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ol>
		</div>
	</div>
</section>
