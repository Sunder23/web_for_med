<?php
/**
 * Shared archive layout for the services / directions / cases CPTs:
 * simple hero + card grid over the main query.
 */

// Per-CPT source of the card excerpt: [ group field, sub field ].
$excerpt_map = array(
	'services'   => array( 'service_hero', 'subtitle' ),
	'directions' => array( 'direction_hero', 'description' ),
	'cases'      => array( 'case_hero', 'subtitle' ),
);
?>
<main class="archive-cpt">
	<section class="s-cpt-hero">
		<div class="s-cpt-hero__wrap l-wrap">
			<div class="s-cpt-hero__inner l-frame-x">
				<?php custom_theme_breadcrumbs(); ?>
				<h1 class="s-cpt-hero__title"><?php post_type_archive_title(); ?></h1>
			</div>
		</div>
	</section>

	<section class="s-archive">
		<div class="s-archive__wrap l-wrap">
			<?php if ( have_posts() ) : ?>
				<div class="archive-grid l-frame-x">
					<?php
					while ( have_posts() ) :
						the_post();
						$excerpt = '';
						$map     = isset( $excerpt_map[ get_post_type() ] ) ? $excerpt_map[ get_post_type() ] : null;
						if ( $map ) {
							$group   = get_field( $map[0] );
							$excerpt = ! empty( $group[ $map[1] ] ) ? $group[ $map[1] ] : '';
						}
					?>
						<a class="archive-card info-card" href="<?php the_permalink(); ?>">
							<div class="info-card__body">
								<h2 class="archive-card__title card-title"><?php the_title(); ?></h2>
								<?php if ( $excerpt ) : ?>
									<p class="archive-card__text card-text"><?php echo esc_html( wp_trim_words( $excerpt, 32 ) ); ?></p>
								<?php endif; ?>
							</div>
							<span class="archive-card__more tag c-tag"><?php esc_html_e( 'Детальніше', 'textdomaintomodify' ); ?> &rarr;</span>
						</a>
					<?php endwhile; ?>
				</div>
				<?php the_posts_pagination(); ?>
			<?php else : ?>
				<p class="archive-empty l-frame-x"><?php esc_html_e( 'Записів поки немає.', 'textdomaintomodify' ); ?></p>
			<?php endif; ?>
		</div>
	</section>
</main>
