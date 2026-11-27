<?php
/**
 * Texts of the services page (Figma desktop 3534:2180, mobile 3589:7251).
 * Used by scripts/update-services-page.php.
 *
 * Service descriptions are copied from the front page `acf/section-services`
 * block at run time; `service_fallback` is used only when that text is empty.
 * The FAQ has one shared answer for now (the answer of the open question in
 * the mock-up, 3543:2990) — replace it in the block editor when real answers
 * are ready.
 *
 * @package Vite_Starter
 */

return array(
	'page_title'       => 'Послуги',
	'meta_description' => 'Розробка сайтів, SEO, реклама в Google, аналітика та комунікація з пацієнтами для медичних закладів — від першого кліку до запису.',

	'hero'             => array(
		'caption' => '/*services*/',
		'title'   => 'Розробка сайтів та маркетинг для медицини. Від першого кліку до запису.',
	),

	'service_list'     => array(
		'intro'          => 'Працюємо з приватними лікарями, невеликими кабінетами та великими медичними центрами, допомагаючи їм вирішувати завдання різного масштабу.',
		// [ title, home icon `_starter_source`, image file in scripts/data/services/ ].
		'items'          => array(
			array( 'Розробка сайтів', 'home-redesign/service-web.svg', 'service-web-2x.png' ),
			array( 'SEO', 'home-redesign/service-seo.svg', 'service-seo-2x.png' ),
			array( 'Реклама в Google', 'home-redesign/service-ads.svg', 'service-ads-2x.png' ),
			array( 'Аналітика та моніторинг', 'home-redesign/service-analytics.svg', 'service-analytics-2x.png' ),
			array( 'Комунікація з пацієнтами та розсилки', 'home-redesign/service-communication.svg', 'service-communication-2x.png' ),
		),
		'default_active' => 2,
		'link_title'     => 'Перейти до кейсів',
		'link_icon'      => 'icons/service-cases.svg',
	),

	'service_fallback' => 'Пошукові кампанії з урахуванням обмежень у медичній тематиці: посадкові сторінки під послуги, адекватні обіцянки, контроль вартості звернення.',

	'capabilities'     => array(
		'title'        => 'Якщо у вас вже є хороший сайт і власна команда маркетингу, нам є чим вас зацікавити',
		// [ icon file in scripts/data/services/icons/, text ].
		'items'        => array(
			array( 'cap-local-seo.svg', 'Локальне SEO та просування в Google Картах' ),
			array( 'cap-google-ads.svg', 'Реклама в Google та управління рекламними кампаніями' ),
			array( 'cap-conversion.svg', 'Оптимізація конверсій' ),
			array( 'cap-analytics.svg', 'Вебаналітика та моніторинг' ),
			array( 'cap-crm.svg', 'Інтеграція CRM та автоматизація' ),
			array( 'cap-email.svg', 'Email-маркетинг та комунікація з пацієнтами' ),
		),
		'tasks_title'  => 'Яке завдання стоїть перед вами?',
		'tasks'        => array(
			'Потрібен новий сайт',
			'Хочемо більше записів на конкретні послуги',
			'Пацієнти мають знаходити нас у Google Картах',
			'Потрібно зрозуміти, які канали дають звернення',
			'Підключити CRM',
			'Чому немає звернень',
			'Хочемо бути в ШІ видачі',
		),
		'button_label' => 'Обговоримо?',
	),

	'stages'           => array(
		'title' => 'Підключаємось на будь-якому етапі роботи над проєктом',
		// [ icon file in scripts/data/services/icons/, title, text ].
		'items' => array(
			array( 'stage-rocket.svg', 'Запуск з нуля', 'Створення цифрової присутності нового медичного бізнесу: від сайту до перших рекламних кампаній.' ),
			array( 'stage-puzzle.svg', 'Окремі завдання', 'Підключення наших спеціалістів для вирішення конкретного завдання або посилення вашої команди.' ),
			array( 'stage-refresh.svg', 'Розвиток наявного проєкту', 'Вдосконалення сайту, пошукового просування, реклами та аналітики без необхідності починати все спочатку.' ),
			array( 'stage-handshake.svg', 'Довгострокове партнерство', 'Регулярна технічна підтримка, маркетинг, аналітика та розвиток цифрових каналів.' ),
		),
	),

	'faq'              => array(
		'title'     => 'Поширені запитання',
		'questions' => array(
			'Чи надаєте техпідтримку готового сайту?',
			'Чи можна замовити лише одну послугу?',
			'Чи працюєте з наявним сайтом клініки?',
			'Коли очікувати перших результатів?',
		),
		'answer'    => 'Так, звичайно. Впродовж 1 місяця після здачі і запуску сайту, проте, якщо сайт великий та/або складний, цей термін може бути більшим.',
	),
);
