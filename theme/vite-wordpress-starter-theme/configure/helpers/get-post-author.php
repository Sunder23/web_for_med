<?php
/**
 * Theme file.
 *
 * @package Vite_Starter
 */

/**
 * Author of a blog post for the card and the single post: name, archive link and avatar.
 * The avatar is the user ACF field `user_avatar` (group_starter_user_profile);
 * when it is empty `avatar_id` is 0 and the template falls back to get_avatar().
 *
 * @param int|WP_Post|null $post Post ID or object; the current post by default.
 * @return array{id:int,name:string,url:string,avatar_id:int}|null Null when the post has no author.
 */
function starter_get_post_author( $post = null ) {
	$post = get_post( $post );
	if ( ! $post instanceof WP_Post ) {
		return null;
	}

	$user_id = (int) $post->post_author;
	$user    = $user_id ? get_userdata( $user_id ) : false;
	if ( ! $user instanceof WP_User ) {
		error_log( 'WARN [blog] post #' . $post->ID . ' has no author' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- WARN-level failure log.
		return null;
	}

	return array(
		'id'        => $user_id,
		'name'      => $user->display_name,
		'url'       => get_author_posts_url( $user_id ),
		'avatar_id' => (int) starter_get_field( 'user_avatar', 'user_' . $user_id, 0 ),
	);
}
