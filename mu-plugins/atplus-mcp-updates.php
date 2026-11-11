<?php
/**
 * Plugin Name: AT Plus MCP Updates
 * Description: Safe updates for atplus-mcp: lists core/plugin/theme updates, copies the current version aside before updating and puts it back on request. A one-time recovery token restores a backup even when the site no longer boots with its plugins. Records PHP fatal errors.
 * Version: 1.0.0
 *
 * Must-use plugin, used together with atplus-mcp-abilities.php. Backups live in
 * wp-content/atplus-mcp-backups/ for ATPLUS_MCP_BACKUP_DAYS days. Files only: database
 * changes made by an update are not reverted.
 */

defined( 'ABSPATH' ) || exit;

const ATPLUS_MCP_BACKUP_DAYS = 7;
const ATPLUS_MCP_ITEM_PATTERN = '/^[A-Za-z0-9._-]+(\/[A-Za-z0-9._-]+)?$/';

// Fatal errors of any request are kept (last 20) so the MCP server can tell whether an update broke
// wp-admin, cron or AJAX even though the public pages still render.
register_shutdown_function(
	static function (): void {
		$error = error_get_last();
		if ( ! $error || ! in_array( $error['type'], array( E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR, E_RECOVERABLE_ERROR ), true ) ) {
			return;
		}
		try {
			$log   = (array) get_option( 'atplus_mcp_fatals', array() );
			$log[] = array(
				'time'    => time(),
				'message' => substr( (string) strtok( (string) $error['message'], "\n" ), 0, 300 ),
				'file'    => str_replace( ABSPATH, '', (string) $error['file'] ),
				'line'    => (int) $error['line'],
				'uri'     => substr( (string) ( $_SERVER['REQUEST_URI'] ?? '' ), 0, 200 ),
			);
			update_option( 'atplus_mcp_fatals', array_slice( $log, -20 ), false );
		} catch ( Throwable $e ) {
			return;
		}
	}
);

function atplus_mcp_backup_root(): string {
	return WP_CONTENT_DIR . '/atplus-mcp-backups';
}

function atplus_mcp_copy( string $from, string $to ): bool {
	if ( is_link( $from ) ) {
		return @symlink( (string) readlink( $from ), $to );
	}
	if ( is_file( $from ) ) {
		return @copy( $from, $to );
	}
	if ( ! is_dir( $to ) && ! @mkdir( $to, 0755, true ) ) {
		return false;
	}
	foreach ( (array) scandir( $from ) as $name ) {
		if ( '.' !== $name && '..' !== $name && ! atplus_mcp_copy( $from . '/' . $name, $to . '/' . $name ) ) {
			return false;
		}
	}
	return true;
}

function atplus_mcp_delete( string $path ): bool {
	if ( is_link( $path ) || is_file( $path ) ) {
		return @unlink( $path );
	}
	if ( ! is_dir( $path ) ) {
		return true;
	}
	foreach ( (array) scandir( $path ) as $name ) {
		if ( '.' !== $name && '..' !== $name ) {
			atplus_mcp_delete( $path . '/' . $name );
		}
	}
	return @rmdir( $path );
}

// rename() fails across filesystems (symlinked plugin directories), so fall back to copying.
function atplus_mcp_move( string $from, string $to ): bool {
	return @rename( $from, $to ) || ( atplus_mcp_copy( $from, $to ) && atplus_mcp_delete( $from ) );
}

// Directory (or single file) that holds a plugin or theme. The item is validated against
// ATPLUS_MCP_ITEM_PATTERN by every caller, so it cannot leave the plugins/themes directory.
function atplus_mcp_update_target( string $type, string $item ): string {
	if ( 'theme' === $type ) {
		return get_theme_root( $item ) . '/' . $item;
	}
	return WP_PLUGIN_DIR . '/' . ( '.' === dirname( $item ) ? $item : dirname( $item ) );
}

function atplus_mcp_installed_version( string $type, string $item ) {
	if ( 'core' === $type ) {
		require ABSPATH . WPINC . '/version.php';
		return $wp_version;
	}
	$file = 'theme' === $type ? atplus_mcp_update_target( $type, $item ) . '/style.css' : WP_PLUGIN_DIR . '/' . $item;
	if ( ! is_readable( $file ) ) {
		return null;
	}
	return get_file_data( $file, array( 'Version' => 'Version' ) )['Version'];
}

