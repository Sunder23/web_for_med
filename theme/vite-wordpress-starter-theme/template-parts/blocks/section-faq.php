<?php
/**
 * Theme file.
 *
 * Section: faq (Figma desktop 3534:2487, mobile 3589:7573).
 * Heading on the left, the accordion on the right (stacked on phones); the
 * accordion markup is the shared partials/parts/faq-list.php, so
 * components/faqAccordion.js drives it. Unlike the CPT FAQ there is no
 * "Не знайшли відповідь?" box.
 *
 * @package Vite_Starter
 */

if ( empty( $args ) || ! is_array( $args ) ) {
	return;
}

$section_title = isset( $args['title'] ) ? trim( (string) $args['title'] ) : '';
$items         = is_array( starter_get_array_value( $args, 'items', array() ) ) ? $args['items'] : array();
$has_question  = false;

foreach ( $items as $item ) {
	if ( is_array( $item ) && ! empty( $item['question'] ) ) {
		$has_question = true;
		break;
	}
}

if ( ! $has_question ) {
	// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional WARN-level diagnostic (minimal logging policy).
	error_log( 'WARN [section-faq] items are empty' );
	return;
}
?>
<section class="faq-block">
	<div class="faq-block__wrap container">
		<div class="faq-block__inner frame">
			<?php if ( '' !== $section_title ) : ?>
				<div class="faq-block__aside">
					<h2 class="section-title faq-block__title"><?php echo esc_html( $section_title ); ?></h2>
				</div>
			<?php endif; ?>
			<?php
			get_template_part(
				'partials/parts/faq-list',
				null,
				array(
					'items'      => $items,
					'id_prefix'  => wp_unique_id( 'faq-block-' ) . '-answer-',
					'open_first' => (bool) starter_get_array_value( $args, 'open_first', true ),
					'class'      => 'faq--block faq-block__list',
				)
			);
			?>
		</div>
	</div>
</section>
