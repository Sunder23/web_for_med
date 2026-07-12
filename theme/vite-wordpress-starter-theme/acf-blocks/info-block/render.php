<?php

/**
 * acf/info-block render template.
 *
 * @var array  $block      Block settings and attributes.
 * @var string $content    Block inner HTML (empty for ACF blocks).
 * @var bool   $is_preview True during editor preview render.
 * @var int    $post_id    ID of the post the block is rendered on.
 */

$title = get_field('title');
$text  = get_field('text');

$anchor = '';
if (! empty($block['anchor'])) {
    $anchor = ' id="' . esc_attr($block['anchor']) . '"';
}

$class_name = 'info-block';
if (! empty($block['className'])) {
    $class_name .= ' ' . $block['className'];
}

if (empty($title) && empty($text)) {
    // nothing to render on the frontend; show a hint in the editor only
    if (! empty($is_preview)) {
        echo '<div class="' . esc_attr($class_name) . '"><p class="info-block__placeholder">'
            . esc_html__('Інфо-блок: заповніть заголовок і текст у бічній панелі.', 'textdomaintomodify')
            . '</p></div>';
    }
    return;
}
?>
<div<?php echo $anchor; ?> class="<?php echo esc_attr($class_name); ?>">
	<?php if (! empty($title)) : ?>
		<h3 class="info-block__title"><?php echo esc_html($title); ?></h3>
	<?php endif; ?>
	<?php if (! empty($text)) : ?>
		<div class="info-block__text"><?php echo wp_kses_post($text); ?></div>
	<?php endif; ?>
</div>
