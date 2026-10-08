<?php
/**
 * Theme file.
 *
 * @package Vite_Starter
 */

if ( ! defined( 'VITE_DIST_DIR' ) ) {
	define( 'VITE_DIST_DIR', 'assets/dist' );
}
if ( ! defined( 'VITE_DIST_URI' ) ) {
	define( 'VITE_DIST_URI', WFB_THEME_URI . '/' . VITE_DIST_DIR );
}
if ( ! defined( 'VITE_DIST_PATH' ) ) {
	define( 'VITE_DIST_PATH', WFB_THEME_PATH . '/' . VITE_DIST_DIR );
}

if ( ! defined( 'VITE_SERVER' ) ) {
	// Default server address and port can be customized in vite.config.js.
	$vite_server_env = getenv( 'VITE_SERVER' );
	define( 'VITE_SERVER', $vite_server_env ? $vite_server_env : 'http://localhost:5173' );
}
if ( ! defined( 'VITE_BUILD' ) ) {
	define( 'VITE_BUILD', file_exists( VITE_DIST_PATH . '/.vite/manifest.json' ) );
}
if ( ! defined( 'VITE_DEV' ) ) {
	define( 'VITE_DEV', ! VITE_BUILD && wp_get_environment_type() === 'local' );
}

/**
 * Lists the theme's main JS/SCSS entry files, keyed by handle.
 *
 * @return array Map with "js" and "scss" entry-file lists.
 */
function starter_vite_asset_lists() {
	return array(
		'js'   => array(
			'main' => 'main.js',
		),
		'scss' => array(
			'main' => 'main.scss',
		),
	);
}

/**
 * Loads and caches the Vite build manifest.
 *
 * @return array|null Decoded manifest data, or null when it cannot be read.
 */
function starter_vite_manifest() {
	static $manifest = null;

	if ( null !== $manifest ) {
		return $manifest;
	}

	$manifest_path = VITE_DIST_PATH . '/.vite/manifest.json';
	if ( ! file_exists( $manifest_path ) ) {
		return null;
	}

	// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Local theme file, not a remote request.
	$manifest = json_decode( file_get_contents( $manifest_path ), true );
	if ( ! is_array( $manifest ) ) {
		$manifest = null;
	}

	return $manifest;
}

/**
 * Tracks script handles that must be output as ES modules, cached per request.
 *
 * @param string|null $add Handle to add to the tracked list, or null to just read it.
 * @return array Tracked ES module script handles.
 */
function starter_vite_module_handles( $add = null ) {
	static $handles = array();

	if ( null !== $add ) {
		$handles[] = $add;
	}

	return $handles;
}

/**
 * Resolves the built asset URI for a Vite manifest entry.
 *
 * Logs a WARN once per missing key when a production build is active.
 *
 * @param array|null $manifest Decoded Vite manifest data.
 * @param string     $key      Manifest entry key (source-relative asset path).
 * @return string|null Built asset URI, or null when the entry is missing.
 */
function starter_vite_manifest_uri( $manifest, $key ) {
	static $reported = array();

	if ( ! $manifest || ! isset( $manifest[ $key ]['file'] ) ) {
		if ( VITE_BUILD && ! isset( $reported[ $key ] ) ) {
			$reported[ $key ] = true;
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional WARN-level diagnostic (minimal logging policy).
			error_log( 'WARN [vite] manifest entry missing: ' . $key );
		}
		return null;
	}

	return VITE_DIST_URI . '/' . $manifest[ $key ]['file'];
}

/**
 * Registers and enqueues the theme's main JS/SCSS entries, dev-server or built.
 *
 * @return void
 */
