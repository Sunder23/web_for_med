<?php
/**
 * Shared archive layout for the services / directions / cases CPTs:
 * hero (title, lead, section switcher, CTA) + card grid over the main query.
 *
 * @package Vite_Starter
 */

// Archive intro under the heading, per post type.
$archive_leads = array(
	'services'   => __( 'Сайти, SEO, реклама та аналітика для медичних закладів. Кожна послуга працює на записи пацієнтів, а не на трафік заради трафіку.', 'vite-starter' ),
	'directions' => __( 'Готові рішення під профіль закладу: від приватного кабінету до багатопрофільної клініки. Враховуємо специфіку пацієнтів, обмеження реклами та правила медичної сфери.', 'vite-starter' ),
	'cases'      => __( 'Реальні проєкти для клінік і медичних центрів: з чого починали, що зробили і який результат отримали.', 'vite-starter' ),
);

$queried_object = get_queried_object();
$current_type   = $queried_object instanceof WP_Post_Type ? $queried_object->name : '';
$archive_lead   = isset( $archive_leads[ $current_type ] ) ? $archive_leads[ $current_type ] : '';

// Switcher between the CPT archives; reuses the blog category chips styles.
$archive_links = array();
foreach ( array_keys( $archive_leads ) as $archive_type ) {
	$type_object = get_post_type_object( $archive_type );
	$type_url    = get_post_type_archive_link( $archive_type );
	if ( ! $type_object || ! $type_url ) {
		continue;
	}
	$archive_links[] = array(
		'label'   => $type_object->labels->name,
		'url'     => $type_url,
		'count'   => (int) wp_count_posts( $archive_type )->publish,
		'current' => $archive_type === $current_type,
	);
}
?>
<main class="archive-cpt">
	<section class="s-cpt-hero">
		<div class="s-cpt-hero__wrap container">
			<div class="s-cpt-hero__inner frame">
				<?php get_template_part( 'partials/breadcrumbs' ); ?>
				<h1 class="s-cpt-hero__title"><?php post_type_archive_title(); ?></h1>
				<?php if ( $archive_lead ) : ?>
					<p class="s-cpt-hero__lead"><?php echo esc_html( $archive_lead ); ?></p>
				<?php endif; ?>
				<div class="s-cpt-hero__bottom">
					<?php if ( count( $archive_links ) > 1 ) : ?>
						<nav class="category-nav" aria-label="<?php esc_attr_e( 'Розділи', 'vite-starter' ); ?>">
							<ul class="category-nav__list">
								<?php foreach ( $archive_links as $archive_link ) : ?>
									<li class="category-nav__item">
										<a class="category-nav__link<?php echo $archive_link['current'] ? ' is-active' : ''; ?>" href="<?php echo esc_url( $archive_link['url'] ); ?>"<?php echo $archive_link['current'] ? ' aria-current="page"' : ''; ?>>
											<?php echo esc_html( $archive_link['label'] ); ?>
											<span class="category-nav__count"><?php echo esc_html( (string) $archive_link['count'] ); ?></span>
										</a>
									</li>
								<?php endforeach; ?>
							</ul>
						</nav>
					<?php endif; ?>
					<a class="button button--primary s-cpt-hero__button" href="#contact-form" data-contact-open><?php esc_html_e( 'Обговорити задачі', 'vite-starter' ); ?></a>
				</div>
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
