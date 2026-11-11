<?php
/**
 * Bulk-converts existing JPEG/PNG attachments in the media library to WebP.
 *
 * Companion to the theme upload filter (configure/helpers/convert-image-to-webp.php), which
 * only handles new uploads. For every image/jpeg|image/png attachment this script:
 *   1. saves a WebP copy next to the original (quality STARTER_WEBP_UPLOAD_QUALITY, default 88);
 *   2. points the attachment at it (_wp_attached_file, post_mime_type, guid) and regenerates sizes;
 *   3. rewrites old file URLs (full + every size) in post_content and postmeta of other posts;
 *   4. deletes the old original and its sizes — only after steps 2–3 succeeded.
 * Attachment IDs stay the same, so ACF image fields (which store IDs) need no changes.
 * Already-WebP attachments are skipped, so the script is safe to re-run.
 *
 * Run (back up DB and uploads first):
 *   wp eval-file /scripts/convert-media-to-webp.php [dry-run] [ids=12,34]
 *
 * Example:
 *   wp eval-file /scripts/convert-media-to-webp.php dry-run
 *   wp eval-file /scripts/convert-media-to-webp.php --debug   (also prints every URL replacement)
 *
 * @package Vite_Starter
 */

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	echo "This script must be run via: wp eval-file scripts/convert-media-to-webp.php [dry-run] [ids=1,2]\n";
	exit( 1 );
}

require_once ABSPATH . 'wp-admin/includes/image.php';

global $wpdb;

$w4m_cli_args = isset( $args ) ? (array) $args : array();
$dry_run      = in_array( 'dry-run', $w4m_cli_args, true );
$only_ids     = array();

foreach ( $w4m_cli_args as $w4m_arg ) {
	if ( 0 === strpos( $w4m_arg, 'ids=' ) ) {
		$only_ids = array_filter( array_map( 'absint', explode( ',', substr( $w4m_arg, 4 ) ) ) );
	}
}

// Postmeta keys that describe the attachment itself or import provenance — never rewritten.
$skip_meta_keys = array( '_wp_attached_file', '_wp_attachment_metadata', '_w4m_import_source' );
$quality        = defined( 'STARTER_WEBP_UPLOAD_QUALITY' ) ? (int) STARTER_WEBP_UPLOAD_QUALITY : 88;

if ( ! wp_image_editor_supports( array( 'mime_type' => 'image/webp' ) ) ) {
	WP_CLI::error( 'No available image editor (Imagick/GD) supports WebP.' );
}

$query_args = array(
	'post_type'      => 'attachment',
	'post_status'    => 'inherit',
	'post_mime_type' => array( 'image/jpeg', 'image/png' ),
	'posts_per_page' => -1,
	'fields'         => 'ids',
	'orderby'        => 'ID',
	'order'          => 'ASC',
);
if ( $only_ids ) {
	$query_args['post__in'] = $only_ids;
}
$attachment_ids = get_posts( $query_args );

WP_CLI::log( sprintf( '%s: %d JPEG/PNG attachment(s) to convert, quality %d.', $dry_run ? 'DRY RUN' : 'RUN', count( $attachment_ids ), $quality ) );

/**
 * Returns old→new URL pairs as uploads-relative paths, plus their JSON-escaped forms
 * (block attributes store "\/wp-content\/uploads\/…"). Paths match absolute and relative URLs alike.
 *
 * @param array $url_map Old URL => new URL.
 * @return array Search => replace.
 */
function w4m_webp_replacement_pairs( array $url_map ) {
	$pairs = array();
	foreach ( $url_map as $old_url => $new_url ) {
		$old_path = (string) wp_parse_url( $old_url, PHP_URL_PATH );
		$new_path = (string) wp_parse_url( $new_url, PHP_URL_PATH );
		if ( '' === $old_path || $old_path === $new_path ) {
			continue;
		}
		$pairs[ $old_path ]                         = $new_path;
		$pairs[ str_replace( '/', '\/', $old_path ) ] = str_replace( '/', '\/', $new_path );
	}
	return $pairs;
}

/**
 * Recursively replaces strings in a (possibly unserialized) meta value.
 *
 * @param mixed $value Value.
 * @param array $pairs Search => replace.
 * @return mixed
 */
function w4m_webp_replace_deep( $value, array $pairs ) {
	if ( is_string( $value ) ) {
		return strtr( $value, $pairs );
	}
	if ( is_array( $value ) ) {
		foreach ( $value as $key => $item ) {
			$value[ $key ] = w4m_webp_replace_deep( $item, $pairs );
		}
	}
	return $value;
}

/**
 * Rewrites old paths in post_content (all non-attachment posts, revisions included) and postmeta.
 *
 * @param array $pairs          Search => replace.
 * @param array $skip_meta_keys Meta keys to leave untouched.
 * @param bool  $dry_run        Only count matches.
 * @return array{posts: int[], meta: int[]} Affected post IDs and meta IDs.
 */
