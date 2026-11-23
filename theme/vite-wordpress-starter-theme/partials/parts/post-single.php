<?php
/**
 * Single blog post body (Figma 3453:479 desktop, 3493:839 mobile): utility bar with the
 * `home / blog / title` breadcrumbs and the "section: NN/MM" counter, then one 880px column —
 * title, author line, lead (= excerpt), cover (featured image), «Зміст» built from the
 * content H2 (starter_get_toc()) and the content itself. Uses the current loop post.
 *
 * CPT singles keep partials/parts/article-layout.php. Content blocks are styled inside
 * `.post-single__content` only (pull quote, `is-style-figure`, `is-style-callout`, …).
 *
 * @package Vite_Starter
 */

$post_toc     = starter_get_toc( get_post() );
$post_total   = 1 + count( $post_toc );
$post_lead    = has_excerpt() ? get_the_excerpt() : '';
$post_toc_pad = static function ( $n ) {
	return str_pad( (string) $n, 2, '0', STR_PAD_LEFT );
};
?>
<div class="post-single__bar">
	<div class="post-single__bar-inner">
		<?php
		get_template_part(
			'partials/breadcrumbs',
			null,
			array(
				'class'  => 'breadcrumbs--bar post-single__breadcrumbs',
				'labels' => array(
					'home' => 'home',
					'blog' => 'blog',
				),
			)
		);
		?>
		<?php if ( $post_total > 1 ) : ?>
			<p class="post-single__counter" data-section-counter>
				section: <span data-section-current>01</span>/<span data-section-total><?php echo esc_html( $post_toc_pad( $post_total ) ); ?></span>
			</p>
		<?php endif; ?>
	</div>
</div>

<div class="container post-single__container">
	<div class="frame post-single__frame">
		<article <?php post_class( 'post-single__column' ); ?>>
			<header class="post-single__intro">
				<?php the_title( '<h1 class="post-single__title">', '</h1>' ); ?>

				<?php
				get_template_part(
					'partials/parts/post-author',
					null,
					array(
						'post'  => get_post(),
						'class' => 'post-single__author',
					)
				);
				?>

				<?php if ( '' !== $post_lead ) : ?>
					<p class="post-single__lead"><?php echo esc_html( $post_lead ); ?></p>
				<?php endif; ?>

				<?php if ( has_post_thumbnail() ) : ?>
					<figure class="post-single__cover">
						<?php
						the_post_thumbnail(
							'full',
							array(
								'class'         => 'post-single__img',
								'alt'           => '',
								'loading'       => 'eager',
								'fetchpriority' => 'high',
								'sizes'         => '(min-width: 1024px) 880px, 100vw',
							)
						);
						?>
					</figure>
				<?php endif; ?>

				<?php if ( $post_toc ) : ?>
					<nav class="post-single__toc" aria-labelledby="post-toc-title">
						<p class="post-single__toc-title" id="post-toc-title"><?php esc_html_e( 'Зміст', 'vite-starter' ); ?></p>
						<ol class="post-single__toc-list">
							<?php foreach ( $post_toc as $post_toc_index => $post_toc_item ) : ?>
								<li class="post-single__toc-item">
									<a class="post-single__toc-link" href="#<?php echo esc_attr( $post_toc_item['id'] ); ?>">
										<span class="post-single__toc-number" aria-hidden="true">[ <?php echo esc_html( $post_toc_pad( $post_toc_index + 1 ) ); ?> ]</span>
										<span class="post-single__toc-text"><?php echo esc_html( $post_toc_item['title'] ); ?></span>
									</a>
								</li>
							<?php endforeach; ?>
						</ol>
					</nav>
				<?php endif; ?>
			</header>

			<div class="entry-content post-single__content">
				<?php the_content(); ?>
			</div>
		</article>
	</div>
</div>
