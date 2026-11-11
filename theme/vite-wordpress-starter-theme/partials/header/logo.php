<?php
/**
 * Text logo «//web4med.» set in the heading font (Grunt Grotesk).
 *
 * Optional $args:
 * - modifier (string) BEM modifier without the `logo--` prefix, e.g. `lg` in the footer.
 *
 * @package Vite_Starter
 */

$starter_logo_class = 'logo';

if ( ! empty( $args['modifier'] ) && is_string( $args['modifier'] ) ) {
	$starter_logo_class .= ' logo--' . sanitize_html_class( $args['modifier'] );
}
?>
<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="<?php echo esc_attr( $starter_logo_class ); ?>" aria-label="<?php esc_attr_e( 'На головну', 'vite-starter' ); ?>">//web4med.</a>
