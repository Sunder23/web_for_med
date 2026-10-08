<?php
/**
 * Render template for the acf/info-block block.
 *
 * @package Vite_Starter
 *
 * @var array  $block      Block settings and attributes.
 * @var string $content    Block inner HTML (empty for ACF blocks).
 * @var bool   $is_preview True during editor preview render.
 * @var int    $post_id    ID of the post the block is rendered on.
 */

$info_title = get_field( 'title' );
$info_text  = get_field( 'text' );

$anchor = '';
if ( ! empty( $block['anchor'] ) ) {
	$anchor = ' id="' . esc_attr( $block['anchor'] ) . '"';
}

$class_name = 'info-block';
if ( ! empty( $block['className'] ) ) {
	$class_name .= ' ' . $block['className'];
}

if ( empty( $info_title ) && empty( $info_text ) ) {
	// Nothing to render on the frontend; show a hint in the editor only.
	if ( ! empty( $is_preview ) ) {
		echo '<div class="' . esc_attr( $class_name ) . '"><p class="info-block__placeholder">'
			. esc_html__( 'Інфо-блок: заповніть заголовок і текст у бічній панелі.', 'vite-starter' )
			. '</p></div>';
	}
	return;
}
?>
<div<?php echo $anchor; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Attribute is built with esc_attr() above. ?> class="<?php echo esc_attr( $class_name ); ?>">
	<?php if ( ! empty( $info_title ) ) : ?>
		<h3 class="info-block__title"><?php echo esc_html( $info_title ); ?></h3>
	<?php endif; ?>
	<?php if ( ! empty( $info_text ) ) : ?>
		<div class="info-block__text"><?php echo wp_kses_post( $info_text ); ?></div>
	<?php endif; ?>
</div>
