<?php
/**
 * Sticky sidebar for article-style singles: table of contents with a sliding
 * active-link indicator (assets/src/js/components/toc.js) + quick-contact CTA.
 *
 * @param array $args {
 *     @type array[] $items TOC rows from starter_get_toc(): [ level, title, id ].
 * }
 *
 * @package Vite_Starter
 */

$items = ! empty( $args['items'] ) && is_array( $args['items'] ) ? $args['items'] : array();

// Quick contact: the footer form (ACF Options) is the primary target; the header button is the fallback.
$contact       = starter_get_option( 'footer_contact' );
$header_button = starter_get_option( 'header_button' );
$mail          = starter_get_option( 'mail' );
$has_form      = ! empty( $contact['title'] ) || ! empty( $contact['contact_form'] );

if ( ! $has_form ) {
	// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional WARN-level diagnostic (minimal logging policy).
	error_log( 'WARN [toc] footer_contact option is empty, TOC CTA falls back to header_button' );
}

$cta_url = $has_form ? '#contacts' : ( ! empty( $header_button['url'] ) ? $header_button['url'] : '' );
?>
<div class="toc"<?php echo $items ? ' data-toc' : ''; ?>>
	<?php if ( $items ) : ?>
		<p class="toc__title"><?php esc_html_e( 'Зміст', 'vite-starter' ); ?></p>
		<nav class="toc__nav" aria-label="<?php esc_attr_e( 'Зміст сторінки', 'vite-starter' ); ?>">
			<span class="toc__indicator" aria-hidden="true"></span>
			<ul class="toc__list">
				<?php foreach ( $items as $item ) : ?>
					<li class="toc__item toc__item--h<?php echo esc_attr( (string) $item['level'] ); ?>">
						<a class="toc__link" href="#<?php echo esc_attr( $item['id'] ); ?>"><?php echo esc_html( $item['title'] ); ?></a>
					</li>
				<?php endforeach; ?>
			</ul>
		</nav>
	<?php endif; ?>

	<?php if ( $cta_url || ! empty( $mail['url'] ) ) : ?>
		<div class="toc__cta">
			<p class="toc__cta-label"><?php esc_html_e( 'Потрібен сайт або реклама для медзакладу?', 'vite-starter' ); ?></p>
			<?php if ( $cta_url ) : ?>
				<a class="button button--primary toc__cta-button" href="<?php echo esc_url( $cta_url ); ?>"><?php esc_html_e( 'Обговорити задачі', 'vite-starter' ); ?></a>
			<?php endif; ?>
			<?php if ( ! empty( $mail['url'] ) ) : ?>
				<a class="toc__cta-mail" href="<?php echo esc_url( $mail['url'] ); ?>"><?php echo esc_html( ! empty( $mail['title'] ) ? $mail['title'] : str_replace( 'mailto:', '', $mail['url'] ) ); ?></a>
			<?php endif; ?>
		</div>
	<?php endif; ?>
</div>
