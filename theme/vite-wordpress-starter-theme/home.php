<?php
get_header();

$posts_page       = (int) get_option( 'page_for_posts' );
$posts_page_title = $posts_page ? get_the_title( $posts_page ) : 'Блог';
?>

<main class="archive-cpt blog-home">
	<section class="s-cpt-hero">
		<div class="s-cpt-hero__wrap l-wrap">
			<div class="s-cpt-hero__inner l-frame-x">
				<?php custom_theme_breadcrumbs(); ?>
				<h1 class="s-cpt-hero__title"><?php echo esc_html( $posts_page_title ); ?></h1>
			</div>
		</div>
	</section>

	<?php $blog_categories = get_categories( array( 'hide_empty' => true ) ); ?>
	<?php if ( ! empty( $blog_categories ) ) : ?>
		<section class="s-blog-filter">
			<div class="s-blog-filter__wrap l-wrap">
				<div class="s-blog-filter__inner l-frame-x">
					<button type="button" class="blog-filter__item tag c-tag is-active" data-blog-filter="all"><?php esc_html_e( 'Всі', 'textdomaintomodify' ); ?></button>
					<?php foreach ( $blog_categories as $category ) : ?>
						<button type="button" class="blog-filter__item tag c-tag" data-blog-filter="<?php echo esc_attr( $category->slug ); ?>"><?php echo esc_html( $category->name ); ?></button>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<section class="s-archive">
		<div class="s-archive__wrap l-wrap">
			<?php if ( have_posts() ) : ?>
				<div class="archive-grid l-frame-x" data-blog-grid>
					<?php while ( have_posts() ) : the_post(); ?>
						<?php $post_category_slugs = implode( ' ', wp_list_pluck( get_the_category(), 'slug' ) ); ?>
						<a class="archive-card info-card" href="<?php the_permalink(); ?>" data-category="<?php echo esc_attr( $post_category_slugs ); ?>">
							<div class="info-card__body">
								<span class="archive-card__date tag c-tag"><?php echo esc_html( get_the_date() ); ?></span>
								<h2 class="archive-card__title card-title"><?php the_title(); ?></h2>
								<p class="archive-card__text card-text"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 32 ) ); ?></p>
							</div>
							<span class="archive-card__more tag c-tag"><?php esc_html_e( 'Читати далі', 'textdomaintomodify' ); ?> &rarr;</span>
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
<?php get_footer();
