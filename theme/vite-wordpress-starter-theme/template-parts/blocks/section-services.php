<?php
/**
 * Theme file.
 *
 * Section: services.
 *
 * @package Vite_Starter
 */

if ( empty( $args ) || ! is_array( $args ) ) {
	return;
}
$items = isset( $args['items'] ) && is_array( $args['items'] ) ? $args['items'] : array();
?>
<section class="services" id="services">
	<div class="services__wrap container">
		<div class="services__header frame">
			<svg class="galaxy-icon" viewBox="-30 -30 60 60" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" id="galaxy_icon" data-name="Galaxy icon">
				<title>Galaxy icon</title>
				<g class="galaxy-icon__body" id="galaxy_3d">
					<g class="galaxy-icon__rotor" id="galaxy_rotor">
						<g transform="translate(.25 -.5)">
							<circle class="galaxy-icon__core" r="8.7"></circle>
							<circle class="galaxy-icon__core" r="12.7"></circle>
							<path class="galaxy-icon__limb" id="galaxy_limb" d="M0-14.2c9 0 15.8 7.5 15.8 17 0 12.5-12 18.3-21 18.3-12 0-21.3-12-21.3-25 0-14 12-23 22.7-25.6q-11 4.5-11.7 7.5c-5 4-8.7 10-8.7 19 0 10 9 21 18 21 14 0 20-9 19.5-22z"></path>
							<use xlink:href="#galaxy_limb" transform="scale(-1)"></use>
						</g>
					</g>
				</g>
			</svg>
			<h2 class="section-title services__title" data-aos="fade-in" data-aos-duration="600"><?php echo esc_html( $args['title'] ); ?></h2>
		</div>
		<div class="services__body frame">
			<div class="services__image glitch-image">
				<?php echo wp_get_attachment_image( $args['image'], 'full', '', array( 'class' => 'services__photo glitch-image__layer' ) ); ?>
			</div>
			<ol class="services-list">
				<?php foreach ( $items as $key => $item ) : ?>
					<li
						class="services-list__item icon-list__item<?php echo 0 === $key ? ' services-list__item--active' : ''; ?>"
						data-service-target="<?php echo esc_attr( (string) $key ); ?>"
						style="--glitch-offset <?php echo absint( $key * 12 ); ?>">
						<?php echo wp_get_attachment_image( $item['icon'], 'full', '', array( 'class' => 'services-list__icon icon-list__icon' ) ); ?>
						<?php if ( ! empty( $item['text'] ) ) : ?>
							<div class="services-list__content">
								<p class="services-list__title"> <span class="services-list__number"><?php echo absint( $key + 1 ); ?>.</span> <?php echo esc_html( $item['title'] ); ?></p>
								<p class="services-list__desc"><?php echo esc_html( $item['text'] ); ?></p>
							</div>
						<?php else : ?>
							<span><?php echo esc_html( $item['title'] ); ?></span>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ol>
		</div>
	</div>
</section>
