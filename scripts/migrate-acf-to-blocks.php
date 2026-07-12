<?php
/**
 * Migrate ACF middle-section fields into Gutenberg block markup in post_content
 * for services / directions / cases CPTs.
 *
 * Non-destructive: source postmeta is NOT deleted. Posts that already have
 * post_content are skipped unless "force" is passed.
 *
 * Run:
 *   wp eval-file scripts/migrate-acf-to-blocks.php            # migrate empty posts
 *   wp eval-file scripts/migrate-acf-to-blocks.php dry-run    # report only
 *   wp eval-file scripts/migrate-acf-to-blocks.php force      # overwrite existing content
 *
 * Migrated fields:
 *   services:   audience, triggers, included, stages, strategy, examples, formats, sections
 *   directions: sections
 *   cases:      facts, sections, results
 * Hero, FAQ, sidebar who/needs and CTA stay in ACF (rendered by templates).
 */

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	echo "This script must be run via: wp eval-file scripts/migrate-acf-to-blocks.php\n";
	exit( 1 );
}

if ( ! function_exists( 'get_field' ) ) {
	WP_CLI::error( 'SCF/ACF is not active — get_field() is unavailable. Activate the plugin first.' );
}

$w4m_mig_args  = isset( $args ) ? (array) $args : array();
$w4m_mig_force = in_array( 'force', $w4m_mig_args, true );
$w4m_mig_dry   = in_array( 'dry-run', $w4m_mig_args, true );

// ---------------------------------------------------------------------------
// Block markup builders (core blocks only)
// ---------------------------------------------------------------------------

function w4m_blk_heading( $text, $level = 2 ) {
	$text = trim( wp_strip_all_tags( (string) $text ) );
	if ( '' === $text ) {
		return '';
	}

	$attrs = 2 === $level ? '' : ' {"level":' . (int) $level . '}';

	return "<!-- wp:heading{$attrs} -->\n<h{$level} class=\"wp-block-heading\">" . esc_html( $text ) . "</h{$level}>\n<!-- /wp:heading -->";
}

function w4m_blk_paragraph( $html ) {
	$html = trim( (string) $html );
	if ( '' === $html ) {
		return '';
	}

	return "<!-- wp:paragraph -->\n<p>{$html}</p>\n<!-- /wp:paragraph -->";
}

function w4m_blk_paragraph_text( $text ) {
	$text = trim( (string) $text );
	if ( '' === $text ) {
		return '';
	}

	return w4m_blk_paragraph( nl2br( esc_html( $text ) ) );
}

/**
 * @param string[] $items_html Inline HTML per list item.
 */
function w4m_blk_list( array $items_html, $ordered = false ) {
	$items_html = array_filter( array_map( 'trim', $items_html ) );
	if ( empty( $items_html ) ) {
		return '';
	}

	$tag   = $ordered ? 'ol' : 'ul';
	$attrs = $ordered ? ' {"ordered":true}' : '';
	$items = array();

	foreach ( $items_html as $item ) {
		$items[] = "<!-- wp:list-item -->\n<li>{$item}</li>\n<!-- /wp:list-item -->";
	}

	return "<!-- wp:list{$attrs} -->\n<{$tag} class=\"wp-block-list\">" . implode( "\n", $items ) . "</{$tag}>\n<!-- /wp:list -->";
}

function w4m_blk_button( $label, $url ) {
	$label = trim( (string) $label );
	if ( '' === $label ) {
		return '';
	}
	$url = trim( (string) $url );

	return "<!-- wp:buttons -->\n<div class=\"wp-block-buttons\"><!-- wp:button -->\n<div class=\"wp-block-button\"><a class=\"wp-block-button__link wp-element-button\" href=\"" . esc_url( $url ? $url : '#' ) . '">' . esc_html( $label ) . "</a></div>\n<!-- /wp:button --></div>\n<!-- /wp:buttons -->";
}

/**
 * Convert a WYSIWYG HTML fragment into block markup: top-level p / ul / ol /
 * h2-h4 become native blocks, anything else is preserved in a wp:html block.
 */
