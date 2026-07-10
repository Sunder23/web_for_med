<?php
get_header();
?>

<main class="single-cpt single-post-page">
	<?php while ( have_posts() ) : the_post(); ?>
		<section class="s-cpt-hero">
			<div class="s-cpt-hero__wrap l-wrap">
				<div class="s-cpt-hero__inner l-frame-x">
					<?php custom_theme_breadcrumbs(); ?>
					<h1 class="s-cpt-hero__title"><?php the_title(); ?></h1>
					<span class="post-meta tag c-tag"><?php echo esc_html( get_the_date() ); ?></span>
				</div>
			</div>
		</section>

		<section class="s-post">
			<div class="s-post__wrap l-wrap">
				<div class="s-post__inner l-frame-x">
					<article class="post-content svc-prose">
						<?php the_content(); ?>
					</article>
				</div>
			</div>
		</section>
	<?php endwhile; ?>
</main>
<?php get_footer();
