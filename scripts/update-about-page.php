<?php
/**
 * Fill the "Про нас" page (slug `about`) with the November 2026 design
 * (Figma huDLClWg3dIpXyzLMuVHEs, desktop 3241:4188, mobile 3255:874).
 *
 * Builds post_content from three section blocks:
 *   acf/section-about-intro — intro text + "No Future Now" image + caption;
 *   acf/section-team        — four members with roles and hover labels;
 *   acf/section-owner-cta   — owner's quote, illustration, Telegram / WhatsApp buttons.
 *
 * Media from scripts/data/about/ is sideloaded once and tagged with
 * `_starter_source` (`about/<file>`), so re-running reuses the attachments.
 * Messenger URLs are placeholders (`https://t.me/`, `https://wa.me/`) until the
 * client sends the real ones — edit them in the page editor.
 *
 * Args:
 *   (none)  dry run — logs what would change, writes nothing;
 *   apply   upload media and write the page;
 *   force   (with apply) overwrite a page that already has acf/section-* blocks.
 *
 * Run: docker compose -f docker/compose.yml run --rm wpcli wp eval-file /scripts/update-about-page.php [apply] [force]
 * (On Windows/Git Bash, prefix with MSYS_NO_PATHCONV=1.)
 *
 * @package Vite_Starter
 */

if ( ! defined( 'WP_CLI' ) ) {
	echo "This script must be run via WP-CLI (wp eval-file).\n";
	exit( 1 );
}

$ab_args  = isset( $args ) && is_array( $args ) ? $args : array();
$ab_apply = in_array( 'apply', $ab_args, true );
$ab_force = in_array( 'force', $ab_args, true );
$ab_dir   = __DIR__ . '/data/about/';

$ab_stats = array(
	'uploaded' => 0,
	'reused'   => 0,
	'warnings' => 0,
);

/**
 * Resolve a file from scripts/data/about/ to an attachment ID.
 *
 * @param string $file Basename inside scripts/data/about/.
 * @param string $alt  Alt text for a new upload.
 * @return int Attachment ID, -1 when it would be uploaded (dry run), 0 on failure.
 */
$ab_attachment = static function ( $file, $alt ) use ( $ab_dir, $ab_apply, &$ab_stats ) {
	$source = 'about/' . $file;
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
		++$ab_stats['reused'];
		WP_CLI::log( "[about-page] reuse {$source} → #{$ids[0]}" );
		return (int) $ids[0];
	}

	$path = $ab_dir . $file;
	if ( ! file_exists( $path ) ) {
		WP_CLI::warning( "[about-page] source file missing: {$path}" );
		++$ab_stats['warnings'];
		return 0;
	}

	if ( ! $ab_apply ) {
		WP_CLI::log( "[about-page] would upload {$source}" );
		return -1;
	}

	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	// media_handle_sideload() moves the file, so hand it a temp copy.
	$tmp = wp_tempnam( $file );
	if ( ! $tmp || ! copy( $path, $tmp ) ) {
		WP_CLI::warning( "[about-page] cannot copy {$path} to a temp file" );
		++$ab_stats['warnings'];
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
		WP_CLI::warning( "[about-page] upload failed for {$source}: " . $id->get_error_message() );
		++$ab_stats['warnings'];
		return 0;
	}

	update_post_meta( $id, '_starter_source', $source );
	update_post_meta( $id, '_wp_attachment_image_alt', $alt );
	++$ab_stats['uploaded'];
	WP_CLI::log( "[about-page] uploaded {$source} → #{$id}" );
	return (int) $id;
};

/**
 * Build ACF block data: every value gets its `_name` → field key reference.
 *
 * @param string $slug   Section slug (`about-intro`).
 * @param array  $values Flat `name => value` map (repeater rows as `rows_0_sub`).
 * @return array Block `data` attribute.
 */
