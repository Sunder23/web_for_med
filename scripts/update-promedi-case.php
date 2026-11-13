<?php
/**
 * Promedi case (`cases/promedi-web-development`): content tweaks for the mobile
 * design (Figma huDLClWg3dIpXyzLMuVHEs, mobile 3124:1383, desktop 3061:891).
 *
 *   1. Chapter headings → block style `is-style-chapter`.
 *   2. Conclusion paragraph → block style `is-style-lead`.
 *   3. Google Keywords Planner gallery → block style `is-style-stack-mobile`.
 *   4. "Технічно: …" caption notes wrapped in <em> (own line, caption colour).
 *   5. 404 screenshot: the desktop image gets `case-single__desktop-only`, a phone
 *      screenshot (scripts/data/promedi/promedi-404-mobile.jpg) is added after it
 *      with `case-single__mobile-only`.
 *
 * Edits go through parse_blocks()/serialize_blocks(): a class lands in both the
 * block comment (`className`) and the saved HTML, so the editor keeps the blocks
 * valid. Each edit is idempotent. The original content is kept once in post meta
 * `_promedi_content_backup`.
 *
 * Args:
 *   (none)  dry run — logs what would change, writes nothing;
 *   apply   upload the media and write the post.
 *
 * Run: docker compose -f docker/compose.yml run --rm wpcli wp eval-file /scripts/update-promedi-case.php [apply]
 * (On Windows/Git Bash, prefix with MSYS_NO_PATHCONV=1.)
 *
 * @package Vite_Starter
 */

if ( ! defined( 'WP_CLI' ) ) {
	echo "This script must be run via WP-CLI (wp eval-file).\n";
	exit( 1 );
}

$pc_args  = isset( $args ) && is_array( $args ) ? $args : array();
$pc_apply = in_array( 'apply', $pc_args, true );
$pc_tag   = '[promedi-content]';

$pc_post = get_page_by_path( 'promedi-web-development', OBJECT, 'cases' );
if ( ! $pc_post instanceof WP_Post ) {
	WP_CLI::error( "{$pc_tag} case `promedi-web-development` not found" );
}

WP_CLI::log( "{$pc_tag} case #{$pc_post->ID}, mode: " . ( $pc_apply ? 'apply' : 'dry run' ) );

/**
 * Add a class to a block: `className` attribute plus the first tag of its markup.
 *
 * @param array  $block Parsed block (by reference).
 * @param string $class Class to add.
 * @return bool False when the block already has it.
 */
$pc_add_class = static function ( array &$block, $class ) {
	$current = isset( $block['attrs']['className'] ) ? (string) $block['attrs']['className'] : '';
	if ( in_array( $class, preg_split( '/\s+/', $current, -1, PREG_SPLIT_NO_EMPTY ), true ) ) {
		return false;
	}

	$block['attrs']['className'] = trim( $current . ' ' . $class );

	$add_to_tag = static function ( $html ) use ( $class ) {
		// First opening tag only: `<h2 class="a">` → `<h2 class="a b">`, `<p>` → `<p class="b">`.
		if ( preg_match( '/^(\s*<[a-z0-9]+\b[^>]*?\bclass=")([^"]*)"/i', $html ) ) {
			return preg_replace( '/^(\s*<[a-z0-9]+\b[^>]*?\bclass=")([^"]*)"/i', '$1$2 ' . $class . '"', $html, 1 );
		}
		return preg_replace( '/^(\s*<[a-z0-9]+)\b/i', '$1 class="' . $class . '"', $html, 1 );
	};

	$block['innerHTML'] = $add_to_tag( (string) $block['innerHTML'] );
	if ( isset( $block['innerContent'][0] ) && is_string( $block['innerContent'][0] ) ) {
		$block['innerContent'][0] = $add_to_tag( $block['innerContent'][0] );
	}
	return true;
};

/**
 * Wrap "<br>Технічно: …" caption notes in <em> in every string of a block.
 *
 * @param array $block Parsed block (by reference).
 * @return int Number of wrapped notes.
 */
