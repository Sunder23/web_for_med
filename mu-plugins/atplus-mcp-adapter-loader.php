<?php
/**
 * Plugin Name: MCP Adapter loader (atplus-mcp)
 * Description: Loads the WordPress MCP Adapter from mu-plugins/mcp-adapter/ on sites where no active plugin bundles it (e.g. Yoast sites; Rank Math ships its own copy).
 * Version: 1.0.0
 *
 * Deleting this file disables the /wp-json/mcp/ endpoint.
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'WP_MCP_DIR' ) && is_readable( __DIR__ . '/mcp-adapter/mcp-adapter.php' ) ) {
	require_once __DIR__ . '/mcp-adapter/mcp-adapter.php';
}
