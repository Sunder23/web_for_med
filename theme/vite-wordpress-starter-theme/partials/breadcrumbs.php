<?php
/**
 * Breadcrumb trail.
 *
 * @param array $args {
 *     @type string $class  Optional extra classes (e.g. `breadcrumbs--bar` for a one-line utility bar).
 *     @type array  $labels Optional label overrides, see starter_get_breadcrumb_items().
 * }
 *
 * @package Vite_Starter
 */

$starter_breadcrumb_args  = isset( $args ) && is_array( $args ) ? $args : array();
$starter_breadcrumb_items = starter_get_breadcrumb_items( isset( $starter_breadcrumb_args['labels'] ) && is_array( $starter_breadcrumb_args['labels'] ) ? $starter_breadcrumb_args['labels'] : array() );
$starter_breadcrumb_class = ! empty( $starter_breadcrumb_args['class'] ) ? ' ' . $starter_breadcrumb_args['class'] : '';

if ( empty( $starter_breadcrumb_items ) ) {
	return;
}
?>
<nav class="breadcrumbs<?php echo esc_attr( $starter_breadcrumb_class ); ?>" aria-label="<?php esc_attr_e( 'Хлібні крихти', 'vite-starter' ); ?>">
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