function w4m_blk_from_html( $html ) {
	$html = trim( (string) $html );
	if ( '' === $html ) {
		return '';
	}

	$html = wpautop( $html );

	if ( ! class_exists( 'DOMDocument' ) ) {
		WP_CLI::warning( 'DOMDocument unavailable — wrapping WYSIWYG content in a wp:html block.' );
		return "<!-- wp:html -->\n{$html}\n<!-- /wp:html -->";
	}

	$dom      = new DOMDocument();
	$previous = libxml_use_internal_errors( true );
	$loaded   = $dom->loadHTML( '<?xml encoding="utf-8" ?><body>' . $html . '</body>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
	libxml_clear_errors();
	libxml_use_internal_errors( $previous );

	if ( ! $loaded ) {
		WP_CLI::warning( 'Failed to parse WYSIWYG HTML — wrapping in a wp:html block.' );
		return "<!-- wp:html -->\n{$html}\n<!-- /wp:html -->";
	}

	$body = $dom->getElementsByTagName( 'body' )->item( 0 );
	if ( ! $body ) {
		return "<!-- wp:html -->\n{$html}\n<!-- /wp:html -->";
	}

	$inner_html = function ( DOMNode $node ) use ( $dom ) {
		$out = '';
		foreach ( $node->childNodes as $child ) {
			$out .= $dom->saveHTML( $child );
		}
		return $out;
	};

	$blocks = array();

	foreach ( $body->childNodes as $node ) {
		if ( XML_TEXT_NODE === $node->nodeType ) {
			$blocks[] = w4m_blk_paragraph( esc_html( trim( $node->textContent ) ) );
			continue;
		}

		if ( XML_ELEMENT_NODE !== $node->nodeType ) {
			continue;
		}

		$tag = strtolower( $node->nodeName );

		if ( 'p' === $tag ) {
			$blocks[] = w4m_blk_paragraph( $inner_html( $node ) );
		} elseif ( 'ul' === $tag || 'ol' === $tag ) {
			$items = array();
			foreach ( $node->childNodes as $li ) {
				if ( XML_ELEMENT_NODE === $li->nodeType && 'li' === strtolower( $li->nodeName ) ) {
					$items[] = $inner_html( $li );
				}
			}
			$blocks[] = w4m_blk_list( $items, 'ol' === $tag );
		} elseif ( in_array( $tag, array( 'h2', 'h3', 'h4' ), true ) ) {
			$blocks[] = w4m_blk_heading( $node->textContent, (int) substr( $tag, 1 ) );
		} else {
			$blocks[] = "<!-- wp:html -->\n" . $dom->saveHTML( $node ) . "\n<!-- /wp:html -->";
		}
	}

	return implode( "\n\n", array_filter( $blocks ) );
}

/**
 * Repeater of [title, text] rows → h3 + body per row.
 */
function w4m_blk_titled_items( $items, $text_is_html = true ) {
	$blocks = array();

	foreach ( (array) $items as $item ) {
		if ( ! empty( $item['title'] ) ) {
			$blocks[] = w4m_blk_heading( $item['title'], 3 );
		}
		if ( ! empty( $item['text'] ) ) {
			$blocks[] = $text_is_html ? w4m_blk_from_html( $item['text'] ) : w4m_blk_paragraph_text( $item['text'] );
		}
	}

	return $blocks;
}

/**
 * Repeater of [title, content(wysiwyg)] section rows → h2 + content blocks.
 */
function w4m_blk_sections( $sections ) {
	$blocks = array();

	foreach ( (array) $sections as $section ) {
		if ( ! empty( $section['title'] ) ) {
			$blocks[] = w4m_blk_heading( $section['title'], 2 );
		}
		if ( ! empty( $section['content'] ) ) {
			$blocks[] = w4m_blk_from_html( $section['content'] );
		}
	}

	return $blocks;
}

// ---------------------------------------------------------------------------
// Per-CPT content builders
// ---------------------------------------------------------------------------

function w4m_build_service_blocks( $post_id ) {
	$blocks = array();

	$audience = get_field( 'service_audience', $post_id );
	if ( ! empty( $audience['title'] ) || ! empty( $audience['cards'] ) ) {
		$blocks[] = w4m_blk_heading( $audience['title'] ?? '' );
		$blocks[] = w4m_blk_paragraph_text( $audience['intro'] ?? '' );
		$blocks   = array_merge( $blocks, w4m_blk_titled_items( $audience['cards'] ?? array(), false ) );
		if ( ! empty( $audience['notice']['title'] ) || ! empty( $audience['notice']['text'] ) ) {
			$blocks[] = w4m_blk_heading( $audience['notice']['title'] ?? '' );
			$blocks[] = w4m_blk_from_html( $audience['notice']['text'] ?? '' );
		}
	}

	$triggers = get_field( 'service_triggers', $post_id );
	if ( ! empty( $triggers['title'] ) || ! empty( $triggers['items'] ) ) {
		$blocks[] = w4m_blk_heading( $triggers['title'] ?? '' );
		$blocks[] = w4m_blk_paragraph_text( $triggers['intro'] ?? '' );
		$blocks[] = w4m_blk_list( array_map(
			static function ( $item ) {
				return esc_html( $item['text'] ?? '' );
			},
			(array) ( $triggers['items'] ?? array() )
		) );
	}

	$included = get_field( 'service_included', $post_id );
	if ( ! empty( $included['title'] ) || ! empty( $included['items'] ) ) {
		$blocks[] = w4m_blk_heading( $included['title'] ?? '' );
		$blocks   = array_merge( $blocks, w4m_blk_titled_items( $included['items'] ?? array() ) );
	}

	$stages = get_field( 'service_stages', $post_id );
	if ( ! empty( $stages['title'] ) || ! empty( $stages['items'] ) ) {
		$blocks[] = w4m_blk_heading( $stages['title'] ?? '' );
		$blocks   = array_merge( $blocks, w4m_blk_titled_items( $stages['items'] ?? array() ) );
	}

	$strategy = get_field( 'service_strategy', $post_id );
	if ( ! empty( $strategy['title'] ) || ! empty( $strategy['text'] ) ) {
		$blocks[] = w4m_blk_heading( $strategy['title'] ?? '' );
		$blocks[] = w4m_blk_from_html( $strategy['text'] ?? '' );
	}

	$examples = get_field( 'service_examples', $post_id );
	if ( ! empty( $examples['title'] ) || ! empty( $examples['items'] ) ) {
		$blocks[] = w4m_blk_heading( $examples['title'] ?? '' );
		$blocks   = array_merge( $blocks, w4m_blk_titled_items( $examples['items'] ?? array(), false ) );
		$blocks[] = w4m_blk_button( $examples['button_label'] ?? '', $examples['button_url'] ?? '' );
	}

	$formats = get_field( 'service_formats', $post_id );
	if ( ! empty( $formats['title'] ) || ! empty( $formats['items'] ) ) {
		$blocks[] = w4m_blk_heading( $formats['title'] ?? '' );
		$blocks[] = w4m_blk_paragraph_text( $formats['intro'] ?? '' );
		$blocks   = array_merge( $blocks, w4m_blk_titled_items( $formats['items'] ?? array() ) );
	}

	$blocks = array_merge( $blocks, w4m_blk_sections( get_field( 'service_sections', $post_id ) ) );

	return implode( "\n\n", array_filter( $blocks ) );
}

function w4m_build_direction_blocks( $post_id ) {
	$blocks = w4m_blk_sections( get_field( 'direction_sections', $post_id ) );

	return implode( "\n\n", array_filter( $blocks ) );
}

function w4m_build_case_blocks( $post_id ) {
	$blocks = array();

	$facts = get_field( 'case_facts', $post_id );
	if ( ! empty( $facts ) ) {
		$blocks[] = w4m_blk_list( array_map(
			static function ( $fact ) {
				$label = trim( (string) ( $fact['label'] ?? '' ) );
				$value = trim( (string) ( $fact['value'] ?? '' ) );
				return '<strong>' . esc_html( $label ) . ':</strong> ' . esc_html( $value );
			},
			(array) $facts
		) );
	}

	$blocks = array_merge( $blocks, w4m_blk_sections( get_field( 'case_sections', $post_id ) ) );

	$results = get_field( 'case_results', $post_id );
	if ( ! empty( $results['metrics'] ) || ! empty( $results['text'] ) ) {
		$blocks[] = w4m_blk_heading( $results['title'] ?? '' );
		if ( ! empty( $results['metrics'] ) ) {
			$blocks[] = w4m_blk_list( array_map(
				static function ( $metric ) {
					$value = trim( (string) ( $metric['value'] ?? '' ) );
					$label = trim( (string) ( $metric['label'] ?? '' ) );
					return '<strong>' . esc_html( $value ) . '</strong> — ' . esc_html( $label );
				},
				(array) $results['metrics']
			) );
		}
		$blocks[] = w4m_blk_from_html( $results['text'] ?? '' );
	}

	return implode( "\n\n", array_filter( $blocks ) );
}

// ---------------------------------------------------------------------------
// Runner
// ---------------------------------------------------------------------------

function w4m_migrate_cpt_to_blocks( $post_type, callable $builder, $force, $dry ) {
	$posts = get_posts( array(
		'post_type'   => $post_type,
		'numberposts' => -1,
		'post_status' => 'any',
	) );

	$migrated = 0;
	$skipped  = 0;
	$empty    = 0;

	foreach ( $posts as $post ) {
		$label = "{$post_type}/{$post->post_name} (#{$post->ID})";

		if ( '' !== trim( $post->post_content ) && ! $force ) {
			WP_CLI::warning( "Skipped {$label}: post_content is not empty (pass \"force\" to overwrite)." );
			$skipped++;
			continue;
		}

		$content = $builder( $post->ID );

		if ( '' === trim( $content ) ) {
			WP_CLI::log( "Nothing to migrate for {$label} — no section field data." );
			$empty++;
			continue;
		}

		if ( $dry ) {
			WP_CLI::log( sprintf( '[dry-run] %s: would write %d bytes of block markup.', $label, strlen( $content ) ) );
			$migrated++;
			continue;
		}

		$result = wp_update_post( array(
			'ID'           => $post->ID,
			'post_content' => $content,
		), true );

		if ( is_wp_error( $result ) ) {
			WP_CLI::warning( "Failed to update {$label}: " . $result->get_error_message() );
			continue;
		}

		WP_CLI::log( "Migrated {$label}." );
		$migrated++;
	}

	WP_CLI::success( sprintf(
		'%s: %d migrated%s, %d skipped (existing content), %d without section data.',
		$post_type,
		$migrated,
		$dry ? ' (dry-run)' : '',
		$skipped,
		$empty
	) );
}

w4m_migrate_cpt_to_blocks( 'services', 'w4m_build_service_blocks', $w4m_mig_force, $w4m_mig_dry );
w4m_migrate_cpt_to_blocks( 'directions', 'w4m_build_direction_blocks', $w4m_mig_force, $w4m_mig_dry );
w4m_migrate_cpt_to_blocks( 'cases', 'w4m_build_case_blocks', $w4m_mig_force, $w4m_mig_dry );