$pc_wrap_notes = static function ( array &$block ) {
	$pattern = '/<br\s*\/?>(Технічно:[^<]*)<\/figcaption>/u';
	$count   = 0;

	$block['innerHTML'] = preg_replace( $pattern, '<br><em>$1</em></figcaption>', (string) $block['innerHTML'] );
	foreach ( $block['innerContent'] as $i => $chunk ) {
		if ( is_string( $chunk ) ) {
			$block['innerContent'][ $i ] = preg_replace( $pattern, '<br><em>$1</em></figcaption>', $chunk, -1, $n );
			$count                      += $n;
		}
	}
	return $count;
};

/**
 * Find or upload scripts/data/promedi/<file>; attachments are tagged with `_starter_source`.
 *
 * @param string $file Basename.
 * @param string $alt  Alt text for a new upload.
 * @return int Attachment ID, -1 when it would be uploaded (dry run), 0 on failure.
 */
$pc_attachment = static function ( $file, $alt ) use ( $pc_apply, $pc_tag ) {
	$source = 'promedi/' . $file;
	$ids    = get_posts(
		array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_key'       => '_starter_source', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- One-off CLI script.
			'meta_value'     => $source, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- One-off CLI script.
		)
	);
	if ( $ids ) {
		WP_CLI::log( "{$pc_tag} reuse {$source} → #{$ids[0]}" );
		return (int) $ids[0];
	}

	$path = __DIR__ . '/data/' . $source;
	if ( ! file_exists( $path ) ) {
		WP_CLI::warning( "{$pc_tag} source file missing: {$path}" );
		return 0;
	}
	if ( ! $pc_apply ) {
		WP_CLI::log( "{$pc_tag} would upload {$source}" );
		return -1;
	}

	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	// media_handle_sideload() moves the file, so hand it a temp copy.
	$tmp = wp_tempnam( $file );
	if ( ! $tmp || ! copy( $path, $tmp ) ) {
		WP_CLI::warning( "{$pc_tag} cannot copy {$path} to a temp file" );
		return 0;
	}
	$id = media_handle_sideload(
		array(
			'name'     => $file,
			'tmp_name' => $tmp,
		),
		0
	);
	if ( is_wp_error( $id ) ) {
		wp_delete_file( $tmp );
		WP_CLI::warning( "{$pc_tag} upload failed for {$source}: " . $id->get_error_message() );
		return 0;
	}
	update_post_meta( $id, '_starter_source', $source );
	update_post_meta( $id, '_wp_attachment_image_alt', $alt );
	WP_CLI::log( "{$pc_tag} uploaded {$source} → #{$id}" );
	return (int) $id;
};

$pc_blocks  = parse_blocks( $pc_post->post_content );
$pc_applied = 0;
$pc_found   = array(
	'chapter' => 0,
	'lead'    => 0,
	'gallery' => 0,
	'notes'   => 0,
	'404'     => 0,
);

$pc_chapters = array( 'Що залишилося за лаштунками дизайну', 'Що отримав Promedi у підсумку' );
$pc_has_m404 = false;
$pc_404_at   = -1;

foreach ( $pc_blocks as $i => &$pc_block ) {
	$name = (string) $pc_block['blockName'];
	$text = trim( wp_strip_all_tags( (string) $pc_block['innerHTML'] ) );

	if ( 'core/heading' === $name && in_array( $text, $pc_chapters, true ) ) {
		++$pc_found['chapter'];
		$done = $pc_add_class( $pc_block, 'is-style-chapter' );
		$pc_applied += $done ? 1 : 0;
		WP_CLI::log( "{$pc_tag} chapter «{$text}»: " . ( $done ? 'applied' : 'already' ) );
	}

	if ( 'core/paragraph' === $name && 0 === strpos( $text, 'У результаті Promedi отримав' ) ) {
		++$pc_found['lead'];
		$done = $pc_add_class( $pc_block, 'is-style-lead' );
		$pc_applied += $done ? 1 : 0;
		WP_CLI::log( "{$pc_tag} lead paragraph: " . ( $done ? 'applied' : 'already' ) );
	}

	if ( 'core/gallery' === $name && false !== strpos( (string) $pc_block['innerHTML'], 'Keywords Planner' ) ) {
		++$pc_found['gallery'];
		$done = $pc_add_class( $pc_block, 'is-style-stack-mobile' );
		$pc_applied += $done ? 1 : 0;
		WP_CLI::log( "{$pc_tag} Keywords Planner gallery: " . ( $done ? 'applied' : 'already' ) );
	}

	if ( in_array( $name, array( 'core/image', 'core/gallery' ), true ) && false !== strpos( (string) $pc_block['innerHTML'], 'Технічно:' ) ) {
		++$pc_found['notes'];
		$n = $pc_wrap_notes( $pc_block );
		$pc_applied += $n;
		WP_CLI::log( "{$pc_tag} caption note in {$name}: " . ( $n ? 'applied' : 'already' ) );
	}

	if ( 'core/image' === $name && isset( $pc_block['attrs']['id'] ) ) {
		if ( 752 === (int) $pc_block['attrs']['id'] ) {
			++$pc_found['404'];
			$pc_404_at = $i;
			$done      = $pc_add_class( $pc_block, 'case-single__desktop-only' );
			$pc_applied += $done ? 1 : 0;
			WP_CLI::log( "{$pc_tag} desktop 404 image: " . ( $done ? 'applied' : 'already' ) );
		}
		if ( false !== strpos( (string) ( $pc_block['attrs']['className'] ?? '' ), 'case-single__mobile-only' ) ) {
			$pc_has_m404 = true;
		}
	}
}
unset( $pc_block );

