<?php
/**
 * Theme file.
 *
 * @package Vite_Starter
 */

/**
 * Singular views whose main body is rendered by the_content() with Gutenberg blocks.
 *
 * Core block assets (wp-block-library, layout support) must stay enabled here.
 *
 * @return bool Whether the current view renders block content.
 */
function starter_is_block_content_view() {
	return is_singular( array( 'post', 'services', 'directions', 'cases' ) );
}
