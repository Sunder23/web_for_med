# Implementation Plan: Новая страница кейса (single-cases.php, Figma)

Branch: feature/single-case-redesign (Tasks 1–4 сделаны в `feature/cases-archive-redesign`, слитой в main 2026-11-11; Tasks 5–9 — в этой ветке)
Created: 2026-11-11

## Settings
- Testing: no (проверка: `npm run lint`, `composer lint`, визуальная сверка с Figma в Chrome на 1920, 1440, 1024, 375)
- Logging: verbose в рамках правил проекта. JS пишет только через `utils/logDebug.js` (без новых `console.log`), PHP — только `error_log( 'WARN [case-single] …' )` на сбоях/пустых данных
- Docs: yes — обязательный docs checkpoint через `/aif-docs` в конце (обновить `docs/single-pages.md`)

## Источники в Figma (fileKey `huDLClWg3dIpXyzLMuVHEs`)
| Что | node |
|---|---|
| Desktop 1920 «Case archive — Promedi» | `3061:891` |
| ↳ utility bar 40px (`/*FILE: PROMEDI */` #218ed6 12px caps + `SECTION: 01/09` caption 12px caps), border-bottom #dbdbdb | `3061:893` |
| ↳ контейнер 1350 (padding 35): сайдбар 180 + gap 60 + контент 880 | `3061:908` |
| ↳ сайдбар «Project metadata»: label 13/1.3 #73777b ls .78px, value 14/1.45 #2e2f30, gap 24 / 6 | `3061:909` |
| ↳ интро: H1 Grunt Grotesk 40/1.1, абзацы Fixel Medium 18/1.45 #3b3e41 gap 14, картинка 880×583 c padding 20 0 | `3061:928` |
| ↳ «Reading column» (пример тела): h2 Grunt 36/1.18, p Fixel Regular 17/1.55, figure-панель bg rgba(80,111,115,.04) p 20, caption 13/1.4 #092a4a | `3061:950` |
| ↳ список с квадратными маркерами 8×8 | `3061:1084` |
| ↳ prev/next полоса 40px, border-top, `PREV`/`NEXT` 13px caps #73777b + название 14px, стрелки 12×24 | `3061:1100` |
| Mobile 375 | `3124:1383` |
| ↳ порядок: utility bar 36 → интро (заголовок, абзацы, картинка) → метаданные → контент → prev/next | `3124:1392`, `3124:1545` |

Перед вёрсткой снять `get_design_context` по мобильным узлам `3124:1423`, `3124:1431`, `3124:1545` (размеры шрифтов/отступы на 375). Цвета брать из `_tokens.scss`, новых токенов не вводить без необходимости.

## Решения
1. **Красный блок со скрина = ACF, кроме заголовка.** Заголовок — `the_title()`. Интро-текст и картинка — ACF. Причина: на мобиле сайдбар встаёт *между* интро и телом статьи, поэтому интро не может жить в `the_content()`.
2. **Картинка интро:** ACF `case_intro.image` (ID), fallback — миниатюра записи; размер `full`, `loading="eager"`, `fetchpriority="high"`.
3. **Сайдбар (метаданные):** `Deliverables`, `Technology`, `Status` — **таксономии** кейсов (значения повторяются между кейсами, вводятся один раз и выбираются галочками; задел под фильтр в архиве). `Duration` и `Live Project` — уникальны для кейса, остаются ACF-полями. Пустые пункты не выводятся; лейблы как в макете на английском через `__()`. Термины выводятся через запятую в порядке `name ASC`.
4. **Сетка:** один экземпляр сайдбара в DOM (без `wp_is_mobile()`, ломает кеш), порядок через `grid-template-areas`: mobile `intro / meta / content`; от `tablet` (1024) — колонки `180px minmax(0, 880px)`, gap 60, области `"meta intro" "meta content"`; сайдбар растянут на обе строки, внутренний блок `position: sticky` (top = высота хедера + utility bar, через существующие CSS-переменные хедера если есть).
5. **Из страницы убираем** FAQ, CTA, «Схожі кейси», breadcrumbs, TOC (по макету их нет). ACF-поля `case_faq`, `case_cta`, `case_hero` не удаляем — данные остаются, `case_hero.subtitle` используется архивом как fallback описания.
6. **Счётчик `SECTION: NN/MM`** — живой scroll-spy: секция 01 = интро, далее каждый `h2` контента. PHP выводит стартовое `01/MM` (MM = 1 + число h2 из `starter_collect_headings()` по уровню 2), JS обновляет через IntersectionObserver.
7. **Метка `/*FILE: …*/`** — ACF `case_intro.client` (короткое имя клиента), fallback — заголовок записи; uppercase через CSS.
8. **Prev/next** — компактная полоса из макета. Логику соседних записей (loop-around) выносим из `post-nav.php` в хелпер, чтобы не дублировать; карточный `post-nav` для услуг/направлений/блога остаётся как есть.
9. **Ассеты:** у кейса свой entry `single-case.scss` / `single-case.js` (Vite подхватывает плоские файлы автоматически); кейсы исключаются из `starter_enqueue_single_cpt_assets`.

## Tasks

### Phase 1 — Данные

- [x] **Task 1: ACF-поля интро и деталей проекта**
- Файл: `configure/acf/acf-json/group_6ba8f99a.json` (группа «Case Page», это рабочий load/save point; копию в корневом `acf-json/` не трогать).
- Добавить в начало две вкладки:
  - Tab «Інтро» → group `case_intro`: `client` (text, «Коротка назва клієнта для мітки /*FILE*/»), `text` (wysiwyg, toolbar basic, `media_upload: 0`, несколько абзацев), `image` (image, return `id`, instructions «Якщо порожньо — береться мініатюра запису»).
  - Tab «Деталі проєкту» → group `case_details`: `duration` (text, «2 months»), `live_url` (url), `live_label` (text, placeholder «View Website»). Deliverables / Technology / Status — не ACF, см. Task 2.
- Ключи `field_` + 8 hex в стиле существующих, уникальные; обновить `modified`.
- В инструкции вкладки `case_hero` пометить: «Використовується як опис в архіві кейсів, якщо порожнє поле архіву».
- Логирование: не требуется (конфиг).

- [x] **Task 2: Таксономии кейсов (Deliverables, Technology, Status)**
- Новые файлы в `configure/taxonomies/` (по одному на таксономию, функция `starter_register_*_taxonomy` на `init`):
  - `case-deliverable.php` → `case_deliverable` («Роботи / Deliverables»: UX/UI Design, Web Development, Content Architecture…)
  - `case-technology.php` → `case_technology` («Технології»: WordPress, Gutenberg, ACF…)
  - `case-status.php` → `case_status` («Статус»: Completed, In progress…)
- Общие аргументы: `object_type` = `cases`; `hierarchical => true` (UI с галочками вместо ввода тегов — меньше дублей с опечатками); `public => false`, `publicly_queryable => false`, `rewrite => false` (страниц терминов нет — без пустых архивов и SEO-мусора; при появлении фильтра в архиве пересмотреть); `show_ui => true`, `show_in_rest => true` (панель в Gutenberg), `show_admin_column => true`; украинские labels, text domain `vite-starter`.
- Status — одно значение по смыслу: выводить первый термин; в описании таксономии указать «оберіть один статус».
- Добавить три файла в массив `$starter_taxonomies` в `configure/taxonomies.php`.
- Логирование: не требуется (регистрация); `WARN [case-single] multiple statuses for case {ID}` в хелпере Task 3, если статусов > 1.

- [x] **Task 3: Хелперы данных кейса и соседних записей** (зависит от 1, 2)
- Новый `configure/helpers/get-case-single-data.php`: `starter_get_case_single_data( $post )` → массив `client`, `intro_html` (fallback: `case_hero.subtitle` в `<p>` если `case_intro.text` пуст), `image_id` (fallback `get_post_thumbnail_id`), `details` — упорядоченный массив `[label, value, url?]` только непустых: Deliverables (`get_the_terms( $post, 'case_deliverable' )` → имена через «, »), Technology (`case_technology`), Duration (ACF), Status (`case_status`, первый термин), Live Project (ACF url + label, по умолчанию «View Website»); `is_wp_error()` терминов → пропуск + WARN. `sections_total` (1 + кол-во h2 через `starter_collect_headings()`). Использовать `starter_get_field()` / `starter_get_array_value()`.
- Новый `configure/helpers/get-adjacent-posts.php`: `starter_get_adjacent_posts()` — перенести логику из `partials/parts/post-nav.php` (adjacent + loop-around + схлопывание при двух записях), вернуть `array( 'prev' => WP_Post|null, 'next' => WP_Post|null )`. `post-nav.php` переписать на хелпер без изменения вывода.
- Зарегистрировать оба файла в массиве `configure/utilities.php`.
- Логирование: `error_log( 'WARN [case-single] no intro image for case {ID}' )` если нет ни ACF-картинки, ни миниатюры; `WARN [case-single] empty intro for case {ID}` если интро пусто после fallback.

### Phase 2 — Разметка

- [x] **Task 4: Партиалы страницы кейса** (зависит от 3)
- Новый `partials/parts/case-single.php` (вызывается в цикле):
  - `.case-single__bar` (utility bar): `<span class="case-single__label">/*FILE: {client} */</span>` + `<p class="case-single__counter" aria-live="polite">Section: <span data-case-section-current>01</span>/{MM}</p>` (counter только если MM > 1).
  - `.container .frame .case-single__grid`:
    - `header.case-single__intro`: `the_title( '<h1 class="case-single__title">' )`, `.case-single__lead` (wp_kses_post интро), `figure.case-single__image` (`wp_get_attachment_image( $id, 'full', … )`).
    - `aside.case-single__meta` > `dl.case-single__details` (`dt` label / `dd` value; Live Project — `<a target="_blank" rel="noopener">` с подчёркиванием).
    - `div.entry-content.case-single__content` → `the_content()`.
- Новый `partials/parts/case-nav.php`: полоса prev/next по `starter_get_adjacent_posts()`; стрелки — `static/images/cases/arrow-{prev,next}.svg` (как в `cases-archive.php`, через `file_get_contents` + `wp_kses` со списком svg-тегов); заголовок с `text-overflow: ellipsis`; `rel="prev|next"`, `aria-label` у `nav`.
- Переписать `single-cases.php`: `get_header()` → `main.case-single` → цикл: `case-single` + `case-nav` → `get_footer()`. FAQ, related-posts, article-layout больше не подключаются.
- Логирование: PHP-предупреждения уже в хелперах; в шаблоне ничего.

### Phase 3 — Стили и скрипт

- [x] **Task 5: Стили `single-case.scss`** (зависит от 4)
- Новый entry `assets/src/scss/single-case.scss`: `@use` `components/entry-content`, `components/lightbox` (если изображения контента открываются лайтбоксом — проверить, кто инициализирует lightbox), новый `components/case-single`.
- Новый `assets/src/scss/components/_case-single.scss` (mobile first, `@include breakpoint(tablet)`, BEM, вложенность ≤ 3):
  - bar: 36px mobile / 40px desktop, border-bottom #dbdbdb, label/counter по `3061:893`; ширина/паддинги как у `.cases-archive__bar` (сверить, при полном совпадении — вынести общие правила не нужно, допускается повтор 5–10 строк).
  - grid/areas и sticky-сайдбар из решения 4; вертикальные линии рамки — через существующий `.frame`.
  - интро, details-типографика по `3061:909`, `3061:928`; картинка `width: 100%; height: auto`.
  - Переопределения контента внутри `.case-single__content`: h2 36/1.18 Grunt (mobile — по `3124:1433`), отступы между блоками 28/36/40 по макету, p 17/1.55, `figure` (wp-block-image, wp-block-gallery, wp-block-video) — панель с фоном rgba(80,111,115,.04), padding 20, `figcaption` 13/1.4 #092a4a слева; `ul > li` с квадратным маркером 8×8 (#092a4a / токен) и отступом 20.
  - nav-полоса по `3061:1100`: 40px, border-top, две половины с gap 40, на mobile — только `PREV`/`NEXT` без названий (`3124:1545`).
- Логирование: не применимо.

- [x] **Task 6: Скрипт `single-case.js` со scroll-spy** (зависит от 4)
- Новый `assets/src/js/components/caseSectionCounter.js`: находит `[data-case-section-current]`, секции = `.case-single__intro` + `.case-single__content h2`; IntersectionObserver (rootMargin с учётом высоты хедера/bar, например `-30% 0px -60% 0px`) → текущий индекс, формат `01`; total пересчитать из DOM и при расхождении с PHP — обновить и `logDebug`. Без счётчика/секций — ранний выход.
- Новый entry `assets/src/js/single-case.js`: `DOMContentLoaded` → `initCaseSectionCounter()` (+ lightbox, если нужен по результату Task 5).
- Логирование через `logDebug`: `case counter: init {total}`, `case counter: section {n}`, `case counter: total mismatch {php, dom}`, `case counter: no sections, skipped`.

- [x] **Task 7: Подключение ассетов** (зависит от 5, 6)
- `configure/theme-hooks/enqueue-listing-templates-assets.php`: из `starter_enqueue_single_cpt_assets` убрать `cases`; новая `starter_enqueue_single_case_assets()` (`is_singular( 'cases' )` → `starter_enqueue_template_assets( 'starter-single-case', 'single-case.scss', 'single-case.js' )`), приоритет 110.
- Проверить `npm run build`: в манифесте появились `single-case` и `js/single-case`.
- Логирование: не требуется.

### Phase 4 — Проверка и документация

- [ ] **Task 8: Контент Promedi и визуальная сверка** (зависит от 7)
- Создать термины из макета (`make wp cmd="term create case_technology WordPress"` и т. д.: Deliverables — UX/UI Design, Web Development, Content Architecture; Technology — WordPress, Gutenberg, ACF; Status — Completed) и назначить Promedi; в админке заполнить `case_intro` и `case_details` (Duration «2 months», ссылка на сайт); убрать дублирующиеся с интро первые абзацы из `post_content`, если они там есть.
- Chrome: 1920, 1440, 1024, 375 — сверить с `3061:891` / `3124:1383`: порядок блоков на мобиле, sticky-сайдбар до конца контента, счётчик меняется при скролле, prev/next с loop-around, кейс без деталей/картинки не ломает сетку.
- `npm run lint`, `composer lint` — без новых ошибок.

- [ ] **Task 9: Документация** (зависит от 8)
- `docs/single-pages.md`: строка `single-cases.php` в таблице (новые поля `case_intro`, `case_details`, таксономии `case_deliverable` / `case_technology` / `case_status`; FAQ/CTA больше не выводятся), entry `single-case.scss/js` в таблице ассетов, `single-cpt` — только services/directions.
- `AGENTS.md`: упомянуть `partials/parts/case-single.php`, `case-nav.php`, новые хелперы и `configure/taxonomies/` (больше не empty) в структуре.

## Commit Plan
- После Task 1–4: `feat(cases): single case fields, taxonomies and new markup`
- После Task 5–7: `feat(cases): single case styles, section counter and assets`
- После Task 8–9: `docs(cases): single case layout and fields`