function atplus_mcp_read_manifest( string $id ) {
	if ( ! preg_match( '/^[a-f0-9]{32}$/', $id ) ) {
		return null;
	}
	$manifest = json_decode( (string) @file_get_contents( atplus_mcp_backup_root() . '/' . $id . '/manifest.json' ), true );
	return is_array( $manifest ) && ( $manifest['id'] ?? '' ) === $id && ( 'core' === $manifest['type'] || preg_match( ATPLUS_MCP_ITEM_PATTERN, (string) $manifest['item'] ) ) ? $manifest : null;
}

function atplus_mcp_write_manifest( array $manifest ): bool {
	return false !== @file_put_contents( atplus_mcp_backup_root() . '/' . $manifest['id'] . '/manifest.json', wp_json_encode( $manifest ) );
}

function atplus_mcp_list_backups(): array {
	$out = array();
	foreach ( (array) @scandir( atplus_mcp_backup_root() ) as $id ) {
		$manifest = atplus_mcp_read_manifest( (string) $id );
		if ( $manifest ) {
			unset( $manifest['tokenHash'] );
			$out[] = $manifest;
		}
	}
	return $out;
}

function atplus_mcp_prune_backups(): void {
	$root = atplus_mcp_backup_root();
	foreach ( (array) @scandir( $root ) as $id ) {
		if ( ! preg_match( '/^[a-f0-9]{32}$/', (string) $id ) ) {
			continue;
		}
		$manifest = atplus_mcp_read_manifest( $id );
		if ( ( $manifest['created'] ?? (int) @filemtime( $root . '/' . $id ) ) < time() - ATPLUS_MCP_BACKUP_DAYS * DAY_IN_SECONDS ) {
			atplus_mcp_delete( $root . '/' . $id );
		}
	}
}

function atplus_mcp_create_backup( string $type, string $item, string $version, array $input ) {
	$root = atplus_mcp_backup_root();
	$id   = preg_match( '/^[a-f0-9]{32}$/', (string) ( $input['backupId'] ?? '' ) ) ? $input['backupId'] : bin2hex( random_bytes( 16 ) );
	$dir  = $root . '/' . $id;
	if ( file_exists( $dir ) || ! wp_mkdir_p( $dir . '/files' ) ) {
		return new WP_Error( 'atplus_mcp_backup', 'Cannot create the backup directory; nothing was updated.' );
	}
	if ( ! file_exists( $root . '/.htaccess' ) ) {
		@file_put_contents( $root . '/.htaccess', "Require all denied\n" );
		@file_put_contents( $root . '/index.php', "<?php\n" );
	}
	if ( 'core' !== $type ) {
		$target = atplus_mcp_update_target( $type, $item );
		if ( ! atplus_mcp_copy( $target, $dir . '/files/' . basename( $target ) ) ) {
			atplus_mcp_delete( $dir );
			return new WP_Error( 'atplus_mcp_backup', 'Cannot copy the current version (disk space or permissions); nothing was updated.' );
		}
	}
	$manifest = array(
		'id'        => $id,
		'type'      => $type,
		'item'      => $item,
		'version'   => $version,
		'locale'    => get_locale(),
		'active'    => 'plugin' === $type && in_array( $item, (array) get_option( 'active_plugins', array() ), true ),
		'created'   => time(),
		'restored'  => 0,
		'tokenHash' => preg_match( '/^[a-f0-9]{64}$/', (string) ( $input['tokenHash'] ?? '' ) ) ? $input['tokenHash'] : '',
	);
	if ( ! atplus_mcp_write_manifest( $manifest ) ) {
		atplus_mcp_delete( $dir );
		return new WP_Error( 'atplus_mcp_backup', 'Cannot write the backup manifest; nothing was updated.' );
	}
	return $manifest;
}

function atplus_mcp_updates_load_admin(): void {
	require_once ABSPATH . 'wp-admin/includes/admin.php';
	require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
}

function atplus_mcp_branch( string $version ): string {
	return implode( '.', array_slice( explode( '.', $version ), 0, 2 ) );
}

