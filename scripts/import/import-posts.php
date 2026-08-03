<?php
/**
 * Import blog posts. Idempotent — run via:
 *   wp eval-file /scripts/import/import-posts.php
 *
 * {{image_N}} tokens in post content are replaced with images sideloaded
 * from /import-images/image_N.png (mounted from ref/images/). Re-running
 * reuses already-sideloaded attachments via the _w4m_import_source meta.
 */

require_once __DIR__ . '/lib/common.php';

if ( ! defined( 'W4M_IMPORT_IMAGES_DIR' ) ) {
	define( 'W4M_IMPORT_IMAGES_DIR', getenv( 'W4M_IMPORT_IMAGES_DIR' ) ? getenv( 'W4M_IMPORT_IMAGES_DIR' ) : '/import-images' );
}

/**
 * Find an already-imported attachment by source filename, or sideload it.
 *
 * @param string $filename e.g. "image_1.png".
 * @param int    $post_id  Post to attach the media to.
 * @return int|WP_Error Attachment ID.
 */
function w4m_import_get_attachment( $filename, $post_id ) {
	$existing = get_posts(
		array(
			'post_type'   => 'attachment',
			'post_status' => 'inherit',
			'numberposts' => 1,
			'fields'      => 'ids',
			'meta_key'    => '_w4m_import_source',
			'meta_value'  => $filename,
		)
	);

	if ( ! empty( $existing ) ) {
		return (int) $existing[0];
	}

	$source = trailingslashit( W4M_IMPORT_IMAGES_DIR ) . $filename;

	if ( ! file_exists( $source ) ) {
		return new WP_Error( 'w4m_missing_image', "Image file not found: {$source}" );
	}

	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$tmp = wp_tempnam( $filename );

	if ( ! copy( $source, $tmp ) ) {
		return new WP_Error( 'w4m_copy_failed', "Could not copy {$source} to a temp file." );
	}

	$attachment_id = media_handle_sideload(
		array(
			'name'     => $filename,
			'tmp_name' => $tmp,
		),
		$post_id
	);

	if ( is_wp_error( $attachment_id ) ) {
		@unlink( $tmp );
		return $attachment_id;
	}

	update_post_meta( $attachment_id, '_w4m_import_source', $filename );

	return $attachment_id;
}

/**
 * Replace {{image_N}} tokens with attachment <figure> markup.
 *
 * @param string $content
 * @param int    $post_id
 * @param array  $errors  Collected error messages (by reference).
 * @return string
 */
function w4m_import_resolve_image_tokens( $content, $post_id, array &$errors ) {
	return preg_replace_callback(
		'/\{\{(image_\d+)\}\}/',
		function ( $matches ) use ( $post_id, &$errors ) {
			$attachment_id = w4m_import_get_attachment( $matches[1] . '.png', $post_id );

			if ( is_wp_error( $attachment_id ) ) {
				$errors[] = $attachment_id->get_error_message();
				return '';
			}

			$image = wp_get_attachment_image( $attachment_id, 'large', false, array( 'loading' => 'lazy' ) );

			return '<figure>' . $image . '</figure>';
		},
		$content
	);
}

$w4m_posts   = require __DIR__ . '/data/posts.php';
$w4m_cta_map = w4m_import_load_key_map( 'group_9d4a1b2c' );

$w4m_created = 0;
$w4m_updated = 0;
$w4m_errors  = array();

foreach ( $w4m_posts as $w4m_slug => $w4m_entry ) {
	$w4m_was_created = false;
	$w4m_post_id     = w4m_import_find_or_create_post(
		$w4m_slug,
		'post',
		array(
			'post_title' => $w4m_entry['title'],
			'post_date'  => $w4m_entry['date'],
		),
		$w4m_was_created
	);

	if ( is_wp_error( $w4m_post_id ) ) {
		$w4m_errors[] = "{$w4m_slug}: " . $w4m_post_id->get_error_message();
		continue;
	}

	$w4m_token_errors = array();
	$w4m_content      = w4m_import_resolve_image_tokens( $w4m_entry['content'], $w4m_post_id, $w4m_token_errors );

	foreach ( $w4m_token_errors as $w4m_message ) {
		$w4m_errors[] = "{$w4m_slug}: {$w4m_message}";
	}

	$w4m_result = wp_update_post(
		array(
			'ID'           => $w4m_post_id,
			'post_content' => $w4m_content,
		),
		true
	);

	if ( is_wp_error( $w4m_result ) ) {
		$w4m_errors[] = "{$w4m_slug}: " . $w4m_result->get_error_message();
		continue;
	}

	if ( ! empty( $w4m_entry['fields']['cta'] ) ) {
		$w4m_field_errors = array();
		w4m_import_update_fields( $w4m_post_id, array( 'cta' => $w4m_entry['fields']['cta'] ), 'post', $w4m_cta_map, $w4m_field_errors );

		foreach ( $w4m_field_errors as $w4m_message ) {
			$w4m_errors[] = "{$w4m_slug}: {$w4m_message}";
		}

		WP_CLI::debug( "Post CTA field written for {$w4m_slug}", 'w4m-import' );
	}

	if ( $w4m_was_created ) {
		$w4m_created++;
	} else {
		$w4m_updated++;
	}
}

foreach ( $w4m_errors as $w4m_message ) {
	WP_CLI::warning( $w4m_message );
}

WP_CLI::success( sprintf(
	'Posts import: %d processed (%d created, %d updated), %d error(s).',
	$w4m_created + $w4m_updated,
	$w4m_created,
	$w4m_updated,
	count( $w4m_errors )
) );
