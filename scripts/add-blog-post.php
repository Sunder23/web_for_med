<?php
/**
 * Blog post from a data file in scripts/data/blog/<name>.php (Figma huDLClWg3dIpXyzLMuVHEs):
 *   private-practice — «Від приватної практики до власного центру: коли потрібен сайт»
 *                      (desktop 3453:479, mobile 3493:839);
 *   ai-trends        — «Як не відставати від трендів в AI» (desktop 3591:7885).
 *
 *   1. Author from the data `author` key (default `valerii-syshchuk`, see
 *      scripts/update-blog-author.php). With `name` the user is created when missing
 *      (role author, random password, no e-mail); with `avatar` the image is uploaded
 *      and stored in the user ACF field `user_avatar`.
 *   2. Media from scripts/data/blog/: cover (featured image) and every figure image,
 *      uploaded once (looked up by `_starter_source`).
 *   3. Post: created, or updated when a post with the same slug or title exists
 *      (the old content is kept once in `_starter_content_backup`). Title, slug,
 *      excerpt (= lead), date, status publish, author, category of the other posts,
 *      featured image.
 *   4. post_content as block markup, texts verbatim from the data file:
 *      H2 sections (they feed the «Зміст» list and the section counter; a section with
 *      an empty heading has no H2), core/pullquote without citation, a `core/group`
 *      with the block style `is-style-figure` (its image list, each image with its own
 *      class, e.g. `is-hidden-mobile` / `is-hidden-desktop`, + one caption), a
 *      `core/group` with `is-style-callout` (H3 so it stays out of the TOC, paragraphs,
 *      a button, e.g. to `#contact-form` which opens the contact drawer).
 *
 * Args:
 *   <name>  data file name without `.php` (required);
 *   apply   create the author, upload the media and write the post;
 *           without it — dry run: logs what would change, writes nothing.
 *
 * Run: docker compose -f docker/compose.yml run --rm wpcli wp eval-file /scripts/add-blog-post.php <name> [apply]
 *   e.g. … /scripts/add-blog-post.php ai-trends apply
 * (On Windows/Git Bash, prefix with MSYS_NO_PATHCONV=1.)
 *
 * @package Vite_Starter
 */

if ( ! defined( 'WP_CLI' ) ) {
	echo "This script must be run via WP-CLI (wp eval-file).\n";
	exit( 1 );
}

$bp_args  = isset( $args ) && is_array( $args ) ? $args : array();
$bp_apply = in_array( 'apply', $bp_args, true );
$bp_tag   = '[blog-post]';
$bp_names = array_values( array_diff( $bp_args, array( 'apply' ) ) );
$bp_name  = $bp_names ? (string) $bp_names[0] : '';

if ( ! preg_match( '/^[a-z0-9-]+$/', $bp_name ) ) {
	WP_CLI::error( "{$bp_tag} pass the data file name, e.g. `wp eval-file /scripts/add-blog-post.php ai-trends [apply]`" );
}
$bp_file = __DIR__ . '/data/blog/' . $bp_name . '.php';
if ( ! file_exists( $bp_file ) ) {
	WP_CLI::error( "{$bp_tag} data file scripts/data/blog/{$bp_name}.php not found" );
}
$bp_data = require $bp_file;

if ( ! is_array( $bp_data ) || empty( $bp_data['sections'] ) || empty( $bp_data['title'] ) || empty( $bp_data['slug'] ) ) {
	WP_CLI::error( "{$bp_tag} data file scripts/data/blog/{$bp_name}.php is invalid" );
}

WP_CLI::log( "{$bp_tag} mode: " . ( $bp_apply ? 'apply' : 'dry run' ) . ", data: {$bp_name}" );

$bp_changes = 0;

// --- 0. Existing post, category ---------------------------------------------------