function starter_vite_add_assets() {
	$assets   = starter_vite_asset_lists();
	$manifest = VITE_BUILD ? starter_vite_manifest() : null;

	if ( ! VITE_DEV && ! $manifest ) {
		return;
	}

	foreach ( $assets['js'] as $handle => $file ) {
		$key    = 'assets/src/js/' . $file;
		$js_uri = VITE_DEV ? VITE_SERVER . '/' . $key : null;

		if ( $manifest ) {
			$js_uri = starter_vite_manifest_uri( $manifest, $key );
			if ( ! $js_uri ) {
				continue;
			}

			// Enqueue CSS extracted from JS imports (e.g. vendor styles).
			foreach ( $manifest[ $key ]['css'] ?? array() as $i => $css_file ) {
				// phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- Content hash is in the file name.
				wp_enqueue_style( $handle . '-js-' . $i, VITE_DIST_URI . '/' . $css_file, array(), null );
			}
		}

		// phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- Content hash is in the file name.
		wp_register_script( $handle, $js_uri, array( 'jquery' ), null, true );
		starter_vite_module_handles( $handle );

		wp_enqueue_script( $handle );
	}

	foreach ( $assets['scss'] as $handle => $file ) {
		$key     = 'assets/src/scss/' . $file;
		$css_uri = VITE_DEV ? VITE_SERVER . '/' . $key : starter_vite_manifest_uri( $manifest, $key );

		if ( $css_uri ) {
			// phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- Content hash is in the file name.
			wp_enqueue_style( $handle, $css_uri, array(), null );
		}
	}
}
add_action( 'wp_enqueue_scripts', 'starter_vite_add_assets', 100 );

/**
 * Outputs the Vite dev-server client script tag when running in dev mode.
 *
 * @return void
 */
function starter_vite_client_head_hook() {
	if ( VITE_DEV ) {
		// phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript -- Vite dev-server client cannot go through wp_enqueue_script.
		echo '<script type="module" crossorigin src="' . esc_url( VITE_SERVER . '/@vite/client' ) . '"></script>';
	}
}
add_action( 'wp_head', 'starter_vite_client_head_hook' );

/**
 * Rewrites the script tag of tracked handles to use type="module".
 *
 * @param string $tag    Original script tag markup.
 * @param string $handle Script handle being filtered.
 * @param string $src    Script source URL.
 * @return string Modified (or unmodified) script tag markup.
 */
function starter_vite_module_type_attribute( $tag, $handle, $src ) {
	if ( in_array( $handle, starter_vite_module_handles(), true ) ) {
		// phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript -- Rewrites an already-enqueued tag to an ES module.
		return '<script type="module" src="' . esc_url( $src ) . '" crossorigin></script>';
	}

	return $tag;
}
add_filter( 'script_loader_tag', 'starter_vite_module_type_attribute', 10, 3 );

/**
 * Registers a theme stylesheet handle resolved through the Vite manifest.
 *
 * @param string $handle   Style handle to register.
 * @param string $filename Vite-relative scss path.
 * @param array  $deps     Style dependency handles.
 * @return string|false Registered style handle, or false when the asset could not be resolved.
 */
function starter_vite_register_style( $handle, $filename, $deps = array() ) {
	$key = 'assets/src/scss/' . $filename;
	$uri = VITE_DEV ? VITE_SERVER . '/' . $key : starter_vite_manifest_uri( starter_vite_manifest(), $key );

	if ( ! $uri ) {
		return false;
	}

	// phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- Content hash is in the file name.
	wp_register_style( $handle, $uri, $deps, null );

	return $handle;
}

/**
 * Registers a theme script handle resolved through the Vite manifest.
 *
 * @param string $handle   Script handle to register.
 * @param string $filename Vite-relative js path.
 * @param array  $deps     Script dependency handles.
 * @return string|false Registered script handle, or false when the asset could not be resolved.
 */
function starter_vite_register_script( $handle, $filename, $deps = array() ) {
	$key = 'assets/src/js/' . $filename;
	$uri = VITE_DEV ? VITE_SERVER . '/' . $key : starter_vite_manifest_uri( starter_vite_manifest(), $key );

	if ( ! $uri ) {
		return false;
	}

	// phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- Content hash is in the file name.
	wp_register_script( $handle, $uri, $deps, null, true );
	starter_vite_module_handles( $handle );

	return $handle;
}

/**
 * Enqueues the Typekit stylesheet with the theme's script font.
 *
 * @return void
 */
function starter_enqueue_typekit() {
	// phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- external CDN URL, no local version to track.
	wp_enqueue_style( 'adobe', 'https://use.typekit.net/jug2qva.css', array(), null );
}
add_action( 'wp_enqueue_scripts', 'starter_enqueue_typekit' );

/**
 * Preloads the built main script and stylesheet (production build only).
 *
 * @return void
 */
