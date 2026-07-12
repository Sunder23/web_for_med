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
 *
 * Values are read from raw postmeta (ACF naming: group "{name}_{sub}",
 * repeater "{name}" = row count + "{name}_{i}_{sub}") — NOT via get_field() —
 * because the migrated fields are already removed from the acf-json
 * definitions and get_field() can no longer resolve them.
 */

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	echo "This script must be run via: wp eval-file scripts/migrate-acf-to-blocks.php\n";
	exit( 1 );
}

// ---------------------------------------------------------------------------
// Raw postmeta readers (ACF storage conventions)
// ---------------------------------------------------------------------------

function w4m_meta( $post_id, $name ) {
	return get_post_meta( $post_id, $name, true );
}

/**
 * Read an ACF repeater from raw postmeta: "{base}" holds the row count,
 * "{base}_{i}_{sub}" holds each sub value.
 *
 * @param string[] $subs Sub-field names.
 * @return array[] Rows of sub => value.
 */
function w4m_meta_rows( $post_id, $base, array $subs ) {
	$count = (int) get_post_meta( $post_id, $base, true );
	$rows  = array();

	for ( $i = 0; $i < $count; $i++ ) {
		$row = array();
		foreach ( $subs as $sub ) {
			$row[ $sub ] = get_post_meta( $post_id, "{$base}_{$i}_{$sub}", true );
		}
		$rows[] = $row;
	}

	return $rows;
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

	$audience = array(
		'title'  => w4m_meta( $post_id, 'service_audience_title' ),
		'intro'  => w4m_meta( $post_id, 'service_audience_intro' ),
		'cards'  => w4m_meta_rows( $post_id, 'service_audience_cards', array( 'title', 'text' ) ),
		'notice' => array(
			'title' => w4m_meta( $post_id, 'service_audience_notice_title' ),
			'text'  => w4m_meta( $post_id, 'service_audience_notice_text' ),
		),
	);
	if ( ! empty( $audience['title'] ) || ! empty( $audience['cards'] ) ) {
		$blocks[] = w4m_blk_heading( $audience['title'] ?? '' );
		$blocks[] = w4m_blk_paragraph_text( $audience['intro'] ?? '' );
		$blocks   = array_merge( $blocks, w4m_blk_titled_items( $audience['cards'] ?? array(), false ) );
		if ( ! empty( $audience['notice']['title'] ) || ! empty( $audience['notice']['text'] ) ) {
			$blocks[] = w4m_blk_heading( $audience['notice']['title'] ?? '' );
			$blocks[] = w4m_blk_from_html( $audience['notice']['text'] ?? '' );
		}
	}

	$triggers = array(
		'title' => w4m_meta( $post_id, 'service_triggers_title' ),
		'intro' => w4m_meta( $post_id, 'service_triggers_intro' ),
		'items' => w4m_meta_rows( $post_id, 'service_triggers_items', array( 'text' ) ),
	);
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

	$included = array(
		'title' => w4m_meta( $post_id, 'service_included_title' ),
		'items' => w4m_meta_rows( $post_id, 'service_included_items', array( 'title', 'text' ) ),
	);
	if ( ! empty( $included['title'] ) || ! empty( $included['items'] ) ) {
		$blocks[] = w4m_blk_heading( $included['title'] ?? '' );
		$blocks   = array_merge( $blocks, w4m_blk_titled_items( $included['items'] ?? array() ) );
	}

	$stages = array(
		'title' => w4m_meta( $post_id, 'service_stages_title' ),
		'items' => w4m_meta_rows( $post_id, 'service_stages_items', array( 'title', 'text' ) ),
	);
	if ( ! empty( $stages['title'] ) || ! empty( $stages['items'] ) ) {
		$blocks[] = w4m_blk_heading( $stages['title'] ?? '' );
		$blocks   = array_merge( $blocks, w4m_blk_titled_items( $stages['items'] ?? array() ) );
	}

	$strategy = array(
		'title' => w4m_meta( $post_id, 'service_strategy_title' ),
		'text'  => w4m_meta( $post_id, 'service_strategy_text' ),
	);
	if ( ! empty( $strategy['title'] ) || ! empty( $strategy['text'] ) ) {
		$blocks[] = w4m_blk_heading( $strategy['title'] ?? '' );
		$blocks[] = w4m_blk_from_html( $strategy['text'] ?? '' );
	}

	$examples = array(
		'title'        => w4m_meta( $post_id, 'service_examples_title' ),
		'items'        => w4m_meta_rows( $post_id, 'service_examples_items', array( 'title', 'text' ) ),
		'button_label' => w4m_meta( $post_id, 'service_examples_button_label' ),
		'button_url'   => w4m_meta( $post_id, 'service_examples_button_url' ),
	);
	if ( ! empty( $examples['title'] ) || ! empty( $examples['items'] ) ) {
		$blocks[] = w4m_blk_heading( $examples['title'] ?? '' );
		$blocks   = array_merge( $blocks, w4m_blk_titled_items( $examples['items'] ?? array(), false ) );
		$blocks[] = w4m_blk_button( $examples['button_label'] ?? '', $examples['button_url'] ?? '' );
	}

	$formats = array(
		'title' => w4m_meta( $post_id, 'service_formats_title' ),
		'intro' => w4m_meta( $post_id, 'service_formats_intro' ),
		'items' => w4m_meta_rows( $post_id, 'service_formats_items', array( 'title', 'text' ) ),
	);
	if ( ! empty( $formats['title'] ) || ! empty( $formats['items'] ) ) {
		$blocks[] = w4m_blk_heading( $formats['title'] ?? '' );
		$blocks[] = w4m_blk_paragraph_text( $formats['intro'] ?? '' );
		$blocks   = array_merge( $blocks, w4m_blk_titled_items( $formats['items'] ?? array() ) );
	}

	$blocks = array_merge( $blocks, w4m_blk_sections( w4m_meta_rows( $post_id, 'service_sections', array( 'title', 'content' ) ) ) );

	return implode( "\n\n", array_filter( $blocks ) );
}

function w4m_build_direction_blocks( $post_id ) {
	$blocks = w4m_blk_sections( w4m_meta_rows( $post_id, 'direction_sections', array( 'title', 'content' ) ) );

	return implode( "\n\n", array_filter( $blocks ) );
}

function w4m_build_case_blocks( $post_id ) {
	$blocks = array();

	$facts = w4m_meta_rows( $post_id, 'case_facts', array( 'label', 'value' ) );
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

	$blocks = array_merge( $blocks, w4m_blk_sections( w4m_meta_rows( $post_id, 'case_sections', array( 'title', 'content' ) ) ) );

	$results = array(
		'title'   => w4m_meta( $post_id, 'case_results_title' ),
		'metrics' => w4m_meta_rows( $post_id, 'case_results_metrics', array( 'value', 'label' ) ),
		'text'    => w4m_meta( $post_id, 'case_results_text' ),
	);
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
