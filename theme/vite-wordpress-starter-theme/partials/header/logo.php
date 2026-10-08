<?php
/**
 * Theme file.
 *
 * @package Vite_Starter
 */

$starter_logo_id = starter_get_option( 'logo' );

if ( empty( $starter_logo_id ) ) {
	return;
}
?>
<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="logo">
	<?php echo wp_get_attachment_image( $starter_logo_id, 'full' ); ?>
</a>
