<?php
/**
 * Plugin Name: AT Plus MCP Abilities
 * Description: Exposes full site administration to the WordPress MCP Adapter (atplus-mcp): any REST route, media upload, SEO meta (Rank Math or Yoast), WPML languages/translations, options.
 * Version: 1.0.0
 *
 * Must-use plugin. Every ability requires manage_options, so only an administrator
 * (the atplus-mcp application password user) can call it.
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'wp_register_ability' ) ) {
	return;
}

function atplus_mcp_can_manage(): bool {
	return current_user_can( 'manage_options' );
}

function atplus_mcp_meta( bool $readonly, bool $destructive ): array {
	return array(
		'annotations' => array(
			'readonly'    => $readonly,
			'destructive' => $destructive,
			'idempotent'  => $readonly,
		),
		'public'      => true,
		'show_in_rest' => true,
		'mcp'         => array(
			'public' => true,
			'type'   => 'tool',
		),
	);
}

function atplus_mcp_post_type_of( int $post_id ) {
	$type = get_post_type( $post_id );
	if ( ! $type ) {
		return new WP_Error( 'atplus_mcp_not_found', 'Post not found: ' . $post_id );
	}
	return $type;
}

add_action(
	'wp_abilities_api_categories_init',
	static function (): void {
		wp_register_ability_category(
			'atplus-admin',
			array(
				'label'       => 'AT Plus site administration',
				'description' => 'Full site administration for the AT Plus MCP server.',
			)
		);
	}
);

add_action(
	'wp_abilities_api_init',
	static function (): void {
		$object = array(
			'type'                 => 'object',
			'additionalProperties' => true,
		);

		wp_register_ability(
			'atplus/rest-request',
			array(
				'label'               => 'WordPress REST request',
				'description'         => 'Performs any WordPress REST API request internally as the current administrator and returns status, pagination headers and data. Covers posts, pages, media metadata, categories, tags, menus, menu items, navigation, templates, global styles, settings, users, comments, plugins, themes and plugin routes (wpml/v1, rankmath/v1, yoast/v1, contact-form-7/v1). Examples: GET /wp/v2/posts with params {"search":"seo","per_page":10}; POST /wp/v2/posts with body {"title":"...","content":"...","status":"draft"}; POST /wp/v2/pages/203 with body {"content":"..."}; GET /wp/v2/pages/203 with params {"context":"edit"} for raw content; DELETE /wp/v2/posts/5 moves to trash (params {"force":true} deletes permanently). Call GET / with params {"context":"help"} or GET /wp/v2 to discover routes.',
				'category'            => 'atplus-admin',
				'input_schema'        => array(
					'type'                 => 'object',
					'properties'           => array(
						'method' => array(
							'type' => 'string',
							'enum' => array( 'GET', 'POST', 'PUT', 'PATCH', 'DELETE' ),
						),
						'route'  => array(
							'type'        => 'string',
							'description' => 'REST route without /wp-json prefix, e.g. /wp/v2/posts/12',
							'pattern'     => '^/',
						),
						'params' => array_merge( $object, array( 'description' => 'Query parameters.' ) ),
						'body'   => array_merge( $object, array( 'description' => 'JSON body for POST/PUT/PATCH.' ) ),
					),
					'required'             => array( 'method', 'route' ),
					'additionalProperties' => false,
				),
				'execute_callback'    => static function ( $input ) {
					$request = new WP_REST_Request( $input['method'], $input['route'] );
					if ( ! empty( $input['params'] ) ) {
						$request->set_query_params( (array) $input['params'] );
					}
					if ( ! empty( $input['body'] ) ) {
						$request->set_header( 'Content-Type', 'application/json' );
						$request->set_body( wp_json_encode( $input['body'] ) );
					}
					$response = rest_do_request( $request );
					$headers  = $response->get_headers();
					return array(
						'status'     => $response->get_status(),
						'total'      => $headers['X-WP-Total'] ?? null,
						'totalPages' => $headers['X-WP-TotalPages'] ?? null,
						'data'       => rest_get_server()->response_to_data( $response, false ),
					);
				},
				'permission_callback' => 'atplus_mcp_can_manage',
				'meta'                => atplus_mcp_meta( false, true ),
			)
		);

		wp_register_ability(
			'atplus/upload-media',
			array(
				'label'               => 'Upload media',
				'description'         => 'Uploads a file to the media library from a public https URL or base64 content. Optionally sets alt text, title, caption, attaches it to a post and makes it the featured image.',
				'category'            => 'atplus-admin',
				'input_schema'        => array(
					'type'                 => 'object',
					'properties'           => array(
						'url'          => array( 'type' => 'string', 'pattern' => '^https://' ),
						'base64'       => array( 'type' => 'string' ),
						'filename'     => array( 'type' => 'string', 'pattern' => '^[A-Za-z0-9._-]{1,120}$' ),
						'title'        => array( 'type' => 'string' ),
						'alt'          => array( 'type' => 'string' ),
						'caption'      => array( 'type' => 'string' ),
						'postId'       => array( 'type' => 'integer', 'minimum' => 1 ),
						'setFeatured'  => array( 'type' => 'boolean' ),
					),
					'required'             => array( 'filename' ),
					'additionalProperties' => false,
				),
				'execute_callback'    => static function ( $input ) {
					require_once ABSPATH . 'wp-admin/includes/file.php';
					require_once ABSPATH . 'wp-admin/includes/media.php';
					require_once ABSPATH . 'wp-admin/includes/image.php';
					if ( ! empty( $input['url'] ) ) {
						$tmp = download_url( $input['url'], 60 );
						if ( is_wp_error( $tmp ) ) {
							return $tmp;
						}
					} elseif ( ! empty( $input['base64'] ) ) {
						$bytes = base64_decode( $input['base64'], true );
						if ( false === $bytes || strlen( $bytes ) > 20 * 1024 * 1024 ) {
							return new WP_Error( 'atplus_mcp_bad_file', 'Invalid base64 or file larger than 20 MiB.' );
						}
						$tmp = wp_tempnam( $input['filename'] );
						file_put_contents( $tmp, $bytes );
					} else {
						return new WP_Error( 'atplus_mcp_no_file', 'Provide url or base64.' );
					}
					$post_id = (int) ( $input['postId'] ?? 0 );
					$id      = media_handle_sideload( array( 'name' => $input['filename'], 'tmp_name' => $tmp ), $post_id, $input['title'] ?? null );
					if ( is_wp_error( $id ) ) {
						@unlink( $tmp );
						return $id;
					}
					if ( isset( $input['alt'] ) ) {
						update_post_meta( $id, '_wp_attachment_image_alt', sanitize_text_field( $input['alt'] ) );
					}
					if ( isset( $input['caption'] ) ) {
						wp_update_post( array( 'ID' => $id, 'post_excerpt' => $input['caption'] ) );
					}
					if ( $post_id && ! empty( $input['setFeatured'] ) ) {
						set_post_thumbnail( $post_id, $id );
					}
					return array( 'id' => $id, 'url' => wp_get_attachment_url( $id ), 'mime' => get_post_mime_type( $id ) );
				},
				'permission_callback' => 'atplus_mcp_can_manage',
				'meta'                => atplus_mcp_meta( false, false ),
			)
		);

		wp_register_ability(
			'atplus/set-seo-meta',
			array(
				'label'               => 'Set SEO meta',
				'description'         => 'Sets SEO title (Rank Math or Yoast SEO, whichever is active), meta description, focus keyword(s), robots directives and canonical URL for a post/page (objectType "post") or category/tag (objectType "term"). Omitted fields are unchanged; an empty string clears a field. Returns previous and new values.',
				'category'            => 'atplus-admin',
				'input_schema'        => array(
					'type'                 => 'object',
					'properties'           => array(
						'objectType'   => array( 'type' => 'string', 'enum' => array( 'post', 'term' ), 'default' => 'post' ),
						'id'           => array( 'type' => 'integer', 'minimum' => 1 ),
						'title'        => array( 'type' => 'string' ),
						'description'  => array( 'type' => 'string' ),
						'focusKeyword' => array( 'type' => 'string', 'description' => 'Comma-separated; first is primary (Yoast free keeps only the first).' ),
						'robots'       => array( 'type' => 'array', 'items' => array( 'type' => 'string', 'enum' => array( 'index', 'noindex', 'follow', 'nofollow', 'noarchive', 'noimageindex', 'nosnippet' ) ) ),
						'canonical'    => array( 'type' => 'string' ),
					),
					'required'             => array( 'id' ),
					'additionalProperties' => false,
				),
				'execute_callback'    => static function ( $input ) {
					$is_term = 'term' === ( $input['objectType'] ?? 'post' );
					$id      = (int) $input['id'];
					$term    = $is_term ? get_term( $id ) : null;
					if ( $is_term ? ( ! $term || is_wp_error( $term ) ) : ! get_post( $id ) ) {
						return new WP_Error( 'atplus_mcp_not_found', 'Object not found: ' . $id );
					}
					if ( defined( 'RANK_MATH_VERSION' ) ) {
						return atplus_mcp_rank_math_seo( $input, $is_term, $id );
					}
					if ( defined( 'WPSEO_VERSION' ) ) {
						return $is_term ? atplus_mcp_yoast_term_seo( $input, $term ) : atplus_mcp_yoast_post_seo( $input, $id );
					}
					return new WP_Error( 'atplus_mcp_no_seo_plugin', 'Neither Rank Math nor Yoast SEO is active.' );
				},
				'permission_callback' => 'atplus_mcp_can_manage',
				'meta'                => atplus_mcp_meta( false, false ),
			)
		);

		wp_register_ability(
			'atplus/wpml-languages',
			array(
				'label'               => 'WPML languages',
				'description'         => 'Lists active WPML languages (code, native name, default flag).',
				'category'            => 'atplus-admin',
				'input_schema'        => array( 'type' => 'object', 'properties' => array(), 'additionalProperties' => false ),
				'execute_callback'    => static function () {
					$languages = apply_filters( 'wpml_active_languages', null, array( 'skip_missing' => 0 ) );
					if ( ! is_array( $languages ) ) {
						return new WP_Error( 'atplus_mcp_no_wpml', 'WPML is not active.' );
					}
					$default = apply_filters( 'wpml_default_language', null );
					$out     = array();
					foreach ( $languages as $code => $language ) {
						$out[] = array( 'code' => $code, 'name' => $language['native_name'] ?? $code, 'default' => $code === $default );
					}
					return array( 'default' => $default, 'languages' => $out );
				},
				'permission_callback' => 'atplus_mcp_can_manage',
				'meta'                => atplus_mcp_meta( true, false ),
			)
		);

		wp_register_ability(
			'atplus/wpml-translations',
			array(
				'label'               => 'WPML translations of a post',
				'description'         => 'Returns the language of a post/page/term and the ids of all its translations. objectType "post" (any post type) or "term" (taxonomy required).',
				'category'            => 'atplus-admin',
				'input_schema'        => array(
					'type'                 => 'object',
					'properties'           => array(
						'objectType' => array( 'type' => 'string', 'enum' => array( 'post', 'term' ), 'default' => 'post' ),
						'id'         => array( 'type' => 'integer', 'minimum' => 1 ),
						'taxonomy'   => array( 'type' => 'string' ),
					),
					'required'             => array( 'id' ),
					'additionalProperties' => false,
				),
				'execute_callback'    => static function ( $input ) {
					$element_type = atplus_mcp_wpml_element_type( $input );
					if ( is_wp_error( $element_type ) ) {
						return $element_type;
					}
					$element_id = atplus_mcp_wpml_element_id( $input );
					$trid       = apply_filters( 'wpml_element_trid', null, $element_id, $element_type );
					$language   = apply_filters( 'wpml_element_language_code', null, array( 'element_id' => $element_id, 'element_type' => $element_type ) );
					$out        = array();
					foreach ( (array) apply_filters( 'wpml_get_element_translations', null, $trid, $element_type ) as $code => $t ) {
						$out[] = array( 'language' => $code, 'id' => (int) $t->element_id, 'original' => (bool) $t->original );
					}
					return array( 'id' => (int) $input['id'], 'language' => $language, 'trid' => $trid, 'translations' => $out );
				},
				'permission_callback' => 'atplus_mcp_can_manage',
				'meta'                => atplus_mcp_meta( true, false ),
			)
		);

		wp_register_ability(
			'atplus/wpml-set-language',
			array(
				'label'               => 'WPML set language / link translation',
				'description'         => 'Sets the language of a post/page/term and optionally links it as a translation of another object (translationOf). Typical flow: create the translated post via atplus-rest-request, then call this with language and translationOf = original id.',
				'category'            => 'atplus-admin',
				'input_schema'        => array(
					'type'                 => 'object',
					'properties'           => array(
						'objectType'    => array( 'type' => 'string', 'enum' => array( 'post', 'term' ), 'default' => 'post' ),
						'id'            => array( 'type' => 'integer', 'minimum' => 1 ),
						'taxonomy'      => array( 'type' => 'string' ),
						'language'      => array( 'type' => 'string', 'pattern' => '^[a-z]{2}(-[a-z]{2,4})?$' ),
						'translationOf' => array( 'type' => 'integer', 'minimum' => 1 ),
					),
					'required'             => array( 'id', 'language' ),
					'additionalProperties' => false,
				),
				'execute_callback'    => static function ( $input ) {
					$element_type = atplus_mcp_wpml_element_type( $input );
					if ( is_wp_error( $element_type ) ) {
						return $element_type;
					}
					$trid   = null;
					$source = null;
					if ( ! empty( $input['translationOf'] ) ) {
						$original = atplus_mcp_wpml_element_id( array_merge( $input, array( 'id' => $input['translationOf'] ) ) );
						$trid     = apply_filters( 'wpml_element_trid', null, $original, $element_type );
						$source   = apply_filters( 'wpml_element_language_code', null, array( 'element_id' => $original, 'element_type' => $element_type ) );
						if ( ! $trid ) {
							return new WP_Error( 'atplus_mcp_no_trid', 'Original has no WPML translation group.' );
						}
					}
					do_action(
						'wpml_set_element_language_details',
						array(
							'element_id'           => atplus_mcp_wpml_element_id( $input ),
							'element_type'         => $element_type,
							'trid'                 => $trid,
							'language_code'        => $input['language'],
							'source_language_code' => $source,
						)
					);
					return wp_get_ability( 'atplus/wpml-translations' )->execute( array_intersect_key( $input, array_flip( array( 'objectType', 'id', 'taxonomy' ) ) ) );
				},
				'permission_callback' => 'atplus_mcp_can_manage',
				'meta'                => atplus_mcp_meta( false, false ),
			)
		);

		wp_register_ability(
			'atplus/get-option',
			array(
				'label'               => 'Get option',
				'description'         => 'Reads any WordPress option by name (wp_options), including plugin settings not exposed through /wp/v2/settings.',
				'category'            => 'atplus-admin',
				'input_schema'        => array(
					'type'                 => 'object',
					'properties'           => array( 'name' => array( 'type' => 'string', 'minLength' => 1, 'maxLength' => 191 ) ),
					'required'             => array( 'name' ),
					'additionalProperties' => false,
				),
				'execute_callback'    => static function ( $input ) {
					$missing = new stdClass();
					$value   = get_option( $input['name'], $missing );
					return array( 'name' => $input['name'], 'exists' => $value !== $missing, 'value' => $value === $missing ? null : $value );
				},
				'permission_callback' => 'atplus_mcp_can_manage',
				'meta'                => atplus_mcp_meta( true, false ),
			)
		);

		wp_register_ability(
			'atplus/update-option',
			array(
				'label'               => 'Update option',
				'description'         => 'Sets any WordPress option (wp_options). Returns the previous value. Changing siteurl/home/active_plugins/template/stylesheet can break the site.',
				'category'            => 'atplus-admin',
				'input_schema'        => array(
					'type'                 => 'object',
					'properties'           => array(
						'name'  => array( 'type' => 'string', 'minLength' => 1, 'maxLength' => 191 ),
						'value' => array( 'type' => array( 'string', 'number', 'integer', 'boolean', 'object', 'array', 'null' ) ),
					),
					'required'             => array( 'name', 'value' ),
					'additionalProperties' => false,
				),
				'execute_callback'    => static function ( $input ) {
					$before = get_option( $input['name'] );
					update_option( $input['name'], $input['value'] );
					return array( 'name' => $input['name'], 'before' => $before, 'after' => get_option( $input['name'] ) );
				},
				'permission_callback' => 'atplus_mcp_can_manage',
				'meta'                => atplus_mcp_meta( false, true ),
			)
		);
	}
);

function atplus_mcp_wpml_element_type( array $input ) {
	if ( 'term' === ( $input['objectType'] ?? 'post' ) ) {
		if ( empty( $input['taxonomy'] ) || ! taxonomy_exists( $input['taxonomy'] ) ) {
			return new WP_Error( 'atplus_mcp_taxonomy', 'A valid taxonomy is required for terms.' );
		}
		return 'tax_' . $input['taxonomy'];
	}
	$type = atplus_mcp_post_type_of( (int) $input['id'] );
	return is_wp_error( $type ) ? $type : 'post_' . $type;
}

// WPML identifies terms by term_taxonomy_id, posts by ID.
function atplus_mcp_wpml_element_id( array $input ): int {
	if ( 'term' === ( $input['objectType'] ?? 'post' ) ) {
		$term = get_term( (int) $input['id'], $input['taxonomy'] );
		return $term && ! is_wp_error( $term ) ? (int) $term->term_taxonomy_id : 0;
	}
	return (int) $input['id'];
}

function atplus_mcp_rank_math_seo( array $input, bool $is_term, int $id ): array {
	$map    = array(
		'title'        => 'rank_math_title',
		'description'  => 'rank_math_description',
		'focusKeyword' => 'rank_math_focus_keyword',
		'robots'       => 'rank_math_robots',
		'canonical'    => 'rank_math_canonical_url',
	);
	$get    = $is_term ? 'get_term_meta' : 'get_post_meta';
	$update = $is_term ? 'update_term_meta' : 'update_post_meta';
	$delete = $is_term ? 'delete_term_meta' : 'delete_post_meta';
	$before = array();
	$after  = array();
	foreach ( $map as $field => $key ) {
		$before[ $field ] = $get( $id, $key, true );
		if ( ! array_key_exists( $field, $input ) ) {
			continue;
		}
		if ( '' === $input[ $field ] || array() === $input[ $field ] ) {
			$delete( $id, $key );
		} else {
			$update( $id, $key, $input[ $field ] );
		}
		$after[ $field ] = $get( $id, $key, true );
	}
	if ( ! $is_term ) {
		clean_post_cache( $id );
	}
	return array( 'plugin' => 'rank-math', 'id' => $id, 'before' => $before, 'after' => $after );
}

function atplus_mcp_first_keyword( string $value ): string {
	return trim( explode( ',', $value )[0] );
}

function atplus_mcp_yoast_post_robots( int $id ): array {
	$robots  = array();
	$noindex = get_post_meta( $id, '_yoast_wpseo_meta-robots-noindex', true );
	if ( '1' === (string) $noindex ) {
		$robots[] = 'noindex';
	} elseif ( '2' === (string) $noindex ) {
		$robots[] = 'index';
	}
	if ( '1' === (string) get_post_meta( $id, '_yoast_wpseo_meta-robots-nofollow', true ) ) {
		$robots[] = 'nofollow';
	}
	$adv = (string) get_post_meta( $id, '_yoast_wpseo_meta-robots-adv', true );
	foreach ( array_filter( array_map( 'trim', explode( ',', $adv ) ) ) as $flag ) {
		if ( 'none' !== $flag ) {
			$robots[] = $flag;
		}
	}
	return $robots;
}

function atplus_mcp_yoast_post_state( int $id ): array {
	return array(
		'title'        => get_post_meta( $id, '_yoast_wpseo_title', true ),
		'description'  => get_post_meta( $id, '_yoast_wpseo_metadesc', true ),
		'focusKeyword' => get_post_meta( $id, '_yoast_wpseo_focuskw', true ),
		'robots'       => atplus_mcp_yoast_post_robots( $id ),
		'canonical'    => get_post_meta( $id, '_yoast_wpseo_canonical', true ),
	);
}

// Yoast keeps per-post robots in three keys: noindex (1 = noindex, 2 = index, absent = default),
// nofollow (1 = nofollow) and a comma list of advanced flags.
function atplus_mcp_yoast_post_seo( array $input, int $id ): array {
	$before = atplus_mcp_yoast_post_state( $id );
	$text   = array(
		'title'       => '_yoast_wpseo_title',
		'description' => '_yoast_wpseo_metadesc',
		'canonical'   => '_yoast_wpseo_canonical',
	);
	if ( array_key_exists( 'focusKeyword', $input ) ) {
		$input['focusKeyword'] = atplus_mcp_first_keyword( (string) $input['focusKeyword'] );
		$text['focusKeyword']  = '_yoast_wpseo_focuskw';
	}
	foreach ( $text as $field => $key ) {
		if ( ! array_key_exists( $field, $input ) ) {
			continue;
		}
		if ( '' === $input[ $field ] ) {
			delete_post_meta( $id, $key );
		} else {
			update_post_meta( $id, $key, $input[ $field ] );
		}
	}
	if ( array_key_exists( 'robots', $input ) ) {
		$robots = (array) $input['robots'];
		if ( in_array( 'noindex', $robots, true ) ) {
			update_post_meta( $id, '_yoast_wpseo_meta-robots-noindex', '1' );
		} elseif ( in_array( 'index', $robots, true ) ) {
			update_post_meta( $id, '_yoast_wpseo_meta-robots-noindex', '2' );
		} else {
			delete_post_meta( $id, '_yoast_wpseo_meta-robots-noindex' );
		}
		if ( in_array( 'nofollow', $robots, true ) ) {
			update_post_meta( $id, '_yoast_wpseo_meta-robots-nofollow', '1' );
		} else {
			delete_post_meta( $id, '_yoast_wpseo_meta-robots-nofollow' );
		}
		$adv = array_values( array_intersect( array( 'noimageindex', 'noarchive', 'nosnippet' ), $robots ) );
		if ( $adv ) {
			update_post_meta( $id, '_yoast_wpseo_meta-robots-adv', implode( ',', $adv ) );
		} else {
			delete_post_meta( $id, '_yoast_wpseo_meta-robots-adv' );
		}
	}
	clean_post_cache( $id );
	atplus_mcp_yoast_reindex( $id, 'post' );
	return array( 'plugin' => 'yoast', 'id' => $id, 'before' => $before, 'after' => atplus_mcp_yoast_post_state( $id ) );
}

function atplus_mcp_yoast_term_state( WP_Term $term ): array {
	$meta    = (array) WPSEO_Taxonomy_Meta::get_term_meta( $term, $term->taxonomy );
	$noindex = $meta['wpseo_noindex'] ?? 'default';
	return array(
		'title'        => $meta['wpseo_title'] ?? '',
		'description'  => $meta['wpseo_desc'] ?? '',
		'focusKeyword' => $meta['wpseo_focuskw'] ?? '',
		'robots'       => 'default' === $noindex ? array() : array( $noindex ),
		'canonical'    => $meta['wpseo_canonical'] ?? '',
	);
}

// Yoast stores term SEO in the wpseo_taxonomy_meta option; set_values replaces the whole
// record, so the current values are merged in first. Terms have no nofollow/advanced flags.
function atplus_mcp_yoast_term_seo( array $input, WP_Term $term ): array {
	$before = atplus_mcp_yoast_term_state( $term );
	$values = (array) WPSEO_Taxonomy_Meta::get_term_meta( $term, $term->taxonomy );
	$map    = array(
		'title'        => 'wpseo_title',
		'description'  => 'wpseo_desc',
		'focusKeyword' => 'wpseo_focuskw',
		'canonical'    => 'wpseo_canonical',
	);
	foreach ( $map as $field => $key ) {
		if ( array_key_exists( $field, $input ) ) {
			$values[ $key ] = 'focusKeyword' === $field ? atplus_mcp_first_keyword( (string) $input[ $field ] ) : $input[ $field ];
		}
	}
	if ( array_key_exists( 'robots', $input ) ) {
		$robots                  = (array) $input['robots'];
		$values['wpseo_noindex'] = in_array( 'noindex', $robots, true ) ? 'noindex' : ( in_array( 'index', $robots, true ) ? 'index' : 'default' );
	}
	WPSEO_Taxonomy_Meta::set_values( $term->term_id, $term->taxonomy, $values );
	atplus_mcp_yoast_reindex( $term->term_id, 'term' );
	return array( 'plugin' => 'yoast', 'id' => $term->term_id, 'before' => $before, 'after' => atplus_mcp_yoast_term_state( $term ) );
}

// Yoast renders from its indexables table, so rebuild the row after changing meta directly.
function atplus_mcp_yoast_reindex( int $id, string $type ): void {
	if ( ! function_exists( 'YoastSEO' ) ) {
		return;
	}
	try {
		$repository = YoastSEO()->classes->get( 'Yoast\\WP\\SEO\\Repositories\\Indexable_Repository' );
		$builder    = YoastSEO()->classes->get( 'Yoast\\WP\\SEO\\Builders\\Indexable_Builder' );
		$builder->build_for_id_and_type( $id, $type, $repository->find_by_id_and_type( $id, $type, false ) );
	} catch ( Throwable $e ) {
		error_log( 'atplus-mcp: Yoast reindex failed for ' . $type . ' ' . $id . ': ' . $e->getMessage() );
	}
}
