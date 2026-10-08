<?php
/**
 * Blog listing layout shared by the posts page (home.php) and post archives
 * (archive.php: categories, tags, dates): hero + category nav + card grid over the main query.
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
?>
<main class="archive-cpt blog-archive">
	<section class="s-cpt-hero">
		<div class="s-cpt-hero__wrap container">
			<div class="s-cpt-hero__inner frame">
				<?php get_template_part( 'partials/breadcrumbs' ); ?>
				<h1 class="s-cpt-hero__title"><?php echo esc_html( $part_title ); ?></h1>
				<?php if ( $description ) : ?>
					<div class="blog-archive__description"><?php echo wp_kses_post( $description ); ?></div>
				<?php endif; ?>
				<?php get_template_part( 'partials/parts/category-nav' ); ?>
			</div>
		</div>
	</section>

	<section class="s-archive">
		<div class="s-archive__wrap container">
			<?php if ( have_posts() ) : ?>
				<div class="archive-grid frame">
					<?php
					while ( have_posts() ) :
						the_post();
						get_template_part(
							'partials/parts/post-card',
							null,
							array(
								'class'     => 'archive-grid__item',
								'title_tag' => 'h2',
								'show_date' => true,
							)
						);
					endwhile;
					?>
				</div>
				<?php the_posts_pagination(); ?>
			<?php else : ?>
				<p class="archive-empty frame"><?php esc_html_e( 'Записів поки немає.', 'vite-starter' ); ?></p>
			<?php endif; ?>
		</div>
	</section>
</main>