function w4m_webp_rewrite_references( array $pairs, array $skip_meta_keys, $dry_run ) {
	global $wpdb;

	$affected = array(
		'posts' => array(),
		'meta'  => array(),
	);

	foreach ( array_keys( $pairs ) as $search ) {
		$like = '%' . $wpdb->esc_like( $search ) . '%';

		$post_ids = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_type <> 'attachment' AND post_content LIKE %s", $like ) );
		foreach ( $post_ids as $post_id ) {
			$affected['posts'][ (int) $post_id ] = (int) $post_id;
			WP_CLI::debug( "post #{$post_id}: {$search} → {$pairs[ $search ]}", 'webp' );
		}

		$skip_in = implode( ',', array_fill( 0, count( $skip_meta_keys ), '%s' ) );
		$rows    = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT meta_id, post_id, meta_key FROM {$wpdb->postmeta} WHERE meta_value LIKE %s AND meta_key NOT IN ($skip_in)", // phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $skip_in holds only %s placeholders.
				array_merge( array( $like ), $skip_meta_keys )
			)
		);
		foreach ( $rows as $row ) {
			$affected['meta'][ (int) $row->meta_id ] = (int) $row->meta_id;
			WP_CLI::debug( "meta #{$row->meta_id} (post #{$row->post_id}, {$row->meta_key}): {$search}", 'webp' );
		}
	}

	if ( $dry_run ) {
		return $affected;
	}

	foreach ( $affected['posts'] as $post_id ) {
		$content = $wpdb->get_var( $wpdb->prepare( "SELECT post_content FROM {$wpdb->posts} WHERE ID = %d", $post_id ) );
		$wpdb->update( $wpdb->posts, array( 'post_content' => strtr( (string) $content, $pairs ) ), array( 'ID' => $post_id ) );
		clean_post_cache( $post_id );
	}

	foreach ( $affected['meta'] as $meta_id ) {
		$row = $wpdb->get_row( $wpdb->prepare( "SELECT post_id, meta_value FROM {$wpdb->postmeta} WHERE meta_id = %d", $meta_id ) );
		if ( ! $row ) {
			continue;
		}
		$value = w4m_webp_replace_deep( maybe_unserialize( $row->meta_value ), $pairs );
		$wpdb->update( $wpdb->postmeta, array( 'meta_value' => maybe_serialize( $value ) ), array( 'meta_id' => $meta_id ) ); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
		wp_cache_delete( (int) $row->post_id, 'post_meta' );
	}

	return $affected;
}

$stats = array(
	'converted'   => 0,
	'skipped'     => 0,
	'failed'      => 0,
	'bytes_old'   => 0,
	'bytes_new'   => 0,
	'posts'       => array(),
	'meta'        => array(),
);