$bp_post = get_page_by_path( $bp_data['slug'], OBJECT, 'post' );
if ( ! $bp_post instanceof WP_Post ) {
	$bp_by_title = get_posts(
		array(
			'post_type'      => 'post',
			'post_status'    => 'any',
			'posts_per_page' => 1,
			'title'          => $bp_data['title'],
		)
	);
	$bp_post     = $bp_by_title ? $bp_by_title[0] : null;
}
WP_CLI::log( $bp_post ? "{$bp_tag} found post #{$bp_post->ID}" : "{$bp_tag} post not found, will create" );

// Same category as the other published posts (the most used one).
$bp_categories  = get_terms(
	array(
		'taxonomy'   => 'category',
		'hide_empty' => true,
		'orderby'    => 'count',
		'order'      => 'DESC',
		'number'     => 1,
	)
);
$bp_category_id = ( ! is_wp_error( $bp_categories ) && $bp_categories ) ? (int) $bp_categories[0]->term_id : (int) get_option( 'default_category' );
WP_CLI::log( "{$bp_tag} category #{$bp_category_id}" );

// --- 1. Media ---------------------------------------------------------------------

/**
 * Attachment for a file from scripts/data/blog/: reuse or upload.
 *
 * @param string $file File name.
 * @param string $alt  Alt text.
 * @return int Attachment ID, -1 when it would be uploaded (dry run), 0 on failure.
 */
$bp_attachment = static function ( $file, $alt ) use ( $bp_apply, $bp_tag, &$bp_changes ) {
	$source = 'blog/' . $file;
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
		WP_CLI::log( "{$bp_tag} reuse {$source} → #{$ids[0]}" );
		return (int) $ids[0];
	}

	$path = __DIR__ . '/data/' . $source;
	if ( ! file_exists( $path ) ) {
		WP_CLI::warning( "{$bp_tag} source file missing: {$path}" );
		return 0;
	}
	++$bp_changes;
	if ( ! $bp_apply ) {
		WP_CLI::log( "{$bp_tag} would upload {$source}" );
		return -1;
	}

	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	// media_handle_sideload() moves the file, so hand it a temp copy.
	$tmp = wp_tempnam( $file );
	if ( ! $tmp || ! copy( $path, $tmp ) ) {
		WP_CLI::warning( "{$bp_tag} cannot copy {$path} to a temp file" );
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
		WP_CLI::warning( "{$bp_tag} upload failed for {$source}: " . $id->get_error_message() );
		return 0;
	}
	update_post_meta( $id, '_starter_source', $source );
	update_post_meta( $id, '_wp_attachment_image_alt', $alt );
	WP_CLI::log( "{$bp_tag} uploaded {$source} → #{$id} (" . get_post_mime_type( $id ) . ')' );
	return (int) $id;
};

$bp_cover_id = $bp_attachment( $bp_data['cover'][0], $bp_data['cover'][1] );

// Figure images, keyed by file name.
$bp_media = array();
foreach ( $bp_data['sections'] as $bp_section ) {
	foreach ( $bp_section['items'] as $bp_item ) {
		if ( 'figure' !== $bp_item[0] ) {
			continue;
		}
		if ( empty( $bp_item[2] ) || ! is_array( $bp_item[2] ) ) {
			WP_CLI::error( "{$bp_tag} figure «{$bp_item[1]}» has no image list" );
		}
		foreach ( $bp_item[2] as $bp_image_data ) {
			if ( ! isset( $bp_media[ $bp_image_data[0] ] ) ) {
				$bp_media[ $bp_image_data[0] ] = $bp_attachment( $bp_image_data[0], $bp_image_data[1] );
			}
		}
	}
}
WP_CLI::log( "{$bp_tag} figure images: " . count( $bp_media ) );

if ( 0 === $bp_cover_id || in_array( 0, $bp_media, true ) ) {
	WP_CLI::error( "{$bp_tag} media is missing, nothing written" );
}

// --- 1b. Author -------------------------------------------------------------------

