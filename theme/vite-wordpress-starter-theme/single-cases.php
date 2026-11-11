<?php
/**
 * Single case: utility bar, intro + project details + content, prev/next strip
 * (partials/parts/case-single.php, case-nav.php).
 *
 * @package Vite_Starter
 */

get_header();
?>

<main class="case-single">
	<?php
	while ( have_posts() ) :
		the_post();

		get_template_part( 'partials/parts/case-single' );
		get_template_part( 'partials/parts/case-nav' );

	endwhile;
	?>
</main>
<?php
get_footer();
