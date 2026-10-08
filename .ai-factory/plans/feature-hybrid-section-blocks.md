# План: гибридная тема по образцу promedi_block

- **Ветка:** `feature/hybrid-section-blocks` (от `main`)
- **Создан:** 2026-10-08
- **Референс:** `ref/promedi_block/theme/vite-wordpress-starter-theme` (структура темы), `ref/promedi_block` (окружение, тулинг, docs)

## Settings

- **Testing:** no (автотестов нет; проверка — `npm run build`, PHPCS/PHPStan, WP-CLI-проверки, визуальное сравнение со скриншотами «до»)
- **Logging:** minimal — как в референсе: `error_log( 'WARN [<area>] …' )` только на сбоях (неизвестный slug секции, нет записи в Vite-манифесте, миграция не нашла данных). WP-CLI-скрипты пишут прогресс через `WP_CLI::log()` / `WP_CLI::warning()`. В JS — существующий `utils/logDebug.js`, без новых `console.log`.
- **Docs:** yes — обязательный docs-checkpoint в конце (через `/aif-docs`)

## Цель

Перевести текущую классическую тему в **гибридную**, как в референсе:

1. **Страницы** (`page`) собираются в Gutenberg из ACF-блоков `acf/section-{slug}`. Каждый блок — тонкая обёртка над `template-parts/blocks/section-{slug}.php`; стили и JS секции подключаются условно, только если блок есть на странице.
2. **Single CPT / блог / архивы** остаются PHP-шаблонами и переиспользуют партиалы напрямую (`get_template_part( …, $args )`), без сборки из блоков.
3. **Структура темы — 1:1 с референсом:** `functions.php` как composition root с `$starter_modules`, агрегаторы `configure/{post-types,taxonomies,theme-hooks,utilities,shortcodes,ajax}.php` с явным массивом файлов (без `glob()`), per-item файлы в подпапках, `configure/acf/{acf-blocks,section-blocks,acf-json}`, плоский SCSS (`_tokens.scss`, `components/`, `template-parts/blocks/`), префикс функций `starter_`, константы `WFB_THEME_PATH/URI/VERSION`, text domain `vite-starter`.
4. Также из референса: Docker-окружение (`docker/compose.yml` + mailpit + wpcli + `mu-plugins/mailpit.php`), PHPCS + PHPStan, модули `analytics.php` (GTM через ACF Options) и `optimize.php`.

## Текущее состояние (зафиксировано при исследовании)

- Страниц в БД две: «Головна» (ID 139, front page) и «Блог» (ID 287, posts page). Главная рендерится монолитом `front-page.php` (362 строки, 11 секций) из полей группы `group_57587b53.json` (`front_page_hero`, `_clinics`, `_quote`, `_problems`, `_banner_text`, `_solutions`, `_services`, `_cases`, `_process`, `_why`, `_contact`).
- `footer.php` читает `front_page_contact` со страницы главной — при переходе на блоки поле надо перенести в ACF Options.
- `.sections_wrapper` объединяет problems + banner + solutions в один скролл-эффект, `s-process--top/--bottom` — одна логическая секция. Они станут **одним блоком** каждая.
- Плоские `configure/*.php` с префиксами `custom_theme_` / `vite_` / без префикса, SCSS в схеме 7-1 (`abstracts/base/components/layout/pages`), один ACF-блок `acf-blocks/info-block` в корне темы, `_front-page.scss` на 1605 строк.
- Тома Docker называются `web_for_med_db_data` / `web_for_med_wordpress_data`; БД и пользователь — `web_for_med` (см. корневой `.env`).
- Плагины: Secure Custom Fields (вместо ACF Pro), CF7, Flamingo, Yoast, All-in-One WP Migration.

## Решения

