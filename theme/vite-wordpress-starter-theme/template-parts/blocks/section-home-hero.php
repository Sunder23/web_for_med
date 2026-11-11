<?php
/**
 * Theme file.
 *
 * Section: home-hero (Figma desktop 3061:1445, mobile 3180:3523).
 *
 * The image is painted as a CSS background (the glitch channels inherit it);
 * `image_mobile` is used below the `mobile` breakpoint and falls back to
 * `image` when empty.
 *
 * @package Vite_Starter
 */

if ( empty( $args ) || ! is_array( $args ) ) {
	return;
}

if ( empty( $args['title'] ) ) {
	// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional WARN-level diagnostic (minimal logging policy).
	error_log( 'WARN [section-home-hero] title is empty' );
}

$starter_primary   = is_array( $args['primary_link'] ?? null ) ? $args['primary_link'] : array();
$starter_secondary = is_array( $args['secondary_link'] ?? null ) ? $args['secondary_link'] : array();

$starter_image_url        = ! empty( $args['image'] ) ? wp_get_attachment_image_url( (int) $args['image'], 'full' ) : '';
$starter_image_mobile_url = ! empty( $args['image_mobile'] ) ? wp_get_attachment_image_url( (int) $args['image_mobile'], 'full' ) : '';

$starter_image_style = '';
if ( $starter_image_url ) {
	$starter_image_style = "--hero-image: url('" . esc_url( $starter_image_url ) . "');";
	if ( $starter_image_mobile_url ) {
		$starter_image_style .= " --hero-image-mobile: url('" . esc_url( $starter_image_mobile_url ) . "');";
	}
} elseif ( ! empty( $args['image'] ) ) {
	// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional WARN-level diagnostic (minimal logging policy).
	error_log( 'WARN [section-home-hero] image attachment not found: ' . (int) $args['image'] );
}
?>
<section class="hero">
	<div class="hero__wrap container">
		<div class="hero__content">
			<div class="hero__text">
				<div class="hero__titles">
					<h1 class="hero__title"><?php echo esc_html( $args['title'] ?? '' ); ?></h1>
					<?php if ( ! empty( $args['subtitle'] ) ) : ?>
						<p class="hero__subtitle" data-aos="fade-up" data-aos-duration="400" data-aos-delay="800"><?php echo esc_html( $args['subtitle'] ); ?></p>
					<?php endif; ?>
				</div>
				<div class="hero__actions">
					<?php if ( ! empty( $starter_primary['url'] ) ) : ?>
						<a href="<?php echo esc_url( $starter_primary['url'] ); ?>" class="button button--primary hero__button"<?php echo starter_contact_trigger_attr( $starter_primary['url'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Fixed attribute literal. ?> data-aos="fade" data-aos-duration="350" data-aos-delay="1050"><?php echo esc_html( $starter_primary['title'] ?? '' ); ?></a>
					<?php endif; ?>
					<?php if ( ! empty( $starter_secondary['url'] ) ) : ?>
						<a href="<?php echo esc_url( $starter_secondary['url'] ); ?>" class="button button--secondary hero__button hero__button--secondary" data-aos="fade" data-aos-duration="350" data-aos-delay="1250"><?php echo esc_html( $starter_secondary['title'] ?? '' ); ?></a>
					<?php endif; ?>
				</div>
			</div>
			<?php if ( $starter_image_style ) : ?>
				<div class="hero__image glitch" data-aos="hero-glitch-reveal" style="<?php echo esc_attr( $starter_image_style ); ?>">
					<div class="glitch__channel glitch__channel--r"></div>
					<div class="glitch__channel glitch__channel--g"></div>
					<div class="glitch__channel glitch__channel--b"></div>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>