foreach ( $pc_found as $key => $count ) {
	if ( ! $count ) {
		WP_CLI::warning( "{$pc_tag} anchor not found: {$key}" );
	}
}

// Phone 404 screenshot right after the desktop one.
if ( $pc_404_at >= 0 && ! $pc_has_m404 ) {
	$alt = 'Сторінка 404 сайту Promedi на телефоні';
	$id  = $pc_attachment( 'promedi-404-mobile.jpg', $alt );

	if ( $id > 0 ) {
		$src  = (string) wp_get_attachment_url( $id );
		$html = '<figure class="wp-block-image aligncenter size-full is-resized case-single__mobile-only"><img src="' . esc_url( $src ) . '" alt="' . esc_attr( $alt ) . '" class="wp-image-' . $id . '" style="width:266px"/></figure>';

		array_splice(
			$pc_blocks,
			$pc_404_at + 1,
			0,
			array(
				array(
					'blockName'    => null,
					'attrs'        => array(),
					'innerBlocks'  => array(),
					'innerHTML'    => "\n\n",
					'innerContent' => array( "\n\n" ),
				),
				array(
					'blockName'    => 'core/image',
					'attrs'        => array(
						'id'              => $id,
						'width'           => '266px',
						'sizeSlug'        => 'full',
						'linkDestination' => 'none',
						'align'           => 'center',
						'className'       => 'case-single__mobile-only',
					),
					'innerBlocks'  => array(),
					'innerHTML'    => "\n" . $html . "\n",
					'innerContent' => array( "\n" . $html . "\n" ),
				),
			)
		);
		++$pc_applied;
		WP_CLI::log( "{$pc_tag} mobile 404 image #{$id}: applied" );
	} elseif ( -1 === $id ) {
		++$pc_applied;
		WP_CLI::log( "{$pc_tag} mobile 404 image: would insert after upload" );
	}
} elseif ( $pc_has_m404 ) {
	WP_CLI::log( "{$pc_tag} mobile 404 image: already" );
}

if ( ! $pc_applied ) {
	WP_CLI::success( "{$pc_tag} nothing to change" );
	return;
}

if ( ! $pc_apply ) {
	WP_CLI::success( "{$pc_tag} dry run: {$pc_applied} change(s) pending, run with `apply` to write" );
	return;
}

if ( '' === (string) get_post_meta( $pc_post->ID, '_promedi_content_backup', true ) ) {
	update_post_meta( $pc_post->ID, '_promedi_content_backup', wp_slash( $pc_post->post_content ) );
	WP_CLI::log( "{$pc_tag} original content saved to `_promedi_content_backup`" );
}

$pc_result = wp_update_post(
	array(
		'ID'           => $pc_post->ID,
		'post_content' => wp_slash( serialize_blocks( $pc_blocks ) ),
	),
	true
);

if ( is_wp_error( $pc_result ) ) {
	WP_CLI::error( "{$pc_tag} update failed: " . $pc_result->get_error_message() );
}

WP_CLI::success( "{$pc_tag} {$pc_applied} change(s) written to case #{$pc_post->ID}" );
