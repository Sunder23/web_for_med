<?php
/**
 * Post card for archives, the blog home and related posts. Uses the current loop post.
 *
 * @param array $args {
 *     @type string $class     Optional extra classes (mix with the parent grid element).
 *     @type bool   $show_date Whether to print the publish date. Default false.
 *     @type string $title_tag Card title tag: h2 or h3. Default h3.
 * }
 *
 * @package Vite_Starter
 */

// Per-CPT source of the card excerpt: [ group field, sub field ]; blog posts use the post excerpt.
$excerpt_map = array(
	'services'   => array( 'service_hero', 'subtitle' ),
	'directions' => array( 'direction_hero', 'description' ),
	'cases'      => array( 'case_hero', 'subtitle' ),
);

$extra_class    = ! empty( $args['class'] ) ? ' ' . $args['class'] : '';
$show_date      = ! empty( $args['show_date'] );
$title_tag      = isset( $args['title_tag'] ) && 'h2' === $args['title_tag'] ? 'h2' : 'h3';
$card_post_type = get_post_type();

if ( isset( $excerpt_map[ $card_post_type ] ) ) {
	$group   = get_field( $excerpt_map[ $card_post_type ][0] );
	$excerpt = ! empty( $group[ $excerpt_map[ $card_post_type ][1] ] ) ? $group[ $excerpt_map[ $card_post_type ][1] ] : '';
} else {
	$excerpt = get_the_excerpt();
}
?>
<a class="post-card<?php echo esc_attr( $extra_class ); ?>" href="<?php the_permalink(); ?>">
	<div class="post-card__body">
		<?php if ( $show_date ) : ?>
			<time class="post-card__date tag" datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date( 'd.m.Y' ) ); ?></time>
		<?php endif; ?>
		<?php the_title( '<' . $title_tag . ' class="post-card__title card-title">', '</' . $title_tag . '>' ); ?>
		<?php if ( $excerpt ) : ?>
			<p class="post-card__text card-text"><?php echo esc_html( wp_trim_words( $excerpt, 32 ) ); ?></p>
		<?php endif; ?>
	</div>
	<span class="post-card__more tag"><?php echo esc_html( 'post' === $card_post_type ? __( 'Читати далі', 'vite-starter' ) : __( 'Детальніше', 'vite-starter' ) ); ?> &rarr;</span>
</a>
