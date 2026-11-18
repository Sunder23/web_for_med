<?php
/**
 * Khmilclinic case (`cases/khmilclinic-redesign`): refill from the design
 * (Figma huDLClWg3dIpXyzLMuVHEs, desktop 3402:462, content column 3402:474).
 *
 *   1. Media: intro picture (3402:501) and the SE Ranking chart (3408:418) from
 *      scripts/data/khmilclinic/, uploaded once (looked up by `_starter_source`).
 *   2. ACF: case_intro (project_name, lead text, image), case_details
 *      (duration, live URL). The client field is left as it is.
 *   3. Terms: case_deliverable, case_technology, case_status (created if missing).
 *   4. post_content rebuilt as block markup, texts verbatim from the design;
 *      chapter headings get the block style `is-style-chapter`.
 *
 * The title is not touched (it already matches the design). The original content
 * is kept once in post meta `_khmilclinic_content_backup`. Legacy meta
 * (case_facts, case_sections, case_results_*) is not rendered and is left alone.
 *
 * Args:
 *   (none)  dry run — logs what would change, writes nothing;
 *   apply   upload the media and write the post.
 *
 * Run: docker compose -f docker/compose.yml run --rm wpcli wp eval-file /scripts/update-khmilclinic-case.php [apply]
 * (On Windows/Git Bash, prefix with MSYS_NO_PATHCONV=1.)
 *
 * @package Vite_Starter
 */

if ( ! defined( 'WP_CLI' ) ) {
	echo "This script must be run via WP-CLI (wp eval-file).\n";
	exit( 1 );
}

if ( ! function_exists( 'update_field' ) ) {
	WP_CLI::error( '[khmilclinic-content] Secure Custom Fields is not active' );
}

$kc_args  = isset( $args ) && is_array( $args ) ? $args : array();
$kc_apply = in_array( 'apply', $kc_args, true );
$kc_tag   = '[khmilclinic-content]';

$kc_post = get_page_by_path( 'khmilclinic-redesign', OBJECT, 'cases' );
if ( ! $kc_post instanceof WP_Post ) {
	WP_CLI::error( "{$kc_tag} case `khmilclinic-redesign` not found" );
}

WP_CLI::log( "{$kc_tag} case #{$kc_post->ID}, mode: " . ( $kc_apply ? 'apply' : 'dry run' ) );

$kc_changes = 0;

/**
 * Attachment for a file from scripts/data/khmilclinic/: reuse or upload.
 *
 * @param string $file File name.
 * @param string $alt  Alt text.
 * @return int Attachment ID, -1 when it would be uploaded (dry run), 0 on failure.
 */
$kc_attachment = static function ( $file, $alt ) use ( $kc_apply, $kc_tag, &$kc_changes ) {
	$source = 'khmilclinic/' . $file;
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
		WP_CLI::log( "{$kc_tag} reuse {$source} → #{$ids[0]}" );
		return (int) $ids[0];
	}

	$path = __DIR__ . '/data/' . $source;
	if ( ! file_exists( $path ) ) {
		WP_CLI::warning( "{$kc_tag} source file missing: {$path}" );
		return 0;
	}
	++$kc_changes;
	if ( ! $kc_apply ) {
		WP_CLI::log( "{$kc_tag} would upload {$source}" );
		return -1;
	}

	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	// media_handle_sideload() moves the file, so hand it a temp copy.
	$tmp = wp_tempnam( $file );
	if ( ! $tmp || ! copy( $path, $tmp ) ) {
		WP_CLI::warning( "{$kc_tag} cannot copy {$path} to a temp file" );
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
		WP_CLI::warning( "{$kc_tag} upload failed for {$source}: " . $id->get_error_message() );
		return 0;
	}
	update_post_meta( $id, '_starter_source', $source );
	update_post_meta( $id, '_wp_attachment_image_alt', $alt );
	WP_CLI::log( "{$kc_tag} uploaded {$source} → #{$id}" );
	return (int) $id;
};

// --- 1. Media ---------------------------------------------------------------------

$kc_intro_alt = 'Ілюстрація до кейсу оновлення сайту клініки професора Стефана Хміля';
$kc_chart_alt = 'Графік показів і кліків сайту khmilclinic.com.ua у пошуку за даними SE Ranking';
$kc_intro_id  = $kc_attachment( 'khmilclinic-intro.jpg', $kc_intro_alt );
$kc_chart_id  = $kc_attachment( 'khmilclinic-se-ranking.png', $kc_chart_alt );