function starter_vite_preload_files() {
	if ( ! VITE_BUILD ) {
		return;
	}

	$manifest = starter_vite_manifest();
	if ( ! $manifest ) {
		return;
	}

	$assets = starter_vite_asset_lists();

	foreach ( $assets['js'] as $file ) {
		$js_uri = starter_vite_manifest_uri( $manifest, 'assets/src/js/' . $file );
		if ( $js_uri ) {
			echo '<link rel="preload" href="' . esc_url( $js_uri ) . '" as="script">';
		}
	}

	foreach ( $assets['scss'] as $file ) {
		$css_uri = starter_vite_manifest_uri( $manifest, 'assets/src/scss/' . $file );
		if ( $css_uri ) {
			echo '<link rel="preload" href="' . esc_url( $css_uri ) . '" as="style">';
		}
	}
}
add_action( 'wp_head', 'starter_vite_preload_files', 1 );

/**
 * Forces scripts to the footer and removes default WordPress styles.
 *
 * Core block styles stay enabled on singular views that render block content via the_content().
 *
 * @return void
 */
function starter_cleanup_wordpress() {
	// Force all scripts to load in the footer.
	remove_action( 'wp_head', 'wp_print_scripts' );
	remove_action( 'wp_head', 'wp_print_head_scripts', 9 );
	remove_action( 'wp_head', 'wp_enqueue_scripts', 1 );

	if ( ! starter_is_block_content_view() ) {
		wp_dequeue_style( 'wp-block-library' );
		wp_dequeue_style( 'wp-block-library-theme' );
		wp_dequeue_style( 'global-styles' );
		remove_action( 'wp_footer', 'wp_enqueue_global_styles', 1 );
		remove_action( 'wp_body_open', 'wp_global_styles_render_svg_filters' );
	}
	wp_dequeue_style( 'wc-block-style' );
	wp_dequeue_style( 'classic-theme-styles' );
}
add_action( 'wp_enqueue_scripts', 'starter_cleanup_wordpress', 100 );

/**
 * Emits theme.json presets as CSS custom properties and utility classes on the front end.
 *
 * Compensates for the dequeued global-styles stylesheet.
 *
 * @return void
 */
function starter_output_theme_json_preset_vars() {
	$theme_json_path = WFB_THEME_PATH . '/theme.json';
	if ( ! file_exists( $theme_json_path ) ) {
		return;
	}

	// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Local theme file, not a remote request.
	$data = json_decode( file_get_contents( $theme_json_path ), true );
	if ( empty( $data['settings'] ) ) {
		return;
	}

	$settings = $data['settings'];
	$lines    = array();

	foreach ( $settings['color']['palette'] ?? array() as $item ) {
		$lines[] = '  --wp--preset--color--' . $item['slug'] . ': ' . $item['color'] . ';';
	}

	foreach ( $settings['typography']['fontFamilies'] ?? array() as $item ) {
		$lines[] = '  --wp--preset--font-family--' . $item['slug'] . ': ' . $item['fontFamily'] . ';';
	}

	foreach ( $settings['typography']['fontSizes'] ?? array() as $item ) {
		$lines[] = '  --wp--preset--font-size--' . $item['slug'] . ': ' . $item['size'] . ';';
	}

	if ( empty( $lines ) ) {
		return;
	}

	$css = ':root {' . "\n" . implode( "\n", $lines ) . "\n}";

	$utilities = array();
	foreach ( $settings['color']['palette'] ?? array() as $item ) {
		$slug        = $item['slug'];
		$var         = 'var(--wp--preset--color--' . $slug . ')';
		$utilities[] = '.has-' . $slug . '-color { color: ' . $var . ' !important; }';
		$utilities[] = '.has-' . $slug . '-background-color { background-color: ' . $var . ' !important; }';
	}
	foreach ( $settings['typography']['fontSizes'] ?? array() as $item ) {
		$slug        = $item['slug'];
		$utilities[] = '.has-' . $slug . '-font-size { font-size: var(--wp--preset--font-size--' . $slug . ') !important; }';
	}
	foreach ( $settings['typography']['fontFamilies'] ?? array() as $item ) {
		$slug        = $item['slug'];
		$utilities[] = '.has-' . $slug . '-font-family { font-family: var(--wp--preset--font-family--' . $slug . ') !important; }';
	}

	if ( ! empty( $utilities ) ) {
		$css .= "\n" . implode( "\n", $utilities );
	}

	wp_add_inline_style( 'main', $css );
}
add_action( 'wp_enqueue_scripts', 'starter_output_theme_json_preset_vars', 101 );
