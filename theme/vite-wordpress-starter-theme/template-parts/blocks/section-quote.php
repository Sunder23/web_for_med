<?php
/**
 * Theme file.
 *
 * Section: quote (Figma desktop 3061:1493, mobile 3180:3567).
 * The decorative gears icon was removed in the November 2026 redesign.
 *
 * @package Vite_Starter
 */

if ( empty( $args ) || ! is_array( $args ) ) {
	return;
}

$text = isset( $args['text'] ) ? (string) $args['text'] : '';

if ( '' === $text ) {
	// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional WARN-level diagnostic (minimal logging policy).
	error_log( 'WARN [section-quote] text is empty' );
	return;
}
?>
<section class="quote">
	<div class="quote__wrap container">
		<div class="quote__inner frame">
			<h2 class="quote__text" data-aos="fade-in" data-aos-duration="600"><?php echo wp_kses( $text, array( 'br' => array( 'class' => true ) ) ); ?></h2>
		</div>
	</div>
</section>
