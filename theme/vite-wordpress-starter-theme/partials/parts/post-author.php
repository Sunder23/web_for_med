<?php
/**
 * Post author line: square avatar 32×32, name linking to the author archive, publish date.
 * Used by the blog card and the single post intro.
 *
 * @param array $args {
 *     @type int|WP_Post $post  Post; the current post by default.
 *     @type string      $class Optional extra classes (mix with the parent element).
 *     @type int         $size  Avatar size in px. Default 32.
 * }
 *
 * @package Vite_Starter
 */

$args        = isset( $args ) && is_array( $args ) ? $args : array();
$author_post = get_post( $args['post'] ?? null );
if ( ! $author_post instanceof WP_Post ) {
	return;
}

$extra_class = ! empty( $args['class'] ) ? ' ' . $args['class'] : '';
$avatar_size = ! empty( $args['size'] ) ? (int) $args['size'] : 32;
$author      = starter_get_post_author( $author_post );
?>
<div class="post-author<?php echo esc_attr( $extra_class ); ?>">
	<?php if ( $author ) : ?>
		<a class="post-author__person" href="<?php echo esc_url( $author['url'] ); ?>">
			<?php
			if ( $author['avatar_id'] ) {
				echo wp_get_attachment_image(
					$author['avatar_id'],
					'thumbnail',
					false,
					array(
						'class'   => 'post-author__avatar',
						'alt'     => '',
						'width'   => $avatar_size,
						'height'  => $avatar_size,
						'loading' => 'lazy',
						'sizes'   => $avatar_size . 'px',
					)
				);
			} else {
				echo get_avatar(
					$author['id'],
					$avatar_size,
					'',
					'',
					array( 'class' => 'post-author__avatar' )
				);
			}
			?>
			<span class="post-author__name"><?php echo esc_html( $author['name'] ); ?></span>
		</a>
	<?php endif; ?>
	<time class="post-author__date" datetime="<?php echo esc_attr( get_the_date( 'c', $author_post ) ); ?>"><?php echo esc_html( get_the_date( 'd.m.Y', $author_post ) ); ?></time>
</div>
