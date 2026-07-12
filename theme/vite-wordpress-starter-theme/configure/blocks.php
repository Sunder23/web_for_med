<?php

/**
 * ACF block registration.
 *
 * Scans acf-blocks/<block>/block.json and registers each block via
 * register_block_type(). Each block folder may ship a <block>.scss next to
 * its block.json; that stylesheet is registered under the handle "<block>"
 * (referenced by "style" in block.json) with a Vite-aware URI:
 * - dev:  served by the Vite dev server (compiled SCSS, same as main.scss)
 * - prod: hashed CSS resolved through the build manifest
 *
 * WordPress loads the registered style on the frontend and inside the editor
 * iframe automatically, so registration must NOT be behind is_admin().
 */

function custom_theme_register_acf_blocks()
{
    $blocks_dir = get_template_directory() . '/acf-blocks';

    if (! is_dir($blocks_dir)) {
        return;
    }

    $block_json_files = glob($blocks_dir . '/*/block.json');
    if (empty($block_json_files)) {
        return;
    }

    foreach ($block_json_files as $block_json) {
        $block_name = basename(dirname($block_json));
        $scss_rel   = 'acf-blocks/' . $block_name . '/' . $block_name . '.scss';

        if (file_exists(get_template_directory() . '/' . $scss_rel)) {
            $style_uri = VITE_SERVER . '/' . $scss_rel;
            if (VITE_BUILD) {
                $style_uri = vite_manifest_uri(vite_manifest(), $scss_rel);
            }

            // silent skip when the manifest has no entry for this block yet
            if ($style_uri) {
                wp_register_style($block_name, $style_uri, null, null);
            }
        }

        register_block_type($block_json);
    }
}
add_action('init', 'custom_theme_register_acf_blocks');
