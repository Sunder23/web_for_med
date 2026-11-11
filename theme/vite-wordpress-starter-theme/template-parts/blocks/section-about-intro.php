<?php
/**
 * Theme file.
 *
 * Section: about-intro (Figma desktop 3241:4190, mobile 3255:883).
 * The page has no visible heading here: the page title is printed as a
 * visually hidden <h1>. The image comes first in the DOM (it is the LCP
 * element and goes on top on phones); from the tablet breakpoint the text
 * column is moved to the left with `order`.
 *
 * @package Vite_Starter
 */

if ( empty( $args ) || ! is_array( $args ) ) {
	return;
}

$text     = isset( $args['text'] ) ? trim( (string) $args['text'] ) : '';
$image_id = ! empty( $args['image'] ) ? (int) $args['image'] : 0;
$caption  = isset( $args['caption'] ) ? (string) $args['caption'] : '';

if ( '' === $text && ! $image_id ) {
	// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional WARN-level diagnostic (minimal logging policy).
	error_log( 'WARN [section-about-intro] text and image are empty' );
	return;
}

if ( ! $image_id ) {
	// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional WARN-level diagnostic (minimal logging policy).
	error_log( 'WARN [section-about-intro] image is empty, rendering text only' );
}
?>
<section class="about-intro">
	<div class="about-intro__wrap container">
		<div class="about-intro__inner frame">
			<h1 class="screen-reader-text"><?php echo esc_html( get_the_title() ); ?></h1>
			<?php if ( $image_id ) : ?>
				<figure class="about-intro__media">
					<?php
					echo wp_get_attachment_image(
						$image_id,
						'large',
						false,
						array(
							'class'         => 'about-intro__image',
							'sizes'         => '(min-width: 1024px) 802px, 100vw',
							'loading'       => 'eager',
							'fetchpriority' => 'high',
						)
					);
					?>
					<?php if ( '' !== $caption ) : ?>
						<figcaption class="about-intro__caption"><?php echo esc_html( $caption ); ?></figcaption>
					<?php endif; ?>
				</figure>
			<?php endif; ?>
			<?php if ( '' !== $text ) : ?>
				<div class="about-intro__text">
					<?php echo wp_kses_post( $text ); ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>
