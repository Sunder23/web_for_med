<?php
/**
 * Page section blocks (acf/section-*): registration, rendering and
 * conditional asset loading.
 *
 * Each block is a thin wrapper around template-parts/blocks/section-{slug}.php —
 * the block's ACF/SCF fields are passed to the template part as $args, the same
 * shape single*.php build by hand. See docs/page-sections.md.
 *
 * @package Vite_Starter
 */

/**
 * Section slugs that have an acf/section-{slug} block.
 *
 * A function rather than a file-level global: WP-CLI loads theme files inside
 * a function scope, where top-level variables are not globals.
 *
 * @return string[] Section slugs.
 */
function starter_get_section_slugs() {
	return array(
		'home-hero',
		'clinics',
		'quote',
		'problems-solutions',
		'services',
		'cases',
		'process',
		'why',
	);
}

/**
 * Registers the style handle for a section slug.
 *
 * @param string $slug Section slug.
 * @return string|false Registered style handle, or false on failure.
 */
function starter_register_section_block_style( $slug ) {
	return starter_vite_register_style( 'starter-section-' . $slug, 'template-parts/blocks/section-' . $slug . '.scss', array( 'main' ) );
}

/**
 * Registers all section blocks found under configure/acf/section-blocks.
 *
 * @return void
 */
function starter_register_section_blocks() {
	if ( ! function_exists( 'register_block_type' ) ) {
		return;
	}

	$block_dirs = glob( WFB_THEME_PATH . '/configure/acf/section-blocks/*', GLOB_ONLYDIR );

	if ( empty( $block_dirs ) ) {
		return;
	}

	foreach ( $block_dirs as $block_dir ) {
		if ( file_exists( $block_dir . '/block.json' ) ) {
			register_block_type( $block_dir );
		}
	}
}
add_action( 'init', 'starter_register_section_blocks' );

/**
 * Adds the "Page sections" category to the block inserter.
 *
 * @param array $categories Existing block categories.
 * @return array Modified list of block categories.
 */
function starter_section_block_categories( $categories ) {
	return array_merge(
		array(
			array(
				'slug'  => 'page-sections',
				'title' => __( 'Секції сторінки', 'vite-starter' ),
			),
		),
		$categories
	);
}
add_filter( 'block_categories_all', 'starter_section_block_categories' );

/**
 * Prints the editor placeholder for a section block.
 *
 * @param string $title Block title.
 * @param string $text  Placeholder hint.
 * @return void
 */
function starter_render_section_block_placeholder( $title, $text ) {
	?>
	<div class="section-block-placeholder">
		<strong class="section-block-placeholder__title"><?php echo esc_html( $title ); ?></strong>
		<span class="section-block-placeholder__text"><?php echo esc_html( $text ); ?></span>
	</div>
	<?php
}

/**
 * Renders a section block through its template part.
 *
 * Called from configure/acf/section-blocks/section-{slug}/render.php.
 *
 * @param string $slug       Section slug (template-parts/blocks/section-{slug}.php).
 * @param array  $block      Block settings passed by ACF.
 * @param bool   $is_preview Whether the block is rendered in the editor.
 * @return void
 */
function starter_render_section_block( $slug, $block, $is_preview = false ) {
	if ( ! in_array( $slug, starter_get_section_slugs(), true ) ) {
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional WARN-level diagnostic (minimal logging policy).
		error_log( 'WARN [section-blocks] unknown section slug: ' . $slug );
		return;
	}

	$title  = ! empty( $block['title'] ) ? (string) $block['title'] : $slug;
	$fields = get_fields();

	if ( ! is_array( $fields ) ) {
		$fields = array();
	}

	ob_start();
	get_template_part( 'template-parts/blocks/section-' . $slug, null, $fields );
	$output = (string) ob_get_clean();

	if ( '' === trim( $output ) ) {
		if ( $is_preview ) {
			starter_render_section_block_placeholder( $title, __( 'Заповніть поля блоку', 'vite-starter' ) );
		}
		return;
	}

	echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Template part output, escaped inside the template.
}

