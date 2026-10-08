<?php
/**
 * FAQ accordion under article singles (CPT FAQ ACF groups).
 * Behavior lives in assets/src/js/components/faqAccordion.js (data-faq).
 *
 * @param array $args {
 *     @type string $title Optional heading.
 *     @type array  $items Rows of [question, answer].
 * }
 *
 * @package Vite_Starter
 */

$part_title = ! empty( $args['title'] ) ? $args['title'] : __( 'Часті запитання', 'vite-starter' );
$items      = ! empty( $args['items'] ) && is_array( $args['items'] ) ? $args['items'] : array();

if ( empty( $items ) ) {
	return;
}
?>
<section class="faq-section">
	<div class="container faq-section__container">
		<div class="frame faq-section__frame">
			<h2 class="faq-section__title section-title"><?php echo esc_html( $part_title ); ?></h2>
			<div class="faq" data-faq>
				<?php foreach ( $items as $key => $item ) : ?>
					<?php
					if ( empty( $item['question'] ) ) {
						continue;
					}
					?>
					<div class="faq__item">
						<button class="faq__question" type="button" aria-expanded="false" aria-controls="faq-answer-<?php echo esc_attr( (string) $key ); ?>">
							<span class="faq__question-text"><?php echo esc_html( $item['question'] ); ?></span>
							<span class="faq__icon" aria-hidden="true"></span>
						</button>
						<div class="faq__answer" id="faq-answer-<?php echo esc_attr( (string) $key ); ?>">
							<div class="faq__answer-inner entry-content"><?php echo wp_kses_post( $item['answer'] ?? '' ); ?></div>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</section>
