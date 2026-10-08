<?php
/**
 * "Related materials" row under article singles: up to 3 posts of the same type.
 * With a tax_query (blog category) and no matches, falls back to the latest posts.
 * Outputs nothing when there are no other posts.
 *
 * @param array $args {
 *     @type string $post_type Post type to query.
 *     @type int    $exclude   Post ID to leave out (the current post).
 *     @type string $title     Section heading.
 *     @type array  $tax_query Optional WP_Query tax_query.
 * }
 *
 * @package Vite_Starter
 */

if ( empty( $args ) || ! is_array( $args ) || empty( $args['post_type'] ) ) {
	return;
}

$query_args = array(
	'post_type'           => $args['post_type'],
	'post_status'         => 'publish',
	'posts_per_page'      => 3,
	'post__not_in'        => ! empty( $args['exclude'] ) ? array( (int) $args['exclude'] ) : array(),
	'no_found_rows'       => true,
	'ignore_sticky_posts' => true,
);

$related = new WP_Query( ! empty( $args['tax_query'] ) ? array_merge( $query_args, array( 'tax_query' => $args['tax_query'] ) ) : $query_args ); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- 3 posts, no pagination.

if ( ! $related->have_posts() && ! empty( $args['tax_query'] ) ) {
	$related = new WP_Query( $query_args );
}

if ( ! $related->have_posts() ) {
	return;
}

$part_title = ! empty( $args['title'] ) ? $args['title'] : __( 'Схожі матеріали', 'vite-starter' );

// "See all" link: the CPT archive, or the posts page for blog posts.
if ( 'post' === $args['post_type'] ) {
	$posts_page = (int) get_option( 'page_for_posts' );
	$all_url    = $posts_page ? get_permalink( $posts_page ) : '';
	$all_label  = __( 'Усі матеріали', 'vite-starter' );
} else {
	$all_url      = get_post_type_archive_link( $args['post_type'] );
	$related_type = get_post_type_object( $args['post_type'] );
	$all_label    = $related_type ? sprintf(
		/* translators: %s: post type plural name, lowercase. */
		__( 'Усі %s', 'vite-starter' ),
		mb_strtolower( $related_type->labels->name )
	) : '';
}
?>
<section class="related-posts">
	<div class="container related-posts__container">
		<div class="frame related-posts__frame">
			<div class="related-posts__head">
				<h2 class="related-posts__title section-title"><?php echo esc_html( $part_title ); ?></h2>
				<?php if ( $all_url && $all_label ) : ?>
					<a class="related-posts__all" href="<?php echo esc_url( $all_url ); ?>"><?php echo esc_html( $all_label ); ?> <span aria-hidden="true">&rarr;</span></a>
				<?php endif; ?>
			</div>
			<div class="related-posts__grid">
				<?php
				while ( $related->have_posts() ) :
					$related->the_post();
					get_template_part(
						'partials/parts/post-card',
						null,
						array(
							'class'     => 'related-posts__item',
							'show_date' => 'post' === $args['post_type'],
						)
					);
				endwhile;
				wp_reset_postdata();
				?>
			</div>
		</div>
	</div>
</section>