// Newest offers from WordPress.org: within the installed branch (minor) and beyond it (major).
function atplus_mcp_core_offers(): array {
	$current = atplus_mcp_installed_version( 'core', '' );
	$offers  = array( 'minor' => null, 'major' => null );
	$updates = get_site_transient( 'update_core' );
	foreach ( (array) ( $updates->updates ?? array() ) as $update ) {
		if ( ! in_array( $update->response ?? '', array( 'upgrade', 'autoupdate' ), true ) || empty( $update->packages->full ) || ! version_compare( $update->version, $current, '>' ) ) {
			continue;
		}
		$kind = atplus_mcp_branch( $update->version ) === atplus_mcp_branch( $current ) ? 'minor' : 'major';
		if ( ! $offers[ $kind ] || version_compare( $update->version, $offers[ $kind ]->version, '>' ) ) {
			$offers[ $kind ] = $update;
		}
	}
	return $offers;
}

function atplus_mcp_update_status( bool $refresh ): array {
	atplus_mcp_updates_load_admin();
	if ( $refresh ) {
		wp_version_check( array(), true );
		wp_update_plugins();
		wp_update_themes();
	}
	$offers    = atplus_mcp_core_offers();
	$installed = get_plugins();
	$active    = (array) get_option( 'active_plugins', array() );
	$plugins   = array();
	foreach ( (array) ( get_site_transient( 'update_plugins' )->response ?? array() ) as $file => $info ) {
		if ( isset( $installed[ $file ] ) ) {
			$plugins[] = array(
				'file'       => $file,
				'name'       => $installed[ $file ]['Name'],
				'version'    => $installed[ $file ]['Version'],
				'newVersion' => $info->new_version ?? null,
				'active'     => in_array( $file, $active, true ),
				'canUpdate'  => ! empty( $info->package ),
			);
		}
	}
	$all_themes = wp_get_themes();
	$themes     = array();
	foreach ( (array) ( get_site_transient( 'update_themes' )->response ?? array() ) as $stylesheet => $info ) {
		if ( isset( $all_themes[ $stylesheet ] ) ) {
			$themes[] = array(
				'stylesheet' => $stylesheet,
				'name'       => $all_themes[ $stylesheet ]->get( 'Name' ),
				'version'    => $all_themes[ $stylesheet ]->get( 'Version' ),
				'newVersion' => $info['new_version'] ?? null,
				'active'     => in_array( $stylesheet, array( get_stylesheet(), get_template() ), true ),
				'canUpdate'  => ! empty( $info['package'] ),
			);
		}
	}
	return array(
		'home'        => home_url( '/' ),
		'php'         => PHP_VERSION,
		'multisite'   => is_multisite(),
		'filesystem'  => get_filesystem_method(),
		'maintenance' => file_exists( ABSPATH . '.maintenance' ),
		'core'        => array(
			'version' => atplus_mcp_installed_version( 'core', '' ),
			'minor'   => $offers['minor']->version ?? null,
			'major'   => $offers['major']->version ?? null,
		),
		'plugins'     => $plugins,
		'themes'      => $themes,
		'versions'    => array(
			'plugins' => array_map( static fn( $plugin ) => $plugin['Version'], $installed ),
			'themes'  => array_map( static fn( $theme ) => $theme->get( 'Version' ), $all_themes ),
		),
		'fatals'      => array_values( (array) get_option( 'atplus_mcp_fatals', array() ) ),
		'backups'     => atplus_mcp_list_backups(),
	);
}

function atplus_mcp_upgrade_error( $result, WP_Ajax_Upgrader_Skin $skin, string $item ) {
	if ( is_array( $result ) && array_key_exists( $item, $result ) ) {
		$result = $result[ $item ];
	}
	foreach ( array( $result, $skin->result ) as $value ) {
		if ( is_wp_error( $value ) ) {
			return $value->get_error_message();
		}
	}
	if ( $skin->get_errors()->has_errors() ) {
		return implode( '; ', $skin->get_errors()->get_error_messages() );
	}
	return false === $result || null === $result ? 'The update did not run (WordPress could not write its files or found no package).' : null;
}

