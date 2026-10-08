<?php
/**
 * Shared article layout for CPT singles and blog posts (structure of ref/promedi_block):
 * hero (breadcrumbs, title, meta, lead, actions) → featured image → blurb cards →
 * block-editor content, with a sticky TOC + CTA sidebar on the right from tablet up.
 *
 * The sidebar is rendered once (no wp_is_mobile(), it breaks page caching);
 * its position on phones (above the content) is set by grid areas in _article.scss.
 * Must be called inside the loop: it uses the_title() / the_content().
 *
 * @param array $args {
 *     @type string   $lead           Optional lead paragraph under the title.
 *     @type string   $note           Optional secondary paragraph under the lead.
 *     @type string[] $meta           Optional meta items (HTML allowed: date, category tag).
 *     @type array[]  $buttons        Optional rows of [ label, url ]; the first one is primary.
 *     @type array[]  $blurbs         Optional rows of [ title, text ].
 *     @type bool     $show_thumbnail Whether to output the featured image. Default true.
 * }
 *
 * @package Vite_Starter
 */

$lead           = ! empty( $args['lead'] ) ? $args['lead'] : '';
$note           = ! empty( $args['note'] ) ? $args['note'] : '';
$meta           = ! empty( $args['meta'] ) && is_array( $args['meta'] ) ? array_filter( $args['meta'] ) : array();
$buttons        = ! empty( $args['buttons'] ) && is_array( $args['buttons'] ) ? $args['buttons'] : array();
$blurbs         = ! empty( $args['blurbs'] ) && is_array( $args['blurbs'] ) ? $args['blurbs'] : array();
$show_thumbnail = ! isset( $args['show_thumbnail'] ) || (bool) $args['show_thumbnail'];
?>
<section class="article">
	<div class="container article__container">
		<div class="frame article__frame">
			<div class="article__grid">
				<header class="article__hero">
					<?php get_template_part( 'partials/breadcrumbs' ); ?>
					<?php the_title( '<h1 class="article__title">', '</h1>' ); ?>

					<?php if ( $meta ) : ?>
						<div class="article__meta">
							<?php foreach ( $meta as $meta_item ) : ?>
								<span class="article__meta-item"><?php echo wp_kses_post( $meta_item ); ?></span>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>

					<?php if ( $lead ) : ?>
						<p class="article__lead"><?php echo esc_html( $lead ); ?></p>
					<?php endif; ?>

					<?php if ( $note ) : ?>
						<p class="article__note"><?php echo esc_html( $note ); ?></p>
					<?php endif; ?>

					<?php if ( $buttons ) : ?>
						<div class="article__actions">
							<?php foreach ( $buttons as $index => $button ) : ?>
								<?php
								if ( empty( $button['label'] ) ) {
									continue;
								}
								?>
								<a href="<?php echo esc_url( ! empty( $button['url'] ) ? $button['url'] : '#contacts' ); ?>" class="button <?php echo 0 === $index ? 'button--primary' : 'button--secondary'; ?>"><?php echo esc_html( $button['label'] ); ?></a>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</header>

				<?php if ( $show_thumbnail && has_post_thumbnail() ) : ?>
					<figure class="article__image">
						<?php
						the_post_thumbnail(
							'large',
							array(
								'loading'       => 'eager',
								'fetchpriority' => 'high',
							)
						);
						?>
					</figure>
				<?php endif; ?>

				<?php if ( $blurbs ) : ?>
					<div class="article__blurbs">
						<?php foreach ( $blurbs as $blurb ) : ?>
							<div class="article__blurb">
								<?php if ( ! empty( $blurb['title'] ) ) : ?>
									<p class="article__blurb-title card-title"><?php echo esc_html( $blurb['title'] ); ?></p>
								<?php endif; ?>
								<?php if ( ! empty( $blurb['text'] ) ) : ?>
									<p class="article__blurb-text card-text"><?php echo esc_html( $blurb['text'] ); ?></p>
								<?php endif; ?>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

				<aside class="article__aside">
					<?php get_template_part( 'partials/parts/toc', null, array( 'items' => starter_get_toc() ) ); ?>
				</aside>

				<div class="entry-content article__content">
					<?php the_content(); ?>
				</div>
			</div>
		</div>
	</div>
</section>
