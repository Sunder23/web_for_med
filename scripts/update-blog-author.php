<?php
/**
 * Blog author: the WP user «Валерій Сищук» with an avatar, set as the author of every blog post.
 *
 *   1. User `valerii-syshchuk` (display name «Валерій Сищук», role author, random password,
 *      no notification e-mail) — created if missing.
 *   2. Avatar scripts/data/blog/author-valerii.jpg — uploaded once (looked up by
 *      `_starter_source`), stored in the user ACF field `user_avatar`
 *      (configure/acf/acf-json/group_starter_user_profile.json).
 *   3. `post_author` of every `post` (any status) whose author is 0 or the admin
 *      account `atpluscompany@gmail.com` is set to the new user.
 *
 * Idempotent: a second run reports "already" for each step.
 *
 * Args:
 *   (none)  dry run — logs what would change, writes nothing;
 *   apply   create the user, upload the avatar, reassign the posts.
 *
 * Run: docker compose -f docker/compose.yml run --rm wpcli wp eval-file /scripts/update-blog-author.php [apply]
 * (On Windows/Git Bash, prefix with MSYS_NO_PATHCONV=1.)
 *
 * @package Vite_Starter
 */

if ( ! defined( 'WP_CLI' ) ) {
	echo "This script must be run via WP-CLI (wp eval-file).\n";
	exit( 1 );
}

if ( ! function_exists( 'update_field' ) ) {
	WP_CLI::error( '[blog-author] Secure Custom Fields is not active' );
}

$ba_args  = isset( $args ) && is_array( $args ) ? $args : array();
$ba_apply = in_array( 'apply', $ba_args, true );
$ba_tag   = '[blog-author]';

$ba_login        = 'valerii-syshchuk';
$ba_display_name = 'Валерій Сищук';
$ba_old_logins   = array( 'atpluscompany@gmail.com' );

WP_CLI::log( "{$ba_tag} mode: " . ( $ba_apply ? 'apply' : 'dry run' ) );

// --- 1. User ----------------------------------------------------------------------

$ba_user = get_user_by( 'login', $ba_login );
if ( $ba_user instanceof WP_User ) {
	WP_CLI::log( "{$ba_tag} user {$ba_login}: already (#{$ba_user->ID})" );
	if ( $ba_apply && $ba_display_name !== $ba_user->display_name ) {
		wp_update_user(
			array(
				'ID'           => $ba_user->ID,
				'display_name' => $ba_display_name,
			)
		);
		WP_CLI::log( "{$ba_tag} display_name updated" );
	}
	$ba_user_id = (int) $ba_user->ID;
} elseif ( ! $ba_apply ) {
	WP_CLI::log( "{$ba_tag} would create user {$ba_login} ({$ba_display_name}, author)" );
	$ba_user_id = -1;
} else {
	$ba_user_id = wp_insert_user(
		array(
			'user_login'   => $ba_login,
			'user_pass'    => wp_generate_password( 24 ),
			'user_email'   => $ba_login . '@example.invalid',
			'display_name' => $ba_display_name,
			'nickname'     => $ba_display_name,
			'first_name'   => 'Валерій',
			'last_name'    => 'Сищук',
			'role'         => 'author',
		)
	);
	if ( is_wp_error( $ba_user_id ) ) {
		WP_CLI::error( "{$ba_tag} cannot create user: " . $ba_user_id->get_error_message() );
	}
	WP_CLI::log( "{$ba_tag} created user {$ba_login} → #{$ba_user_id}" );
}

// --- 2. Avatar --------------------------------------------------------------------

$ba_source    = 'blog/author-valerii.jpg';
$ba_avatar_id = 0;
$ba_found     = get_posts(
	array(
		'post_type'      => 'attachment',
		'post_status'    => 'inherit',
		'posts_per_page' => 1,
		'fields'         => 'ids',
		'meta_key'       => '_starter_source', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- One-off CLI script.
		'meta_value'     => $ba_source, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value -- One-off CLI script.
	)
);

