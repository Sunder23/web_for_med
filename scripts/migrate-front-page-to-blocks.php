<?php
/**
 * Migrate the front page from the old `front_page_*` ACF fields (rendered by the
 * removed front-page.php) to acf/section-* blocks in post_content.
 *
 * - Reads raw postmeta (not the ACF API): a field group stored on the page is
 *   `front_page_hero_title` / `_front_page_hero_title` (+ nested repeater keys),
 *   which is exactly the ACF block `data` format once the group prefix is cut.
 *   Old field keys are translated to the new `field_starter_section_*` keys by
 *   walking the old group JSON (configure/acf/acf-json/group_57587b53.json)
 *   against the same names path the new groups were generated from.
 * - `front_page_contact` is copied to the ACF Options field `footer_contact`
 *   (only when the option is still empty).
 * - Non-empty old post_content is backed up to `_starter_pre_blocks_content`.
 * - A page that already contains acf/section-* blocks is skipped (re-running is safe).
 *
 * Args:
 *   (none)   dry run — logs what would happen, writes nothing;
 *   apply    write post_content and the footer_contact option;
 *   cleanup  (only with apply) delete `front_page_*` meta of the page and its revisions;
 *   page=ID  migrate this page instead of page_on_front (testing on a copy).
 *
 * NOTE: needs group_57587b53.json (removed after the migration; restore it from git history to re-run).
 *
 * Run: docker compose -f docker/compose.yml run --rm wpcli wp eval-file /scripts/migrate-front-page-to-blocks.php [apply] [cleanup] [page=ID]
 * (On Windows/Git Bash, prefix with MSYS_NO_PATHCONV=1 so /scripts/... is not
 * mangled into a Windows path.)
 *
 * @package Vite_Starter
 */

if ( ! defined( 'WP_CLI' ) ) {
	echo "This script must be run via WP-CLI (wp eval-file).\n";
	exit( 1 );
}

$migrate_args    = isset( $args ) && is_array( $args ) ? $args : array();
$migrate_apply   = in_array( 'apply', $migrate_args, true );
$migrate_cleanup = $migrate_apply && in_array( 'cleanup', $migrate_args, true );
$migrate_page_id = (int) get_option( 'page_on_front' );

foreach ( $migrate_args as $migrate_arg ) {
	if ( 0 === strpos( (string) $migrate_arg, 'page=' ) ) {
		$migrate_page_id = (int) substr( (string) $migrate_arg, 5 );
	}
}

if ( in_array( 'cleanup', $migrate_args, true ) && ! $migrate_apply ) {
	WP_CLI::warning( '[migrate] `cleanup` is ignored without `apply`.' );
}

WP_CLI::log( '[migrate] mode: ' . ( $migrate_apply ? 'APPLY' : 'DRY RUN' ) . ( $migrate_cleanup ? ' + CLEANUP' : '' ) . ", page #{$migrate_page_id}" );

kses_remove_filters();

$migrate_registry = WP_Block_Type_Registry::get_instance();

if ( ! $migrate_registry->is_registered( 'acf/section-home-hero' ) ) {
	WP_CLI::error( '[migrate] acf/section-* blocks are not registered — is the theme active and configure/section-blocks.php loaded?' );
}

$migrate_old_json = get_stylesheet_directory() . '/configure/acf/acf-json/group_57587b53.json';

if ( ! file_exists( $migrate_old_json ) ) {
	WP_CLI::error( '[migrate] old field group group_57587b53.json is missing — it is needed to translate field keys.' );
}

