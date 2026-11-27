<?php
/**
 * FAQ accordion list (`.faq[data-faq]`), shared by partials/parts/cpt-faq.php and the
 * services page `faq` section. Behaviour: assets/src/js/components/faqAccordion.js.
 *
 * @param array $args {
 *     @type array  $items      Rows of [question, answer]; rows without a question are skipped.
 *     @type string $id_prefix  Prefix of the answer ids (unique per list). Default `faq-answer-`.
 *     @type bool   $open_first Render the first item open. Default false.
 *     @type string $class      Extra classes of the list (modifiers, mixes).
 * }
 *
 * @package Vite_Starter
 */

$faq_items      = ! empty( $args['items'] ) && is_array( $args['items'] ) ? $args['items'] : array();
$faq_id_prefix  = ! empty( $args['id_prefix'] ) ? (string) $args['id_prefix'] : 'faq-answer-';
$faq_open_first = ! empty( $args['open_first'] );
$faq_class      = ! empty( $args['class'] ) ? ' ' . (string) $args['class'] : '';
$faq_opened     = false;

if ( empty( $faq_items ) ) {
	return;
}
?>
<div class="faq<?php echo esc_attr( $faq_class ); ?>" data-faq>
	<?php foreach ( $faq_items as $key => $item ) : ?>
		<?php
		if ( empty( $item['question'] ) ) {
			continue;
		}
		$is_open    = $faq_open_first && ! $faq_opened;
		$faq_opened = true;
		?>
		<div class="faq__item<?php echo $is_open ? ' faq__item--open' : ''; ?>">
			<button class="faq__question" type="button" aria-expanded="<?php echo $is_open ? 'true' : 'false'; ?>" aria-controls="<?php echo esc_attr( $faq_id_prefix . $key ); ?>">
				<span class="faq__question-text"><?php echo esc_html( $item['question'] ); ?></span>
				<span class="faq__icon" aria-hidden="true"></span>
			</button>
			<div class="faq__answer" id="<?php echo esc_attr( $faq_id_prefix . $key ); ?>">
				<div class="faq__answer-inner entry-content"><?php echo wp_kses_post( $item['answer'] ?? '' ); ?></div>
			</div>
		</div>
	<?php endforeach; ?>
</div>
