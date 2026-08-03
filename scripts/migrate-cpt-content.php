<?php
/**
 * Generic ACF content-migration runner: writes structured field data (authored per post,
 * restructuring existing content into the new field shapes) into named ACF fields for
 * services/directions/cases via update_field().
 *
 * This is the opposite direction of scripts/migrate-acf-to-blocks.php (which flattens ACF
 * fields into post_content block markup) — here we write structured data INTO ACF fields
 * that are still defined in acf-json, so update_field() (not raw postmeta) is used.
 *
 * Data files return: array( '<post_slug>' => array( '<acf_field_name>' => <value>, ... ), ... )
 * Only field names present in a post's array are written; any other existing fields on
 * that post are left untouched.
 *
 * Run:
 *   wp eval-file scripts/migrate-cpt-content.php <post_type> <data-file> [dry-run]
 *
 * Example:
 *   wp eval-file scripts/migrate-cpt-content.php directions scripts/data/directions-content.php dry-run
 */

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	echo "This script must be run via: wp eval-file scripts/migrate-cpt-content.php <post_type> <data-file> [dry-run]\n";
	exit( 1 );
}

$w4m_cli_args = isset( $args ) ? (array) $args : array();
$post_type    = $w4m_cli_args[0] ?? '';
$data_file    = $w4m_cli_args[1] ?? '';
$dry_run      = in_array( 'dry-run', $w4m_cli_args, true );

if ( '' === $post_type || '' === $data_file ) {
	WP_CLI::error( 'Usage: wp eval-file scripts/migrate-cpt-content.php <post_type> <data-file> [dry-run]' );
}

if ( ! file_exists( $data_file ) ) {
	WP_CLI::error( "Data file not found: {$data_file}" );
}

$content_map = require $data_file;

if ( ! is_array( $content_map ) ) {
	WP_CLI::error( 'Data file must return an array keyed by post slug.' );
}

/**
 * Loosely compare a written value against what get_field() reads back. WYSIWYG sub-fields
 * get normalized on save (wpautop adds trailing newlines, etc.), so an exact === compare
 * would false-flag a correct write; this only cares that content matches after trimming.
 */
function w4m_loosely_equal( $expected, $actual ) {
	// ACF returns false (not []) for an empty repeater — both mean "no rows".
	if ( is_array( $expected ) && empty( $expected ) && empty( $actual ) ) {
		return true;
	}

	if ( is_array( $expected ) && is_array( $actual ) ) {
		// Only the keys we actually set are checked — ACF fills in the full group
		// schema (e.g. unset sub-fields/sub-groups) when reading a group back, so
		// requiring exact key parity would false-flag a correct, partial write.
		foreach ( $expected as $key => $value ) {
			if ( ! array_key_exists( $key, $actual ) || ! w4m_loosely_equal( $value, $actual[ $key ] ) ) {
				return false;
			}
		}
		return true;
	}

	if ( is_array( $expected ) !== is_array( $actual ) ) {
		return false;
	}

	$normalize = static function ( $str ) {
		$str = preg_replace( '/\s+/', ' ', (string) $str );
		$str = preg_replace( '/>\s+</', '><', $str );
		return trim( $str );
	};

	return $normalize( $expected ) === $normalize( $actual );
}

$updated       = 0;
$missing       = 0;
$failed_fields = 0;

foreach ( $content_map as $slug => $fields ) {
	$post = get_page_by_path( $slug, OBJECT, $post_type );

	if ( ! $post ) {
		WP_CLI::warning( "No {$post_type} post found for slug \"{$slug}\" — skipped." );
		$missing++;
		continue;
	}

	$label = "{$post_type}/{$slug} (#{$post->ID})";
	WP_CLI::log( "Processing {$label}: " . count( $fields ) . ' field(s).' );

	foreach ( $fields as $field_name => $value ) {
		if ( $dry_run ) {
			WP_CLI::log( "  [dry-run] would write \"{$field_name}\" (" . strlen( wp_json_encode( $value ) ) . ' bytes).' );
			continue;
		}

		// Note: update_field() returns an unreliable boolean for "group"-type fields
		// (ACF's Group field update_value() does not consistently return true even on a
		// successful write), so success is verified by re-reading the field instead of
		// trusting the return value.
		update_field( $field_name, $value, $post->ID );
		$written = get_field( $field_name, $post->ID );

		if ( ! w4m_loosely_equal( $value, $written ) ) {
			WP_CLI::warning( "  Failed to write \"{$field_name}\" for {$label}." );
			$failed_fields++;
			continue;
		}

		error_log( "[W4M migrate-cpt-content] wrote {$field_name} for {$label}" );
		WP_CLI::log( "  Wrote \"{$field_name}\"." );
	}

	$updated++;
}

WP_CLI::success( sprintf(
	'%s: %d post(s) processed%s, %d not found, %d field write failure(s).',
	$post_type,
	$updated,
	$dry_run ? ' (dry-run)' : '',
	$missing,
	$failed_fields
) );