function atplus_mcp_update_apply( array $input ) {
	$type = (string) $input['type'];
	$item = 'core' === $type ? '' : (string) ( $input['item'] ?? '' );
	if ( is_multisite() ) {
		return new WP_Error( 'atplus_mcp_multisite', 'Multisite is not supported.' );
	}
	if ( 'core' !== $type && ! preg_match( ATPLUS_MCP_ITEM_PATTERN, $item ) ) {
		return new WP_Error( 'atplus_mcp_item', 'Pass the plugin file (akismet/akismet.php) or the theme stylesheet (twentytwentyfive) as item.' );
	}
	atplus_mcp_updates_load_admin();
	if ( 'direct' !== get_filesystem_method() ) {
		return new WP_Error( 'atplus_mcp_filesystem', 'WordPress cannot write its own files on this hosting (it asks for FTP credentials), so it cannot be updated from here.' );
	}
	ignore_user_abort( true );
	if ( function_exists( 'set_time_limit' ) ) {
		@set_time_limit( 600 );
	}
	atplus_mcp_prune_backups();

	$offer = null;
	if ( 'core' === $type ) {
		wp_version_check();
		$offers = atplus_mcp_core_offers();
		$offer  = ! empty( $input['allowMajor'] ) && $offers['major'] ? $offers['major'] : $offers['minor'];
		if ( ! $offer ) {
			return new WP_Error( 'atplus_mcp_no_update', $offers['major'] ? 'Only a major WordPress update (' . $offers['major']->version . ') is available; pass allowMajor to install it.' : 'WordPress is up to date.' );
		}
	} else {
		$available = (array) ( get_site_transient( 'update_' . $type . 's' )->response ?? array() );
		if ( ! isset( $available[ $item ] ) ) {
			// A rollback drops the cached list of updates; ask WordPress.org again.
			'plugin' === $type ? wp_update_plugins() : wp_update_themes();
			$available = (array) ( get_site_transient( 'update_' . $type . 's' )->response ?? array() );
		}
		$package   = isset( $available[ $item ] ) ? ( (array) $available[ $item ] )['package'] ?? '' : '';
		if ( ! $package ) {
			return new WP_Error( 'atplus_mcp_no_update', isset( $available[ $item ] ) ? 'The update has no download package (a licence is probably required).' : 'No update is available for ' . $item . '.' );
		}
	}
	$from = atplus_mcp_installed_version( $type, $item );
	if ( null === $from ) {
		return new WP_Error( 'atplus_mcp_item', 'Not installed: ' . $item );
	}
	$manifest = atplus_mcp_create_backup( $type, $item, $from, $input );
	if ( is_wp_error( $manifest ) ) {
		return $manifest;
	}

	$skin   = new WP_Ajax_Upgrader_Skin();
	$result = null;
	$thrown = null;
	ob_start(); // Upgraders may print; stray output would corrupt the MCP response.
	try {
		if ( 'core' === $type ) {
			$result = ( new Core_Upgrader( $skin ) )->upgrade( $offer );
		} elseif ( 'plugin' === $type ) {
			// bulk_upgrade keeps an active plugin active (upgrade() would deactivate it).
			$result = ( new Plugin_Upgrader( $skin ) )->bulk_upgrade( array( $item ) );
		} else {
			$result = ( new Theme_Upgrader( $skin ) )->bulk_upgrade( array( $item ) );
		}
	} catch ( Throwable $e ) {
		$thrown = $e->getMessage();
	}
	ob_end_clean();

	$to    = atplus_mcp_installed_version( $type, $item );
	$error = $thrown ?? atplus_mcp_upgrade_error( $result, $skin, $item );
	if ( $to === $from ) {
		atplus_mcp_delete( atplus_mcp_backup_root() . '/' . $manifest['id'] );
	}
	return array(
		'ok'       => null !== $to && $to !== $from,
		'type'     => $type,
		'item'     => $item,
		'from'     => $from,
		'to'       => $to,
		'backupId' => $to === $from ? null : $manifest['id'],
		'error'    => $error ? substr( wp_strip_all_tags( $error ), 0, 500 ) : null,
		'messages' => array_slice( array_map( 'wp_strip_all_tags', $skin->get_upgrade_messages() ), -8 ),
	);
}

// Installs an exact WordPress release over the current files, the same way "Re-install" does.
function atplus_mcp_core_reinstall( string $version, string $locale ) {
	if ( ! preg_match( '/^\d+(\.\d+){1,2}$/', $version ) || ! preg_match( '/^[A-Za-z_]{2,10}$/', $locale ) ) {
		return new WP_Error( 'atplus_mcp_backup', 'Invalid core backup.' );
	}
	atplus_mcp_updates_load_admin();
	$package = 'https://downloads.wordpress.org/release/' . ( 'en_US' === $locale ? '' : $locale . '/' ) . 'wordpress-' . $version . '.zip';
	$offer   = (object) array(
		'response'        => 'reinstall',
		'download'        => $package,
		'locale'          => $locale,
		'current'         => $version,
		'version'         => $version,
		'php_version'     => PHP_VERSION,
		'mysql_version'   => '5.0',
		'new_bundled'     => '',
		'partial_version' => '',
		'packages'        => (object) array( 'full' => $package, 'no_content' => false, 'new_bundled' => false, 'partial' => false, 'rollback' => false ),
	);
	ob_start();
	try {
		$result = ( new Core_Upgrader( new WP_Ajax_Upgrader_Skin() ) )->upgrade( $offer );
	} catch ( Throwable $e ) {
		$result = new WP_Error( 'atplus_mcp_core', $e->getMessage() );
	}
	ob_end_clean();
	return $result;
}

