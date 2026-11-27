<?php
/**
 * Theme file.
 *
 * Section: services-hero (Figma desktop 3537:2555 + 3537:2570, mobile 3589:7272 + 3589:7275).
 * Caption + page heading, then the decorative "clinic overview" dashboard on
 * the 45px grid (partials/parts/clinic-dashboard.php). The dashboard is drawn
 * at its design size and scaled to the column width by dashboardScale.js.
 *
 * @package Vite_Starter
 */

if ( empty( $args ) || ! is_array( $args ) ) {
	return;
}

$caption    = isset( $args['caption'] ) ? trim( (string) $args['caption'] ) : '';
$hero_title = isset( $args['title'] ) ? trim( (string) $args['title'] ) : '';

if ( '' === $hero_title ) {
	// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional WARN-level diagnostic (minimal logging policy).
	error_log( 'WARN [section-services-hero] title is empty' );
	return;
}
?>
<section class="services-hero">
	<div class="services-hero__wrap container">
		<div class="services-hero__inner frame">
			<?php if ( '' !== $caption ) : ?>
				<p class="services-hero__caption"><?php echo esc_html( $caption ); ?></p>
			<?php endif; ?>
			<h1 class="services-hero__title"><?php echo esc_html( $hero_title ); ?></h1>
		</div>
	</div>
	<div class="services-hero__cover">
		<div class="services-hero__cover-inner container">
			<?php get_template_part( 'partials/parts/clinic-dashboard' ); ?>
		</div>
	</div>
</section>
