<?php
/**
 * Shared helpers for wp-cli content import scripts (wp eval-file).
 *
 * Field values are written with update_field() by FIELD KEYS. Key maps are
 * built at runtime from the theme acf-json group files, so the scripts stay
 * in sync with the SCF field groups without hardcoding keys.
 */

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	echo "This script must be run via: wp eval-file <script>\n";
	exit( 1 );
}

if ( ! function_exists( 'update_field' ) ) {
	WP_CLI::error( 'SCF/ACF is not active — update_field() is unavailable. Activate the plugin first.' );
}

/**
 * Load a field group JSON from the theme acf-json dir and return a
 * recursive name => array( key, type, sub ) map.
 *
 * @param string $group_id e.g. "group_9033ee91".
 * @return array
 */
function w4m_import_load_key_map( $group_id ) {
	$file = get_template_directory() . '/acf-json/' . $group_id . '.json';

	if ( ! file_exists( $file ) ) {
		WP_CLI::error( "Field group JSON not found: {$file}" );
	}

	$group = json_decode( file_get_contents( $file ), true );

	if ( ! is_array( $group ) || empty( $group['fields'] ) ) {
		WP_CLI::error( "Field group JSON is invalid or has no fields: {$file}" );
	}

	return w4m_import_build_key_map( $group['fields'] );
}

/**
 * Recursively build name => array( key, type, sub ) from an SCF fields array.
 * Fields without a name (tabs) are skipped.
 *
 * @param array $fields
 * @return array
 */
function w4m_import_build_key_map( array $fields ) {
	$map = array();

	foreach ( $fields as $field ) {
		if ( empty( $field['name'] ) || empty( $field['key'] ) ) {
			continue;
		}

		$entry = array(
			'key'  => $field['key'],
			'type' => isset( $field['type'] ) ? $field['type'] : '',
		);

		if ( ! empty( $field['sub_fields'] ) && is_array( $field['sub_fields'] ) ) {
			$entry['sub'] = w4m_import_build_key_map( $field['sub_fields'] );
		}

		$map[ $field['name'] ] = $entry;
	}

	return $map;
}

/**
 * Convert a name-keyed value into a field-key-keyed value according to a
 * key-map entry (groups: assoc by name; repeaters: list of name-keyed rows).
 *
 * @param mixed $value
 * @param array $entry  Key-map entry for this field.
 * @param array $errors Collected error messages (by reference).
 * @param string $path  Human-readable field path for error messages.
 * @return mixed
 */
function w4m_import_convert_value( $value, array $entry, array &$errors, $path ) {
	if ( empty( $entry['sub'] ) || ! is_array( $value ) ) {
		return $value;
	}

	if ( 'group' === $entry['type'] ) {
		return w4m_import_convert_names( $value, $entry['sub'], $errors, $path );
	}

	if ( 'repeater' === $entry['type'] ) {
		$rows = array();
		foreach ( $value as $i => $row ) {
			$rows[] = w4m_import_convert_names( (array) $row, $entry['sub'], $errors, "{$path}[{$i}]" );
		}
		return $rows;
	}

	return $value;
}

/**
 * Convert a name-keyed assoc array into a key-keyed one using a sub-map.
 *
 * @param array  $values
 * @param array  $map
 * @param array  $errors
 * @param string $path
 * @return array
 */
function w4m_import_convert_names( array $values, array $map, array &$errors, $path ) {
	$out = array();

	foreach ( $values as $name => $value ) {
		if ( ! isset( $map[ $name ] ) ) {
			$errors[] = "Unknown field name \"{$path}.{$name}\" — not found in field group JSON.";
			continue;
		}
		$out[ $map[ $name ]['key'] ] = w4m_import_convert_value( $value, $map[ $name ], $errors, "{$path}.{$name}" );
	}

	return $out;
}

/**
 * Find a post by slug within a post type, create or update it. Idempotent:
 * re-running never creates duplicates.
 *
 * @param string $slug
 * @param string $post_type
 * @param array  $args    Additional wp_insert_post/wp_update_post args.
 * @param bool   $created Set to true when a new post was created (by reference).
 * @return int|WP_Error Post ID.
 */
function w4m_import_find_or_create_post( $slug, $post_type, array $args, &$created ) {
	$created  = false;
	$existing = get_page_by_path( $slug, OBJECT, $post_type );

	$args = array_merge(
		array(
			'post_name'   => $slug,
			'post_type'   => $post_type,
			'post_status' => 'publish',
		),
		$args
	);

	if ( $existing instanceof WP_Post ) {
		$args['ID'] = $existing->ID;
		if ( ! empty( $args['post_date'] ) ) {
			$args['edit_date'] = true;
		}
		return wp_update_post( $args, true );
	}

	$created = true;
	return wp_insert_post( $args, true );
}

/**
 * Write all SCF fields of one imported entry by field keys.
 *
 * @param int    $post_id
 * @param array  $fields  Name-keyed data (top-level names WITHOUT the CPT prefix).
 * @param string $prefix  Field name prefix, e.g. "service" -> "service_hero".
 * @param array  $map     Key map from w4m_import_load_key_map().
 * @param array  $errors  Collected error messages (by reference).
 */
function w4m_import_update_fields( $post_id, array $fields, $prefix, array $map, array &$errors ) {
	foreach ( $fields as $name => $value ) {
		$full_name = $prefix . '_' . $name;

		if ( ! isset( $map[ $full_name ] ) ) {
			$errors[] = "Unknown top-level field \"{$full_name}\" — not found in field group JSON.";
			continue;
		}

		$entry     = $map[ $full_name ];
		$converted = w4m_import_convert_value( $value, $entry, $errors, $full_name );

		update_field( $entry['key'], $converted, $post_id );
	}
}

/**
 * Run a full import for one CPT: iterate data entries, create/update posts,
 * write fields by keys, print errors and a single summary line.
 *
 * @param string $label     Human label for the summary, e.g. "Services".
 * @param string $post_type
 * @param string $prefix    SCF field name prefix, e.g. "service".
 * @param string $group_id  acf-json group id, e.g. "group_9033ee91".
 * @param array  $data      slug => array( title, fields ) entries.
 */
function w4m_import_run_cpt( $label, $post_type, $prefix, $group_id, array $data ) {
	$map     = w4m_import_load_key_map( $group_id );
	$created = 0;
	$updated = 0;
	$errors  = array();

	foreach ( $data as $slug => $entry ) {
		$was_created = false;
		$post_id     = w4m_import_find_or_create_post(
			$slug,
			$post_type,
			array( 'post_title' => $entry['title'] ),
			$was_created
		);

		if ( is_wp_error( $post_id ) ) {
			$errors[] = "{$slug}: " . $post_id->get_error_message();
			continue;
		}

		$field_errors = array();
		w4m_import_update_fields( $post_id, $entry['fields'], $prefix, $map, $field_errors );

		foreach ( $field_errors as $message ) {
			$errors[] = "{$slug}: {$message}";
		}

		if ( $was_created ) {
			$created++;
		} else {
			$updated++;
		}
	}

	foreach ( $errors as $message ) {
		WP_CLI::warning( $message );
	}

	$total = $created + $updated;
	WP_CLI::success( sprintf(
		'%s import: %d processed (%d created, %d updated), %d error(s).',
		$label,
		$total,
		$created,
		$updated,
		count( $errors )
	) );
}
