<?php
/**
 * Theme file.
 *
 * @package Vite_Starter
 */

/**
 * Checks whether analytics tracking is enabled in theme options.
 *
 * @return bool True when analytics is enabled.
 */
function starter_is_analytics_enabled() {
	return starter_get_field( 'analytics_enabled', 'options' );
}

/**
 * Retrieves the configured Google Tag Manager container ID.
 *
 * @return string GTM container ID, or an empty value when unset.
 */
function starter_get_analytics_gtm_id() {
	return starter_get_field( 'analytics_gtm_id', 'options' );
}

/**
 * Outputs the Google Tag Manager script in the document head.
 *
 * @return void
 */
function starter_render_analytics_head() {
	if ( ! starter_is_analytics_enabled() ) {
		return;
	}

	$gtm_id = starter_get_analytics_gtm_id();

	if ( empty( $gtm_id ) ) {
		return;
	}
	?>
		<!-- Google Tag Manager -->
		<script>
			(function(w, d, s, l, i) {
				w[l] = w[l] || [];
				w[l].push({
					'gtm.start': new Date().getTime(),
					event: 'gtm.js'
				});
				var f = d.getElementsByTagName(s)[0],
					j = d.createElement(s),
					dl = l != 'dataLayer' ? '&l=' + l : '';
				j.async = true;
				j.src =
					'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
				f.parentNode.insertBefore(j, f);
			})(window, document, 'script', 'dataLayer', '<?php echo esc_js( $gtm_id ); ?>');
		</script>
		<!-- End Google Tag Manager -->
	<?php
}
add_action( 'wp_head', 'starter_render_analytics_head' );

/**
 * Outputs the Google Tag Manager noscript iframe right after the opening body tag.
 *
 * @return void
 */
function starter_render_analytics_body_open() {
	if ( ! starter_is_analytics_enabled() ) {
		return;
	}

	$gtm_id = starter_get_analytics_gtm_id();

	if ( empty( $gtm_id ) ) {
		return;
	}
	?>
	<!-- Google Tag Manager (noscript) -->
	<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=<?php echo esc_attr( $gtm_id ); ?>"
			height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
	<!-- End Google Tag Manager (noscript) -->
	<?php
}
add_action( 'wp_body_open', 'starter_render_analytics_body_open' );
