<?php
/**
 * Theme file.
 *
 * @package Vite_Starter
 */

/**
 * Converts an uploaded JPEG/PNG image to WebP and replaces it in the upload result.
 *
 * Hooked to `wp_handle_upload` from configure/optimize.php (flag STARTER_CONVERT_UPLOADS_TO_WEBP).
 *
 * @param array $file Upload data as produced by `wp_handle_upload`.
 * @return array Modified upload data pointing at the WebP file, or the original data.
 */
function starter_convert_image_to_webp( array $file ) {
	if ( empty( $file['type'] ) || ! in_array( $file['type'], array( 'image/jpeg', 'image/png' ), true ) ) {
		return $file;
	}

	if ( ! wp_image_editor_supports( array( 'mime_type' => 'image/webp' ) ) ) {
		error_log( 'WARN [webp] no image editor supports WebP, keeping original: ' . basename( $file['file'] ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		return $file;
	}

	$editor = wp_get_image_editor( $file['file'] );

	if ( is_wp_error( $editor ) ) {
		error_log( 'WARN [webp] cannot open ' . basename( $file['file'] ) . ': ' . $editor->get_error_message() ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		return $file;
	}

	$dir      = dirname( $file['file'] );
	$new_name = preg_replace( '/\.(jpe?g|png)$/i', '.webp', basename( $file['file'] ) );
	$new_path = trailingslashit( $dir ) . wp_unique_filename( $dir, $new_name );

	$saved = $editor->save( $new_path, 'image/webp' );

	clearstatcache();
	if ( is_wp_error( $saved ) || empty( $saved['path'] ) || ! file_exists( $saved['path'] ) || 0 === filesize( $saved['path'] ) ) {
		$reason = is_wp_error( $saved ) ? $saved->get_error_message() : 'empty or missing file';
		if ( ! is_wp_error( $saved ) && ! empty( $saved['path'] ) ) {
			wp_delete_file( $saved['path'] );
		}
		error_log( 'WARN [webp] cannot save ' . basename( $new_path ) . ': ' . $reason ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		return $file;
	}

	wp_delete_file( $file['file'] );

	$old_basename = basename( $file['file'] );
	$new_basename = basename( $saved['path'] );

	$file['file'] = $saved['path'];
	$file['url']  = substr( $file['url'], 0, -strlen( $old_basename ) ) . $new_basename;
	$file['type'] = 'image/webp';

	return $file;
}
