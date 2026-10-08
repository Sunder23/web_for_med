<?php
/**
 * Single blog post: shared article layout + related posts and CTA.
 *
 * @package Vite_Starter
 */

get_header();
?>

<main class="single-cpt single-post-page">
	<?php
	while ( have_posts() ) :
		the_post();

		$categories = get_the_category();
		$category   = ! empty( $categories ) ? $categories[0] : null;
		$meta       = array();

		if ( $category ) {
			$meta[] = '<a class="tag" href="' . esc_url( get_category_link( $category ) ) . '">' . esc_html( $category->name ) . '</a>';
		}
		$meta[] = '<time datetime="' . esc_attr( get_the_date( 'c' ) ) . '">' . esc_html( get_the_date( 'd.m.Y' ) ) . '</time>';

		get_template_part(
			'partials/parts/article-layout',
			null,
			array(
				'lead' => has_excerpt() ? get_the_excerpt() : '',
				'meta' => $meta,
			)
		);

		get_template_part(
			'partials/parts/related-posts',
			null,
			array(
				'post_type' => 'post',
				'exclude'   => get_the_ID(),
				'title'     => __( 'Схожі матеріали', 'vite-starter' ),
				'tax_query' => $category ? array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- 3 posts, no pagination.
					array(
						'taxonomy' => 'category',
						'field'    => 'term_id',
						'terms'    => $category->term_id,
					),
				) : array(),
			)
		);

		$header_button = starter_get_option( 'header_button' );

		get_template_part(
			'partials/parts/cpt-cta',
			null,
			array(
				'title'        => __( 'Обговорімо ваш медзаклад', 'vite-starter' ),
				'text'         => __( 'Розкажіть про задачі клініки — запропонуємо рішення для сайту, SEO чи реклами.', 'vite-starter' ),
				'button_label' => __( 'Обговорити задачі', 'vite-starter' ),
				'button_url'   => ! empty( $header_button['url'] ) ? $header_button['url'] : '#contacts',
			)
		);
	endwhile;
	?>
</main>
<?php
get_footer();