- **Секции главной (8 блоков):** `home-hero`, `clinics`, `quote`, `problems-solutions` (problems + banner_text + solutions + обёртка `.sections_wrapper`), `services`, `cases`, `process` (top + bottom), `why`. Ключи полей — новые `field_starter_section_*`, имена полей — как sub_fields текущих групп (`title`, `items`, …), чтобы миграция была механической.
- `front_page_contact` переезжает в Options (вкладка Footer, поле `footer_contact`); `footer.php` читает его через `starter_get_option()`.
- `front-page.php` удаляется → главную рендерит `page.php` (`has_blocks() ? the_content() : the_title() + the_content()`).
- Docker: в `docker/compose.yml` задаётся `name: web_for_med`, чтобы **сохранить существующие тома** с данными. Сервис `cli` переименовывается в `wpcli` (без profile), монтирование `ref/images` сохраняется.
- jQuery: переход на bundled core jQuery, как в референсе (CDN-подмену убрать), только если `main.js` и CF7 работают без изменений; иначе оставить CDN и отметить в docs.
- `postcss-pxtorem` в prod-сборке — переносим для паритета с референсом. Обязательно визуальное сравнение после `npm run build`.

## Tasks

### Phase 0 — Подготовка

- [ ] **T0. Бэкап и baseline.**
  - `mysqldump` из контейнера `db` → `backups/web_for_med-pre-hybrid-<date>.sql` (добавить `backups/` в `.gitignore`).
  - Скриншоты «до» (desktop 1440 + mobile 390): `/`, `/services/`, одна service/direction/case-запись, `/blog/` и одна запись блога → `screens/before/`.
  - Логи: не нужны (разовая операция); результат дампа проверить по размеру файла.

### Phase 1 — Окружение и тулинг

- [ ] **T1. Docker как в референсе.**
  - Создать `docker/compose.yml` (`name: web_for_med`, сервисы `db` с healthcheck, `mailpit`, `wordpress` с `WORDPRESS_CONFIG_EXTRA` → `WP_ENVIRONMENT_TYPE=local`, `WP_DEBUG_LOG`, `WP_HOME/SITEURL`, `wpcli`, `phpmyadmin`), `docker/.env.example` (с `MAILPIT_PORT`), перенести `uploads.ini` → `docker/uploads.ini`, корневой `.env` → `docker/.env`.
  - Монтирования: тема, `../mu-plugins`, `../plugin`, `../scripts:/scripts`, `../ref/images:/import-images:ro`.
  - `mu-plugins/mailpit.php` — порт из референса.
  - Удалить корневой `docker-compose.yml`; `Makefile` → `docker compose -f docker/compose.yml …`, `cli` → `wpcli`; поправить `.gitignore` (`docker/.env`).
  - Проверка: `docker compose -f docker/compose.yml up -d` поднимает **те же** тома (сайт отдаёт 200, контент на месте), Mailpit на `:8025`.
  - Логи: нет (конфигурация).

- [ ] **T2. PHPCS + PHPStan.**
  - `composer.json` (require-dev и scripts `lint`/`analyse`, как в референсе), `phpcs.xml.dist` (text domain `vite-starter`, `is_theme`), `phpstan.neon` (level 5, `configure` + `functions.php`, stubs SCF/ACF), `phpstan-bootstrap.php` (константы `WFB_*`, `VITE_*`); `vendor/` в `.gitignore`.
  - Baseline `phpstan-baseline.neon` генерировать **после** Phase 2, чтобы не захватить старый код.
  - Логи: нет.

### Phase 2 — Скелет темы (без изменения поведения на фронте)

