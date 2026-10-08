<?php
/**
 * 404: brand error screen with ways back (home, blog, services).
 *
 * @package Vite_Starter
 */

get_header();

$posts_page   = (int) get_option( 'page_for_posts' );
$services_url = get_post_type_archive_link( 'services' );
$services     = get_posts(
	array(
		'post_type'      => 'services',
		'posts_per_page' => 4,
		'orderby'        => 'menu_order title',
		'order'          => 'ASC',
		'no_found_rows'  => true,
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

				<?php if ( $services ) : ?>
					<nav class="error-page__links" aria-label="<?php esc_attr_e( 'Послуги', 'vite-starter' ); ?>">
						<p class="error-page__links-title">
							<?php if ( $services_url ) : ?>
								<a href="<?php echo esc_url( $services_url ); ?>"><?php esc_html_e( 'Послуги', 'vite-starter' ); ?></a>
							<?php else : ?>
								<?php esc_html_e( 'Послуги', 'vite-starter' ); ?>
							<?php endif; ?>
						</p>
						<ul class="error-page__list">
							<?php foreach ( $services as $service ) : ?>
								<li class="error-page__item">
									<a class="error-page__link" href="<?php echo esc_url( get_permalink( $service ) ); ?>">
										<span class="error-page__link-text"><?php echo esc_html( get_the_title( $service ) ); ?></span>
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
