<?php
/**
 * Shared archive layout for the services / directions / cases CPTs:
 * simple hero + card grid over the main query.
 *
 * @package Vite_Starter
 */

?>
<main class="archive-cpt">
	<section class="s-cpt-hero">
		<div class="s-cpt-hero__wrap container">
			<div class="s-cpt-hero__inner frame">
				<?php get_template_part( 'partials/breadcrumbs' ); ?>
				<h1 class="s-cpt-hero__title"><?php post_type_archive_title(); ?></h1>
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
