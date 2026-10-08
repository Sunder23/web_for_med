<?php
/**
 * Theme file.
 *
 * @package Vite_Starter
 */

$starter_header = array(
	'header_button' => starter_get_option( 'header_button' ),
	'mail'          => starter_get_option( 'mail' ),
	'socials'       => starter_get_option( 'socials' ),
);
?>
	<header class="header" id="header">
		<div class="header__wrap">
		<div class="header__inner">
			<?php get_template_part( 'partials/header/logo' ); ?>

			<!-- Desktop nav — inside header for flex layout -->
			<nav class="nav nav--desktop" aria-label="<?php esc_attr_e( 'Main navigation', 'vite-starter' ); ?>">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'menu-main',
					'menu_id'        => 'menu-main-desktop',
					'bem_block'      => 'nav',
					'container'      => false,
					'fallback_cb'    => false,
				)
			);
			?>
			</nav>

			<?php if ( ! empty( $starter_header['header_button'] ) ) : ?>
			<a href="<?php echo esc_url( $starter_header['header_button']['url'] ); ?>" class="button button--primary button--sm header__cta">
				<?php echo esc_html( $starter_header['header_button']['title'] ); ?>
			</a>
			<?php endif; ?>

			<button class="burger" id="burger" aria-label="<?php esc_attr_e( 'Відкрити меню', 'vite-starter' ); ?>" aria-expanded="false" aria-controls="mainNav">
			<span class="burger__line"></span><span class="burger__line"></span><span class="burger__line"></span>
			</button>
		</div>
		</div>
	</header>

	<!-- Mobile overlay nav — outside header so its z-index is compared against header in root context -->
	<nav class="nav nav--mobile-overlay" id="mainNav" aria-label="<?php esc_attr_e( 'Mobile navigation', 'vite-starter' ); ?>" aria-hidden="true">

		<div class="nav__links">
		<?php
		wp_nav_menu(
			array(
				'theme_location' => 'menu-main',
				'menu_id'        => 'menu-main-mobile',
				'bem_block'      => 'nav',
				'container'      => false,
				'fallback_cb'    => false,
			)
		);
		?>
		</div>

		<div class="nav__mobile-footer">
		<?php if ( ! empty( $starter_header['socials'] ) || ! empty( $starter_header['mail'] ) ) : ?>
			<div class="nav__socials-row">
			<?php if ( ! empty( $starter_header['socials'] ) ) : ?>
				<div class="nav__social-icons">
				<?php foreach ( $starter_header['socials'] as $social ) : ?>
					<a href="<?php echo esc_url( $social['link'] ); ?>" class="nav__social-item" target="_blank" rel="noopener noreferrer">
					<?php echo wp_get_attachment_image( $social['icon'], 'full', '', array( 'class' => 'nav__social-icon' ) ); ?>
					</a>
				<?php endforeach; ?>
				</div>
			<?php endif; ?>
			<?php if ( ! empty( $starter_header['mail'] ) ) : ?>
				<a href="<?php echo esc_attr( $starter_header['mail']['url'] ); ?>" class="nav__email" target="_blank" rel="noopener noreferrer">
				<?php echo esc_html( $starter_header['mail']['title'] ); ?>
				</a>
			<?php endif; ?>
			</div>
		<?php endif; ?>

		<?php if ( ! empty( $starter_header['header_button'] ) ) : ?>
			<div class="nav__cta-row">
			<a href="<?php echo esc_url( $starter_header['header_button']['url'] ); ?>" class="button button--primary nav__cta-button">
				<?php echo esc_html( $starter_header['header_button']['title'] ); ?>
			</a>
			</div>
		<?php endif; ?>
		</div>

	</nav>

