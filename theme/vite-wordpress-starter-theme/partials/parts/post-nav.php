<?php
/**
 * Previous / next navigation under article singles: the adjacent posts of the same type
 * (by date), looping around at the ends. Uses the current loop post. Outputs nothing when
 * the post is the only one of its type.
 *
 * @package Vite_Starter
 */

$nav_post_type = get_post_type();
$current_id    = get_the_ID();

// Labels per type: [ previous, next ].
$nav_labels = array(
	'cases'      => array( __( 'Попередній кейс', 'vite-starter' ), __( 'Наступний кейс', 'vite-starter' ) ),
	'services'   => array( __( 'Попередня послуга', 'vite-starter' ), __( 'Наступна послуга', 'vite-starter' ) ),
	'directions' => array( __( 'Попередній напрям', 'vite-starter' ), __( 'Наступний напрям', 'vite-starter' ) ),
	'post'       => array( __( 'Попередня стаття', 'vite-starter' ), __( 'Наступна стаття', 'vite-starter' ) ),
);
$labels     = isset( $nav_labels[ $nav_post_type ] ) ? $nav_labels[ $nav_post_type ] : array( __( 'Попередній запис', 'vite-starter' ), __( 'Наступний запис', 'vite-starter' ) );

/**
 * Oldest or newest published post of the type, other than the current one (loop-around target).
 *
 * @param string $post_type  Post type.
 * @param int    $exclude_id Current post ID.
 * @param string $order      ASC for the oldest, DESC for the newest.
 * @return WP_Post|null
 */
$nav_edge_post = static function ( $post_type, $exclude_id, $order ) {
	$found = get_posts(
		array(
			'post_type'      => $post_type,
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'orderby'        => 'date',
			'order'          => $order,
			'post__not_in'   => array( $exclude_id ),
			'no_found_rows'  => true,
		)
	);
	return $found ? $found[0] : null;
};

$prev_post = get_adjacent_post( false, '', true );
$next_post = get_adjacent_post( false, '', false );

if ( ! $prev_post instanceof WP_Post ) {
	$prev_post = $nav_edge_post( $nav_post_type, $current_id, 'DESC' );
}
if ( ! $next_post instanceof WP_Post ) {
	$next_post = $nav_edge_post( $nav_post_type, $current_id, 'ASC' );
}

// Two posts in total: both directions point at the same post, show it once.
if ( $prev_post && $next_post && $prev_post->ID === $next_post->ID ) {
	$prev_post = null;
}

$nav_items = array_filter(
	array(
		'prev' => $prev_post ? array( $prev_post, $labels[0] ) : null,
		'next' => $next_post ? array( $next_post, $labels[1] ) : null,
	)
);

if ( ! $nav_items ) {
	return;
}
?>
<nav class="post-nav" aria-label="<?php esc_attr_e( 'Навігація між записами', 'vite-starter' ); ?>">
	<div class="container post-nav__container">
		<div class="frame post-nav__frame">
			<?php foreach ( $nav_items as $direction => $nav_item ) : ?>
				<?php list( $nav_post, $nav_label ) = $nav_item; ?>
				<a class="post-nav__link post-nav__link--<?php echo esc_attr( $direction ); ?>" href="<?php echo esc_url( get_permalink( $nav_post ) ); ?>" rel="<?php echo esc_attr( $direction ); ?>">
					<?php if ( has_post_thumbnail( $nav_post ) ) : ?>
						<span class="post-nav__image">
							<?php
							echo get_the_post_thumbnail(
								$nav_post,
								'medium',
								array(
									'alt'     => '',
									'loading' => 'lazy',
								)
							);
							?>
						</span>
					<?php endif; ?>
					<span class="post-nav__body">
						<span class="post-nav__label tag">
							<?php if ( 'prev' === $direction ) : ?>
								<span aria-hidden="true">&larr;</span>
							<?php endif; ?>
							<?php echo esc_html( $nav_label ); ?>
							<?php if ( 'next' === $direction ) : ?>
								<span aria-hidden="true">&rarr;</span>
							<?php endif; ?>
						</span>
						<span class="post-nav__title"><?php echo esc_html( get_the_title( $nav_post ) ); ?></span>
					</span>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</nav>