$bp_author_data = isset( $bp_data['author'] ) && is_array( $bp_data['author'] ) ? $bp_data['author'] : array();
$bp_login       = ! empty( $bp_author_data['login'] ) ? (string) $bp_author_data['login'] : 'valerii-syshchuk';
$bp_author      = get_user_by( 'login', $bp_login );
$bp_author_id   = 0;

if ( $bp_author instanceof WP_User ) {
	$bp_author_id = (int) $bp_author->ID;
	WP_CLI::log( "{$bp_tag} author {$bp_login}: already (#{$bp_author_id})" );
} elseif ( empty( $bp_author_data['name'] ) ) {
	WP_CLI::warning( "{$bp_tag} user {$bp_login} is missing — run scripts/update-blog-author.php apply first; the post keeps the current user as author" );
} elseif ( ! $bp_apply ) {
	++$bp_changes;
	WP_CLI::log( "{$bp_tag} would create user {$bp_login} ({$bp_author_data['name']}, author)" );
} else {
	++$bp_changes;
	$bp_name_parts = explode( ' ', (string) $bp_author_data['name'], 2 );
	$bp_new_user   = wp_insert_user(
		array(
			'user_login'   => $bp_login,
			'user_pass'    => wp_generate_password( 24 ),
			'user_email'   => $bp_login . '@example.invalid',
			'display_name' => $bp_author_data['name'],
			'nickname'     => $bp_author_data['name'],
			'first_name'   => $bp_name_parts[0],
			'last_name'    => isset( $bp_name_parts[1] ) ? $bp_name_parts[1] : '',
			'role'         => 'author',
		)
	);
	if ( is_wp_error( $bp_new_user ) ) {
		WP_CLI::error( "{$bp_tag} cannot create user {$bp_login}: " . $bp_new_user->get_error_message() );
	}
	$bp_author_id = (int) $bp_new_user;
	WP_CLI::log( "{$bp_tag} created user {$bp_login} → #{$bp_author_id}" );
}

if ( ! empty( $bp_author_data['avatar'] ) ) {
	if ( ! function_exists( 'update_field' ) ) {
		WP_CLI::warning( "{$bp_tag} Secure Custom Fields is not active, avatar skipped" );
	} else {
		$bp_avatar_id = $bp_attachment( (string) $bp_author_data['avatar'], '' );
		if ( $bp_author_id > 0 && $bp_avatar_id > 0 ) {
			if ( (int) get_field( 'user_avatar', 'user_' . $bp_author_id, false ) === $bp_avatar_id ) {
				WP_CLI::log( "{$bp_tag} user_avatar: already" );
			} elseif ( ! $bp_apply ) {
				++$bp_changes;
				WP_CLI::log( "{$bp_tag} would set user_avatar = #{$bp_avatar_id}" );
			} else {
				++$bp_changes;
				update_field( 'field_starter_user_profile_avatar', $bp_avatar_id, 'user_' . $bp_author_id );
				WP_CLI::log( "{$bp_tag} user_avatar set to #{$bp_avatar_id}" );
			}
		} elseif ( ! $bp_apply ) {
			WP_CLI::log( "{$bp_tag} would set user_avatar after the user/avatar are created" );
		} else {
			WP_CLI::warning( "{$bp_tag} user_avatar not set (user #{$bp_author_id}, avatar #{$bp_avatar_id})" );
		}
	}
}

// --- 2. Block markup --------------------------------------------------------------

$bp_json = static function ( array $attrs ) {
	return $attrs ? ' ' . wp_json_encode( $attrs, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) : '';
};

$bp_heading = static function ( $text, $level = 2 ) use ( $bp_json ) {
	$attrs = 2 === $level ? array() : array( 'level' => $level );
	return '<!-- wp:heading' . $bp_json( $attrs ) . " -->\n<h{$level} class=\"wp-block-heading\">" . esc_html( $text ) . "</h{$level}>\n<!-- /wp:heading -->";
};

