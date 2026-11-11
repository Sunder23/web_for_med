<?php
/**
 * Prepare case content for the new /cases/ archive (Figma huDLClWg3dIpXyzLMuVHEs, node 3025:141).
 *
 * - Promedi (#566): published, menu_order = 1, `case_archive` title / description
 *   from the mockup, cover from scripts/data/cases-archive/promedi-cover.png
 *   (Figma node 3025:173).
 * - Every other published case: menu_order 2…N (newest first), only when it is
 *   still 0; `case_archive` title / description get a placeholder only when empty.
 *
 * Idempotent: the cover is sideloaded once and tagged with `_starter_source`
 * (`cases-archive/<file>`); values that already match are skipped.
 *
 * Args:
 *   (none)  dry run — logs what would change, writes nothing;
 *   apply   upload media and write the changes.
 *
 * Run: docker compose -f docker/compose.yml run --rm wpcli wp eval-file /scripts/update-cases-archive.php [apply]
 * (On Windows/Git Bash, prefix with MSYS_NO_PATHCONV=1.)
 *
 * @package Vite_Starter
 */

if ( ! defined( 'WP_CLI' ) ) {
	echo "This script must be run via WP-CLI (wp eval-file).\n";
	exit( 1 );
}

if ( ! function_exists( 'update_field' ) ) {
	WP_CLI::error( '[cases-archive] ACF / SCF is not active.' );
}

$ca_args  = isset( $args ) && is_array( $args ) ? $args : array();
$ca_apply = in_array( 'apply', $ca_args, true );

$ca_promedi_id = 566;
$ca_data_dir   = __DIR__ . '/data/cases-archive/';
$ca_group_key  = array(
	'group'       => 'field_a3c1e7b1',
	'title'       => 'field_a3c1e7b2',
	'description' => 'field_a3c1e7b3',
	'cover'       => 'field_a3c1e7b4',
);
$ca_promedi    = array(
	'title'       => 'Як спроєктувати медичний сайт без галузевих шаблонів. Досвід Promedi',
	'description' => 'Показуємо, як зрозуміла навігація, точні тексти та лаконічний дизайн допомагають формувати довіру до медичного центру й перетворювати інтерес до лікування на звернення.',
	'cover'       => $ca_data_dir . 'promedi-cover.png',
);
$ca_placeholder = 'Заголовок і опис буде пізніше';

$ca_stats = array(
	'updated'  => 0,
	'skipped'  => 0,
	'uploaded' => 0,
	'warnings' => 0,
);

WP_CLI::log( '[cases-archive] start' . ( $ca_apply ? '' : ' (dry run)' ) );

/**
 * Resolve a file to an attachment ID: reuse a tagged one or (apply mode) sideload it.
 *
 * @param string $file Absolute path.
 * @return int Attachment ID, -1 when it would be uploaded (dry run), 0 on failure.
 */
$ca_resolve_attachment = static function ( $file ) use ( $ca_apply, &$ca_stats ) {
	$source = 'cases-archive/' . basename( $file );
	$found  = get_posts(
		array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_key'       => '_starter_source', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- One-off CLI script.
			'meta_value'     => $source, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- One-off CLI script.
		)
	);
	if ( $found ) {
		return (int) $found[0];
	}

	if ( ! file_exists( $file ) ) {
		WP_CLI::warning( "[cases-archive] source file missing: {$file}" );
		++$ca_stats['warnings'];
		return 0;
	}

	if ( ! $ca_apply ) {
		WP_CLI::log( "[cases-archive] would upload {$source}" );
		return -1;
	}

	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	// media_handle_sideload() moves the file, so hand it a temp copy.
	$tmp = wp_tempnam( basename( $file ) );
	if ( ! $tmp || ! copy( $file, $tmp ) ) {
		WP_CLI::warning( "[cases-archive] cannot copy {$file} to a temp file" );
		++$ca_stats['warnings'];
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
		WP_CLI::warning( "[cases-archive] upload failed for {$source}: " . $id->get_error_message() );
		++$ca_stats['warnings'];
		return 0;
	}

	update_post_meta( $id, '_starter_source', $source );
	update_post_meta( $id, '_wp_attachment_image_alt', '' );
	++$ca_stats['uploaded'];
	WP_CLI::log( "[cases-archive] uploaded {$source} → #{$id}" );
	return (int) $id;
};

/**
 * Write the `case_archive` sub-fields of one case.
 *
 * @param int   $post_id   Case ID.
 * @param array $values    Sub-field name => new value.
 * @param bool  $only_empty Write a value only when the current one is empty.
 * @return string[] Names of the changed sub-fields.
 */
