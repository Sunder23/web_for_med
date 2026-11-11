<?php
/**
 * Prev / next strip under a single case (Figma 3061:1100, mobile 3124:1545): the adjacent
 * cases by date, looping around at the ends. Titles are hidden on mobile. Uses the current
 * loop post; outputs nothing when the case is the only one.
 *
 * @package Vite_Starter
 */

$case_nav = array_filter( starter_get_adjacent_posts() );

if ( ! $case_nav ) {
	return;
}

$case_nav_labels = array(
	'prev' => __( 'Prev', 'vite-starter' ),
	'next' => __( 'Next', 'vite-starter' ),
);
$case_nav_svg    = static function ( $name ) {
	$file = get_template_directory() . '/static/images/cases/nav-' . $name . '.svg';
	return file_exists( $file ) ? (string) file_get_contents( $file ) : ''; // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Local theme SVG.
};
$case_nav_kses   = array(
	'svg'  => array(
		'width'       => true,
		'height'      => true,
		'viewbox'     => true,
		'fill'        => true,
		'xmlns'       => true,
		'aria-hidden' => true,
		'focusable'   => true,
	),
	'g'    => array( 'id' => true ),
	'path' => array(
		'id'   => true,
		'd'    => true,
		'fill' => true,
	),
);
?>
<nav class="case-nav" aria-label="<?php esc_attr_e( 'Інші кейси', 'vite-starter' ); ?>">
	<div class="case-nav__inner">
		<?php foreach ( array( 'prev', 'next' ) as $case_nav_dir ) : ?>
			<?php if ( empty( $case_nav[ $case_nav_dir ] ) ) : ?>
				<span class="case-nav__spacer" aria-hidden="true"></span>
				<?php continue; ?>
			<?php endif; ?>
			<?php $case_nav_post = $case_nav[ $case_nav_dir ]; ?>
			<a class="case-nav__link case-nav__link--<?php echo esc_attr( $case_nav_dir ); ?>" href="<?php echo esc_url( get_permalink( $case_nav_post ) ); ?>" rel="<?php echo esc_attr( $case_nav_dir ); ?>">
				<span class="case-nav__control">
					<span class="case-nav__arrow" aria-hidden="true"><?php echo wp_kses( $case_nav_svg( $case_nav_dir ), $case_nav_kses ); ?></span>
					<span class="case-nav__label"><?php echo esc_html( $case_nav_labels[ $case_nav_dir ] ); ?></span>
				</span>
				<span class="case-nav__title"><?php echo esc_html( get_the_title( $case_nav_post ) ); ?></span>
			</a>
		<?php endforeach; ?>
	</div>
</nav>
