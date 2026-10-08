<?php
/**
 * Theme file.
 *
 * @package Vite_Starter
 */

$starter_breadcrumb_items = starter_get_breadcrumb_items();

if ( empty( $starter_breadcrumb_items ) ) {
	return;
}
?>
<nav class="breadcrumbs" aria-label="<?php esc_attr_e( 'Хлібні крихти', 'vite-starter' ); ?>">
	<ol class="breadcrumbs__list">
		<?php foreach ( $starter_breadcrumb_items as $starter_breadcrumb_item ) : ?>
			<li class="breadcrumbs__item">
				<?php if ( ! empty( $starter_breadcrumb_item['url'] ) ) : ?>
					<a class="breadcrumbs__link" href="<?php echo esc_url( $starter_breadcrumb_item['url'] ); ?>"><?php echo esc_html( $starter_breadcrumb_item['label'] ); ?></a>
				<?php else : ?>
					<span class="breadcrumbs__current" aria-current="page"><?php echo esc_html( $starter_breadcrumb_item['label'] ); ?></span>
				<?php endif; ?>
			</li>
		<?php endforeach; ?>
	</ol>
</nav>