$ab_block_data = static function ( $slug, array $values ) {
	$prefix = 'field_starter_section_' . str_replace( '-', '_', $slug ) . '_';
	$data   = array();

	foreach ( $values as $name => $value ) {
		$data[ $name ] = $value;
		// Repeater sub fields: `members_0_name` → `field_…_members_name`.
		$data[ '_' . $name ] = $prefix . preg_replace( '/_\d+_/', '_', $name );
	}

	return $data;
};

/**
 * One serialisable ACF block.
 *
 * @param string $slug Section slug.
 * @param array  $data Block data.
 * @return array Parsed-block shape for serialize_blocks().
 */
$ab_block = static function ( $slug, array $data ) {
	return array(
		'blockName'    => 'acf/section-' . $slug,
		'attrs'        => array(
			'name' => 'acf/section-' . $slug,
			'data' => $data,
			'mode' => 'preview',
		),
		'innerBlocks'  => array(),
		'innerHTML'    => '',
		'innerContent' => array(),
	);
};

// --- Page ----------------------------------------------------------------

$ab_page = get_page_by_path( 'about', OBJECT, 'page' );

if ( ! $ab_page ) {
	WP_CLI::error( '[about-page] page with slug "about" not found.' );
}

WP_CLI::log( sprintf( '[about-page] page #%d "%s"%s', $ab_page->ID, $ab_page->post_title, $ab_apply ? '' : ' (dry run)' ) );

$ab_old_content = (string) $ab_page->post_content;

if ( false !== strpos( $ab_old_content, '<!-- wp:acf/section-' ) && ! $ab_force ) {
	WP_CLI::warning( '[about-page] page already has acf/section-* blocks; pass "force" to overwrite.' );
	WP_CLI::halt( 0 );
}

// --- Content (texts from Figma 3241:4193, 3241:4199, 3241:4222) -----------

$ab_intro_text = implode(
	"\n\n",
	array(
		'У вашого бізнесу вже є своє обличчя. Люди повертаються до вас, радять знайомим, цінують вашу роботу й увагу. Але той, хто чує про вас уперше, ще нічого цього не знає. І має вирішити, чи варто прийти.',
		'Клієнти можуть знаходити вас і без сайту: через рекомендації, соцмережі чи випадково побачену вивіску. Але не кожен готовий одразу записатися. Хтось захоче дізнатися більше про послуги, побачити спеціалістів, порівняти ціни або просто придивитися без поспіху.',
		'Ми віримо, що сайт має бути схожим на бізнес, для якого його створили: передавати його характер, говорити його голосом і бути уважним до відвідувача. Щоб людина, яка ще кілька хвилин тому нічого про вас не знала, пішла з відчуттям, що ви вже трохи знайомі.',
		'Ми невелика команда. Разом думаємо над структурою, пишемо, малюємо й розробляємо. Кожен займається своєю справою, але всі розуміють спільну ідею й зберігають її до останньої деталі.',
		'Ми хочемо, щоб перше знайомство з вами ставало початком довгої історії.',
	)
);

$ab_members = array(
	array( 'team-syschuk-2x.png', 'Валерій Сищук', 'Founder / Project Manager / SEO & Web Analytics', 'Іронічний прагматик' ),
	array( 'team-buhutska-2x.png', 'Іра Бугуцька', 'Backend Developer / DevOps Engineer', 'Володарка коду' ),
	array( 'team-unhurian-2x.png', 'Оля Унгурян', 'Web Designer', 'Адвокат деталей' ),
	array( 'team-kravchuk-2x.png', 'Макс Кравчук', 'Frontend Developer / AI Explorer', 'Верстає силою думки' ),
);

$ab_team = array( 'members' => count( $ab_members ) );
foreach ( $ab_members as $ab_i => $ab_member ) {
	$ab_team[ "members_{$ab_i}_photo" ] = $ab_attachment( $ab_member[0], $ab_member[1] );
	$ab_team[ "members_{$ab_i}_name" ]  = $ab_member[1];
	$ab_team[ "members_{$ab_i}_role" ]  = $ab_member[2];
	$ab_team[ "members_{$ab_i}_label" ] = $ab_member[3];
}

