<?php
/**
 * Theme file.
 *
 * Section: cases.
 *
 * @package Vite_Starter
 */

if ( empty( $args ) || ! is_array( $args ) ) {
	return;
}

// Real cases: the ones picked in the block, or all published ones (newest first).
$case_ids = ! empty( $args['cases'] ) && is_array( $args['cases'] ) ? array_map( 'absint', $args['cases'] ) : array();
if ( ! $case_ids ) {
	$case_ids = get_posts(
		array(
			'post_type'      => 'cases',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'no_found_rows'  => true,
		)
	);
}

// Card text: "Що зроблено" (case_done), falling back to the hero subtitle.
$items = array();
foreach ( $case_ids as $case_id ) {
	if ( 'publish' !== get_post_status( $case_id ) ) {
		continue;
	}
	$case_text = (string) get_field( 'case_done', $case_id );
	if ( '' === trim( $case_text ) ) {
		$case_hero = (array) get_field( 'case_hero', $case_id );
		$case_text = isset( $case_hero['subtitle'] ) ? (string) $case_hero['subtitle'] : '';
	}
	$items[] = array(
		'title' => get_the_title( $case_id ),
		'url'   => get_permalink( $case_id ),
		'text'  => $case_text,
	);
}

if ( ! $items ) {
	return;
}
?>
<section class="cases" id="cases">
	<div class="cases__wrap container">
		<div class="cases__header frame">
			<h2 class="section-title cases__title"><?php echo esc_html( $args['title'] ); ?></h2>
			<div class="cases__nav">
				<button class="cases__button" id="casePrev" aria-label="<?php esc_attr_e( 'Стрілка вліво', 'vite-starter' ); ?>">
					<svg width="31" height="33" viewBox="0 0 31 33" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M13.6757 16.5L23 6.91667L20.1622 4L8 16.5L20.1622 29L23 26.0833L13.6757 16.5Z" fill="#AEBBC4" />
					</svg>
				</button>
				<button class="cases__button" id="caseNext" aria-label="<?php esc_attr_e( 'Стрілка вправо', 'vite-starter' ); ?>">
					<svg width="31" height="33" viewBox="0 0 31 33" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M17.3243 16.5L8 6.91667L10.8378 4L23 16.5L10.8378 29L8 26.0833L17.3243 16.5Z" fill="#AEBBC4" />
					</svg>
				</button>
			</div>
		</div>
		<div class="cases-slider swiper" id="casesSlider">
			<?php
			$image_url = wp_get_attachment_image_url( $args['bg_image'], 'full' );
			echo wp_get_attachment_image( $args['bg_image'], 'full', '', array( 'class' => 'cases-slider__background' ) );
			?>
			<div class="cases-slider__track swiper-wrapper">

				<?php
				foreach ( $items as $key => $item ) :
					switch ( $key % 3 ) {
						case 0:
							$position = 'left center';
							break;

						case 1:
							$position = 'center center';
							break;

						case 2:
							$position = 'right center';
							break;
					}
					?>
					<div class="cases-slide swiper-slide">
						<a class="cases-slide__inner" href="<?php echo esc_url( $item['url'] ); ?>">
							<div class="cases-slide__desc">
								<h3 class="cases-slide__title"><?php echo esc_html( $item['title'] ); ?></h3>
							</div>
							<div class="cases-slide__visual" style="
							--case-bg-mobile: url('<?php echo esc_url( $image_url ); ?>');
							--case-bg-position: <?php echo esc_attr( $position ); ?>; ">
								<?php if ( $item['text'] ) : ?>
									<div class="cases-slide__card">
										<p class="cases-slide__text"><?php echo esc_html( $item['text'] ); ?></p>
									</div>
								<?php endif; ?>
							</div>
						</a>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<div class="cases__dots" id="casesDots"></div>
	</div>
</section>
