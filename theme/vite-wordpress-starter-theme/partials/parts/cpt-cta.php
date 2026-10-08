<?php
/**
 * Final navy CTA banner under article singles (CPT CTA ACF groups or template defaults).
 *
 * @param array $args {
 *     @type string $title        Optional heading.
 *     @type string $text         Optional paragraph.
 *     @type string $button_label Button caption.
 *     @type string $button_url   Button link. Defaults to the footer form (#contacts).
 * }
 *
 * @package Vite_Starter
 */

$part_title   = ! empty( $args['title'] ) ? $args['title'] : '';
$text         = ! empty( $args['text'] ) ? $args['text'] : '';
$button_label = ! empty( $args['button_label'] ) ? $args['button_label'] : '';
$button_url   = ! empty( $args['button_url'] ) ? $args['button_url'] : '#contacts';

if ( ! $part_title && ! $text && ! $button_label ) {
	return;
}
?>
<section class="cta-banner">
	<div class="container cta-banner__container">
		<div class="frame cta-banner__frame">
			<div class="cta-banner__inner">
				<div class="cta-banner__content">
					<?php if ( $part_title ) : ?>
						<h2 class="cta-banner__title"><?php echo esc_html( $part_title ); ?></h2>
					<?php endif; ?>
					<?php if ( $text ) : ?>
						<p class="cta-banner__text"><?php echo esc_html( $text ); ?></p>
					<?php endif; ?>
				</div>
				<?php if ( $button_label ) : ?>
					<a href="<?php echo esc_url( $button_url ); ?>" class="button button--primary cta-banner__button"><?php echo esc_html( $button_label ); ?></a>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>