$ab_blocks = array(
	$ab_block(
		'about-intro',
		$ab_block_data(
			'about-intro',
			array(
				'text'    => $ab_intro_text,
				'image'   => $ab_attachment( 'about-hero-2x.png', 'Команда web4med — No Future Now' ),
				'caption' => '[*Inspired by “No Future Now”, Dmitry Dulfan, 1997]',
			)
		)
	),
	$ab_block( 'team', $ab_block_data( 'team', $ab_team ) ),
	$ab_block(
		'owner-cta',
		$ab_block_data(
			'owner-cta',
			array(
				'image'           => $ab_attachment( 'owner-optimism-2x.png', 'Optimism: 99%' ),
				'text'            => "Валерій пережив кілька смертей SEO, зміну алгоритмів та не одну веб революцію.\nОптимізму не втратив",
				'subtext'         => 'Обговоріть з ним ідею чи задачу особисто',
				'buttons'         => 2,
				'buttons_0_link'  => array(
					'title'  => 'Написати в Telegram',
					'url'    => 'https://t.me/',
					'target' => '_blank',
				),
				'buttons_0_style' => 'primary',
				'buttons_1_link'  => array(
					'title'  => 'Зв’язатися у Whatsapp',
					'url'    => 'https://wa.me/',
					'target' => '_blank',
				),
				'buttons_1_style' => 'outline',
			)
		)
	),
);

foreach ( $ab_blocks as $ab_built ) {
	WP_CLI::log( sprintf( '[about-page] block %s: %d field(s)', $ab_built['blockName'], count( $ab_built['attrs']['data'] ) / 2 ) );
}

WP_CLI::warning( '[about-page] Telegram / WhatsApp URLs are placeholders (https://t.me/, https://wa.me/) — replace them in the editor.' );
++$ab_stats['warnings'];

// --- Menu check (report only) ----------------------------------------------

foreach ( wp_get_nav_menus() as $ab_menu ) {
	foreach ( (array) wp_get_nav_menu_items( $ab_menu->term_id ) as $ab_item ) {
		if ( false === mb_stripos( (string) $ab_item->title, 'про нас' ) ) {
			continue;
		}
		$ab_points_here = 'post_type' === $ab_item->type && (int) $ab_item->object_id === (int) $ab_page->ID;
		$ab_points_here = $ab_points_here || untrailingslashit( (string) $ab_item->url ) === untrailingslashit( get_permalink( $ab_page ) );
		if ( $ab_points_here ) {
			WP_CLI::log( "[about-page] menu \"{$ab_menu->name}\": \"{$ab_item->title}\" → page #{$ab_page->ID}" );
		} else {
			WP_CLI::warning( "[about-page] menu \"{$ab_menu->name}\": \"{$ab_item->title}\" points to {$ab_item->url}, not the about page" );
			++$ab_stats['warnings'];
		}
	}
}

// --- Write ---------------------------------------------------------------

if ( $ab_apply ) {
	if ( '' !== trim( $ab_old_content ) ) {
		update_post_meta( $ab_page->ID, '_starter_pre_blocks_content', wp_slash( $ab_old_content ) );
		WP_CLI::log( '[about-page] previous content backed up to _starter_pre_blocks_content' );
	}

	$ab_result = wp_update_post(
		array(
			'ID'           => $ab_page->ID,
			'post_content' => wp_slash( serialize_blocks( $ab_blocks ) ),
		),
		true
	);

	if ( is_wp_error( $ab_result ) ) {
		WP_CLI::error( '[about-page] wp_update_post failed — ' . $ab_result->get_error_message() );
	}

	WP_CLI::log( "[about-page] page #{$ab_page->ID} saved" );
}

WP_CLI::success(
	sprintf(
		'[about-page] %s: %d upload(s), %d reused, %d warning(s).',
		$ab_apply ? 'applied' : 'dry run',
		$ab_stats['uploaded'],
		$ab_stats['reused'],
		$ab_stats['warnings']
	)
);
