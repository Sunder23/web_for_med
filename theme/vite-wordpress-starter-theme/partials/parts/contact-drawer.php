<?php
/**
 * Off-canvas contact drawer (Figma 3155:2962 desktop / 3155:2916 mobile,
 * states 3155:2617). Rendered once per page from footer.php; opened by
 * contactDrawer.js on `[data-contact-open]` / `a[href$="#contact-form"]`.
 *
 * The CF7 form comes from the `footer_contact` option (Footer tab); its
 * `title` / `text` override the default heading when filled.
 *
 * @package Vite_Starter
 */

$starter_contact = starter_get_option( 'footer_contact' );

if ( empty( $starter_contact['contact_form'] ) ) {
	// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional WARN-level diagnostic (minimal logging policy).
	error_log( 'WARN [contact-drawer] footer_contact.contact_form is empty, drawer not rendered' );
	return;
}

$starter_contact_title = ! empty( $starter_contact['title'] ) ? $starter_contact['title'] : __( 'Обговорити задачі', 'vite-starter' );
$starter_contact_text  = ! empty( $starter_contact['text'] ) ? $starter_contact['text'] : '';
?>
<div class="contact-drawer" id="contact-drawer" role="dialog" aria-modal="true" aria-labelledby="contact-drawer-title" inert>
	<div class="contact-drawer__overlay" data-contact-close></div>

	<div class="contact-drawer__panel">
		<div class="contact-drawer__head">
			<div class="contact-drawer__bar">
				<p class="contact-drawer__eyebrow"><?php esc_html_e( 'Почнімо з розмови', 'vite-starter' ); ?></p>
				<button type="button" class="contact-drawer__close" data-contact-close aria-label="<?php esc_attr_e( 'Закрити форму', 'vite-starter' ); ?>">
					<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg>
				</button>
			</div>
			<p class="contact-drawer__title" id="contact-drawer-title"><?php echo esc_html( $starter_contact_title ); ?></p>
			<?php if ( $starter_contact_text ) : ?>
				<p class="contact-drawer__text"><?php echo esc_html( $starter_contact_text ); ?></p>
			<?php endif; ?>
		</div>

		<div class="contact-drawer__body" data-lenis-prevent>
			<div class="contact-drawer__alert" role="alert" hidden>
				<svg class="contact-drawer__alert-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
				<div>
					<p class="contact-drawer__alert-title"><?php esc_html_e( 'Не вдалося відправити', 'vite-starter' ); ?></p>
					<p class="contact-drawer__alert-text"><?php esc_html_e( 'Перевірте з’єднання та спробуйте ще раз. Ваші дані збережено.', 'vite-starter' ); ?></p>
				</div>
			</div>

			<div class="contact-drawer__form">
				<?php echo do_shortcode( $starter_contact['contact_form'] ); ?>
			</div>

			<div class="contact-drawer__success" hidden>
				<span class="contact-drawer__success-mark" aria-hidden="true">
					<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
				</span>
				<p class="contact-drawer__success-title"><?php esc_html_e( 'Дякуємо!', 'vite-starter' ); ?></p>
				<p class="contact-drawer__success-text"><?php esc_html_e( 'Ми отримали ваше повідомлення і зв’яжемося з вами найближчим часом.', 'vite-starter' ); ?></p>
				<button type="button" class="button button--primary contact-drawer__success-button" data-contact-close><?php esc_html_e( 'Закрити', 'vite-starter' ); ?></button>
			</div>
		</div>
	</div>
</div>