$migrate_old_group = json_decode( (string) file_get_contents( $migrate_old_json ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Local theme file.
$migrate_old       = array();

foreach ( $migrate_old_group['fields'] as $migrate_field ) {
	if ( ! empty( $migrate_field['name'] ) ) {
		$migrate_old[ $migrate_field['name'] ] = $migrate_field;
	}
}

/**
 * Section => sources. A `group` source flattens the group's sub fields to the
 * block root; a `field` source maps one old top-level field to a new name.
 */
$migrate_sections = array(
	'home-hero'          => array( array( 'group', 'front_page_hero' ) ),
	'clinics'            => array( array( 'group', 'front_page_clinics' ) ),
	'quote'              => array( array( 'group', 'front_page_quote', array( 'text' ) ) ),
	'problems-solutions' => array(
		array( 'field', 'front_page_problems', 'problems' ),
		array( 'field', 'front_page_banner_text', 'banner_text' ),
		array( 'field', 'front_page_solutions', 'solutions' ),
	),
	'services'           => array( array( 'group', 'front_page_services' ) ),
	'cases'              => array( array( 'group', 'front_page_cases' ) ),
	'process'            => array( array( 'group', 'front_page_process' ) ),
	'why'                => array( array( 'group', 'front_page_why' ) ),
);

/**
 * Walks an old field definition and records old key => new key, deriving the new
 * key from the names path exactly as the section groups were generated.
 *
 * @param array  $field   Old field definition.
 * @param string $slug_u  Section slug with underscores.
 * @param array  $path    Names path of the parents.
 * @param array  $map     Accumulated old key => new key map.
 * @param string $renamed Optional new name for the field.
 * @return void
 */
function migrate_front_page_map_keys( $field, $slug_u, $path, &$map, $renamed = '' ) {
	$name   = '' !== $renamed ? $renamed : $field['name'];
	$path[] = $name;

	$map[ $field['key'] ] = 'field_starter_section_' . $slug_u . '_' . implode( '_', $path );

	foreach ( $field['sub_fields'] ?? array() as $sub ) {
		migrate_front_page_map_keys( $sub, $slug_u, $path, $map );
	}
}

/**
 * Builds a parsed-block array for an ACF section block.
 *
 * @param string $slug Section slug.
 * @param array  $data ACF block data (name => value, _name => field key).
 * @return array Block array for serialize_blocks().
 */
function migrate_front_page_make_block( $slug, $data ) {
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
}

/**
 * Deletes the front_page_* meta (values + field-key references) of a page and its revisions.
 *
 * @param int $page_id Page ID.
 * @return void
 */
function migrate_front_page_cleanup_meta( $page_id ) {
	$deleted  = 0;
	$post_ids = array_merge( array( $page_id ), wp_get_post_revisions( $page_id, array( 'fields' => 'ids' ) ) );

	foreach ( $post_ids as $post_id ) {
		foreach ( array_keys( get_post_meta( $post_id ) ) as $meta_key ) {
			$meta_key = (string) $meta_key;

			if ( 0 === strpos( $meta_key, 'front_page_' ) || 0 === strpos( $meta_key, '_front_page_' ) ) {
				delete_metadata( 'post', $post_id, $meta_key );
				++$deleted;
			}
		}
	}

	WP_CLI::log( "[migrate] page #{$page_id}: cleanup removed {$deleted} front_page_* meta keys (incl. " . ( count( $post_ids ) - 1 ) . ' revisions)' );
}

$migrate_page = $migrate_page_id ? get_post( $migrate_page_id ) : null;

if ( ! $migrate_page || 'page' !== $migrate_page->post_type ) {
	WP_CLI::error( "[migrate] page #{$migrate_page_id} not found (set page_on_front or pass page=ID)." );
}

$migrate_stats = array(
	'migrated' => 0,
	'skipped'  => 0,
	'warnings' => 0,
);

$migrate_meta = get_post_meta( $migrate_page_id );

// ---- footer contact -> Options.
$migrate_contact = array();
foreach ( array( 'title', 'text', 'contact_form' ) as $migrate_contact_key ) {
	$migrate_contact_value = isset( $migrate_meta[ 'front_page_contact_' . $migrate_contact_key ][0] ) ? (string) $migrate_meta[ 'front_page_contact_' . $migrate_contact_key ][0] : '';

	if ( '' !== $migrate_contact_value ) {
		$migrate_contact[ $migrate_contact_key ] = $migrate_contact_value;
	}
}

$migrate_existing_contact = function_exists( 'get_field' ) ? get_field( 'footer_contact', 'options' ) : array();

if ( empty( $migrate_contact ) ) {
	WP_CLI::warning( '[migrate] no front_page_contact meta found — footer_contact option not touched' );
	++$migrate_stats['warnings'];
} elseif ( ! empty( $migrate_existing_contact['title'] ) || ! empty( $migrate_existing_contact['text'] ) || ! empty( $migrate_existing_contact['contact_form'] ) ) {
	WP_CLI::log( '[migrate] footer_contact option already filled — left untouched' );
} else {
	WP_CLI::log( '[migrate] footer_contact <- front_page_contact (' . count( $migrate_contact ) . ' fields)' );

	if ( $migrate_apply ) {
		update_field( 'footer_contact', $migrate_contact, 'options' );
	}
}

// ---- sections -> blocks.
if ( false !== strpos( $migrate_page->post_content, '<!-- wp:acf/section-' ) ) {
	WP_CLI::log( "[migrate] page #{$migrate_page_id}: skip: already has section blocks" );
	++$migrate_stats['skipped'];

	if ( $migrate_cleanup ) {
		migrate_front_page_cleanup_meta( $migrate_page_id );
	}

	WP_CLI::success( "[migrate] {$migrate_stats['migrated']} migrated, {$migrate_stats['skipped']} skipped, {$migrate_stats['warnings']} warnings" );
	return;
}

$migrate_blocks = array();

foreach ( $migrate_sections as $migrate_slug => $migrate_sources ) {
	$migrate_slug_u = str_replace( '-', '_', $migrate_slug );
	$migrate_data   = array();
	$migrate_keymap = array();
	$migrate_rules  = array(); // old meta prefix => new name prefix.

	foreach ( $migrate_sources as $migrate_source ) {
		$migrate_old_field = $migrate_old[ $migrate_source[1] ] ?? null;

		if ( ! $migrate_old_field ) {
			WP_CLI::warning( "[migrate] section {$migrate_slug}: old field {$migrate_source[1]} not in group JSON" );
			++$migrate_stats['warnings'];
			continue;
		}

		if ( 'group' === $migrate_source[0] ) {
			foreach ( $migrate_old_field['sub_fields'] as $migrate_sub ) {
				if ( isset( $migrate_source[2] ) && ! in_array( $migrate_sub['name'], $migrate_source[2], true ) ) {
					continue;
				}

				migrate_front_page_map_keys( $migrate_sub, $migrate_slug_u, array(), $migrate_keymap );
				$migrate_rules[ $migrate_source[1] . '_' . $migrate_sub['name'] ] = $migrate_sub['name'];
			}
		} else {
			migrate_front_page_map_keys( $migrate_old_field, $migrate_slug_u, array(), $migrate_keymap, $migrate_source[2] );
			$migrate_rules[ $migrate_source[1] ] = $migrate_source[2];
		}
	}

	foreach ( $migrate_meta as $migrate_meta_key => $migrate_meta_values ) {
		$migrate_meta_key = (string) $migrate_meta_key;
		$migrate_is_ref   = 0 === strpos( $migrate_meta_key, '_' );
		$migrate_bare     = $migrate_is_ref ? substr( $migrate_meta_key, 1 ) : $migrate_meta_key;

		foreach ( $migrate_rules as $migrate_old_prefix => $migrate_new_prefix ) {
			if ( $migrate_bare !== $migrate_old_prefix && 0 !== strpos( $migrate_bare, $migrate_old_prefix . '_' ) ) {
				continue;
			}

			$migrate_new_name = $migrate_new_prefix . substr( $migrate_bare, strlen( $migrate_old_prefix ) );
			$migrate_value    = isset( $migrate_meta_values[0] ) ? maybe_unserialize( $migrate_meta_values[0] ) : '';

			if ( $migrate_is_ref ) {
				if ( isset( $migrate_keymap[ $migrate_value ] ) ) {
					$migrate_data[ '_' . $migrate_new_name ] = $migrate_keymap[ $migrate_value ];
				}
			} else {
				$migrate_data[ $migrate_new_name ] = $migrate_value;
			}
			break;
		}
	}

	// Keep only values whose field-key reference could be translated.
	foreach ( array_keys( $migrate_data ) as $migrate_data_key ) {
		if ( 0 !== strpos( $migrate_data_key, '_' ) && ! isset( $migrate_data[ '_' . $migrate_data_key ] ) ) {
			unset( $migrate_data[ $migrate_data_key ] );
		}
	}

	ksort( $migrate_data );

	$migrate_field_count = count(
		array_filter(
			array_keys( $migrate_data ),
			static function ( $migrate_key ) {
				return 0 !== strpos( (string) $migrate_key, '_' );
			}
		)
	);

	if ( 0 === $migrate_field_count ) {
		WP_CLI::warning( "[migrate] section {$migrate_slug}: no old meta found — block skipped" );
		++$migrate_stats['warnings'];
		continue;
	}

	WP_CLI::log( "[migrate]   {$migrate_slug}: {$migrate_field_count} fields" );
	$migrate_blocks[] = migrate_front_page_make_block( $migrate_slug, $migrate_data );
}

if ( empty( $migrate_blocks ) ) {
	WP_CLI::warning( "[migrate] page #{$migrate_page_id}: no blocks built — page left untouched" );
	++$migrate_stats['warnings'];
	++$migrate_stats['skipped'];
	WP_CLI::success( "[migrate] {$migrate_stats['migrated']} migrated, {$migrate_stats['skipped']} skipped, {$migrate_stats['warnings']} warnings" );
	return;
}

$migrate_new_content = serialize_blocks( $migrate_blocks );

WP_CLI::log( "[migrate] page #{$migrate_page_id} '{$migrate_page->post_title}': " . count( $migrate_blocks ) . ' blocks (' . strlen( $migrate_new_content ) . ' bytes)' );

if ( $migrate_apply ) {
	if ( '' !== trim( $migrate_page->post_content ) ) {
		update_post_meta( $migrate_page_id, '_starter_pre_blocks_content', wp_slash( $migrate_page->post_content ) );
		WP_CLI::log( "[migrate] INFO page #{$migrate_page_id}: old post_content backed up to _starter_pre_blocks_content" );
	}

	$migrate_result = wp_update_post(
		array(
			'ID'           => $migrate_page_id,
			'post_content' => wp_slash( $migrate_new_content ),
		),
		true
	);

	if ( is_wp_error( $migrate_result ) ) {
		WP_CLI::warning( "[migrate] page #{$migrate_page_id}: wp_update_post failed — " . $migrate_result->get_error_message() );
		++$migrate_stats['warnings'];
	} else {
		++$migrate_stats['migrated'];

		if ( $migrate_cleanup ) {
			migrate_front_page_cleanup_meta( $migrate_page_id );
		}
	}
} else {
	++$migrate_stats['migrated'];
}

WP_CLI::success( "[migrate] {$migrate_stats['migrated']} migrated, {$migrate_stats['skipped']} skipped, {$migrate_stats['warnings']} warnings" . ( $migrate_apply ? '' : ' (dry run, nothing written)' ) );