$bp_paragraph = static function ( $text, $css_class = '' ) use ( $bp_json ) {
	$attrs = $css_class ? array( 'className' => $css_class ) : array();
	$html  = $css_class ? '<p class="' . esc_attr( $css_class ) . '">' : '<p>';
	return '<!-- wp:paragraph' . $bp_json( $attrs ) . " -->\n{$html}" . esc_html( $text ) . "</p>\n<!-- /wp:paragraph -->";
};

$bp_quote = static function ( $text ) {
	return "<!-- wp:pullquote -->\n<figure class=\"wp-block-pullquote\"><blockquote><p>" . esc_html( $text ) . "</p></blockquote></figure>\n<!-- /wp:pullquote -->";
};

$bp_image = static function ( $id, $alt, $css_class ) use ( $bp_json ) {
	$src   = $id > 0 ? (string) wp_get_attachment_url( $id ) : '';
	$attrs = array(
		'id'              => (int) $id,
		'sizeSlug'        => 'full',
		'linkDestination' => 'none',
	);
	if ( '' !== $css_class ) {
		$attrs['className'] = $css_class;
	}
	return '<!-- wp:image' . $bp_json( $attrs ) . " -->\n"
		. '<figure class="' . esc_attr( trim( 'wp-block-image size-full ' . $css_class ) ) . '"><img src="' . esc_url( $src ) . '" alt="' . esc_attr( $alt ) . '" class="wp-image-' . (int) $id . '"/></figure>' . "\n"
		. '<!-- /wp:image -->';
};

$bp_group = static function ( $style, array $inner ) use ( $bp_json ) {
	$class = 'is-style-' . $style;
	return '<!-- wp:group' . $bp_json( array( 'className' => $class ) ) . " -->\n"
		. '<div class="wp-block-group ' . $class . '">' . implode( "\n\n", $inner ) . "</div>\n"
		. '<!-- /wp:group -->';
};

$bp_button = static function ( $label, $url ) {
	return "<!-- wp:buttons -->\n<div class=\"wp-block-buttons\"><!-- wp:button -->\n"
		. '<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="' . esc_url( $url ) . '">' . esc_html( $label ) . "</a></div>\n"
		. "<!-- /wp:button --></div>\n<!-- /wp:buttons -->";
};

$bp_blocks = array();
foreach ( $bp_data['sections'] as $bp_section ) {
	if ( '' !== (string) $bp_section['heading'] ) {
		$bp_blocks[] = $bp_heading( $bp_section['heading'] );
	}
	foreach ( $bp_section['items'] as $bp_item ) {
		switch ( $bp_item[0] ) {
			case 'p':
				$bp_blocks[] = $bp_paragraph( $bp_item[1] );
				break;
			case 'quote':
				$bp_blocks[] = $bp_quote( $bp_item[1] );
				break;
			case 'figure':
				$bp_inner = array();
				foreach ( $bp_item[2] as $bp_image_data ) {
					$bp_inner[] = $bp_image( $bp_media[ $bp_image_data[0] ], $bp_image_data[1], isset( $bp_image_data[2] ) ? (string) $bp_image_data[2] : '' );
				}
				$bp_inner[]  = $bp_paragraph( $bp_item[1], 'article-figure__caption' );
				$bp_blocks[] = $bp_group( 'figure', $bp_inner );
				break;
			case 'callout':
				$bp_inner = array( $bp_heading( $bp_item[1], 3 ) );
				foreach ( $bp_item[2] as $bp_text ) {
					$bp_inner[] = $bp_paragraph( $bp_text );
				}
				$bp_inner[]  = $bp_button( $bp_item[3][0], $bp_item[3][1] );
				$bp_blocks[] = $bp_group( 'callout', $bp_inner );
				break;
			default:
				WP_CLI::warning( "{$bp_tag} unknown item type {$bp_item[0]}" );
		}
	}
}

$bp_content = implode( "\n\n", $bp_blocks );
WP_CLI::log( "{$bp_tag} content: " . count( $bp_blocks ) . ' top-level blocks, ' . count( array_filter( array_column( $bp_data['sections'], 'heading' ) ) ) . ' H2 sections' );

