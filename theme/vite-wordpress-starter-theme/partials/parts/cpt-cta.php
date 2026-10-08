<?php
/**
 * Shared final CTA section for CPT single pages.
 *
 * @param array $args {
 *     @type string $title        Optional heading.
 *     @type string $text         Optional paragraph.
 *     @type string $button_label Button caption.
 *     @type string $button_url   Button link.
 * }
 */

$title        = ! empty( $args['title'] ) ? $args['title'] : '';
$text         = ! empty( $args['text'] ) ? $args['text'] : '';
$button_label = ! empty( $args['button_label'] ) ? $args['button_label'] : '';
$button_url   = ! empty( $args['button_url'] ) ? $args['button_url'] : '#';

if ( ! $title && ! $text && ! $button_label ) {
	return;
}
?>
<section class="s-cpt-cta">
	<div class="s-cpt-cta__wrap l-wrap">
		<div class="s-cpt-cta__inner l-frame-x">
			<?php if ( $title ) : ?>
				<h2 class="s-cpt-cta__title section-title"><?php echo esc_html( $title ); ?></h2>
			<?php endif; ?>
			<?php if ( $text ) : ?>
				<p class="s-cpt-cta__text"><?php echo esc_html( $text ); ?></p>
			<?php endif; ?>
			<?php if ( $button_label ) : ?>
				<a href="<?php echo esc_url( $button_url ); ?>" class="btn btn--primary"><?php echo esc_html( $button_label ); ?></a>
			<?php endif; ?>
		</div>
	</div>
</section>
