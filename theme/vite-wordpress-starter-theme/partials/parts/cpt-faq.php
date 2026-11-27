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
			<div class="faq-section__aside">
				<h2 class="faq-section__title section-title"><?php echo esc_html( $part_title ); ?></h2>
				<div class="faq-section__ask">
					<p class="faq-section__ask-title"><?php esc_html_e( 'Не знайшли відповідь?', 'vite-starter' ); ?></p>
					<p class="faq-section__ask-text"><?php esc_html_e( 'Опишіть задачу вашого медзакладу — відповімо по суті й запропонуємо рішення.', 'vite-starter' ); ?></p>
					<a class="button button--primary faq-section__ask-button" href="#contact-form" data-contact-open><?php esc_html_e( 'Поставити запитання', 'vite-starter' ); ?></a>
				</div>
			</div>
			<?php
			get_template_part(
				'partials/parts/faq-list',
				null,
				array(
					'items' => $items,
					'class' => 'faq-section__list',
				)
			);
			?>
		</div>
	</div>
</section>
