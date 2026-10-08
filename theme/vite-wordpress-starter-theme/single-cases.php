<?php
/**
 * Theme file.
 *
 * @package Vite_Starter
 */

get_header();

$hero = get_field( 'case_hero' );
$faq  = get_field( 'case_faq' );
$cta  = get_field( 'case_cta' );
?>

<main class="single-cpt single-case">
	<section class="s-cpt-hero">
		<div class="s-cpt-hero__wrap container">
			<div class="s-cpt-hero__inner frame">
				<?php get_template_part( 'partials/breadcrumbs' ); ?>
				<h1 class="s-cpt-hero__title"><?php the_title(); ?></h1>
				<?php if ( ! empty( $hero['subtitle'] ) ) : ?>
					<p class="s-cpt-hero__subtitle"><?php echo esc_html( $hero['subtitle'] ); ?></p>
				<?php endif; ?>
			</div>
		</div>
	</section>

	<?php
	while ( have_posts() ) :
		the_post();
		?>
		<?php get_template_part( 'partials/parts/content-with-toc' ); ?>
	<?php endwhile; ?>

	<?php get_template_part( 'partials/parts/cpt-faq', null, (array) $faq ); ?>

	<?php get_template_part( 'partials/parts/cpt-cta', null, (array) $cta ); ?>
</main>
<?php
get_footer();
