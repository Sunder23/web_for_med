<?php
/**
 * Blog category chips: "Усі матеріали" + every non-empty category, current one highlighted.
 * Hidden while the blog has fewer than two categories (nothing to switch between).
 *
 * @package Vite_Starter
 */

$categories = get_categories(
	array(
		'hide_empty' => true,
		'orderby'    => 'name',
	)
);

if ( count( $categories ) < 2 ) {
	return;
}

$posts_page = (int) get_option( 'page_for_posts' );
$all_url    = $posts_page ? get_permalink( $posts_page ) : home_url( '/' );
$current_id = is_category() ? (int) get_queried_object_id() : 0;
?>
<nav class="category-nav" aria-label="<?php esc_attr_e( 'Рубрики блогу', 'vite-starter' ); ?>">
	<ul class="category-nav__list">
		<li class="category-nav__item">
			<a class="category-nav__link<?php echo $current_id ? '' : ' is-active'; ?>" href="<?php echo esc_url( $all_url ); ?>"<?php echo $current_id ? '' : ' aria-current="page"'; ?>><?php esc_html_e( 'Усі матеріали', 'vite-starter' ); ?></a>
		</li>
		<?php foreach ( $categories as $category ) : ?>
			<?php $is_current = $current_id === (int) $category->term_id; ?>
			<li class="category-nav__item">
				<a class="category-nav__link<?php echo $is_current ? ' is-active' : ''; ?>" href="<?php echo esc_url( get_category_link( $category ) ); ?>"<?php echo $is_current ? ' aria-current="page"' : ''; ?>>
					<?php echo esc_html( $category->name ); ?>
					<span class="category-nav__count"><?php echo esc_html( (string) $category->count ); ?></span>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
</nav>
