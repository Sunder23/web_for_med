<?php

// Table of contents: heading anchor injection + TOC data helper

/**
 * Extract h2/h3 headings from HTML and compute a unique anchor id for each.
 *
 * Existing id attributes are respected; missing ids are generated from the
 * heading text. Both the anchor-injection filter and custom_theme_get_toc()
 * use this function on the same heading sequence, so ids always match.
 *
 * @param string $html HTML markup to scan.
 * @return array[] List of [ 'level' => 2|3, 'title' => string, 'id' => string ].
 */
function custom_theme_collect_headings( $html ) {
	$headings = array();

	if ( ! is_string( $html ) || '' === $html ) {
		return $headings;
	}

	if ( ! preg_match_all( '/<h([23])([^>]*)>(.*?)<\/h\1>/is', $html, $matches, PREG_SET_ORDER ) ) {
		return $headings;
	}

	$used = array();

	foreach ( $matches as $index => $match ) {
		$title = trim( wp_strip_all_tags( $match[3] ) );
		if ( '' === $title ) {
			continue;
		}
		// decode entities so templates can esc_html() without double-escaping
		$title = html_entity_decode( $title, ENT_QUOTES | ENT_HTML5, 'UTF-8' );

		$id = '';
		if ( preg_match( '/\sid=["\']([^"\']+)["\']/i', $match[2], $id_match ) ) {
			$id = $id_match[1];
		} else {
			// urldecode keeps Cyrillic slugs readable instead of percent-encoded
			$id = urldecode( sanitize_title( $title ) );
			if ( '' === $id ) {
				$id = 'section-' . ( $index + 1 );
			}
		}

		$base = $id;
		$n    = 2;
		while ( isset( $used[ $id ] ) ) {
			$id = $base . '-' . $n;
			$n++;
		}
		$used[ $id ] = true;

		$headings[] = array(
			'level' => (int) $match[1],
			'title' => $title,
			'id'    => $id,
		);
	}

	return $headings;
}

/**
 * the_content filter: inject anchor ids into h2/h3 headings on block content views.
 */
function custom_theme_inject_heading_anchors( $content ) {
	if ( is_admin() || ! in_the_loop() || ! is_main_query() ) {
		return $content;
	}

	if ( ! custom_theme_is_block_content_view() ) {
		return $content;
	}

	$headings = custom_theme_collect_headings( $content );
	if ( empty( $headings ) ) {
		return $content;
	}

	$cursor = 0; // walk headings in order; skip ones that already have an id

	$content = preg_replace_callback(
		'/<h([23])([^>]*)>(.*?)<\/h\1>/is',
		function ( $match ) use ( $headings, &$cursor ) {
			if ( '' === trim( wp_strip_all_tags( $match[3] ) ) ) {
				return $match[0];
			}

			$heading = $headings[ $cursor ] ?? null;
			$cursor++;

			if ( ! $heading || preg_match( '/\sid=["\']/i', $match[2] ) ) {
				return $match[0];
			}

			return '<h' . $match[1] . $match[2] . ' id="' . esc_attr( $heading['id'] ) . '">' . $match[3] . '</h' . $match[1] . '>';
		},
		$content
	);

	return $content;
}
add_filter( 'the_content', 'custom_theme_inject_heading_anchors', 20 );

/**
 * Build the TOC data for a post from its h2/h3 headings.
 *
 * Parses raw post_content (block markup contains literal heading tags),
 * so it can be called before the_content() runs in the template.
 *
 * @param int|WP_Post|null $post Post to build the TOC for. Defaults to current post.
 * @return array[] List of [ 'level' => 2|3, 'title' => string, 'id' => string ].
 */
function custom_theme_get_toc( $post = null ) {
	$post = get_post( $post );
	if ( ! $post ) {
		return array();
	}

	return custom_theme_collect_headings( $post->post_content );
}
