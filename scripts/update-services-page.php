<?php
/**
 * Services page (`/services/`, Figma desktop 3534:2180, mobile 3589:7251).
 *
 * Creates (or refills) the page with slug `services` from the section blocks
 * services-hero → service-list → capabilities → stages → faq, with the texts from
 * scripts/data/services/content.php:
 *   - service descriptions are copied once from the front page `acf/section-services`
 *     block (`items_N_text`, same order: web, seo, ads, analytics, communication);
 *     an empty one falls back to the shared text from content.php (with a warning);
 *   - service icons reuse the front page attachments (`_starter_source`
 *     `home-redesign/service-*.svg`); images and the other icons are sideloaded from
 *     scripts/data/services/ once and tagged `_starter_source` = `services/<file>`,
 *     so re-runs reuse them;
 *   - the SEO description goes to Rank Math / Yoast when the plugin is active.
 *
 * Run scripts/remove-services-cpt.php first: the script stops while posts of the
 * removed `services` post type still exist (they would keep the slug busy).
 * A page that already has `acf/section-*` blocks is left alone unless `force` is
 * passed; non-empty old content is backed up to `_starter_pre_blocks_content`.
 *
 * Args:
 *   (none)  dry run — logs what would change, writes nothing;
 *   apply   upload media, create / update the page;
 *   force   overwrite existing section blocks.
 *
 * Run: docker compose -f docker/compose.yml run --rm wpcli wp eval-file /scripts/update-services-page.php [apply] [force]
 * (On Windows/Git Bash, prefix with MSYS_NO_PATHCONV=1.)
 *
 * @package Vite_Starter
 */

if ( ! defined( 'WP_CLI' ) ) {
	echo "This script must be run via WP-CLI (wp eval-file).\n";
	exit( 1 );
}

global $wpdb;

$sp_args    = isset( $args ) && is_array( $args ) ? $args : array();
$sp_apply   = in_array( 'apply', $sp_args, true );
$sp_force   = in_array( 'force', $sp_args, true );
$sp_dir     = __DIR__ . '/data/services/';
$sp_content = require $sp_dir . 'content.php';

$sp_stats = array(
	'uploaded' => 0,
	'pending'  => 0,
	'reused'   => 0,
	'warnings' => 0,
);

WP_CLI::log( '[services-page] ' . ( $sp_apply ? 'apply' : 'dry run' ) . ( $sp_force ? ' (force)' : '' ) );

// --- Guards ----------------------------------------------------------------

$sp_cpt_left = (int) $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
	$wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s", 'services' )
);
if ( $sp_cpt_left ) {
	WP_CLI::error( "[services-page] {$sp_cpt_left} post(s) of the removed services post type still exist — run remove-services-cpt.php first." );
}

/**
 * Find an attachment by its `_starter_source` meta.
 *
 * @param string $source Source tag.
 * @return int Attachment ID or 0.
 */
$sp_find = static function ( $source ) {
	$ids = get_posts(
		array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_key'       => '_starter_source', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- One-off CLI script.
			'meta_value'     => $source, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- One-off CLI script.
		)
	);
	return $ids ? (int) $ids[0] : 0;
};

/**
 * Resolve a file from scripts/data/services/ to an attachment ID (sideload once).
 *
 * @param string $file Path inside scripts/data/services/ (e.g. `icons/stage-rocket.svg`).
 * @param string $alt  Alt text for a new upload.
 * @return int Attachment ID, -1 when it would be uploaded (dry run), 0 on failure.
 */
$sp_attachment = static function ( $file, $alt = '' ) use ( $sp_dir, $sp_apply, $sp_find, &$sp_stats ) {
	$source = 'services/' . $file;
	$found  = $sp_find( $source );

	if ( $found ) {
		++$sp_stats['reused'];
		WP_CLI::log( "[services-page] reuse {$source} → #{$found}" );
		return $found;
	}

	$path = $sp_dir . $file;
	if ( ! file_exists( $path ) ) {
		WP_CLI::warning( "[services-page] source file missing: {$path}" );
		++$sp_stats['warnings'];
		return 0;
	}

	if ( ! $sp_apply ) {
		++$sp_stats['pending'];
		WP_CLI::log( "[services-page] would upload {$source}" );
		return -1;
	}

	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	// media_handle_sideload() moves the file, so hand it a temp copy.
	$tmp = wp_tempnam( basename( $file ) );
	if ( ! $tmp || ! copy( $path, $tmp ) ) {
		WP_CLI::warning( "[services-page] cannot copy {$path} to a temp file" );
		++$sp_stats['warnings'];
		return 0;
	}

	$id = media_handle_sideload(
		array(
			'name'     => basename( $file ),
			'tmp_name' => $tmp,
		),
		0
	);

	if ( is_wp_error( $id ) ) {
		wp_delete_file( $tmp );
		WP_CLI::warning( "[services-page] upload failed for {$source}: " . $id->get_error_message() );
		++$sp_stats['warnings'];
		return 0;
	}

	update_post_meta( $id, '_starter_source', $source );
	update_post_meta( $id, '_wp_attachment_image_alt', $alt );
	++$sp_stats['uploaded'];
	WP_CLI::log( "[services-page] uploaded {$source} → #{$id}" );
	return (int) $id;
};