if ( 0 === $kc_intro_id || 0 === $kc_chart_id ) {
	WP_CLI::error( "{$kc_tag} media is missing, nothing written" );
}

// --- 2. ACF -----------------------------------------------------------------------

$kc_lead = 'Понад два роки наша команда оновлювала сайт клініки репродуктивної медицини “Медичний центр “Клініка професора Стефана Хміля”, який налічує понад 900 сторінок. Перебудовували структуру, оновили дизайн і SEO, при цьому сайт продовжував працювати та наповнюватися новими матеріалами. За цей час провели шість релізів, уникнувши просідання пошукового трафіку.';

$kc_intro_now = get_field( 'case_intro', $kc_post->ID, false );
$kc_intro_now = is_array( $kc_intro_now ) ? $kc_intro_now : array();

// Keys as stored (unformatted): field keys of configure/acf/acf-json/group_6ba8f99a.json.
$kc_fields = array(
	'case_intro'   => array(
		'key'    => 'field_c7d41e11',
		'values' => array(
			'field_c7d41e12' => (string) ( $kc_intro_now['field_c7d41e12'] ?? '' ), // client: keep.
			'field_c7d41e13' => 'khmil clinic',
			'field_c7d41e20' => '<p>' . $kc_lead . '</p>',
			'field_c7d41e21' => max( 0, $kc_intro_id ),
		),
	),
	'case_details' => array(
		'key'    => 'field_c7d41e31',
		'values' => array(
			'field_c7d41e32' => 'more than 2 years',
			'field_c7d41e33' => 'https://khmilclinic.com.ua/',
			'field_c7d41e34' => '',
		),
	),
);

foreach ( $kc_fields as $kc_name => $kc_field ) {
	$now = get_field( $kc_name, $kc_post->ID, false );
	$now = is_array( $now ) ? $now : array();
	$new = $kc_field['values'];

	$diff = array();
	foreach ( $new as $sub_key => $value ) {
		if ( (string) ( $now[ $sub_key ] ?? '' ) !== (string) $value ) {
			$diff[] = $sub_key;
		}
	}
	if ( ! $diff ) {
		WP_CLI::log( "{$kc_tag} {$kc_name}: already" );
		continue;
	}

	++$kc_changes;
	if ( ! $kc_apply ) {
		WP_CLI::log( "{$kc_tag} {$kc_name}: would set " . implode( ', ', $diff ) );
		continue;
	}
	update_field( $kc_field['key'], $new, $kc_post->ID );
	WP_CLI::log( "{$kc_tag} {$kc_name}: set " . implode( ', ', $diff ) );
}

// --- 3. Terms ---------------------------------------------------------------------

$kc_terms = array(
	'case_deliverable' => array( 'Information Architecture', 'UX/UI Design', 'SEO Migration', 'Technical QA', 'Conversion Tracking' ),
	'case_technology'  => array( 'WordPress', 'ACF' ),
	'case_status'      => array( 'Completed' ),
);

foreach ( $kc_terms as $kc_taxonomy => $kc_names ) {
	if ( ! taxonomy_exists( $kc_taxonomy ) ) {
		WP_CLI::warning( "{$kc_tag} taxonomy {$kc_taxonomy} is not registered" );
		continue;
	}
	$now = wp_get_object_terms( $kc_post->ID, $kc_taxonomy, array( 'fields' => 'names' ) );
	$now = is_wp_error( $now ) ? array() : $now;
	$a   = $now;
	$b   = $kc_names;
	sort( $a );
	sort( $b );
	if ( $a === $b ) {
		WP_CLI::log( "{$kc_tag} {$kc_taxonomy}: already" );
		continue;
	}

	++$kc_changes;
	if ( ! $kc_apply ) {
		WP_CLI::log( "{$kc_tag} {$kc_taxonomy}: would set " . implode( ', ', $kc_names ) );
		continue;
	}
	$result = wp_set_object_terms( $kc_post->ID, $kc_names, $kc_taxonomy, false );
	if ( is_wp_error( $result ) ) {
		WP_CLI::warning( "{$kc_tag} {$kc_taxonomy}: " . $result->get_error_message() );
		continue;
	}
	WP_CLI::log( "{$kc_tag} {$kc_taxonomy}: set " . implode( ', ', $kc_names ) );
}

// --- 4. Content -------------------------------------------------------------------

