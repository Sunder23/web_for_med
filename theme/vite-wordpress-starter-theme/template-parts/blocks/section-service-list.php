<?php
/**
 * Theme file.
 *
 * Section: service-list (Figma desktop 3534:2296 + states 3589:7642 / 7688 / 7734 / 7780,
 * mobile 3589:7383).
 * From the tablet breakpoint the left column previews the active service
 * (image + description) and hovering / focusing / clicking a row switches it;
 * on phones each row is an accordion with the description and the image
 * under it. Behaviour: assets/src/js/components/serviceList.js.
 * The block class is not `.services` on purpose: servicesAccordion.js of the
 * front page binds to that class.
 *
 * @package Vite_Starter
 */

if ( empty( $args ) || ! is_array( $args ) ) {
	return;
}

$intro = isset( $args['intro'] ) ? trim( (string) $args['intro'] ) : '';
$items = array_values(
	array_filter(
		is_array( starter_get_array_value( $args, 'items', array() ) ) ? $args['items'] : array(),
		static function ( $item ) {
			return is_array( $item ) && '' !== trim( (string) starter_get_array_value( $item, 'title', '' ) );
		}
	)
);

if ( empty( $items ) ) {
	// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional WARN-level diagnostic (minimal logging policy).
	error_log( 'WARN [section-service-list] items are empty' );
	return;
}

// 1-based in the editor; out of range falls back to the first service.
$active_index = (int) starter_get_array_value( $args, 'default_active', 1 ) - 1;
if ( $active_index < 0 || $active_index >= count( $items ) ) {
	// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional WARN-level diagnostic (minimal logging policy).
	error_log( 'WARN [section-service-list] default_active ' . ( $active_index + 1 ) . ' is out of range, using 1' );
	$active_index = 0;
}

foreach ( $items as $item_index => $item ) {
	if ( empty( $item['image'] ) ) {
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional WARN-level diagnostic (minimal logging policy).
		error_log( 'WARN [section-service-list] item ' . ( $item_index + 1 ) . ' has no image' );
	}
}

$list_link      = starter_get_array_value( $args, 'link', array() );
$list_link_url  = is_array( $list_link ) && ! empty( $list_link['url'] ) ? (string) $list_link['url'] : '';
$list_link_text = is_array( $list_link ) && ! empty( $list_link['title'] ) ? (string) $list_link['title'] : __( 'Перейти до кейсів', 'vite-starter' );
$list_link_icon = (int) starter_get_array_value( $args, 'link_icon', 0 );
$list_uid       = wp_unique_id( 'service-list-' );
?>
<section class="service-list" data-service-list>
	<div class="service-list__wrap container">
		<div class="service-list__inner">
			<div class="service-list__preview">
				<?php foreach ( $items as $item_index => $item ) : ?>
					<?php $is_active = $item_index === $active_index; ?>
					<div class="service-list__slide<?php echo $is_active ? ' is-active' : ''; ?>" data-service-preview>
						<div class="service-list__frame">
							<?php
							if ( ! empty( $item['image'] ) ) {
								echo wp_get_attachment_image(
									(int) $item['image'],
									'large',
									false,
									array(
										'class'   => 'service-list__image',
										'alt'     => '',
										'sizes'   => '375px',
										'loading' => $is_active ? 'eager' : 'lazy',
									)
								);
							}
							?>
						</div>
						<?php if ( ! empty( $item['text'] ) ) : ?>
							<p class="service-list__summary"><?php echo esc_html( (string) $item['text'] ); ?></p>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>

			<div class="service-list__body">
				<?php if ( '' !== $intro ) : ?>
					<p class="service-list__intro"><?php echo esc_html( $intro ); ?></p>
				<?php endif; ?>

				<ul class="service-list__items">
					<?php foreach ( $items as $item_index => $item ) : ?>
						<?php
						$is_active = $item_index === $active_index;
						$toggle_id = $list_uid . '-toggle-' . $item_index;
						$panel_id  = $list_uid . '-panel-' . $item_index;
						?>
						<li class="service-list__item<?php echo $is_active ? ' is-active is-open' : ''; ?>" data-service-item>
							<button type="button" class="service-list__toggle" id="<?php echo esc_attr( $toggle_id ); ?>" aria-expanded="<?php echo $is_active ? 'true' : 'false'; ?>" aria-controls="<?php echo esc_attr( $panel_id ); ?>">
								<span class="service-list__icon">
									<?php
									if ( ! empty( $item['icon'] ) ) {
										echo wp_get_attachment_image( (int) $item['icon'], 'full', false, array( 'alt' => '' ) );
									}
									?>
								</span>
								<span class="service-list__name"><?php echo esc_html( (string) $item['title'] ); ?></span>
								<span class="service-list__sign" aria-hidden="true"></span>
							</button>
							<div class="service-list__details" id="<?php echo esc_attr( $panel_id ); ?>" role="region" aria-labelledby="<?php echo esc_attr( $toggle_id ); ?>">
								<div class="service-list__details-inner">
									<?php if ( ! empty( $item['text'] ) ) : ?>
										<p class="service-list__text"><?php echo esc_html( (string) $item['text'] ); ?></p>
									<?php endif; ?>
									<?php
									if ( ! empty( $item['image'] ) ) {
										echo wp_get_attachment_image(
											(int) $item['image'],
											'large',
											false,
											array(
												'class'   => 'service-list__details-image',
												'alt'     => '',
												'sizes'   => '(min-width: 766px) 700px, 100vw',
												'loading' => 'lazy',
											)
										);
									}
									?>
								</div>
							</div>
						</li>
					<?php endforeach; ?>

					<?php if ( '' !== $list_link_url ) : ?>
						<li class="service-list__item service-list__item--link">
							<a class="service-list__link" href="<?php echo esc_url( $list_link_url ); ?>"<?php echo ! empty( $list_link['target'] ) ? ' target="' . esc_attr( (string) $list_link['target'] ) . '" rel="noopener"' : ''; ?>>
								<span class="service-list__icon">
									<?php
									if ( $list_link_icon ) {
										echo wp_get_attachment_image( $list_link_icon, 'full', false, array( 'alt' => '' ) );
									}
									?>
								</span>
								<span class="service-list__name"><?php echo esc_html( $list_link_text ); ?></span>
								<img class="service-list__arrow" src="<?php echo esc_url( starter_get_static_dir() . '/images/icons/arrow-more.svg' ); ?>" width="36" height="36" alt="" aria-hidden="true">
							</a>
						</li>
					<?php endif; ?>
				</ul>
			</div>
		</div>
	</div>
</section>