$ca_update_archive = static function ( $post_id, array $values, $only_empty ) use ( $ca_apply, $ca_group_key ) {
	$current = get_field( 'case_archive', $post_id, false );
	$current = is_array( $current ) ? $current : array();
	$merged  = array(
		'title'       => (string) ( $current[ $ca_group_key['title'] ] ?? '' ),
		'description' => (string) ( $current[ $ca_group_key['description'] ] ?? '' ),
		'cover'       => (int) ( $current[ $ca_group_key['cover'] ] ?? 0 ),
	);
	$changed = array();

	foreach ( $values as $name => $value ) {
		$old = $merged[ $name ];
		if ( $old === $value || ( $only_empty && ! empty( $old ) ) ) {
			continue;
		}

		$merged[ $name ] = $value;
		$changed[]       = $name;
	}

	// Sub-fields of a group are saved through the group (meta `case_archive_<name>`);
	// updating a sub-field key directly would write a bare `<name>` meta key.
	if ( $changed && $ca_apply ) {
		update_field( $ca_group_key['group'], $merged, $post_id );
	}

	return $changed;
};

// --- Promedi --------------------------------------------------------------

$ca_post = get_post( $ca_promedi_id );

if ( ! $ca_post || 'cases' !== $ca_post->post_type ) {
	WP_CLI::warning( "[cases-archive] Promedi case #{$ca_promedi_id} not found, skipped" );
	++$ca_stats['warnings'];
} else {
	$ca_changes = array();

	if ( 'publish' !== $ca_post->post_status || 1 !== (int) $ca_post->menu_order ) {
		$ca_changes[] = 'status=publish, menu_order=1';
		if ( $ca_apply ) {
			$ca_result = wp_update_post(
				array(
					'ID'          => $ca_promedi_id,
					'post_status' => 'publish',
					'menu_order'  => 1,
				),
				true
			);
			if ( is_wp_error( $ca_result ) ) {
				WP_CLI::warning( "[cases-archive] #{$ca_promedi_id}: wp_update_post failed — " . $ca_result->get_error_message() );
				++$ca_stats['warnings'];
			}
		}
	}

	$ca_cover_id = $ca_resolve_attachment( $ca_promedi['cover'] );
	$ca_values   = array(
		'title'       => $ca_promedi['title'],
		'description' => $ca_promedi['description'],
	);
	if ( $ca_cover_id > 0 ) {
		$ca_values['cover'] = $ca_cover_id;
	} elseif ( -1 === $ca_cover_id ) {
		$ca_changes[] = 'cover (pending upload)';
	}

	$ca_changes = array_merge( $ca_changes, $ca_update_archive( $ca_promedi_id, $ca_values, false ) );

	if ( $ca_changes ) {
		++$ca_stats['updated'];
		WP_CLI::log( "[cases-archive] #{$ca_promedi_id} Promedi: " . implode( ', ', $ca_changes ) );
	} else {
		++$ca_stats['skipped'];
		WP_CLI::log( "[cases-archive] #{$ca_promedi_id} Promedi: up to date" );
	}
}

// --- Other published cases --------------------------------------------------

$ca_others = get_posts(
	array(
		'post_type'      => 'cases',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'post__not_in'   => array( $ca_promedi_id ),
		'orderby'        => array(
			'menu_order' => 'ASC',
			'date'       => 'DESC',
		),
	)
);

$ca_order = 2;
foreach ( $ca_others as $ca_other ) {
	$ca_changes = array();

	if ( 0 === (int) $ca_other->menu_order ) {
		$ca_changes[] = "menu_order={$ca_order}";
		if ( $ca_apply ) {
			$ca_result = wp_update_post(
				array(
					'ID'         => $ca_other->ID,
					'menu_order' => $ca_order,
				),
				true
			);
			if ( is_wp_error( $ca_result ) ) {
				WP_CLI::warning( "[cases-archive] #{$ca_other->ID}: wp_update_post failed — " . $ca_result->get_error_message() );
				++$ca_stats['warnings'];
			}
		}
	}
	++$ca_order;

	$ca_changes = array_merge(
		$ca_changes,
		$ca_update_archive(
			$ca_other->ID,
			array(
				'title'       => $ca_placeholder,
				'description' => $ca_placeholder,
			),
			true
		)
	);

	if ( $ca_changes ) {
		++$ca_stats['updated'];
		WP_CLI::log( "[cases-archive] #{$ca_other->ID} {$ca_other->post_title}: " . implode( ', ', $ca_changes ) );
	} else {
		++$ca_stats['skipped'];
	}
}

WP_CLI::success(
	sprintf(
		'[cases-archive] %s: updated %d, skipped %d, uploaded %d, warnings %d',
		$ca_apply ? 'done' : 'dry run',
		$ca_stats['updated'],
		$ca_stats['skipped'],
		$ca_stats['uploaded'],
		$ca_stats['warnings']
	)
);
