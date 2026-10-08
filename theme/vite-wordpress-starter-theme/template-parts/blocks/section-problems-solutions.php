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
<div class="problems-solutions">
	<section class="problems">
		<div class="problems__wrap container">
			<div class="problems__inner">
				<?php foreach ( $problems as $item ) : ?>
					<div class="problem-card info-card">
						<span class="tag problem-card__tag"><?php echo esc_html( $item['tag'] ); ?></span>
						<div class="problem-card__body info-card__body">
							<h3 class="problem-card__title card-title"><?php echo esc_html( $item['title'] ); ?></h3>
							<p class="problem-card__text card-text"><?php echo esc_html( $item['text'] ); ?></p>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<div class="problems-solutions__inner">
		<section class="banner">
			<div class="banner__inner container">
				<h2 class="banner__text"><?php echo esc_html( $banner_text ); ?></h2>
			</div>
		</section>

		<section class="solutions" id="about">
			<div class="solutions__wrap container">
				<div class="solutions__inner">
					<?php foreach ( $solutions as $item ) : ?>
						<div class="solution-card info-card">
							<?php if ( $item['image'] ) : ?>
								<div class="solution-card__media">
									<?php echo wp_get_attachment_image( $item['image'], array( 200, 308 ), false, array( 'class' => 'solution-card__image' ) ); ?>
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
