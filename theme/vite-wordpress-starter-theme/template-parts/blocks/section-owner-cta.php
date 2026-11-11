<?php
/**
 * Theme file.
 *
 * Section: owner-cta (Figma desktop 3241:4222, mobile 3255:920).
 * Owner's quote with an illustration and contact buttons (Telegram / WhatsApp).
 * Buttons come from the section itself; style is `primary` or `outline`.
 *
 * @package Vite_Starter
 */

if ( empty( $args ) || ! is_array( $args ) ) {
	return;
}

$text     = isset( $args['text'] ) ? trim( (string) $args['text'] ) : '';
$subtext  = isset( $args['subtext'] ) ? (string) $args['subtext'] : '';
$image_id = ! empty( $args['image'] ) ? (int) $args['image'] : 0;
$rows     = isset( $args['buttons'] ) && is_array( $args['buttons'] ) ? $args['buttons'] : array();

if ( '' === $text && ! $rows ) {
	// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional WARN-level diagnostic (minimal logging policy).
	error_log( 'WARN [section-owner-cta] text is empty' );
	return;
}

$buttons = array();

foreach ( $rows as $index => $row ) {
	$button_link = isset( $row['link'] ) && is_array( $row['link'] ) ? $row['link'] : array();
	$url         = isset( $button_link['url'] ) ? (string) $button_link['url'] : '';
	$name        = isset( $button_link['title'] ) ? (string) $button_link['title'] : '';

	if ( '' === $url || '' === $name ) {
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional WARN-level diagnostic (minimal logging policy).
		error_log( 'WARN [section-owner-cta] button #' . (int) $index . ' has no url or title, skipped' );
		continue;
	}

	$style  = isset( $row['style'] ) && 'outline' === $row['style'] ? 'outline' : 'primary';
	$target = isset( $button_link['target'] ) ? (string) $button_link['target'] : '';
	$host   = wp_parse_url( $url, PHP_URL_HOST );

	// Messenger links leave the site: open them in a new tab.
	if ( '' === $target && $host && wp_parse_url( home_url(), PHP_URL_HOST ) !== $host ) {
		$target = '_blank';
	}

	$buttons[] = array(
		'url'    => $url,
		'title'  => $name,
		'style'  => $style,
		'target' => $target,
	);
}
?>
<section class="owner-cta">
	<div class="owner-cta__wrap container">
		<div class="owner-cta__inner frame">
			<div class="owner-cta__person">
				<?php if ( $image_id ) : ?>
					<?php
					echo wp_get_attachment_image(
						$image_id,
						'medium_large',
						false,
						array(
							'class'   => 'owner-cta__image',
							'sizes'   => '(min-width: 766px) 177px, 100vw',
							'loading' => 'lazy',
						)
					);
					?>
				<?php endif; ?>
				<?php if ( '' !== $text || '' !== $subtext ) : ?>
					<figure class="owner-cta__body">
						<?php if ( '' !== $text ) : ?>
							<blockquote class="owner-cta__text"><?php echo wp_kses_post( $text ); ?></blockquote>
						<?php endif; ?>
						<?php if ( '' !== $subtext ) : ?>
							<figcaption class="owner-cta__subtext"><?php echo esc_html( $subtext ); ?></figcaption>
						<?php endif; ?>
					</figure>
				<?php endif; ?>
			</div>
			<?php if ( $buttons ) : ?>
				<div class="owner-cta__actions">
					<?php foreach ( $buttons as $button ) : ?>
						<a
							href="<?php echo esc_url( $button['url'] ); ?>"
							class="button button--<?php echo esc_attr( $button['style'] ); ?> owner-cta__button"
							<?php if ( '_blank' === $button['target'] ) : ?>
								target="_blank" rel="noopener"
							<?php endif; ?>
							<?php echo starter_contact_trigger_attr( $button['url'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Fixed attribute literal. ?>
						><?php echo esc_html( $button['title'] ); ?></a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>
