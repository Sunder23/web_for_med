<?php
/**
 * Single case body (Figma 3061:891 desktop, 3124:1383 mobile): utility bar with the
 * /*FILE*\/ label and the "Section: NN/MM" counter, then the intro (title, lead, image),
 * the "project metadata" sidebar and the block-editor content.
 *
 * One sidebar in the DOM: the grid areas put it between the intro and the content on
 * mobile and into a sticky left column from `tablet`. Uses the current loop post.
 *
 * @package Vite_Starter
 */

$case_data = starter_get_case_single_data( get_post() );

if ( ! $case_data ) {
	return;
}

$case_total = (int) $case_data['sections_total'];
?>
<div class="case-single__bar">
	<div class="case-single__bar-inner">
		<span class="case-single__label">/*FILE: <?php echo esc_html( $case_data['client'] ); ?> */</span>
		<?php if ( $case_total > 1 ) : ?>
			<p class="case-single__counter" data-case-section-counter>
				<?php esc_html_e( 'Section', 'vite-starter' ); ?>: <span data-case-section-current>01</span>/<span data-case-section-total><?php echo esc_html( str_pad( (string) $case_total, 2, '0', STR_PAD_LEFT ) ); ?></span>
			</p>
		<?php endif; ?>
	</div>
</div>

<div class="container case-single__container">
	<div class="frame case-single__grid">
		<header class="case-single__intro">
			<?php the_title( '<h1 class="case-single__title">', '</h1>' ); ?>

			<?php if ( '' !== $case_data['intro_html'] ) : ?>
				<div class="case-single__lead"><?php echo wp_kses_post( $case_data['intro_html'] ); ?></div>
			<?php endif; ?>

			<?php if ( $case_data['image_id'] ) : ?>
				<figure class="case-single__image">
					<?php
					echo wp_get_attachment_image(
						$case_data['image_id'],
						'full',
						false,
						array(
							'class'         => 'case-single__img',
							'loading'       => 'eager',
							'fetchpriority' => 'high',
							'sizes'         => '(min-width: 1024px) 880px, 100vw',
						)
					);
					?>
				</figure>
			<?php endif; ?>
		</header>

		<?php if ( $case_data['details'] ) : ?>
			<aside class="case-single__meta" aria-label="<?php esc_attr_e( 'Деталі проєкту', 'vite-starter' ); ?>">
				<dl class="case-single__details">
					<?php foreach ( $case_data['details'] as $case_detail ) : ?>
						<div class="case-single__detail case-single__detail--<?php echo esc_attr( sanitize_html_class( $case_detail['key'] ) ); ?>">
							<dt class="case-single__detail-label"><?php echo esc_html( $case_detail['label'] ); ?></dt>
							<dd class="case-single__detail-value">
								<?php if ( '' !== $case_detail['url'] ) : ?>
									<a class="case-single__detail-link" href="<?php echo esc_url( $case_detail['url'] ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $case_detail['value'] ); ?></a>
								<?php else : ?>
									<?php echo esc_html( $case_detail['value'] ); ?>
								<?php endif; ?>
							</dd>
						</div>
					<?php endforeach; ?>
				</dl>
			</aside>
		<?php endif; ?>

		<div class="entry-content case-single__content">
			<?php the_content(); ?>
		</div>
	</div>
</div>
