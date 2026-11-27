<?php
/**
 * Theme file.
 *
 * Section: stages (Figma desktop 3539:2727, mobile 3589:7492).
 * A band of the decorative grid, the heading and the stage cards
 * (4 columns from `notebook`, 2 from `mobile`, 1 on phones). Card content slides
 * in on scroll via the shared components/cardReveal.js.
 *
 * @package Vite_Starter
 */

if ( empty( $args ) || ! is_array( $args ) ) {
	return;
}

$section_title = isset( $args['title'] ) ? trim( (string) $args['title'] ) : '';
$items         = array_values(
	array_filter(
		is_array( starter_get_array_value( $args, 'items', array() ) ) ? $args['items'] : array(),
		static function ( $item ) {
			return is_array( $item ) && '' !== trim( (string) starter_get_array_value( $item, 'title', '' ) );
		}
	)
);

if ( empty( $items ) ) {
	// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional WARN-level diagnostic (minimal logging policy).
	error_log( 'WARN [section-stages] items are empty' );
	return;
}
?>
<section class="stages">
	<div class="stages__wrap container">
		<div class="stages__frame frame">
			<div class="stages__band grid-lines" aria-hidden="true"></div>
			<?php if ( '' !== $section_title ) : ?>
				<div class="stages__head">
					<h2 class="section-title stages__title"><?php echo esc_html( $section_title ); ?></h2>
				</div>
			<?php endif; ?>
			<ul class="stages__list">
				<?php foreach ( $items as $item ) : ?>
					<li class="stages__item">
						<span class="stages__icon">
							<?php
							if ( ! empty( $item['icon'] ) ) {
								echo wp_get_attachment_image( (int) $item['icon'], 'full', false, array( 'alt' => '' ) );
							}
							?>
						</span>
						<h3 class="stages__item-title"><?php echo esc_html( (string) $item['title'] ); ?></h3>
						<?php if ( ! empty( $item['text'] ) ) : ?>
							<p class="stages__text"><?php echo esc_html( (string) $item['text'] ); ?></p>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	</div>
</section>