/**
 * Heading block.
 *
 * @param string $text    Heading text.
 * @param int    $level   2 or 3.
 * @param bool   $chapter Block style "chapter" (h2 only).
 * @return string Block markup.
 */
$kc_heading = static function ( $text, $level = 2, $chapter = false ) {
	$attrs = array();
	if ( 2 !== $level ) {
		$attrs['level'] = $level;
	}
	if ( $chapter ) {
		$attrs['className'] = 'is-style-chapter';
	}
	$json  = $attrs ? ' ' . wp_json_encode( $attrs, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) : '';
	$class = 'wp-block-heading' . ( $chapter ? ' is-style-chapter' : '' );

	return "<!-- wp:heading{$json} -->\n<h{$level} class=\"{$class}\">" . esc_html( $text ) . "</h{$level}>\n<!-- /wp:heading -->";
};

/**
 * Paragraph block.
 *
 * @param string $text Paragraph text (plain).
 * @return string Block markup.
 */
$kc_paragraph = static function ( $text ) {
	return "<!-- wp:paragraph -->\n<p>" . esc_html( $text ) . "</p>\n<!-- /wp:paragraph -->";
};

/**
 * Image block with a caption.
 *
 * @param int    $id      Attachment ID (-1 in a dry run before the upload).
 * @param string $alt     Alt text.
 * @param string $caption Caption.
 * @return string Block markup.
 */
$kc_image = static function ( $id, $alt, $caption ) {
	$src = $id > 0 ? (string) wp_get_attachment_url( $id ) : '';

	return '<!-- wp:image {"id":' . (int) $id . ',"sizeSlug":"full","linkDestination":"none"} -->' . "\n"
		. '<figure class="wp-block-image size-full"><img src="' . esc_url( $src ) . '" alt="' . esc_attr( $alt ) . '" class="wp-image-' . (int) $id . '"/>'
		. '<figcaption class="wp-element-caption">' . esc_html( $caption ) . '</figcaption></figure>' . "\n"
		. '<!-- /wp:image -->';
};

