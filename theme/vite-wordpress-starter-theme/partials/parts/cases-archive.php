<?php
/**
 * Cases archive (/cases/): utility bar + case slider (endless vertical loop on desktop, looped lane on phones) (Figma 3025:141, mobile 3166:3416).
 * The slider fills the screen under the header; the footer is always in the normal flow below it.
 *
 * The captions (Client / Final product) are static. Case titles and descriptions
 * form two vertical reels (all items stacked, the track slides to the active one,
 * as on grndwrk.studio; casesReel.js); one «Читати кейс» link under them points
 * at the active case. Covers are rendered for every case; casesArchive.js runs
 * Swiper on the covers (viewport = container, track = wrapper, cover = slide)
 * and drives the reels.
 * Without JS the covers form a horizontal scroll-snap lane, titles and
 * descriptions are listed.
 *
 * @package Vite_Starter
 */

$starter_cases = array();
while ( have_posts() ) {
	the_post();
	$starter_case = starter_get_case_archive_item( get_post() );
	if ( $starter_case ) {
		$starter_cases[] = $starter_case;
	}
}
wp_reset_postdata();

$starter_has_description = in_array( true, array_map( static fn( $c ) => '' !== (string) $c['description'], $starter_cases ), true );

$starter_count     = count( $starter_cases );
$starter_count_str = str_pad( (string) $starter_count, 2, '0', STR_PAD_LEFT );
?>
<main class="cases-archive" data-cases-archive data-count="<?php echo esc_attr( (string) $starter_count ); ?>">
	<?php // Before the first paint: no flash of the unenhanced layout (the footer is always in the flow below). ?>
	<script>document.currentScript.parentElement.classList.add('is-enhanced');</script>
	<h1 class="cases-archive__title"><?php post_type_archive_title(); ?></h1>

	<div class="cases-archive__bar">
		<div class="cases-archive__bar-inner">
			<span class="cases-archive__label">/*CASE ARCHIVE*/</span>

			<?php if ( $starter_count > 1 ) : ?>
				<div class="cases-archive__status">
					<p class="cases-archive__counter" aria-live="polite" aria-atomic="true">
						<span class="cases-archive__counter-mode"><?php esc_html_e( 'Режим перегляду:', 'vite-starter' ); ?></span>
						<span data-cases-current>01</span>/<?php echo esc_html( $starter_count_str ); ?>
					</p>
					<div class="cases-archive__dots">
						<?php foreach ( $starter_cases as $starter_i => $starter_case ) : ?>
							<button
								type="button"
								class="cases-archive__dot<?php echo 0 === $starter_i ? ' is-active' : ''; ?>"
								data-cases-dot="<?php echo esc_attr( (string) $starter_i ); ?>"
								aria-controls="cases-archive-track"
								<?php /* translators: %d: case number. */ ?>
								aria-label="<?php echo esc_attr( sprintf( __( 'Кейс %d', 'vite-starter' ), $starter_i + 1 ) ); ?>"
								<?php echo 0 === $starter_i ? 'aria-current="true"' : ''; ?>
							></button>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endif; ?>
		</div>
	</div>

	<div class="cases-archive__wrap">
		<?php if ( ! $starter_count ) : ?>
			<div class="cases-archive__stage cases-archive__stage--empty">
				<p class="cases-archive__empty"><?php esc_html_e( 'Кейсів поки немає.', 'vite-starter' ); ?></p>
			</div>
		<?php else : ?>
			<div
				class="cases-archive__stage"
				data-cases-stage
				role="region"
				aria-roledescription="carousel"
				aria-label="<?php esc_attr_e( 'Кейси', 'vite-starter' ); ?>"
			>
				<div class="cases-archive__heads">
					<p class="cases-archive__caption"><?php esc_html_e( 'Client', 'vite-starter' ); ?></p>
					<div class="cases-archive__reel" data-cases-reel="title">
						<div class="cases-archive__reel-track" data-cases-reel-track>
							<?php foreach ( $starter_cases as $starter_i => $starter_case ) : ?>
								<h2 class="cases-archive__case-title cases-archive__reel-item<?php echo 0 === $starter_i ? ' is-active' : ''; ?>" data-cases-reel-item="<?php echo esc_attr( (string) $starter_i ); ?>" data-url="<?php echo esc_url( $starter_case['url'] ); ?>"><?php echo esc_html( $starter_case['title'] ); ?></h2>
							<?php endforeach; ?>
						</div>
					</div>
				</div>

				<div class="cases-archive__details">
					<?php if ( $starter_has_description ) : ?>
						<p class="cases-archive__caption"><?php esc_html_e( 'Final product', 'vite-starter' ); ?></p>
					<?php endif; ?>
					<?php // Same reel as the titles; an empty item keeps the indices for a case without a description. ?>
					<div class="cases-archive__reel cases-archive__reel--text" data-cases-reel="description">
						<div class="cases-archive__reel-track" data-cases-reel-track>
							<?php foreach ( $starter_cases as $starter_i => $starter_case ) : ?>
								<p class="cases-archive__description cases-archive__reel-item<?php echo 0 === $starter_i ? ' is-active' : ''; ?>" data-cases-reel-item="<?php echo esc_attr( (string) $starter_i ); ?>"><?php echo esc_html( (string) $starter_case['description'] ); ?></p>
							<?php endforeach; ?>
						</div>
					</div>
					<?php // One link, casesArchive.js points it at the active case (without JS the covers are the links). ?>
					<a class="button button--secondary cases-archive__link" href="<?php echo esc_url( $starter_cases[0]['url'] ); ?>" data-cases-link>
						<?php esc_html_e( 'Читати кейс', 'vite-starter' ); ?>
					</a>
				</div>

				<div class="cases-archive__viewport" data-cases-viewport>
					<div class="cases-archive__track" id="cases-archive-track" data-cases-track>
						<?php foreach ( $starter_cases as $starter_i => $starter_case ) : ?>
							<a
								class="cases-archive__cover<?php echo 0 === $starter_i ? ' is-active' : ''; ?>"
								href="<?php echo esc_url( $starter_case['url'] ); ?>"
								data-cases-cover="<?php echo esc_attr( (string) $starter_i ); ?>"
								aria-label="<?php echo esc_attr( $starter_case['title'] ); ?>"
							>
								<?php
								if ( $starter_case['cover_id'] ) {
									echo wp_get_attachment_image(
										$starter_case['cover_id'],
										'large',
										false,
										array(
											'class'   => 'cases-archive__image',
											'alt'     => '',
											'loading' => 0 === $starter_i ? 'eager' : 'lazy',
											'sizes'   => '(min-width: 1024px) 522px, 80vw',
										)
									);
								} else {
									$starter_placeholder = starter_get_case_placeholder_image();
									printf(
										'<img class="cases-archive__image cases-archive__image--placeholder" src="%1$s" width="%2$d" height="%3$d" alt="" loading="%4$s" decoding="async">',
										esc_url( $starter_placeholder['url'] ),
										(int) $starter_placeholder['width'],
										(int) $starter_placeholder['height'],
										esc_attr( 0 === $starter_i ? 'eager' : 'lazy' )
									);
								}
								?>
							</a>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
		<?php endif; ?>
	</div>

	<?php if ( $starter_count > 1 ) : ?>
		<?php // Slider mode: bottom zone hint, wheel down there scrolls to the footer (casesArchive.js); a button on touch screens. ?>
		<button type="button" class="cases-archive__footer-hint" data-cases-footer-hint aria-controls="contacts">
			<span><?php esc_html_e( 'До футера', 'vite-starter' ); ?></span>
			<span class="cases-archive__footer-arrow" aria-hidden="true">↓</span>
		</button>
	<?php endif; ?>
</main>
