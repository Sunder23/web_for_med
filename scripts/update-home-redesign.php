<?php
/**
 * Move the front page content to the November 2026 redesign (Figma huDLClWg3dIpXyzLMuVHEs).
 *
 * - Section titles / quote text (п.2): quote, services, why.
 * - Icons (п.13): clinics, solutions, services, process and why reasons are
 *   swapped for the SVGs from the theme's static/images/home/.
 * - Hero images: 2x versions from scripts/data/home-redesign/ (the old 375×480
 *   mobile image was upscaled and looked blurry, note 3178:3487).
 * - Contact CTAs (hero primary link, `header_button` option) → `#contact-form`,
 *   which opens the contact drawer.
 * - CF7 form markup — delegated to update-cf7-contact-form.php.
 *
 * Every media file is sideloaded once and tagged with `_starter_source`
 * (`home-redesign/<file>`), so re-running reuses the attachment instead of
 * uploading a duplicate. Values that already match are skipped.
 *
 * Args:
 *   (none)  dry run — logs what would change, writes nothing;
 *   apply   upload media and write the changes.
 *
 * Run: docker compose -f docker/compose.yml run --rm wpcli wp eval-file /scripts/update-home-redesign.php [apply]
 * (On Windows/Git Bash, prefix with MSYS_NO_PATHCONV=1.)
 *
 * @package Vite_Starter
 */

if ( ! defined( 'WP_CLI' ) ) {
	echo "This script must be run via WP-CLI (wp eval-file).\n";
	exit( 1 );
}

$hr_args  = isset( $args ) && is_array( $args ) ? $args : array();
$hr_apply = in_array( 'apply', $hr_args, true );

$hr_theme_icons = get_template_directory() . '/static/images/home/';
$hr_data_dir    = __DIR__ . '/data/home-redesign/';

/*
 * Plan: block name => list of [ data key, new value ].
 * A value is either a string (text) or array( 'file' => absolute path ) (attachment).
 */
$hr_icon = static function ( $name ) use ( $hr_theme_icons ) {
	return array( 'file' => $hr_theme_icons . $name . '.svg' );
};

$hr_plan = array(
	'acf/section-home-hero'          => array(
		'image'                    => array( 'file' => $hr_data_dir . 'hero-desktop-2x.png' ),
		'image_mobile'             => array( 'file' => $hr_data_dir . 'hero-mobile-2x.png' ),
		'primary_link.url'         => '#contact-form',
	),
	'acf/section-clinics'            => array(
		'items_0_icon' => $hr_icon( 'clinic-dental' ),
		'items_1_icon' => $hr_icon( 'clinic-aesthetic' ),
		'items_2_icon' => $hr_icon( 'clinic-reproductive' ),
		'items_3_icon' => $hr_icon( 'clinic-rehab' ),
		'items_4_icon' => $hr_icon( 'clinic-multiprofile' ),
		'items_5_icon' => $hr_icon( 'clinic-private' ),
		'aside_icon'   => $hr_icon( 'clinic-puzzle' ),
	),
	'acf/section-quote'              => array(
		'text' => 'Цифрові процеси мають працювати в унісон з медичним бізнесом',
	),
	'acf/section-problems-solutions' => array(
		'solutions_0_image' => $hr_icon( 'solution-magnet' ),
		'solutions_1_image' => $hr_icon( 'solution-yarn' ),
		'solutions_2_image' => $hr_icon( 'solution-funnel' ),
	),
	'acf/section-services'           => array(
		'title'        => 'За кожною послугою стоїть розуміння того, як працює клініка зсередини',
		'items_0_icon' => $hr_icon( 'service-web' ),
		'items_1_icon' => $hr_icon( 'service-seo' ),
		'items_2_icon' => $hr_icon( 'service-ads' ),
		'items_3_icon' => $hr_icon( 'service-analytics' ),
		'items_4_icon' => $hr_icon( 'service-communication' ),
	),
	'acf/section-process'            => array(
		'title'         => 'Починаємо проєкт із визначення актуальних задач клініки, а не з дизайну',
		'title_mobile'  => 'Починаємо проєкт із визначення актуальних задач клініки',
		'items_0_image' => $hr_icon( 'process-discovery' ),
		'items_1_image' => $hr_icon( 'process-strategy' ),
		'items_2_image' => $hr_icon( 'process-implementation' ),
		'items_3_image' => $hr_icon( 'process-monitoring' ),
	),
	'acf/section-why'                => array(
		'title'                => 'Чому з нами співпрацюють роками',
		'items_0_reasons_icon' => $hr_icon( 'why-specialization' ),
		'items_1_reasons_icon' => $hr_icon( 'why-transparency' ),
		'items_2_reasons_icon' => $hr_icon( 'why-system' ),
		'items_3_reasons_icon' => $hr_icon( 'why-communication' ),
	),
);

