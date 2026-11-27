<?php
/**
 * 404: brand error screen with ways back (home, blog, main sections).
 *
 * @package Vite_Starter
 */

get_header();

$posts_page    = (int) get_option( 'page_for_posts' );
$services_page = get_page_by_path( 'services' );
$about_page    = get_page_by_path( 'about' );

if ( ! $services_page ) {
	error_log( 'WARN [404] services page not found' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
}

// Ways back into the main sections; each one only when its page / archive exists.
$section_links = array_filter(
	array(
		$services_page ? array( get_the_title( $services_page ), get_permalink( $services_page ) ) : null,
		get_post_type_archive_link( 'cases' ) ? array( __( 'Кейси', 'vite-starter' ), get_post_type_archive_link( 'cases' ) ) : null,
		$about_page ? array( __( 'Про нас', 'vite-starter' ), get_permalink( $about_page ) ) : null,
	)
);
?>

<main class="error-page">
	<section class="error-page__section">
		<div class="container error-page__container">
			<div class="frame error-page__frame">
				<div class="error-page__crumbs"><?php get_template_part( 'partials/breadcrumbs' ); ?></div>

				<div class="error-page__body">
					<p class="error-page__code" aria-hidden="true">404</p>
					<h1 class="error-page__title"><?php esc_html_e( 'Сторінку не знайдено', 'vite-starter' ); ?></h1>
					<p class="error-page__text"><?php esc_html_e( 'Можливо, посилання застаріло або в адресі є помилка. Поверніться на головну або оберіть потрібний розділ.', 'vite-starter' ); ?></p>

					<div class="error-page__actions">
						<a class="button button--primary" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'На головну', 'vite-starter' ); ?></a>
						<a class="button button--secondary" href="<?php echo esc_url( $posts_page ? get_permalink( $posts_page ) : home_url( '/' ) ); ?>"><?php esc_html_e( 'Читати блог', 'vite-starter' ); ?></a>
					</div>
				</div>

				<?php if ( $section_links ) : ?>
					<nav class="error-page__links" aria-label="<?php esc_attr_e( 'Розділи сайту', 'vite-starter' ); ?>">
						<p class="error-page__links-title"><?php esc_html_e( 'Розділи сайту', 'vite-starter' ); ?></p>
						<ul class="error-page__list">
							<?php foreach ( $section_links as $section_link ) : ?>
								<li class="error-page__item">
									<a class="error-page__link" href="<?php echo esc_url( $section_link[1] ); ?>">
										<span class="error-page__link-text"><?php echo esc_html( $section_link[0] ); ?></span>
										<span class="error-page__link-arrow" aria-hidden="true">&rarr;</span>
									</a>
								</li>
							<?php endforeach; ?>
						</ul>
					</nav>
				<?php endif; ?>
			</div>
		</div>
	</section>
</main>

<?php
get_footer();
