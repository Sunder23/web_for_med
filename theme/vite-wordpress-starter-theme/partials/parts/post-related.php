<?php
/**
 * «Актуальні теми» under a blog post (Figma 3481:1404 desktop, 3493:839 mobile): heading
 * with prev/next arrows and a Swiper row of compact blog cards (postRelatedSlider.js).
 *
 * Up to 8 latest posts of the same category; when it gives fewer than 2, the row is
 * topped up with the latest posts of any category. Outputs nothing without other posts.
 *
 * @param array $args {
 *     @type int    $exclude     Post ID to leave out (the current post).
 *     @type int    $category_id Category to prefer.
 *     @type string $title       Section heading.
 * }
 *
 * @package Vite_Starter
 */

$args        = isset( $args ) && is_array( $args ) ? $args : array();
$exclude     = ! empty( $args['exclude'] ) ? (int) $args['exclude'] : 0;
$category_id = ! empty( $args['category_id'] ) ? (int) $args['category_id'] : 0;
$part_title  = ! empty( $args['title'] ) ? $args['title'] : __( 'Актуальні теми', 'vite-starter' );
$limit       = 8;

$base_query = array(
	'post_type'           => 'post',
	'post_status'         => 'publish',
	'posts_per_page'      => $limit,
	'post__not_in'        => array( $exclude ),
	'ignore_sticky_posts' => true,
	'no_found_rows'       => true,
	'fields'              => 'ids',
);

$related_ids = $category_id
	? get_posts( array_merge( $base_query, array( 'cat' => $category_id ) ) )
	: array();

if ( count( $related_ids ) < 2 ) {
	$fill_ids    = get_posts(
		array_merge(
			$base_query,
			array(
				'post__not_in'   => array_merge( array( $exclude ), $related_ids ),
				'posts_per_page' => $limit - count( $related_ids ),
			)
		)
	);
	$related_ids = array_merge( $related_ids, $fill_ids );
}

if ( ! $related_ids ) {
	return;
}

$related_query = new WP_Query(
	array(
		'post_type'           => 'post',
		'post__in'            => $related_ids,
		'orderby'             => 'post__in',
		'posts_per_page'      => count( $related_ids ),
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	)
);

$arrow_url = starter_get_static_dir() . '/images/icons/';
?>
<section class="post-related" aria-labelledby="post-related-title">
	<div class="container post-related__container">
		<div class="frame post-related__head">
			<h2 class="post-related__title" id="post-related-title"><?php echo esc_html( $part_title ); ?></h2>
			<div class="post-related__nav">
				<button class="post-related__arrow post-related__arrow--prev" type="button" aria-label="<?php esc_attr_e( 'Попередні статті', 'vite-starter' ); ?>" data-related-prev>
					<img src="<?php echo esc_url( $arrow_url . 'arrow-prev.svg' ); ?>" width="14" height="24" alt="">
				</button>
				<button class="post-related__arrow post-related__arrow--next" type="button" aria-label="<?php esc_attr_e( 'Наступні статті', 'vite-starter' ); ?>" data-related-next>
					<img src="<?php echo esc_url( $arrow_url . 'arrow-next.svg' ); ?>" width="14" height="24" alt="">
				</button>
			</div>
		</div>
		<div class="frame post-related__body">
			<div class="post-related__slider swiper" data-related-slider>
				<div class="swiper-wrapper">
					<?php
					while ( $related_query->have_posts() ) :
						$related_query->the_post();
						?>
						<div class="swiper-slide post-related__slide">
							<?php
							get_template_part(
								'partials/parts/blog-card',
								null,
								array(
									'variant'   => 'compact',
									'title_tag' => 'h3',
								)
							);
							?>
						</div>
						<?php
					endwhile;
					wp_reset_postdata();
					?>
				</div>
			</div>
		</div>
	</div>
</section>