if ( $ba_found ) {
	$ba_avatar_id = (int) $ba_found[0];
	WP_CLI::log( "{$ba_tag} reuse {$ba_source} → #{$ba_avatar_id}" );
} elseif ( ! $ba_apply ) {
	WP_CLI::log( "{$ba_tag} would upload {$ba_source}" );
} else {
	$ba_path = __DIR__ . '/data/' . $ba_source;
	if ( ! file_exists( $ba_path ) ) {
		WP_CLI::warning( "{$ba_tag} source file missing: {$ba_path}" );
	} else {
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		// media_handle_sideload() moves the file, so hand it a temp copy.
		$ba_tmp = wp_tempnam( basename( $ba_path ) );
		if ( ! $ba_tmp || ! copy( $ba_path, $ba_tmp ) ) {
			WP_CLI::warning( "{$ba_tag} cannot copy {$ba_path} to a temp file" );
		} else {
			$ba_id = media_handle_sideload(
				array(
					'name'     => basename( $ba_path ),
					'tmp_name' => $ba_tmp,
				),
				0
			);
			if ( is_wp_error( $ba_id ) ) {
				wp_delete_file( $ba_tmp );
				WP_CLI::warning( "{$ba_tag} upload failed for {$ba_source}: " . $ba_id->get_error_message() );
			} else {
				update_post_meta( $ba_id, '_starter_source', $ba_source );
				update_post_meta( $ba_id, '_wp_attachment_image_alt', '' );
				$ba_avatar_id = (int) $ba_id;
				WP_CLI::log( "{$ba_tag} uploaded {$ba_source} → #{$ba_avatar_id}" );
			}
		}
	}
}

if ( $ba_user_id > 0 && $ba_avatar_id > 0 ) {
	$ba_now = (int) get_field( 'user_avatar', 'user_' . $ba_user_id, false );
	if ( $ba_now === $ba_avatar_id ) {
		WP_CLI::log( "{$ba_tag} user_avatar: already" );
	} elseif ( ! $ba_apply ) {
		WP_CLI::log( "{$ba_tag} would set user_avatar = #{$ba_avatar_id}" );
	} else {
		update_field( 'field_starter_user_profile_avatar', $ba_avatar_id, 'user_' . $ba_user_id );
		WP_CLI::log( "{$ba_tag} user_avatar set to #{$ba_avatar_id}" );
	}
} elseif ( ! $ba_apply ) {
	WP_CLI::log( "{$ba_tag} would set user_avatar after the upload" );
} else {
	WP_CLI::warning( "{$ba_tag} user_avatar not set (user #{$ba_user_id}, avatar #{$ba_avatar_id})" );
}

// --- 3. Posts ---------------------------------------------------------------------

$ba_old_ids = array( 0 );
foreach ( $ba_old_logins as $ba_old_login ) {
	$ba_old = get_user_by( 'login', $ba_old_login );
	if ( $ba_old instanceof WP_User ) {
		$ba_old_ids[] = (int) $ba_old->ID;
	}
}

$ba_posts = get_posts(
	array(
		'post_type'      => 'post',
		'post_status'    => 'any',
		'posts_per_page' => -1,
		'orderby'        => 'ID',
		'order'          => 'ASC',
	)
);

$ba_reassigned = 0;
foreach ( $ba_posts as $ba_post ) {
	$ba_author = (int) $ba_post->post_author;
	if ( $ba_user_id > 0 && $ba_author === $ba_user_id ) {
		WP_CLI::log( "{$ba_tag} post #{$ba_post->ID}: already" );
		continue;
	}
	if ( ! in_array( $ba_author, $ba_old_ids, true ) ) {
		WP_CLI::log( "{$ba_tag} post #{$ba_post->ID}: author #{$ba_author} kept (not a placeholder author)" );
		continue;
	}
	++$ba_reassigned;
	if ( ! $ba_apply ) {
		WP_CLI::log( "{$ba_tag} post #{$ba_post->ID}: would set author (was #{$ba_author})" );
		continue;
	}
	$ba_result = wp_update_post(
		array(
			'ID'          => $ba_post->ID,
			'post_author' => $ba_user_id,
		),
		true
	);
	if ( is_wp_error( $ba_result ) ) {
		WP_CLI::warning( "{$ba_tag} post #{$ba_post->ID}: " . $ba_result->get_error_message() );
		continue;
	}
	WP_CLI::log( "{$ba_tag} post #{$ba_post->ID}: author #{$ba_author} → #{$ba_user_id}" );
}

WP_CLI::success( "{$ba_tag} done, posts " . ( $ba_apply ? 'reassigned' : 'to reassign' ) . ": {$ba_reassigned}" );