/**
 * Collects section slugs from parsed blocks (recursively through innerBlocks).
 *
 * @param array $blocks Parsed blocks.
 * @param array $slugs  Accumulated slugs.
 * @return array Unique section slugs.
 */
function starter_collect_section_block_slugs( $blocks, $slugs = array() ) {
	foreach ( $blocks as $block ) {
		$name = isset( $block['blockName'] ) ? (string) $block['blockName'] : '';

		if ( 0 === strpos( $name, 'acf/section-' ) ) {
			$slug = substr( $name, strlen( 'acf/section-' ) );

			if ( ! in_array( $slug, $slugs, true ) ) {
				$slugs[] = $slug;
			}
		}

		if ( ! empty( $block['innerBlocks'] ) && is_array( $block['innerBlocks'] ) ) {
			$slugs = starter_collect_section_block_slugs( $block['innerBlocks'], $slugs );
		}
	}

	return $slugs;
}

/**
 * Collects the distinct section block slugs used in the current singular post.
 *
 * @return array List of active section slugs.
 */
function starter_section_blocks_active_slugs() {
	static $slugs = null;

	if ( null !== $slugs ) {
		return $slugs;
	}

	$slugs = array();

	if ( ! is_singular() ) {
		return $slugs;
	}

	$content = (string) get_post_field( 'post_content', get_queried_object_id() );

	if ( false === strpos( $content, '<!-- wp:acf/section-' ) ) {
		return $slugs;
	}

	$slugs = starter_collect_section_block_slugs( parse_blocks( $content ) );

	return $slugs;
}

/**
 * Whether the current singular post is built from section blocks only
 * (no regular content blocks next to them).
 *
 * @return bool True when every top-level block is an acf/section-* block.
 */
function starter_section_blocks_only_page() {
	static $only = null;

	if ( null !== $only ) {
		return $only;
	}

	$only = false;

	if ( empty( starter_section_blocks_active_slugs() ) ) {
		return $only;
	}

	$only = true;

	foreach ( parse_blocks( (string) get_post_field( 'post_content', get_queried_object_id() ) ) as $block ) {
		$name = isset( $block['blockName'] ) ? (string) $block['blockName'] : '';

		// Freeform chunks between blocks are just whitespace in block content.
		if ( '' === $name ) {
			if ( '' !== trim( $block['innerHTML'] ) ) {
				$only = false;
				break;
			}
			continue;
		}

		if ( 0 !== strpos( $name, 'acf/section-' ) ) {
			$only = false;
			break;
		}
	}

	return $only;
}

/**
 * Enqueues the style/script assets for each section block on the current page.
 *
 * @return void
 */
function starter_enqueue_section_block_assets() {
	foreach ( starter_section_blocks_active_slugs() as $slug ) {
		if ( ! in_array( $slug, starter_get_section_slugs(), true ) ) {
			continue;
		}

		// A section may ship only a stylesheet, only a script, or both.
		if ( file_exists( WFB_THEME_PATH . '/assets/src/scss/template-parts/blocks/section-' . $slug . '.scss' ) ) {
			$style_handle = starter_register_section_block_style( $slug );
			if ( $style_handle ) {
				wp_enqueue_style( $style_handle );
			}
		}

		if ( file_exists( WFB_THEME_PATH . '/assets/src/js/template-parts/blocks/section-' . $slug . '.js' ) ) {
			$script_handle = starter_vite_register_script( 'starter-section-' . $slug, 'template-parts/blocks/section-' . $slug . '.js', array( 'jquery' ) );
			if ( $script_handle ) {
				wp_enqueue_script( $script_handle );
			}
		}
	}
}
add_action( 'wp_enqueue_scripts', 'starter_enqueue_section_block_assets', 110 );