foreach ( $attachment_ids as $attachment_id ) {
	$old_file = get_attached_file( $attachment_id );

	if ( ! $old_file || ! file_exists( $old_file ) ) {
		WP_CLI::warning( "#{$attachment_id}: file missing on disk ({$old_file}), skipped." );
		++$stats['skipped'];
		continue;
	}

	$old_mime = get_post_mime_type( $attachment_id );
	$old_guid = get_post_field( 'guid', $attachment_id, 'raw' );
	$old_meta = wp_get_attachment_metadata( $attachment_id );
	$old_meta = is_array( $old_meta ) ? $old_meta : array();
	$old_url  = wp_get_attachment_url( $attachment_id );
	$dir      = dirname( $old_file );
	$base_url = substr( $old_url, 0, -strlen( basename( $old_file ) ) );

	// Old files (absolute paths) to delete afterwards, and old size URLs keyed by size name.
	$old_files     = array( $old_file );
	$old_size_urls = array();
	foreach ( ( $old_meta['sizes'] ?? array() ) as $size_name => $size ) {
		if ( empty( $size['file'] ) ) {
			continue;
		}
		$old_files[]                 = trailingslashit( $dir ) . $size['file'];
		$old_size_urls[ $size_name ] = $base_url . $size['file'];
	}
	if ( ! empty( $old_meta['original_image'] ) ) {
		$old_files[] = trailingslashit( $dir ) . $old_meta['original_image'];
	}
	$old_files = array_unique( $old_files );

	$new_name = preg_replace( '/\.(jpe?g|png)$/i', '.webp', basename( $old_file ) );
	$new_path = trailingslashit( $dir ) . wp_unique_filename( $dir, $new_name );
	$old_size = (int) filesize( $old_file );

	if ( $dry_run ) {
		$pairs    = w4m_webp_replacement_pairs( array_fill_keys( array_merge( array( $old_url ), array_values( $old_size_urls ) ), $base_url . basename( $new_path ) ) );
		$affected = w4m_webp_rewrite_references( $pairs, $skip_meta_keys, true );
		WP_CLI::log( sprintf( '#%d %s → %s (%s, %d size file(s); refs: %d post(s), %d meta)', $attachment_id, basename( $old_file ), basename( $new_path ), size_format( $old_size ), count( $old_files ) - 1, count( $affected['posts'] ), count( $affected['meta'] ) ) );
		$stats['posts'] += $affected['posts'];
		$stats['meta']  += $affected['meta'];
		++$stats['converted'];
		continue;
	}

	$editor = wp_get_image_editor( $old_file );
	if ( is_wp_error( $editor ) ) {
		WP_CLI::warning( "#{$attachment_id}: cannot open {$old_file}: " . $editor->get_error_message() );
		++$stats['failed'];
		continue;
	}
	$editor->set_quality( $quality );

	$saved = $editor->save( $new_path, 'image/webp' );
	clearstatcache();
	// GD can "succeed" with an empty file (e.g. palette PNGs), so the size is checked too.
	if ( is_wp_error( $saved ) || empty( $saved['path'] ) || ! file_exists( $saved['path'] ) || 0 === filesize( $saved['path'] ) ) {
		if ( ! is_wp_error( $saved ) && ! empty( $saved['path'] ) ) {
			wp_delete_file( $saved['path'] );
		}
		WP_CLI::warning( "#{$attachment_id}: cannot save WebP: " . ( is_wp_error( $saved ) ? $saved->get_error_message() : 'empty or missing file' ) );
		++$stats['failed'];
		continue;
	}
	$new_path = $saved['path'];

	update_attached_file( $attachment_id, $new_path );
	$wpdb->update(
		$wpdb->posts,
		array(
			'post_mime_type' => 'image/webp',
			'guid'           => $base_url . basename( $new_path ),
		),
		array( 'ID' => $attachment_id )
	);
	clean_post_cache( $attachment_id );

	$new_meta = wp_generate_attachment_metadata( $attachment_id, $new_path );
	if ( empty( $new_meta ) ) {
		// Roll back the pointer so the attachment keeps working with the untouched original.
		update_attached_file( $attachment_id, $old_file );
		$wpdb->update(
			$wpdb->posts,
			array(
				'post_mime_type' => $old_mime,
				'guid'           => $old_guid,
			),
			array( 'ID' => $attachment_id )
		);
		clean_post_cache( $attachment_id );
		wp_delete_file( $new_path );
		WP_CLI::warning( "#{$attachment_id}: metadata generation failed, rolled back." );
		++$stats['failed'];
		continue;
	}
	wp_update_attachment_metadata( $attachment_id, $new_meta );

	// Old URL → new URL: full file, then each size by name (falling back to the new full file).
	$new_url = wp_get_attachment_url( $attachment_id );
	$url_map = array( $old_url => $new_url );
	foreach ( $old_size_urls as $size_name => $size_url ) {
		$url_map[ $size_url ] = ! empty( $new_meta['sizes'][ $size_name ]['file'] ) ? $base_url . $new_meta['sizes'][ $size_name ]['file'] : $new_url;
	}

	$affected        = w4m_webp_rewrite_references( w4m_webp_replacement_pairs( $url_map ), $skip_meta_keys, false );
	$stats['posts'] += $affected['posts'];
	$stats['meta']  += $affected['meta'];

	foreach ( $old_files as $file_to_delete ) {
		if ( file_exists( $file_to_delete ) && $file_to_delete !== $new_path ) {
			wp_delete_file( $file_to_delete );
			WP_CLI::debug( "deleted {$file_to_delete}", 'webp' );
		}
	}

	$new_size              = (int) filesize( $new_path );
	$stats['bytes_old']   += $old_size;
	$stats['bytes_new']   += $new_size;
	++$stats['converted'];

	WP_CLI::log( sprintf( '#%d %s → %s, %s → %s (refs: %d post(s), %d meta)', $attachment_id, basename( $old_file ), basename( $new_path ), size_format( $old_size ), size_format( $new_size ), count( $affected['posts'] ), count( $affected['meta'] ) ) );
}

$summary = sprintf(
	'%s %d, skipped %d, failed %d; references in %d post(s)/revision(s), %d meta row(s).',
	$dry_run ? 'Would convert' : 'Converted',
	$stats['converted'],
	$stats['skipped'],
	$stats['failed'],
	count( $stats['posts'] ),
	count( $stats['meta'] )
);
if ( ! $dry_run && $stats['bytes_old'] > 0 ) {
	$summary .= sprintf( ' Originals %s → WebP %s.', size_format( $stats['bytes_old'] ), size_format( $stats['bytes_new'] ) );
}

if ( $stats['failed'] > 0 ) {
	WP_CLI::warning( $summary );
} else {
	WP_CLI::success( $summary );
}