/**
 * Build ACF block data: every value gets its `_name` → field key reference.
 *
 * @param string $slug   Section slug.
 * @param array  $values Flat `name => value` map (repeater rows as `rows_0_sub`).
 * @return array Block `data` attribute.
 */
$sp_block_data = static function ( $slug, array $values ) {
	$prefix = 'field_starter_section_' . str_replace( '-', '_', $slug ) . '_';
	$data   = array();

	foreach ( $values as $name => $value ) {
		$data[ $name ]       = $value;
		$data[ '_' . $name ] = $prefix . preg_replace( '/_\d+_/', '_', $name );
	}

	return $data;
};

/**
 * One serialisable ACF block.
 *
 * @param string $slug   Section slug.
 * @param array  $values Field values.
 * @return array Parsed-block shape for serialize_blocks().
 */
$sp_block = static function ( $slug, array $values ) use ( $sp_block_data ) {
	$data = $sp_block_data( $slug, $values );
	WP_CLI::log( sprintf( '[services-page] block acf/section-%s: %d field(s)', $slug, count( $values ) ) );

	return array(
		'blockName'    => 'acf/section-' . $slug,
		'attrs'        => array(
			'name' => 'acf/section-' . $slug,
			'data' => $data,
			'mode' => 'preview',
		),
		'innerBlocks'  => array(),
		'innerHTML'    => '',
		'innerContent' => array(),
	);
};

// --- Service descriptions from the front page --------------------------------

$sp_home_texts = array();
$sp_front_id   = (int) get_option( 'page_on_front' );

if ( $sp_front_id ) {
	foreach ( parse_blocks( (string) get_post_field( 'post_content', $sp_front_id ) ) as $sp_home_block ) {
		if ( 'acf/section-services' !== $sp_home_block['blockName'] ) {
			continue;
		}
		$sp_home_data = isset( $sp_home_block['attrs']['data'] ) ? (array) $sp_home_block['attrs']['data'] : array();
		$sp_home_rows = isset( $sp_home_data['items'] ) ? (int) $sp_home_data['items'] : 0;
		for ( $sp_i = 0; $sp_i < $sp_home_rows; $sp_i++ ) {
			$sp_home_texts[ $sp_i ] = trim( (string) ( $sp_home_data[ "items_{$sp_i}_text" ] ?? '' ) );
		}
		break;
	}
}

if ( ! $sp_home_texts ) {
	WP_CLI::warning( '[services-page] front page acf/section-services block not found, using the fallback descriptions' );
	++$sp_stats['warnings'];
}

// --- Blocks ------------------------------------------------------------------

$sp_list   = $sp_content['service_list'];
$sp_values = array(
	'intro'          => $sp_list['intro'],
	'items'          => count( $sp_list['items'] ),
	'default_active' => $sp_list['default_active'],
	'link_icon'      => $sp_attachment( $sp_list['link_icon'] ),
	'link'           => array(
		'title'  => $sp_list['link_title'],
		'url'    => '/cases/',
		'target' => '',
	),
);

foreach ( $sp_list['items'] as $sp_i => $sp_item ) {
	$sp_text = $sp_home_texts[ $sp_i ] ?? '';
	if ( '' === $sp_text ) {
		WP_CLI::warning( '[services-page] home description ' . ( $sp_i + 1 ) . ' is empty, using fallback' );
		++$sp_stats['warnings'];
		$sp_text = $sp_content['service_fallback'];
	}

	$sp_icon = $sp_find( $sp_item[1] );
	if ( ! $sp_icon ) {
		WP_CLI::warning( "[services-page] home icon {$sp_item[1]} not found — run update-home-redesign.php or set the icon in the editor" );
		++$sp_stats['warnings'];
	}

	$sp_values[ "items_{$sp_i}_icon" ]  = $sp_icon;
	$sp_values[ "items_{$sp_i}_title" ] = $sp_item[0];
	$sp_values[ "items_{$sp_i}_text" ]  = $sp_text;
	$sp_values[ "items_{$sp_i}_image" ] = $sp_attachment( $sp_item[2], $sp_item[0] );
}

$sp_caps        = $sp_content['capabilities'];
$sp_caps_values = array(
	'title'        => $sp_caps['title'],
	'items'        => count( $sp_caps['items'] ),
	'tasks_title'  => $sp_caps['tasks_title'],
	'tasks'        => count( $sp_caps['tasks'] ),
	'button_label' => $sp_caps['button_label'],
);
foreach ( $sp_caps['items'] as $sp_i => $sp_item ) {
	$sp_caps_values[ "items_{$sp_i}_icon" ] = $sp_attachment( 'icons/' . $sp_item[0] );
	$sp_caps_values[ "items_{$sp_i}_text" ] = $sp_item[1];
}
foreach ( $sp_caps['tasks'] as $sp_i => $sp_task ) {
	$sp_caps_values[ "tasks_{$sp_i}_label" ] = $sp_task;
}