function atplus_mcp_update_restore( array $manifest ) {
	$type = $manifest['type'];
	$item = (string) $manifest['item'];
	if ( empty( $manifest['restored'] ) ) {
		if ( 'core' === $type ) {
			$result = atplus_mcp_core_reinstall( (string) $manifest['version'], (string) $manifest['locale'] );
			if ( is_wp_error( $result ) ) {
				return $result;
			}
		} else {
			$target = atplus_mcp_update_target( $type, $item );
			$dir    = atplus_mcp_backup_root() . '/' . $manifest['id'];
			$source = $dir . '/files/' . basename( $target );
			if ( ! file_exists( $source ) ) {
				return new WP_Error( 'atplus_mcp_backup', 'The backup has no files.' );
			}
			if ( file_exists( $target ) && ! atplus_mcp_move( $target, $dir . '/replaced' ) ) {
				return new WP_Error( 'atplus_mcp_restore', 'Cannot move the updated version away (permissions).' );
			}
			if ( ! atplus_mcp_move( $source, $target ) ) {
				atplus_mcp_move( $dir . '/replaced', $target );
				return new WP_Error( 'atplus_mcp_restore', 'Cannot put the previous version back (permissions).' );
			}
			// A failed update may have deactivated the plugin; it was already activated before, so no hooks.
			$active = (array) get_option( 'active_plugins', array() );
			if ( ! empty( $manifest['active'] ) && ! in_array( $item, $active, true ) ) {
				$active[] = $item;
				sort( $active );
				update_option( 'active_plugins', $active );
			}
			delete_site_transient( 'update_' . $type . 's' );
		}
		@unlink( ABSPATH . '.maintenance' );
		if ( function_exists( 'opcache_reset' ) ) {
			@opcache_reset();
		}
		$manifest['restored'] = time();
		atplus_mcp_write_manifest( $manifest );
	}
	return array(
		'restored' => true,
		'type'     => $type,
		'item'     => $item,
		'version'  => atplus_mcp_installed_version( $type, $item ),
	);
}

// Recovery: POST / with "X-Atplus-Recovery: <backupId>.<token>". The token was chosen by the MCP
// server before the update and only its hash is stored here; it can do nothing but restore that one
// backup. Must-use plugins load before regular plugins and the theme, so this still works when one of
// them throws a fatal error on every request.
function atplus_mcp_recovery_respond( $result ): void {
	http_response_code( is_wp_error( $result ) ? 500 : 200 );
	header( 'Content-Type: application/json; charset=utf-8' );
	header( 'Cache-Control: no-store' );
	echo json_encode( is_wp_error( $result ) ? array( 'restored' => false, 'error' => $result->get_error_message() ) : $result );
	exit;
}

if ( isset( $_SERVER['HTTP_X_ATPLUS_RECOVERY'] ) && 'POST' === ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) {
	$atplus_mcp_recovery = null;
	if ( preg_match( '/^([a-f0-9]{32})\.([a-f0-9]{64})$/', (string) $_SERVER['HTTP_X_ATPLUS_RECOVERY'], $atplus_mcp_match ) ) {
		$atplus_mcp_recovery = atplus_mcp_read_manifest( $atplus_mcp_match[1] );
		if ( $atplus_mcp_recovery && ( empty( $atplus_mcp_recovery['tokenHash'] ) || ! hash_equals( $atplus_mcp_recovery['tokenHash'], hash( 'sha256', $atplus_mcp_match[2] ) ) || $atplus_mcp_recovery['created'] < time() - ATPLUS_MCP_BACKUP_DAYS * DAY_IN_SECONDS ) ) {
			$atplus_mcp_recovery = null;
		}
	}
	if ( ! $atplus_mcp_recovery ) {
		http_response_code( 403 );
		exit;
	}
	if ( 'core' !== $atplus_mcp_recovery['type'] ) {
		atplus_mcp_recovery_respond( atplus_mcp_update_restore( $atplus_mcp_recovery ) );
	}
	// Reinstalling core needs a booted WordPress: finish loading it without plugins and without a theme.
	add_filter( 'pre_option_active_plugins', '__return_empty_array' );
	add_filter( 'pre_option_template', static fn() => 'atplus-mcp-recovery' );
	add_filter( 'pre_option_stylesheet', static fn() => 'atplus-mcp-recovery' );
	add_action( 'init', static fn() => atplus_mcp_recovery_respond( atplus_mcp_update_restore( $atplus_mcp_recovery ) ), 0 );
}