- [ ] **T3. `functions.php` и агрегаторы `configure/`.**
  - `functions.php` → константы `WFB_THEME_PATH/URI/VERSION` + массив `$starter_modules` + `require_once`, `admin.php` под `is_admin()` (как в референсе).
  - `configure/configure.php` разбить на `configure/theme-hooks/`: `register-menus.php`, `theme-support.php` (вместе с editor-styles, text domain `vite-starter`), `image-sizes.php`, `remove-wp-generator.php` (+ emoji, wp-embed), `allow-svg-uploads.php`, `disable-auto-update-emails.php`, `deprioritize-yoast-metabox.php`, `disable-autoparagraph-wrapping-cf7.php` (из `utilities.php`), `jquery-source.php` (см. решение про jQuery); агрегатор `configure/theme-hooks.php` с явным массивом.
  - `configure/cpt-taxonomy.php` → `configure/post-types.php` + `post-types/{services,directions,cases}.php`; `configure/taxonomies.php` (пустой массив) + `taxonomies/.gitkeep`.
  - `configure/utilities.php` → агрегатор `helpers/`: `get-array-value.php`, `get-option.php`, `is-block-content-view.php`, `get-static-dir.php`; хлебные крошки → `partials/breadcrumbs.php` + `helpers/get-breadcrumb-items.php`.
  - Пустые агрегаторы `shortcodes.php`, `ajax.php` (+ `ajax/.gitkeep`).
  - Переименовать все функции в `starter_*` и обновить вызовы в шаблонах (`custom_theme_breadcrumbs()`, `custom_theme_get_toc()` и т.д.) — `grep` по `custom_theme_|vite_manifest|add_vite_assets` должен вернуть пусто.
  - Проверка: страницы (`/`, CPT, блог) отдают 200 и выглядят как baseline; в `debug.log` нет fatal/notice.
  - Логи: нет новых (рефакторинг).

- [ ] **T4. ACF: local JSON и контентные блоки.**
  - `acf-json/` → `configure/acf/acf-json/`; `configure/acf.php` — save + load point на новую папку (как в референсе).
  - `acf-blocks/info-block` → `configure/acf/acf-blocks/info-block/{block.json,render.php}`, стиль → `assets/src/scss/block-info-block.scss` (handle регистрируется через `starter_vite_register_style()`); `configure/blocks.php` → `configure/acf-blocks.php` (`glob()` по папкам блоков — единственное разрешённое исключение).
  - Проверка: `wp eval 'echo count( acf_get_field_groups() );'` даёт прежнее число групп, в админке нет «Sync available», блок `info-block` рендерится в записях блога.
  - Логи: `WARN [acf-blocks] style entry missing for <block>`, если стиль блока не найден в манифесте.

- [ ] **T5. Vite-интеграция, analytics, optimize.**
  - `configure/js-css.php` — порт API референса: `starter_vite_manifest()`, `starter_vite_manifest_uri()`, `starter_vite_register_style()/register_script()`, `starter_vite_module_handles()`, `VITE_DEV` (через `wp_get_environment_type()`), `starter_vite_add_assets()`, editor canvas (`enqueue_block_assets` + `block_editor_settings_all` strip для `page`), preload, inline-переменные пресетов `theme.json`, cleanup core-стилей (через `starter_is_block_content_view()`), Typekit-шрифт.
  - `vite.config.js` — как в референсе: плоские entries `scss/` и `js/` + второй уровень `template-parts/blocks/`, алиасы, `postcss-pxtorem` на build (добавить в `package.json`). Записи `acf-blocks/*` убрать (стиль блока теперь `block-*.scss`).
  - `configure/analytics.php` (GTM по `analytics_enabled` / `analytics_gtm_id`) + вкладка «Google / GTM» в группе Options; `configure/optimize.php` с выключенными по умолчанию флагами.
  - Проверка: `npm run build` без ошибок, в манифесте есть `main.js`/`main.scss`; dev (`npm run dev`) и prod (собранный `dist`) дают идентичную главную.
  - Логи: `WARN [vite] manifest entry missing: <key>` в `starter_vite_register_*` (один раз на ключ).

- [ ] **T6. SCSS/JS — плоская структура и условные entries.**
  - SCSS: `abstracts/_variables.scss` → `_tokens.scss`; `base/_fonts`, `_base` (+ `_animations`) → корневые `_fonts.scss`, `_base.scss`; `mixins/_breakpoint.scss`; `layout/*` и `components/*` → плоский `components/` (`_header`, `_footer`, `_forms`, `_grid`, `_buttons`, `_tag`, `_section-title`, `_info-card`, `_icon-list`, `_modal`, `_breadcrumbs`); `vendors/_fancybox.scss`. Папки `abstracts/ base/ layout/ pages/` удалить.
  - Постраничные entries (без `_`): `single-cpt.scss` (`cpt-common` + `service` + `direction` + `case`), `single-post.scss`, `archive.scss` (архивы CPT + `home.php`). Подключение — `configure/theme-hooks/enqueue-listing-templates-assets.php` (одна функция на тип шаблона, как в референсе). `main.scss` — только глобальное (tokens, fonts, base, header, footer, общие компоненты). `_front-page.scss` пока подключён из `main.scss` (разбирается в Phase 3).
  - JS: `main.js` — только глобальное (`mobileNav`, `smoothScroll`, `activeNav`, `contactForm`, анимации футера, `lightbox`, если нужен везде); entries `single-cpt.js` (`faqAccordion`, `toc`), `archive.js` (`blogFilter`). Импорты только через `@js` / `@scss`.
  - Проверка: визуальное сравнение всех baseline-страниц (dev и build), на CPT/архивах грузятся только их entries (DevTools → Network).
  - Логи: нет.

