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
$items = isset( $args['items'] ) && is_array( $args['items'] ) ? $args['items'] : array();
?>
<section class="s-cases" id="cases">
	<div class="s-cases__wrap l-wrap">
		<div class="s-cases__header l-frame-x">
			<h2 class="section-title"><?php echo esc_html($args['title']); ?></h2>
			<div class="s-cases__nav">
				<button class="s-cases__btn" id="casePrev" aria-label="<?php esc_attr_e('Стрілка вліво', 'vite-starter'); ?>">
					<svg width="31" height="33" viewBox="0 0 31 33" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M13.6757 16.5L23 6.91667L20.1622 4L8 16.5L20.1622 29L23 26.0833L13.6757 16.5Z" fill="#AEBBC4" />
					</svg>
				</button>
				<button class="s-cases__btn" id="caseNext" aria-label="<?php esc_attr_e('Стрілка вправо', 'vite-starter'); ?>">
					<svg width="31" height="33" viewBox="0 0 31 33" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M17.3243 16.5L8 6.91667L10.8378 4L23 16.5L10.8378 29L8 26.0833L17.3243 16.5Z" fill="#AEBBC4" />
					</svg>
				</button>
			</div>
		</div>
		<div class="cases-slider-wrap swiper" id="casesSlider">
			<?php
			$image_url  = wp_get_attachment_image_url($args['bg_image'], 'full');
			echo wp_get_attachment_image($args['bg_image'], 'full', '', ['class' => 'cases-slider-bg']); ?>
			<div class="cases-slider swiper-wrapper">

				<?php foreach ($items as $key => $item) :
					switch ($key % 3) {
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
						<div class="cases-slide-card">
							<div class="cases-slide__desc">
								<p><?php echo esc_html($item['description']); ?></p>
							</div>
							<div class="cases-slide__visual" style="
							--case-bg-mobile: url('<?php echo esc_url($image_url); ?>');
							--case-bg-position: <?php echo esc_attr($position); ?>; ">
								<div class="cases-slide__card">
									<p><?php echo esc_html($item['result']); ?></p>
								</div>
							</div>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<div class="cases-dots" id="casesDots"></div>
	</div>
</section>
