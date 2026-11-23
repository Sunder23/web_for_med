<?php
/**
 * Blog listing (Figma 3446:656 desktop, 3493:1082 mobile) shared by the posts page
 * (home.php) and post archives (archive.php: categories, tags, dates, authors):
 * hero «section owner» with the /*blog*\/ label and H1, a grid of blog cards over the
 * main query (6 per page, configure/theme-hooks/blog-query.php) and «Показати ще».
 *
 * «Показати ще» is a plain link to the next page, so it works without JS;
 * assets/src/js/components/blogLoadMore.js appends the next page's cards in place.
 *
 * @param array $args {
 *     @type string $title       Page heading.
 *     @type string $description Optional intro under the heading (term description, HTML allowed).
 * }
 *
 * @package Vite_Starter
 */

$part_title  = ! empty( $args['title'] ) ? $args['title'] : __( 'Блог', 'vite-starter' );
$description = ! empty( $args['description'] ) ? $args['description'] : '';
$current     = max( 1, (int) get_query_var( 'paged' ) );
$has_next    = $current < (int) $GLOBALS['wp_query']->max_num_pages;
$next_url    = $has_next ? get_pagenum_link( $current + 1 ) : '';
?>
<main class="blog-archive">
	<section class="blog-archive__hero">
		<div class="container">
			<div class="frame blog-archive__hero-inner">
				<span class="blog-archive__label">/*blog*/</span>
				<div class="blog-archive__heading">
					<h1 class="blog-archive__title"><?php echo esc_html( $part_title ); ?></h1>
					<?php if ( $description ) : ?>
						<div class="blog-archive__description"><?php echo wp_kses_post( $description ); ?></div>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</section>

	<section class="blog-archive__list">
		<div class="container blog-archive__container">
			<?php if ( have_posts() ) : ?>
				<div class="blog-archive__grid" data-blog-grid>
					<?php
					while ( have_posts() ) :
						the_post();
						get_template_part(
							'partials/parts/blog-card',
							null,
							array(
								'class'     => 'blog-archive__item',
								'title_tag' => 'h2',
							)
						);
					endwhile;
					?>
				</div>
				<?php if ( $has_next ) : ?>
					<div class="blog-archive__more-row">
						<a class="button button--secondary blog-archive__more" href="<?php echo esc_url( $next_url ); ?>" data-blog-more>
							<?php esc_html_e( 'Показати ще', 'vite-starter' ); ?>
						</a>
					</div>
				<?php endif; ?>
			<?php else : ?>
				<p class="blog-archive__empty"><?php esc_html_e( 'Записів поки немає.', 'vite-starter' ); ?></p>
			<?php endif; ?>
		</div>
	</section>
</main>
