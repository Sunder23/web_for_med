<?php
/**
 * Cases archive (/cases/): utility bar + looped case slider (Figma 3025:141, mobile 3166:3416).
 *
 * Text panels (head: client + title, details: description + link) and covers are
 * rendered for every case; casesArchive.js runs Swiper on the covers (viewport =
 * container, track = wrapper, cover = slide) and switches the active panels.
 * Without JS the covers form a horizontal scroll-snap lane.
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

$starter_count     = count( $starter_cases );
$starter_count_str = str_pad( (string) $starter_count, 2, '0', STR_PAD_LEFT );
$starter_static    = get_template_directory() . '/static/images/cases/';
$starter_arrow_svg = static function ( $name ) use ( $starter_static ) {
	$file = $starter_static . 'arrow-' . $name . '.svg';
	return file_exists( $file ) ? (string) file_get_contents( $file ) : ''; // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Local theme SVG.
};
$starter_svg_kses  = array(
	'svg'  => array(
		'width'       => true,
		'height'      => true,
		'viewbox'     => true,
		'fill'        => true,
		'xmlns'       => true,
		'aria-hidden' => true,
		'focusable'   => true,
	),
	'g'    => array( 'id' => true ),
	'path' => array(
		'id'   => true,
		'd'    => true,
		'fill' => true,
	),
);
?>
<main class="cases-archive" data-cases-archive data-count="<?php echo esc_attr( (string) $starter_count ); ?>">
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
					<?php foreach ( $starter_cases as $starter_i => $starter_case ) : ?>
						<div class="cases-archive__panel cases-archive__panel--head<?php echo 0 === $starter_i ? ' is-active' : ''; ?>" data-cases-panel="<?php echo esc_attr( (string) $starter_i ); ?>">
							<p class="cases-archive__caption"><?php esc_html_e( 'Client', 'vite-starter' ); ?></p>
							<h2 class="cases-archive__case-title"><?php echo esc_html( $starter_case['title'] ); ?></h2>
						</div>
					<?php endforeach; ?>
				</div>

				<div class="cases-archive__details">
					<?php foreach ( $starter_cases as $starter_i => $starter_case ) : ?>
						<div class="cases-archive__panel cases-archive__panel--details<?php echo 0 === $starter_i ? ' is-active' : ''; ?>" data-cases-panel="<?php echo esc_attr( (string) $starter_i ); ?>">
							<?php if ( $starter_case['description'] ) : ?>
								<p class="cases-archive__caption"><?php esc_html_e( 'Final product', 'vite-starter' ); ?></p>
								<p class="cases-archive__description"><?php echo esc_html( $starter_case['description'] ); ?></p>
							<?php endif; ?>
							<a class="button button--secondary cases-archive__link" href="<?php echo esc_url( $starter_case['url'] ); ?>">
								<?php esc_html_e( 'Читати кейс', 'vite-starter' ); ?>
							</a>
						</div>
					<?php endforeach; ?>
				</div>

				<?php if ( $starter_count > 1 ) : ?>
					<div class="cases-archive__arrows">
						<button type="button" class="cases-archive__arrow" data-cases-prev aria-controls="cases-archive-track" aria-label="<?php esc_attr_e( 'Попередній кейс', 'vite-starter' ); ?>">
							<?php echo wp_kses( $starter_arrow_svg( 'prev' ), $starter_svg_kses ); ?>
						</button>
						<button type="button" class="cases-archive__arrow" data-cases-next aria-controls="cases-archive-track" aria-label="<?php esc_attr_e( 'Наступний кейс', 'vite-starter' ); ?>">
							<?php echo wp_kses( $starter_arrow_svg( 'next' ), $starter_svg_kses ); ?>
						</button>
					</div>
				<?php endif; ?>

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
									echo '<span class="cases-archive__image cases-archive__image--placeholder" aria-hidden="true"></span>';
								}
								?>
							</a>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
		<?php endif; ?>
	</div>
</main>
