<?php
/**
 * Blog post card (Figma `Article 1 basic` 3446:799, `Article 1 simalar` 3481:1342):
 * cover, title (max two lines), author line. Uses the current loop post.
 *
 * The title link is stretched over the whole card (`::after`); the author link sits
 * above it, so both stay clickable. CPT cards keep using post-card.php.
 *
 * @param array $args {
 *     @type string $class     Optional extra classes (mix with the parent element).
 *     @type string $title_tag h2 or h3. Default h2.
 *     @type string $variant   default | compact («Актуальні теми»: 361×220 cover, full title on phones).
 *     @type string $sizes     Optional `sizes` for the cover.
 * }
 *
 * @package Vite_Starter
 */

$args        = isset( $args ) && is_array( $args ) ? $args : array();
$extra_class = ! empty( $args['class'] ) ? ' ' . $args['class'] : '';
$title_tag   = isset( $args['title_tag'] ) && 'h3' === $args['title_tag'] ? 'h3' : 'h2';
$variant     = isset( $args['variant'] ) && 'compact' === $args['variant'] ? 'compact' : 'default';
$cover_sizes = ! empty( $args['sizes'] )
	? $args['sizes']
	: ( 'compact' === $variant ? '(min-width: 1024px) 361px, 100vw' : '(min-width: 1024px) 411px, (min-width: 766px) 50vw, 100vw' );
$card_class  = 'blog-card blog-card--' . $variant . $extra_class;
$permalink   = get_permalink();
?>
<article class="<?php echo esc_attr( $card_class ); ?>">
	<div class="blog-card__cover">
		<?php
		if ( has_post_thumbnail() ) {
			the_post_thumbnail(
				'blog-card',
				array(
					'class'   => 'blog-card__img',
					'alt'     => '',
					'loading' => 'lazy',
					'sizes'   => $cover_sizes,
				)
			);
		} else {
			$placeholder = starter_get_case_placeholder_image();
			?>
			<img
				class="blog-card__img blog-card__img--placeholder"
				src="<?php echo esc_url( $placeholder['url'] ); ?>"
				width="<?php echo esc_attr( (string) $placeholder['width'] ); ?>"
				height="<?php echo esc_attr( (string) $placeholder['height'] ); ?>"
				alt=""
				loading="lazy"
				decoding="async"
			>
		<?php } ?>
	</div>
	<div class="blog-card__body">
		<<?php echo esc_html( $title_tag ); ?> class="blog-card__title">
			<a class="blog-card__link" href="<?php echo esc_url( $permalink ); ?>"><?php the_title(); ?></a>
		</<?php echo esc_html( $title_tag ); ?>>
		<?php
		get_template_part(
			'partials/parts/post-author',
			null,
			array(
				'post'  => get_post(),
				'class' => 'blog-card__meta' . ( 'compact' === $variant ? ' post-author--small' : '' ),
			)
		);
		?>
	</div>
</article>