$kc_blocks = array(
	$kc_heading( 'З чого починали' ),
	$kc_paragraph( 'Клініка має кілька філій у двох містах України, контакти яких не було належним чином представлено на сайті. Через заплутану структуру послуг пацієнтам складно було знайти потрібну інформацію і записатися на прийом. Статті в блозі часто дублювали одна одну. Наприклад, на тему чоловічого безпліддя було створено п\'ять сторінок. Дизайн не відповідав актуальним потребам клініки, а частина контенту потребувала перегляду й оновлення.' ),
	$kc_paragraph( 'Водночас будь-які зміни структури потрібно було впроваджувати без ризику для чинних URL і пошукового трафіку. Сайт налічував понад 900 сторінок, тому помилки під час перенесення або зміни адрес могли призвести до непрацюючих посилань і втрати пошукового трафіку.' ),

	$kc_heading( 'Як працювали', 2, true ),
	$kc_heading( '1. Шість релізів замість одного великого запуску', 3 ),
	$kc_paragraph( 'Ми відмовилися від ідеї “заморозити сайт і переробити”. Сайт мав залишатися доступним для пацієнтів та наповнюватися новими матеріалами. Тому ми розділили роботу на шість етапів. Кожен розділ оновлювали, перевіряли та публікували окремо.' ),
	$kc_paragraph( 'Почали з головної сторінки. Оновили шапку та футер сайту, додали контакти всіх філій, структурували меню напрямків та послуг, затвердили систему візуальних стилів сайту, налаштували форми запису та відстеження звернень.' ),
	$kc_heading( '2. SEO-міграція без втрат', 3 ),
	$kc_paragraph( 'Оновлення структури сайту, що налічував понад 900 сторінок, вимагало особливої уваги до SEO. Щоб після зміни URL-адрес відвідувачі та пошукові системи потрапляли на потрібні сторінки, ми налаштували автоматичні редіректи для типових змін адрес і понад 500 індивідуальних 301-редіректів для важливих сторінок. Це допомогло зберегти накопичені SEO-сигнали та уникнути втрати пошукового трафіку під час перебудови сайту.' ),
	$kc_heading( '3. Контроль якості перед кожним релізом', 3 ),
	$kc_paragraph( 'Перед кожною публікацією оновлень сканували сайт на помилки (зокрема 404) через NetPeak Spider та закривали проблеми до релізу. Після релізу повторно перевіряли роботу сайту та стежили за змінами пошукового трафіку.' ),
	$kc_heading( '4. Відстеження звернень окремо для кожної філії', 3 ),
	$kc_paragraph( 'Для клініки було важливо бачити не лише загальну активність на сайті, а й те, як потенційні пацієнти звертаються до кожної філії. Тому ми налаштували відстеження кліків на телефонні номери окремих філій, відкриття форм запису та їх успішне відправлення.' ),
	$kc_paragraph( 'Завдяки цьому команда клініки отримала можливість оцінювати активність відвідувачів перед зверненням до клініки, бачити, які способи зв\'язку вони обирають, та окремо аналізувати кліки на телефонні номери кожної філії.' ),

	$kc_heading( 'Результати', 2, true ),
	$kc_heading( '1. Пошукова видимість', 3 ),
	$kc_paragraph( 'Попри масштабні зміни структури та оновлення, нам вдалося зберегти стабільність пошукового трафіку. Головний результат міграції: після жодного із шести релізів ми не зафіксували помітного просідання пошукового трафіку, а графік пошукової видимості показує зростання кількості показів протягом тривалого періоду.' ),
	$kc_paragraph( 'За даними SE Ranking, сайт отримав 15,8 млн показів у пошуку та 448 тис. кліків. Середній CTR становив 2,8%, а середня позиція за даними GSC у видачі - 9,9.' ),
	$kc_image( $kc_chart_id, $kc_chart_alt, 'Графік SEO-показників із SE Ranking' ),
	$kc_heading( '2. Конверсійні дії (Google Analytics 4)', 3 ),
	$kc_paragraph( 'Окрім пошукового трафіку, ми відстежували дії відвідувачів, які свідчать про намір звернутися до клініки. За даними GA4, кількість кліків на телефонні номери зростала протягом місяців. Також відстежували відкриття форм запису та їх успішне надсилання.' ),
	$kc_paragraph( 'Окреме налаштування подій для телефонних номерів кожної філії дозволило команді клініки аналізувати активність відвідувачів не лише на рівні всього сайту, а й за окремими філіями.' ),

	$kc_heading( 'Оновлення без зупинки роботи', 2, true ),
	$kc_paragraph( 'Цей проєкт показав, що навіть великий сайт можна оновлювати поступово, не зупиняючи його роботу та не втрачаючи накопичених SEO-результатів. Шість послідовних релізів дозволили нам перебудувати структуру, оновити дизайн і покращити навігацію, водночас контролюючи технічний стан сайту та пошуковий трафік. У результаті клініка отримала оновлений сайт, зручніший для пацієнтів і пристосований до подальшого розвитку.' ),
);

$kc_content = implode( "\n\n", $kc_blocks );
WP_CLI::log( "{$kc_tag} content: " . count( $kc_blocks ) . ' blocks, ' . count( parse_blocks( $kc_content ) ) . ' parsed entries' );

if ( trim( $kc_post->post_content ) === $kc_content ) {
	WP_CLI::log( "{$kc_tag} post_content: already" );
} else {
	++$kc_changes;
	if ( ! $kc_apply ) {
		WP_CLI::log( "{$kc_tag} post_content: would replace (" . strlen( $kc_post->post_content ) . ' → ' . strlen( $kc_content ) . ' bytes)' );
	} else {
		if ( '' === (string) get_post_meta( $kc_post->ID, '_khmilclinic_content_backup', true ) ) {
			update_post_meta( $kc_post->ID, '_khmilclinic_content_backup', wp_slash( $kc_post->post_content ) );
			WP_CLI::log( "{$kc_tag} original content saved to `_khmilclinic_content_backup`" );
		}

		$kc_result = wp_update_post(
			array(
				'ID'           => $kc_post->ID,
				'post_content' => wp_slash( $kc_content ),
			),
			true
		);
		if ( is_wp_error( $kc_result ) ) {
			WP_CLI::error( "{$kc_tag} update failed: " . $kc_result->get_error_message() );
		}
		WP_CLI::log( "{$kc_tag} post_content: replaced" );
	}
}

if ( ! $kc_changes ) {
	WP_CLI::success( "{$kc_tag} nothing to change" );
	return;
}

if ( ! $kc_apply ) {
	WP_CLI::success( "{$kc_tag} dry run: {$kc_changes} change(s) pending, run with `apply` to write" );
	return;
}

WP_CLI::success( "{$kc_tag} {$kc_changes} change(s) written to case #{$kc_post->ID}" );