// --- 3. Post ----------------------------------------------------------------------

$bp_postarr = array(
	'post_type'     => 'post',
	'post_status'   => 'publish',
	'post_title'    => $bp_data['title'],
	'post_name'     => $bp_data['slug'],
	'post_excerpt'  => $bp_data['excerpt'],
	'post_content'  => $bp_content,
	'post_date'     => $bp_data['date'],
	'post_category' => array( $bp_category_id ),
);
if ( $bp_author_id ) {
	$bp_postarr['post_author'] = $bp_author_id;
}

if ( $bp_post instanceof WP_Post ) {
	$bp_diff = array();
	foreach ( array( 'post_status', 'post_title', 'post_name', 'post_excerpt', 'post_date' ) as $bp_field ) {
		if ( (string) $bp_post->$bp_field !== (string) $bp_postarr[ $bp_field ] ) {
			$bp_diff[] = $bp_field;
		}
	}
	if ( trim( $bp_post->post_content ) !== $bp_content ) {
		$bp_diff[] = 'post_content';
	}
	if ( $bp_author_id && (int) $bp_post->post_author !== $bp_author_id ) {
		$bp_diff[] = 'post_author';
	}
	if ( ! $bp_diff ) {
		WP_CLI::log( "{$bp_tag} post fields: already" );
	} else {
		++$bp_changes;
		if ( ! $bp_apply ) {
			WP_CLI::log( "{$bp_tag} would update " . implode( ', ', $bp_diff ) );
		} else {
			if ( '' === (string) get_post_meta( $bp_post->ID, '_starter_content_backup', true ) ) {
				update_post_meta( $bp_post->ID, '_starter_content_backup', wp_slash( $bp_post->post_content ) );
				WP_CLI::log( "{$bp_tag} original content saved to `_starter_content_backup`" );
			}
			$bp_postarr['ID'] = $bp_post->ID;
			$bp_result        = wp_update_post( wp_slash( $bp_postarr ), true );
			if ( is_wp_error( $bp_result ) ) {
				WP_CLI::error( "{$bp_tag} update failed: " . $bp_result->get_error_message() );
			}
			WP_CLI::log( "{$bp_tag} updated " . implode( ', ', $bp_diff ) );
		}
	}
	$bp_post_id = (int) $bp_post->ID;
} else {
	++$bp_changes;
	if ( ! $bp_apply ) {
		WP_CLI::log( "{$bp_tag} would create the post" );
		$bp_post_id = 0;
	} else {
		$bp_post_id = wp_insert_post( wp_slash( $bp_postarr ), true );
		if ( is_wp_error( $bp_post_id ) ) {
			WP_CLI::error( "{$bp_tag} insert failed: " . $bp_post_id->get_error_message() );
		}
		WP_CLI::log( "{$bp_tag} created post #{$bp_post_id}" );
	}
}

if ( $bp_post_id && $bp_cover_id > 0 ) {
	if ( (int) get_post_thumbnail_id( $bp_post_id ) === $bp_cover_id ) {
		WP_CLI::log( "{$bp_tag} featured image: already" );
	} else {
		++$bp_changes;
		if ( $bp_apply ) {
			set_post_thumbnail( $bp_post_id, $bp_cover_id );
			WP_CLI::log( "{$bp_tag} featured image set to #{$bp_cover_id}" );
		} else {
			WP_CLI::log( "{$bp_tag} would set featured image #{$bp_cover_id}" );
		}
	}
}

if ( ! $bp_changes ) {
	WP_CLI::success( "{$bp_tag} nothing to change: " . get_permalink( $bp_post_id ) );
	return;
}

if ( ! $bp_apply ) {
	WP_CLI::success( "{$bp_tag} dry run: {$bp_changes} change(s) pending, run with `apply` to write" );
	return;
}

WP_CLI::success( "{$bp_tag} {$bp_changes} change(s) written: " . get_permalink( $bp_post_id ) );