add_action(
	'wp_abilities_api_init',
	static function (): void {
		if ( ! function_exists( 'atplus_mcp_meta' ) ) {
			return;
		}
		$backup_id = array( 'type' => 'string', 'pattern' => '^[a-f0-9]{32}$' );

		wp_register_ability(
			'atplus/update-status',
			array(
				'label'               => 'Available updates',
				'description'         => 'Lists available WordPress core (minor and major), plugin and theme updates, installed versions, recent PHP fatal errors and the backups kept by atplus/update-apply. refresh=false skips asking WordPress.org.',
				'category'            => 'atplus-admin',
				'input_schema'        => array(
					'type'                 => 'object',
					'properties'           => array( 'refresh' => array( 'type' => 'boolean', 'default' => true ) ),
					'additionalProperties' => false,
				),
				'execute_callback'    => static fn( $input ) => atplus_mcp_update_status( (bool) ( $input['refresh'] ?? true ) ),
				'permission_callback' => 'atplus_mcp_can_manage',
				'meta'                => atplus_mcp_meta( true, false ),
			)
		);

		wp_register_ability(
			'atplus/update-apply',
			array(
				'label'               => 'Update core, a plugin or a theme',
				'description'         => 'Copies the current version to wp-content/atplus-mcp-backups, then updates one plugin (item = plugin file), one theme (item = stylesheet) or WordPress core (minor release; allowMajor for the next major). Returns the versions and a backupId for atplus/update-rollback. Does not check that the site still works: the MCP server tool wp_update does that and rolls back automatically.',
				'category'            => 'atplus-admin',
				'input_schema'        => array(
					'type'                 => 'object',
					'properties'           => array(
						'type'       => array( 'type' => 'string', 'enum' => array( 'core', 'plugin', 'theme' ) ),
						'item'       => array( 'type' => 'string', 'maxLength' => 200 ),
						'allowMajor' => array( 'type' => 'boolean' ),
						'backupId'   => $backup_id,
						'tokenHash'  => array( 'type' => 'string', 'pattern' => '^[a-f0-9]{64}$' ),
					),
					'required'             => array( 'type' ),
					'additionalProperties' => false,
				),
				'execute_callback'    => static fn( $input ) => atplus_mcp_update_apply( (array) $input ),
				'permission_callback' => 'atplus_mcp_can_manage',
				'meta'                => atplus_mcp_meta( false, true ),
			)
		);

		wp_register_ability(
			'atplus/update-rollback',
			array(
				'label'               => 'Roll back an update',
				'description'         => 'Puts back the plugin or theme files saved by atplus/update-apply, or reinstalls the previous WordPress release for a core backup. Files only; database changes made by the update stay.',
				'category'            => 'atplus-admin',
				'input_schema'        => array(
					'type'                 => 'object',
					'properties'           => array( 'backupId' => $backup_id ),
					'required'             => array( 'backupId' ),
					'additionalProperties' => false,
				),
				'execute_callback'    => static function ( $input ) {
					$manifest = atplus_mcp_read_manifest( (string) $input['backupId'] );
					if ( ! $manifest ) {
						return new WP_Error( 'atplus_mcp_backup', 'Backup not found (backups are kept for ' . ATPLUS_MCP_BACKUP_DAYS . ' days).' );
					}
					ignore_user_abort( true );
					if ( function_exists( 'set_time_limit' ) ) {
						@set_time_limit( 600 );
					}
					return atplus_mcp_update_restore( $manifest );
				},
				'permission_callback' => 'atplus_mcp_can_manage',
				'meta'                => atplus_mcp_meta( false, true ),
			)
		);
	}
);
