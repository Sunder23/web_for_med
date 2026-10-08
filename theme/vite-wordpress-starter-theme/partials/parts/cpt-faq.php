<?php
/**
 * Shared FAQ accordion section for CPT single pages.
 * Extracted from single-services.php; behavior lives in
 * assets/src/js/components/faqAccordion.js (data-faq).
 *
 * @param array $args {
 *     @type string $title Optional heading.
 *     @type array  $items Rows of [question, answer].
 * }
 */

$title = ! empty( $args['title'] ) ? $args['title'] : '';
$items = ! empty( $args['items'] ) ? $args['items'] : array();

if ( empty( $items ) ) {
	return;
}
?>
<section class="s-svc s-svc--faq">
	<div class="s-svc__wrap l-wrap">
		<div class="s-svc__inner l-frame-x">
			<?php if ( $title ) : ?>
				<h2 class="s-svc__title section-title"><?php echo esc_html( $title ); ?></h2>
			<?php endif; ?>
			<div class="faq" data-faq>
				<?php foreach ( $items as $key => $item ) : ?>
					<div class="faq__item">
						<button class="faq__question" type="button" aria-expanded="false" aria-controls="faq-answer-<?php echo esc_attr( (string) $key ); ?>">
							<span><?php echo esc_html( $item['question'] ); ?></span>
							<svg class="faq__chevron" width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
								<path d="M2 5.5L8 11.5L14 5.5" stroke="currentColor" stroke-width="2" />
							</svg>
						</button>
						<div class="faq__answer" id="faq-answer-<?php echo esc_attr( (string) $key ); ?>">
							<div class="faq__answer-inner svc-prose"><?php echo wp_kses_post( $item['answer'] ); ?></div>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</section>
