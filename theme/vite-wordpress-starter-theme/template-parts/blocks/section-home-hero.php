<?php
/**
 * Theme file.
 *
 * Section: home-hero.
 *
 * @package Vite_Starter
 */

if ( empty( $args ) || ! is_array( $args ) ) {
	return;
}
?>
<section class="hero">
	<div class="hero__wrap container">
		<div class="hero__content frame">
			<div class="hero__text">
				<div class="hero__titles">
					<h1 class="hero__title"><?php echo esc_html( $args['title'] ); ?></h1>
					<p class="hero__subtitle" data-aos="fade-up" data-aos-duration="400" data-aos-delay="800"><?php echo esc_html( $args['subtitle'] ); ?></p>
				</div>
				<div class="hero__actions">
					<a href="<?php echo esc_url( ( $args['primary_link'] )['url'] ?? '#' ); ?>" class="button button--primary hero__button" data-aos="fade" data-aos-duration="350" data-aos-delay="1050"><?php echo esc_html( ( $args['primary_link'] )['title'] ?? '' ); ?></a>
					<a href="<?php echo esc_url( ( $args['secondary_link'] )['url'] ?? '#' ); ?>" class="button button--secondary hero__button hero__button--secondary" data-aos="fade" data-aos-duration="350" data-aos-delay="1250"><?php echo esc_html( ( $args['secondary_link'] )['title'] ?? '' ); ?></a>
				</div>
			</div>
			<?php if ( $args['image'] ) : ?>
				<div class="hero__image glitch" data-aos="hero-glitch-reveal"
					style="
					--hero-image: url('<?php echo esc_url( wp_get_attachment_image_url( $args['image'], 'full' ) ); ?>');
					--hero-image-mobile: url('<?php echo esc_url( wp_get_attachment_image_url( $args['image_mobile'], 'full' ) ); ?>');
					">
					<div class="glitch__channel glitch__channel--r"></div>
					<div class="glitch__channel glitch__channel--g"></div>
					<div class="glitch__channel glitch__channel--b"></div>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>
