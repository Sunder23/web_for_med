<?php
/**
 * Theme file.
 *
 * Pages are built from acf/section-* blocks (configure/section-blocks.php);
 * each block renders its template-parts/blocks/section-{slug}.php inside
 * the_content(). Pages without blocks fall back to title + classic content.
 *
 * @package Vite_Starter
 */

get_header();
?>

<main id="main" class="site-main">
	<?php
	while ( have_posts() ) :
		the_post();

		if ( has_blocks() ) :
			the_content();
		else :
			the_title( '<h1>', '</h1>' );
			the_content();
		endif;
	endwhile;
	?>
</main><!-- #main -->

<?php
get_footer();