// Fields added to a block after its content was saved: created in the block
// data (with ACF's `_name` → field key reference) instead of being skipped.
$hr_new_fields = array(
	'acf/section-process' => array(
		'title_mobile' => 'field_starter_section_process_title_mobile',
	),
);

$hr_stats = array(
	'changed'  => 0,
	'skipped'  => 0,
	'uploaded' => 0,
	'warnings' => 0,
);

/**
 * Find the attachment previously sideloaded from $file (by `_starter_source`).
 *
 * @param string $source Source tag, `home-redesign/<basename>`.
 * @return int Attachment ID or 0.
 */
$hr_find_attachment = static function ( $source ) {
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
 * Resolve a file to an attachment ID: reuse a tagged one or (apply mode) sideload it.
 *
 * @param string $file Absolute path.
 * @return int Attachment ID, -1 when it would be uploaded (dry run), 0 on failure.
 */
$hr_resolve_attachment = static function ( $file ) use ( $hr_apply, $hr_find_attachment, &$hr_stats ) {
	$source = 'home-redesign/' . basename( $file );
	$found  = $hr_find_attachment( $source );
	if ( $found ) {
		return $found;
	}

	if ( ! file_exists( $file ) ) {
		WP_CLI::warning( "[home-redesign] source file missing: {$file}" );
		++$hr_stats['warnings'];
		return 0;
	}

	if ( ! $hr_apply ) {
		WP_CLI::log( "[home-redesign] would upload {$source}" );
		return -1;
	}

	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	// media_handle_sideload() moves the file, so hand it a temp copy.
	$tmp = wp_tempnam( basename( $file ) );
	if ( ! $tmp || ! copy( $file, $tmp ) ) {
		WP_CLI::warning( "[home-redesign] cannot copy {$file} to a temp file" );
		++$hr_stats['warnings'];
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
		WP_CLI::warning( "[home-redesign] upload failed for {$source}: " . $id->get_error_message() );
		++$hr_stats['warnings'];
		return 0;
	}

	update_post_meta( $id, '_starter_source', $source );
	update_post_meta( $id, '_wp_attachment_image_alt', '' );
	++$hr_stats['uploaded'];
	WP_CLI::log( "[home-redesign] uploaded {$source} → #{$id}" );
	return (int) $id;
};

// --- Front page blocks ---------------------------------------------------

$hr_page_id = (int) get_option( 'page_on_front' );
$hr_page    = $hr_page_id ? get_post( $hr_page_id ) : null;

if ( ! $hr_page ) {
	WP_CLI::error( '[home-redesign] no static front page (page_on_front) is set.' );
}

WP_CLI::log( sprintf( '[home-redesign] front page #%d "%s"%s', $hr_page_id, $hr_page->post_title, $hr_apply ? '' : ' (dry run)' ) );

$hr_blocks       = parse_blocks( $hr_page->post_content );
$hr_seen         = array();
$hr_page_changed = false;

foreach ( $hr_blocks as $hr_index => $hr_block ) {
	$hr_name = (string) $hr_block['blockName'];
	if ( ! isset( $hr_plan[ $hr_name ] ) ) {
		continue;
	}
	$hr_seen[ $hr_name ] = true;

	$hr_data = isset( $hr_block['attrs']['data'] ) && is_array( $hr_block['attrs']['data'] ) ? $hr_block['attrs']['data'] : array();

	foreach ( $hr_plan[ $hr_name ] as $hr_path => $hr_value ) {
		$hr_parts = explode( '.', $hr_path );
		$hr_key   = $hr_parts[0];
		$hr_sub   = $hr_parts[1] ?? null;

		if ( ! array_key_exists( $hr_key, $hr_data ) && isset( $hr_new_fields[ $hr_name ][ $hr_key ] ) ) {
			WP_CLI::log( "[home-redesign] {$hr_name}: new field \"{$hr_key}\"" );
			$hr_data[ $hr_key ]       = '';
			$hr_data[ '_' . $hr_key ] = $hr_new_fields[ $hr_name ][ $hr_key ];
		}

		if ( ! array_key_exists( $hr_key, $hr_data ) ) {
			WP_CLI::warning( "[home-redesign] {$hr_name}: field \"{$hr_key}\" not found in block data, skipped" );
			++$hr_stats['warnings'];
			continue;
		}

		$hr_current = null === $hr_sub ? $hr_data[ $hr_key ] : ( is_array( $hr_data[ $hr_key ] ) ? ( $hr_data[ $hr_key ][ $hr_sub ] ?? null ) : null );

		if ( is_array( $hr_value ) ) {
			$hr_new = $hr_resolve_attachment( $hr_value['file'] );
			if ( 0 === $hr_new ) {
				continue;
			}
			if ( -1 !== $hr_new && (int) $hr_current === $hr_new ) {
				++$hr_stats['skipped'];
				continue;
			}
			$hr_label = -1 === $hr_new ? 'new upload' : "#{$hr_new}";
			WP_CLI::log( "[home-redesign] {$hr_name}: {$hr_path} #" . (int) $hr_current . " → {$hr_label}" );
		} else {
			$hr_new = $hr_value;
			if ( (string) $hr_current === $hr_new ) {
				++$hr_stats['skipped'];
				continue;
			}
			WP_CLI::log( "[home-redesign] {$hr_name}: {$hr_path} \"" . mb_substr( (string) $hr_current, 0, 60 ) . "…\" → \"{$hr_new}\"" );
		}

		++$hr_stats['changed'];
		if ( ! $hr_apply ) {
			continue;
		}

		if ( null === $hr_sub ) {
			$hr_data[ $hr_key ] = $hr_new;
		} else {
			$hr_data[ $hr_key ]            = is_array( $hr_data[ $hr_key ] ) ? $hr_data[ $hr_key ] : array();
			$hr_data[ $hr_key ][ $hr_sub ] = $hr_new;
		}
		$hr_page_changed = true;
	}

	$hr_blocks[ $hr_index ]['attrs']['data'] = $hr_data;
}

foreach ( array_keys( $hr_plan ) as $hr_name ) {
	if ( empty( $hr_seen[ $hr_name ] ) ) {
		WP_CLI::warning( "[home-redesign] block {$hr_name} not found on the front page" );
		++$hr_stats['warnings'];
	}
}

if ( $hr_apply && $hr_page_changed ) {
	$hr_result = wp_update_post(
		array(
			'ID'           => $hr_page_id,
			'post_content' => wp_slash( serialize_blocks( $hr_blocks ) ),
		),
		true
	);

	if ( is_wp_error( $hr_result ) ) {
		WP_CLI::warning( "[home-redesign] front page #{$hr_page_id}: wp_update_post failed — " . $hr_result->get_error_message() );
		++$hr_stats['warnings'];
	} else {
		WP_CLI::log( "[home-redesign] front page #{$hr_page_id} saved" );
	}
}

// --- Options: header CTA -------------------------------------------------

$hr_header_button = function_exists( 'get_field' ) ? get_field( 'header_button', 'options' ) : null;

if ( ! is_array( $hr_header_button ) ) {
	WP_CLI::warning( '[home-redesign] option header_button is empty, skipped' );
	++$hr_stats['warnings'];
} elseif ( ( $hr_header_button['url'] ?? '' ) === '#contact-form' ) {
	++$hr_stats['skipped'];
} else {
	WP_CLI::log( '[home-redesign] option header_button.url "' . ( $hr_header_button['url'] ?? '' ) . '" → "#contact-form"' );
	++$hr_stats['changed'];
	if ( $hr_apply ) {
		$hr_header_button['url'] = '#contact-form';
		update_field( 'header_button', $hr_header_button, 'options' );
	}
}

// --- CF7 form ------------------------------------------------------------

$hr_cf7_script = __DIR__ . '/update-cf7-contact-form.php';
if ( file_exists( $hr_cf7_script ) ) {
	$args = $hr_apply ? array( 'apply' ) : array(); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- Passes the mode to the included CLI script.
	include $hr_cf7_script;
	$args = $hr_args; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- Restore.
} else {
	WP_CLI::warning( "[home-redesign] {$hr_cf7_script} not found, CF7 form not checked" );
	++$hr_stats['warnings'];
}

WP_CLI::success(
	sprintf(
		'[home-redesign] %s: %d change(s), %d already up to date, %d upload(s), %d warning(s).',
		$hr_apply ? 'applied' : 'dry run',
		$hr_stats['changed'],
		$hr_stats['skipped'],
		$hr_stats['uploaded'],
		$hr_stats['warnings']
	)
);
