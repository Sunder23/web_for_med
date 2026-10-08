<?php
/**
 * Theme file.
 *
 * @package Vite_Starter
 */

?>
	</div><!-- #content -->

	<?php
	// Footer contact form: ACF Options (Footer tab), so it renders on every page.
	$contant     = starter_get_option( 'footer_contact' );
	$has_contact = ! empty( $contant['title'] ) || ! empty( $contant['text'] ) || ! empty( $contant['contact_form'] );
	if ( ! $has_contact ) {
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional WARN-level diagnostic (minimal logging policy).
		error_log( 'WARN [footer] footer_contact option is empty, skipping footer form section' );
	}
	$footer = array(
		'logo'    => get_field( 'logo', 'options' ),
		'cover'   => get_field( 'cover', 'options' ),
		'mail'    => get_field( 'mail', 'options' ),
		'socials' => get_field( 'socials', 'options' ),
	)
	?>

	<footer class="footer" id="contacts">
	<?php if ( $has_contact ) : ?>
	<div class="footer__form-section">
		<div class="container footer__container">
		<div class="footer__form-wrap">
			<div class="footer__form-content">
			<?php if ( ! empty( $contant['title'] ) ) : ?>
				<h2 class="footer__form-title" data-aos="fade-up-sm" data-aos-duration="700" data-aos-delay="0" data-aos-anchor="#contacts"><?php echo esc_html( $contant['title'] ); ?></h2>
			<?php endif; ?>
			<?php if ( ! empty( $contant['text'] ) ) : ?>
				<p class="footer__form-subtitle" data-aos="fade-up-xs" data-aos-duration="700" data-aos-delay="150" data-aos-anchor="#contacts"><?php echo esc_html( $contant['text'] ); ?></p>
			<?php endif; ?>
			</div>
			<?php if ( ! empty( $contant['contact_form'] ) ) : ?>
			<div class="contact-form">
				<?php echo do_shortcode( $contant['contact_form'] ); ?>
			</div>
			<?php endif; ?>
		</div>
		</div>
	</div>
	<?php endif; ?>

	<div class="footer__cover">
		<div class="container footer__container">
		<div class="frame footer__cover-frame">
			<div class="footer__cover-text">
			Далі буде...
			</div>
			<div class="footer__cover-image glitch" style="--footer-cover-image: url('<?php echo esc_url( wp_get_attachment_image_url( $footer['cover'], 'full' ) ); ?>');">
			<div class="glitch__channel glitch__channel--r"></div>
			<div class="glitch__channel glitch__channel--g"></div>
			<div class="glitch__channel glitch__channel--b"></div>
			</div>
		</div>
		</div>
	</div>

	<div class="footer__bar">
		<div class="footer__bar-wrap">
		<div class="footer__bar-inner">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="logo logo--sm footer__logo">
			<?php echo wp_get_attachment_image( $footer['logo'], 'full', '', array( 'class' => 'logo__image' ) ); ?>
			</a>
			<!-- <nav class="footer__nav" aria-label="<?php esc_attr_e( 'Footer navigation', 'vite-starter' ); ?>">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'menu-main',
					'menu_id'        => 'footer-menu-main',
					'bem_block'      => 'footer',
					'container'      => false,
					'fallback_cb'    => false,
				)
			);
			?>
			</nav> -->
			<div class="footer__contacts">
			<?php if ( ! empty( $footer['mail'] ) ) : ?>
				<a href="<?php echo esc_attr( $footer['mail']['url'] ); ?>" class="footer__email" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $footer['mail']['title'] ); ?></a>
			<?php endif; ?>
			<?php if ( ! empty( $footer['socials'] ) ) : ?>
				<div class="footer__socials">
				<?php foreach ( $footer['socials'] as $social ) : ?>
					<a href="<?php echo esc_url( $social['link'] ); ?>" class="footer__social">
					<?php echo wp_get_attachment_image( $social['icon'], 'full', '', array( 'class' => 'footer__social-icon' ) ); ?>
					</a>
				<?php endforeach; ?>
				</div>
			<?php endif; ?>
			</div>
		</div>
		</div>
	</div>
	</footer>
	</div>
	<?php wp_footer(); ?>
	</body>

	</html>