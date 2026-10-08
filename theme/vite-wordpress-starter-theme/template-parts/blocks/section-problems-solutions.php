<?php
/**
 * Theme file.
 *
 * Section: problems-solutions.
 *
 * @package Vite_Starter
 */

if ( empty( $args ) || ! is_array( $args ) ) {
	return;
}
$problems    = isset( $args['problems'] ) && is_array( $args['problems'] ) ? $args['problems'] : array();
$solutions   = isset( $args['solutions'] ) && is_array( $args['solutions'] ) ? $args['solutions'] : array();
$banner_text = isset( $args['banner_text'] ) ? (string) $args['banner_text'] : '';
?>
<div class="sections_wrapper">
	<section class="s-problems">
		<div class="s-problems__wrap l-wrap">
			<div class="s-problems__inner">
				<?php foreach ( $problems as $item ) : ?>
					<div class="problem-card info-card">
						<span class="tag c-tag"><?php echo esc_html( $item['tag'] ); ?></span>
						<div class="problem-card__body info-card__body">
							<h3 class="problem-card__title card-title"><?php echo esc_html( $item['title'] ); ?></h3>
							<p class="problem-card__text card-text"><?php echo esc_html( $item['text'] ); ?></p>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<div class="sections_inner">
		<section class="s-banner">
			<div class="s-banner__inner">
				<h2 class="s-banner__text"><?php echo esc_html( $banner_text ); ?></h2>
			</div>
		</section>

		<section class="s-solutions" id="about">
			<div class="s-solutions__wrap l-wrap">
				<div class="s-solutions__inner">
					<?php foreach ( $solutions as $item ) : ?>
						<div class="solution-card info-card">
							<?php if ( $item['image'] ) : ?>
								<div class="solution-card-image">
									<?php echo wp_get_attachment_image( $item['image'], array( 200, 308 ) ); ?>
								</div>
							<?php endif; ?>
							<div class="solution-card__body info-card__body">
								<h3 class="solution-card__title card-title"><?php echo esc_html( $item['title'] ); ?></h3>
								<p class="solution-card__text card-text"><?php echo esc_html( $item['text'] ); ?></p>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	</div>
</div>