$sp_stages        = $sp_content['stages'];
$sp_stages_values = array(
	'title' => $sp_stages['title'],
	'items' => count( $sp_stages['items'] ),
);
foreach ( $sp_stages['items'] as $sp_i => $sp_item ) {
	$sp_stages_values[ "items_{$sp_i}_icon" ]  = $sp_attachment( 'icons/' . $sp_item[0] );
	$sp_stages_values[ "items_{$sp_i}_title" ] = $sp_item[1];
	$sp_stages_values[ "items_{$sp_i}_text" ]  = $sp_item[2];
}

$sp_faq        = $sp_content['faq'];
$sp_faq_values = array(
	'title'      => $sp_faq['title'],
	'items'      => count( $sp_faq['questions'] ),
	'open_first' => 1,
);
foreach ( $sp_faq['questions'] as $sp_i => $sp_question ) {
	$sp_faq_values[ "items_{$sp_i}_question" ] = $sp_question;
	$sp_faq_values[ "items_{$sp_i}_answer" ]   = '<p>' . $sp_faq['answer'] . '</p>';
}

$sp_blocks = array(
	$sp_block( 'services-hero', $sp_content['hero'] ),
	$sp_block( 'service-list', $sp_values ),
	$sp_block( 'capabilities', $sp_caps_values ),
	$sp_block( 'stages', $sp_stages_values ),
	$sp_block( 'faq', $sp_faq_values ),
);

// --- Page --------------------------------------------------------------------

$sp_page = get_page_by_path( 'services', OBJECT, 'page' );

if ( $sp_page ) {
	WP_CLI::log( sprintf( '[services-page] page #%d "%s" exists', $sp_page->ID, $sp_page->post_title ) );
	$sp_old_content = (string) $sp_page->post_content;

	if ( false !== strpos( $sp_old_content, '<!-- wp:acf/section-' ) && ! $sp_force ) {
		WP_CLI::warning( '[services-page] page already has acf/section-* blocks; pass "force" to overwrite.' );
		WP_CLI::halt( 0 );
	}
} else {
	WP_CLI::log( '[services-page] page "services" not found: ' . ( $sp_apply ? 'creating' : 'would be created' ) );
	$sp_old_content = '';
}

if ( ! $sp_apply ) {
	WP_CLI::success( sprintf( '[services-page] dry run: %d upload(s) pending, %d reused, %d warning(s). Run with `apply`.', $sp_stats['pending'], $sp_stats['reused'], $sp_stats['warnings'] ) );
	return;
}

$sp_postarr = array(
	'post_type'    => 'page',
	'post_status'  => 'publish',
	'post_title'   => $sp_content['page_title'],
	'post_name'    => 'services',
	'post_content' => wp_slash( serialize_blocks( $sp_blocks ) ),
);

if ( $sp_page ) {
	if ( '' !== trim( $sp_old_content ) ) {
		update_post_meta( $sp_page->ID, '_starter_pre_blocks_content', wp_slash( $sp_old_content ) );
		WP_CLI::log( '[services-page] previous content backed up to _starter_pre_blocks_content' );
	}
	$sp_postarr['ID'] = $sp_page->ID;
	$sp_page_id       = wp_update_post( $sp_postarr, true );
} else {
	$sp_page_id = wp_insert_post( $sp_postarr, true );
}

if ( is_wp_error( $sp_page_id ) ) {
	WP_CLI::error( '[services-page] saving the page failed — ' . $sp_page_id->get_error_message() );
}

$sp_slug = get_post_field( 'post_name', $sp_page_id );
if ( 'services' !== $sp_slug ) {
	WP_CLI::warning( "[services-page] page got the slug \"{$sp_slug}\" instead of \"services\" — something else holds the slug" );
	++$sp_stats['warnings'];
}

// SEO description.
if ( defined( 'RANK_MATH_VERSION' ) ) {
	update_post_meta( $sp_page_id, 'rank_math_description', $sp_content['meta_description'] );
	WP_CLI::log( '[services-page] Rank Math description set' );
} elseif ( defined( 'WPSEO_VERSION' ) ) {
	update_post_meta( $sp_page_id, '_yoast_wpseo_metadesc', $sp_content['meta_description'] );
	WP_CLI::log( '[services-page] Yoast description set' );
} else {
	WP_CLI::log( '[services-page] no SEO plugin active, description skipped' );
}

WP_CLI::log( "[services-page] page #{$sp_page_id} saved: " . get_permalink( $sp_page_id ) );

WP_CLI::success(
	sprintf(
		'[services-page] applied: %d upload(s), %d reused, %d warning(s).',
		$sp_stats['uploaded'],
		$sp_stats['reused'],
		$sp_stats['warnings']
	)
);