- [ ] **T7. Партиалы.**
  - `header.php` → каркас + `partials/header/{header,logo}.php`; `template-parts/{cpt-cta,cpt-faq,archive-cpt,content-with-toc}.php` → `partials/parts/` (сохраняя контракт `$args`); обновить все `get_template_part()`.
  - Проверка: `grep` по старым путям пуст, страницы совпадают с baseline.
  - Логи: нет.

### Phase 3 — Секционные блоки для страниц

- [ ] **T8. Движок секций.**
  - `configure/section-blocks.php` — порт из референса: `starter_get_section_slugs()`, регистрация `glob()` по `configure/acf/section-blocks/*`, категория «Секції сторінки», `starter_render_section_block()` (get_fields → `template-parts/blocks/section-{slug}.php`, плейсхолдер в редакторе), `starter_section_blocks_active_slugs()` / `starter_section_blocks_only_page()`, условный enqueue `section-{slug}.scss/js`. Ветки Swiper не нужны (только если какая-то секция его использует).
  - `page.php` — как в референсе. Блочный редактор для `page` и `post` (`theme-hooks/enable-block-editor-for-post.php`, если сейчас он где-то выключен).
  - `assets/src/scss/editor-section-blocks.scss`, `assets/src/js/editor-section-blocks.js`, `assets/src/js/editor-link-guard.js`; `toc.php` не вставляет TOC на страницах «только из секций».
  - Проверка: в инсертере есть категория (пока пустая), страница «Блог» и записи не изменились.
  - Логи: `WARN [section-blocks] unknown section slug: <slug>`, `WARN [section-blocks] __unstableResolvedAssets.styles missing …` (как в референсе).

- [ ] **T9. Секции главной, часть 1: `home-hero`, `clinics`, `quote`, `problems-solutions`.**
  - Для каждого slug: `template-parts/blocks/section-{slug}.php` (guard `empty( $args )`, поля через `starter_get_array_value()`, экранирование как в текущем `front-page.php`), `assets/src/scss/template-parts/blocks/section-{slug}.scss` (вырезать из `_front-page.scss`), JS-entry при необходимости (`section-home-hero.js` → `heroAnimations` / `glitchImage`, `section-problems-solutions.js`, если есть скролл-логика), `configure/acf/section-blocks/section-{slug}/{block.json,render.php}`, `configure/acf/acf-json/group_starter_section_{slug}.json`.
  - Добавить slug'и в `starter_get_section_slugs()`.
  - Проверка: блоки вставляются на тестовой черновой странице, превью в редакторе совпадает с фронтом.
  - Логи: нет новых (общие WARN движка).

- [ ] **T10. Секции главной, часть 2: `services`, `cases`, `process`, `why`.**
  - То же, что T9; JS: `section-services.js` (`servicesAccordion`), `section-cases.js` (`casesSlider`), `section-why.js` (`whySection`); пульс-иконка `process` — только CSS. Анкоры `id="services|cases|process|about"` сохранить (на них ссылается меню / `activeNav`).
  - После T9 + T10 `_front-page.scss` должен быть пустым → удалить; из `main.js` убрать секционные инициализации.
  - Проверка: тестовая страница со всеми 8 блоками визуально совпадает с baseline главной.
  - Логи: нет.

