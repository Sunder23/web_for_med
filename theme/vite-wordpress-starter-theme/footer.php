<?php
/**
 * Site footer (Figma 3139:1682 desktop / 3129:1582 mobile): logo + menu,
 * contact-form CTA, socials + email. The "Далі буде…" cover is front-page only.
 * The contact form itself lives in the off-canvas drawer (partials/parts/contact-drawer.php).
 *
 * @package Vite_Starter
 */

$starter_footer = array(
	'cover'   => starter_get_option( 'cover' ),
	'mail'    => starter_get_option( 'mail' ),
	'socials' => starter_get_option( 'socials' ),
);

$starter_footer_menu = has_nav_menu( 'menu-footer' ) ? 'menu-footer' : 'menu-main';
?>
	</div><!-- #content -->

	<footer class="footer" id="contacts">
		<div class="footer__bar">
			<div class="footer__bar-wrap">
				<div class="footer__bar-inner">
					<div class="footer__col footer__col--brand">
						<?php get_template_part( 'partials/header/logo', null, array( 'modifier' => 'lg' ) ); ?>
						<nav class="footer__nav" aria-label="<?php esc_attr_e( 'Footer navigation', 'vite-starter' ); ?>">
							<?php
							wp_nav_menu(
								array(
									'theme_location' => $starter_footer_menu,
									'menu_id'        => 'menu-footer',
									'bem_block'      => 'footer',
									'container'      => false,
									'depth'          => 1,
									'fallback_cb'    => false,
								)
							);
							?>
						</nav>
					</div>

					<div class="footer__col footer__col--cta">
						<p class="footer__cta-label"><?php esc_html_e( 'Обговорити ваш запит', 'vite-starter' ); ?></p>
						<a href="#contact-form" class="button button--primary footer__cta" data-contact-open><?php esc_html_e( 'Заповнити форму', 'vite-starter' ); ?></a>
					</div>

					<div class="footer__col footer__col--contacts">
						<?php if ( ! empty( $starter_footer['socials'] ) ) : ?>
							<div class="footer__socials">
								<?php foreach ( $starter_footer['socials'] as $starter_social ) : ?>
									<a href="<?php echo esc_url( $starter_social['link'] ); ?>" class="footer__social" target="_blank" rel="noopener noreferrer">
										<?php echo wp_get_attachment_image( $starter_social['icon'], 'full', false, array( 'class' => 'footer__social-icon' ) ); ?>
									</a>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>
						<?php if ( ! empty( $starter_footer['mail']['url'] ) ) : ?>
							<a href="<?php echo esc_url( $starter_footer['mail']['url'] ); ?>" class="footer__email"><?php echo esc_html( $starter_footer['mail']['title'] ); ?></a>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>

		<?php if ( is_front_page() ) : ?>
			<div class="footer__cover">
				<div class="container footer__container">
					<div class="frame footer__cover-frame">
						<div class="footer__cover-text">
							<?php esc_html_e( 'Далі буде...', 'vite-starter' ); ?>
						</div>
						<div class="footer__cover-image glitch" style="--footer-cover-image: url('<?php echo esc_url( (string) wp_get_attachment_image_url( $starter_footer['cover'], 'full' ) ); ?>');">
							<div class="glitch__channel glitch__channel--r"></div>
							<div class="glitch__channel glitch__channel--g"></div>
							<div class="glitch__channel glitch__channel--b"></div>
						</div>
					</div>
				</div>
			</div>
		<?php endif; ?>
	</footer>
	</div>
	<?php get_template_part( 'partials/parts/contact-drawer' ); ?>
	<?php wp_footer(); ?>
	</body>

	</html>
