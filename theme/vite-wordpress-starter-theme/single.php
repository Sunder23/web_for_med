<?php
get_header();
?>

<main class="single-cpt single-post-page">
	<?php while ( have_posts() ) : the_post(); ?>
		<?php
		$categories = get_the_category();
		$category   = ! empty( $categories ) ? $categories[0] : null;

		if ( WP_DEBUG ) {
			error_log( '[W4M single] category=' . ( $category ? $category->name : 'none' ) . ' post=' . get_the_ID() );
		}
		?>
		<section class="s-cpt-hero">
			<div class="s-cpt-hero__wrap l-wrap">
				<div class="s-cpt-hero__inner l-frame-x">
					<?php custom_theme_breadcrumbs(); ?>
					<div class="post-meta-row">
						<?php if ( $category ) : ?>
							<span class="tag"><?php echo esc_html( $category->name ); ?></span>
						<?php endif; ?>
						<span class="post-meta"><?php echo esc_html( get_the_date() ); ?></span>
					</div>
					<h1 class="s-cpt-hero__title"><?php the_title(); ?></h1>
				</div>
			</div>
		</section>

		<section class="s-post">
			<div class="s-post__wrap l-wrap">
				<div class="s-post__inner l-frame-x">
					<div class="post-content entry-content svc-prose"><?php the_content(); ?></div>
				</div>
			</div>
		</section>

		<?php get_template_part( 'template-parts/cpt-cta', null, (array) get_field( 'post_cta' ) ); ?>
	<?php endwhile; ?>
</main>
<?php get_footer();