- [ ] **T11. Контакты футера → Options.**
  - Добавить поле-группу `footer_contact` (`title`, `text`, `contact_form`) во вкладку Footer группы Options; `footer.php` читает `starter_get_option( 'footer_contact' )`.
  - Перенос значения — шагом в скрипте T12 (или отдельным `wp eval`).
  - Проверка: форма в футере выводится на всех страницах, включая CPT и блог.
  - Логи: `WARN [footer] footer_contact option is empty`, только если поле пустое.

- [ ] **T12. Миграция главной в блоки.**
  - `scripts/migrate-front-page-to-blocks.php` (как `migrate-page-sections-to-blocks.php` в референсе): читает сырые postmeta `front_page_*` страницы ID из `page_on_front`, собирает `data` для 8 блоков (`field_name` + `_field_name` → ключ поля, включая вложенные repeater-ключи), записывает `post_content` из `<!-- wp:acf/section-* {...} /-->`; копирует `front_page_contact` → option `footer_contact`. Режимы: dry-run (по умолчанию), `apply`, `apply cleanup` (удалить мету `front_page_*` у страницы и ревизий). Старый непустой `post_content` → мета `_starter_pre_blocks_content`; повторный запуск пропускает страницу, где уже есть `acf/section-*`.
  - Удалить `front-page.php` и `group_57587b53.json` (после `cleanup`).
  - `make` target `migrate-front-page`.
  - Проверка: перед `apply` — свежий `mysqldump`; после — главная совпадает с baseline (desktop + mobile), в редакторе 8 блоков с заполненными полями.
  - Логи: `WP_CLI::log()` на каждый блок (slug + число полей), `WP_CLI::warning()` при отсутствии меты секции, итоговая строка `migrated/skipped`.

### Phase 4 — Документация и финальная проверка

- [ ] **T13. Lint и сборка.**
  - `composer install`, `composer lint`: исправить PHPCS-нарушения в новом и перенесённом коде, остаток в `phpstan-baseline.neon`; `npx biome check`; `npm run build`.
  - Финальное визуальное сравнение всех baseline-страниц (build + dev).
  - Логи: нет.

- [ ] **T14. Документация (docs-checkpoint).**
  - Через `/aif-docs`: `AGENTS.md` (новое дерево, точки входа, команды с `-f docker/compose.yml`), `.ai-factory/ARCHITECTURE.md` (гибридная модель, агрегаторы, плоский SCSS), `.ai-factory/DESCRIPTION.md` (Docker, Mailpit, PHPCS/PHPStan, GTM), `.ai-factory/rules/base.md` (префикс `starter_`, text domain, алиасы), `docs/page-sections.md` и `docs/getting-started.md` (по образцу референса), `README.md`.
  - Логи: нет.

## Commit Plan

1. После **T0–T2:** `chore(docker): move compose to docker/ with mailpit, wpcli and healthchecks; add PHPCS/PHPStan tooling`
2. После **T3–T5:** `refactor(theme): restructure configure/ into aggregators and per-item modules, port Vite integration, analytics and optimize`
3. После **T6–T7:** `refactor(assets): flatten SCSS, split per-template entries, move partials`
4. После **T8–T10:** `feat(blocks): add acf/section-* engine and port front page sections to blocks`
5. После **T11–T12:** `feat(migration): migrate front page content to section blocks, move footer contact to options`
6. После **T13–T14:** `docs: document hybrid section-block theme structure`

## Риски

- **Потеря данных при миграции** → дамп перед `apply`, dry-run по умолчанию, `cleanup` отдельным шагом.
- **Тома Docker** при смене расположения compose-файла → явный `name: web_for_med`, проверка, что сайт после `up` отдаёт прежний контент.
- **Регрессия вёрстки** при разбиении `_front-page.scss` (каскад и порядок правил) и при `pxtorem` → сравнение скриншотов после каждой фазы.
- **JS, завязанный на DOM главной** (`heroAnimations`, `whySection`, `casesSlider`) — в редакторе не исполняется: превью должно быть видимым без JS (скрывающие классы ставит только JS).
- **SCF вместо ACF Pro:** формат local JSON и блоки совместимы; stubs для PHPStan — `acf-pro-stubs`.
