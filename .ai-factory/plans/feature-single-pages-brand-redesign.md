# Редизайн single-страниц в фирменном стиле (по структуре ref/promedi_block)

- **Branch:** `feature/single-pages-brand-redesign`
- **Created:** 2026-10-08
- **Type:** enhancement / redesign

## Settings
- **Testing:** no (тестового харнесса в теме нет) — проверка: `composer lint`, `npm run lint`, `npm run build`, скриншоты Playwright
- **Logging:** minimal — по правилам проекта: PHP `error_log( 'WARN [area] …' )` только на сбоях; JS только `utils/logDebug.js`, без новых `console.log`
- **Docs:** yes — обязательный docs-checkpoint в конце (`/aif-docs`)

## Контекст (проверено)
- Контент засижен: 4 services (264–267), 7 directions (268–274), 3 cases (275–277), 3 posts (278, 283, 285). Всё лежит в `post_content` core-блоками (`heading`, `paragraph`, `list`, `html`, `buttons`) — структурированные ACF-секции из `RESEARCH.md` **неактуальны**, переносить данные не нужно.
- Сейчас все single-шаблоны рендерят `partials/parts/content-with-toc.php` (контент + TOC справа) поверх старых светлых стилей `_cpt-common/_two-col/_toc/_service/_case/_single-post` (в `ignoreFiles` stylelint, префиксы `s-`/`svc-`). Внешне не совпадает с главной.
- **Фирменный стиль = главная:** светлый фон `--c-bg`, сетка-рамка `.frame` (`--border`), синие кнопки `.button--primary` (`--c-blue`), navy-плашки (`--c-navy`), карточки с тонкой рамкой, uppercase-хлебные крошки. Токены — второй блок `:root` в `_tokens.scss` (`--c-*`, `--border`, `--pad-x`, `--header-h`, `--radius`). Старые тёмные `--color-bg-*`/`--color-teal` на новых страницах не использовать.
- **Структура референса** (`ref/promedi_block/theme/vite-wordpress-starter-theme/single*.php`, `assets/src/scss/article.scss`, `partials/toc.php`):
  `breadcrumbs` → `section.section-article > .container > .wrapper` (grid `auto 295px`, gap 100px с `tablet`) → `article.article-main` (max-width 885px): `article-hero` (H1, meta-line, `article-lead`) → `article-image` (featured, eager) → `.entry-content` ; `aside.sidebar-toc` (sticky, top под шапку, `toc` с индикатором активного пункта + `toc__quick-contact` с кнопкой и телефоном) → ниже секции (врачи/CTA/статьи).
- **`.entry-content` в рефе** (`_base.scss`): h1–h6 `margin-block: 28px 12px` → с `mobile` `40px 20px`; `p, ul, ol, dl` 16px/26px, `margin-block: 12px` → с `mobile` 17px/150%, `16px`; `img { border-radius: 16px }`; `> *:last-child { margin-block-end: 0 }`.
- **Ширина в админке в рефе:** для не-page редакторов `.editor-styles-wrapper .is-root-container { max-width: 800px; margin: 40px auto 0 }`, page — на всю ширину. У нас статьи используют `style-editor.css` (через `add_editor_style`, page его вырезает), сейчас там `.wp-block { max-width: 760px }` и старые шрифты/цвета.
- **Лайтбокс:** сейчас Fancybox (`components/lightbox.js`, init в `main.js`, `@fancyapps/ui`). Решение: перейти на core lightbox (`theme.json` → `settings.blocks.core/image.lightbox`), Fancybox удалить. `STARTER_STRIP_BLOCK_STYLES` = false, значит `wp-block-library` (стили оверлея `.wp-lightbox-overlay`) грузится — проверить.

## Решения пользователя
- Лайтбокс: только core, Fancybox удалить.
- Hero CPT: стиль рефа + сохранить ACF — `subtitle`/`description` → `article-lead`, `buttons` → кнопки под lead, `blurbs` → ряд карточек под hero в фирменном стиле.
- Под контентом: FAQ + CTA (текущие ACF-группы, рестайл), блок «Схожі матеріали» (3 записи того же типа), CTA-блок в сайдбаре под TOC (как `toc__quick-contact`, контакты из Options).
- Single CPT/блог по-прежнему НЕ собираются из section-блоков.

## Tasks

### Phase 1 — Фундамент

- [x] **Task 1. Core lightbox вместо Fancybox**
- `theme/vite-wordpress-starter-theme/theme.json`: добавить в `settings`:
  ```json
  "blocks": { "core/image": { "lightbox": { "enabled": true, "allowEditing": true } } }
  ```
- Удалить `assets/src/js/components/lightbox.js`, импорт и вызов `initLightbox()` в `assets/src/js/main.js`; `npm uninstall @fancyapps/ui` (package.json/lock). Убрать `fancybox*` из исключений в `rules/base.md`/stylelint, упоминание Fancybox в `.ai-factory/DESCRIPTION.md` (Integrations) — через docs-checkpoint.
- Проверить, что на фронте у `figure.wp-block-image` появляются `data-wp-interactive="core/image"` и кнопка `.lightbox-trigger`, а `@wordpress/interactivity` / `core/image` view-скрипт подключается. Если `wp-block-library` не грузится на single — не включать `STARTER_STRIP_BLOCK_STYLES`, а при необходимости явно `wp_enqueue_style( 'wp-block-image' )`.
- Стили оверлея в фирменных цветах — в новом `components/_entry-content.scss` (Task 3): `.wp-lightbox-overlay .scrim { background-color: var(--c-bg) }`, крестик `--c-dark`, иконка-триггер `.lightbox-trigger` — `--c-blue`/`--radius`.
- Logging: нет (в JS ничего не логируем; init удалён).

- [x] **Task 2. TOC в стиле рефа + CTA в сайдбаре**
- Новый `partials/parts/toc.php` (args: `items` из `starter_get_toc()`): `nav.toc[data-toc]` → `p.toc__title` «Зміст» → `div.toc__nav` с `span.toc__indicator` + `ul.toc__list` (`toc__item--h2`) → `div.toc__cta`: кнопка `.button.button--primary` «Обговорити задачі» (ссылка на форму/контакт из `starter_get_option( 'footer_contact' )`, fallback `#contact`) + телефон/email из той же опции (проверить реальные подполя в `configure/acf/acf-json` группы Options). Если items пусто — выводить только CTA-блок.
- `assets/src/js/components/toc.js`: добавить движущийся индикатор как в рефе (`ref/.../assets/src/js/components/toc.js`: `transform` + `height` к активной ссылке, классы `toc__nav--ready` / `toc__nav--animated` после первой расстановки, `prefers-reduced-motion`). Scrollspy/клик через Lenis сохранить. Заменить два `console.warn` на `logDebug`.
- Новый `assets/src/scss/components/_toc.scss` (перезаписать старый, убрать из `ignoreFiles`): mobile first, BEM, токены `--c-*`, фон `--c-bg-light`, рамка `--border`, индикатор `1px solid var(--c-blue)`, активная ссылка `--c-blue` / weight 500; на `tablet` — `max-height: calc(100dvh - var(--header-h) - 40px)`, скролл только у `toc__nav`.
- Logging: JS — `logDebug` при init/skip; PHP — `WARN [toc] footer_contact option is empty` если опции нет (один раз на рендер).

- [x] **Task 3. Стили `.entry-content` и ширина редактора**
- Новый `assets/src/scss/components/_entry-content.scss` (подключается в `single-cpt.scss` и `single-post.scss`): отступы/кегли 1:1 из рефа (см. «Контекст») + фирменная типографика главной (шрифт/цвет `--c-dark`, заголовки как на главной): `ul` с маркерами-квадратами `--c-blue`, `ol` с номерами, `a` синие с подчёркиванием, `strong`, `blockquote` (левая линия `--c-blue`, фон `--c-bg-light`), `.wp-block-buttons .wp-block-button__link` = вид `.button--primary`, `figure`/`figcaption`, `img` (radius — `--radius` бренда вместо 16px, если на главной радиус иной), `table`, `> *:first-child { margin-top: 0 }`, `> *:last-child { margin-block-end: 0 }`, `scroll-margin-top: calc(var(--header-h) + 24px)` у h2.
- Убрать класс `svc-prose` из разметки (заменяется `.entry-content`).
- `style-editor.css`: ширина как в рефе — `.is-root-container { max-width: 800px; margin: 40px auto 0 }` (вместо `.wp-block { max-width: 760px }`; `alignwide`/`alignfull` оставить), типографику/цвета/шрифты синхронизировать с `_entry-content.scss` (фирменные, не старые `#3b3e41`/Fixel, если главная использует другие). Page-редактор не трогать (там стиль вырезается `starter_strip_admin_styles_from_page_canvas`).
- Logging: нет.

### Phase 2 — Разметка страниц

- [ ] **Task 4. Общий layout статьи: `partials/parts/article-layout.php`** (заменяет `content-with-toc.php`)
- Args: `lead` (string), `meta` (array строк/HTML: дата, рубрика-тег), `buttons` (array `label`/`url`), `blurbs` (array `title`/`text`), `show_thumbnail` (bool, default true).
- Разметка (BEM без префиксов): `section.article` → `div.container.article__container` (рамка `.frame`) → `div.article__grid`:
  - `header.article__hero`: `partials/breadcrumbs`, `h1.article__title`, `div.article__meta`, `p.article__lead`, `div.article__actions` (кнопки `.button--primary` / `.button--secondary`);
  - `figure.article__image` — `the_post_thumbnail( 'large', [ loading => eager, fetchpriority => high ] )`, если есть;
  - `div.article__blurbs` — карточки blurbs (стиль карточек главной);
  - `aside.article__aside` → `partials/parts/toc` (sticky);
  - `div.entry-content.article__content` → `the_content()`.
- **Без `wp_is_mobile()`** (ломает кеш страниц): aside рендерится один раз, порядок задаётся grid-областями — mobile: `hero / image / blurbs / aside / content`; с `tablet`: колонки `minmax(0, 885px) 295px`, gap 100px (как в рефе; на `laptop` при нехватке места — уменьшить gap), aside в правой колонке `grid-row: 1 / -1`, `position: sticky; top: calc(var(--header-h) + 20px)` (учесть скрытие шапки, если есть `header--hidden`-аналог).
- Новый `assets/src/scss/components/_article.scss`.
- Удалить `partials/parts/content-with-toc.php`, `components/_content-with-toc.scss`, `components/_two-col.scss` (проверить grep, что их больше никто не использует).
- Logging: нет.

- [ ] **Task 5. Single CPT: services / directions / cases** (зависит от 2–4)
- `single-services.php`: `article-layout` с `lead = service_hero.subtitle`, `buttons = service_hero.buttons`, `blurbs = service_hero.blurbs`; `service_hero.text` — второй абзац lead (`p.article__note`) или в meta — по виду. Затем `cpt-faq` (`service_faq`), `related-posts`, `cpt-cta` (`service_cta`).
- `single-directions.php`: `lead = direction_hero.description`, `blurbs = direction_hero.blurbs`; далее FAQ/related/CTA из `direction_*`.
- `single-cases.php`: `lead = case_hero.subtitle`; далее FAQ/related/CTA из `case_*`.
- Убрать `s-cpt-hero` из single-шаблонов (архив `partials/parts/archive-cpt.php` пока использует его — не ломать).
- Logging: нет (данные опциональны, пустые поля просто не выводятся).

- [ ] **Task 6. Single post (блог)** (зависит от 2–4)
- `single.php`: `article-layout` с `meta` = тег первой рубрики (ссылка на рубрику, `.tag`) + дата `get_the_date( 'd.m.Y' )`, `lead` = `has_excerpt() ? get_the_excerpt() : ''`; затем `related-posts` (по рубрике, fallback — свежие) и `cpt-cta` с дефолтными строками (`__()`): «Обговорімо ваш медзаклад» / кнопка на контакт из Options.
- Logging: нет.

- [ ] **Task 7. Нижние блоки: FAQ, CTA, «Схожі матеріали»**
- `partials/parts/cpt-faq.php`: BEM `faq-section`/`faq` в фирменном стиле (строки с `--border`, плюс/шеврон `--c-blue`, ширина = колонка контента, внутри `.frame`); JS `faqAccordion.js` не менять кроме селекторов при необходимости.
- `partials/parts/cpt-cta.php`: navy-плашка как на главной (`--c-navy`, белый текст, кнопка `.button--primary` / светлая), классы `cta-banner__*`.
- Новый `partials/parts/post-card.php` (вынести карточку из `archive-cpt.php`, переиспользовать в архиве и related — excerpt-map туда же) и `partials/parts/related-posts.php` (args: `post_type`, `exclude`, `tax_query` опц.; `WP_Query` 3 записи, `no_found_rows`, `ignore_sticky_posts`; заголовок «Схожі послуги/напрямки/кейси/матеріали»; сетка 1 → 3 колонки на `tablet`). Ничего не выводить, если записей нет.
- SCSS: `components/_faq.scss`, `components/_cta-banner.scss`, `components/_related-posts.scss`, `_post-card` (или переиспользовать `_archive.scss` карточку после выноса).
- Logging: нет.

### Phase 3 — Сборка, чистка, проверка

- [ ] **Task 8. Entry-файлы, enqueue и удаление legacy SCSS**
- `single-cpt.scss` / `single-post.scss`: подключить `_article`, `_entry-content`, `_toc`, `_faq`, `_cta-banner`, `_related-posts`, `_post-card`; убрать `cpt-common`, `content-with-toc`, `service`, `case`, `single-post`.
- Удалить неиспользуемые legacy-partials (`_service`, `_case`, `_single-post`, `_two-col`, `_content-with-toc`; `_cpt-common` — только если `archive.scss`/`archive-cpt.php` от него не зависят, иначе оставить в архивном entry) и убрать их из `ignoreFiles` в `.stylelintrc.json`.
- `single-post.js` / `single-cpt.js`: оставить `initToc` (+ `initFaqAccordion` где нужен FAQ — для posts не нужен). Проверить, что `enqueue-listing-templates-assets.php` (prio 110, dep `main`) не требует изменений; `jquery` в deps single-cpt убрать, если `faqAccordion.js` без jQuery.
- Logging: существующий `WARN [vite] manifest entry missing` покрывает отсутствующие entries.

- [ ] **Task 9. Проверка**
- `npm run build`, `npm run lint`, `composer lint` (PHPCS + PHPStan; baseline не расширять).
- Playwright 1440 и 390 px: `/services/web-development/`, `/directions/addiction-treatment/`, `/cases/lviv-medical-center/`, `/navishcho-likariu-sait/` (уточнить permalink поста), главная — без регрессий; sticky TOC + индикатор, CTA в сайдбаре, related, FAQ, CTA.
- Лайтбокс: временно вставить `core/image` в черновик поста через WP-CLI (или в редакторе), проверить открытие/закрытие оверлея, затем удалить тестовый контент.
- Редактор поста/услуги: колонка 800px по центру, типографика как на фронте; редактор page — без изменений.

## Commit Plan
1. После Task 1–3: `feat(single): core image lightbox, brand TOC and entry-content styles`
2. После Task 4–7: `feat(single): rebuild CPT and blog singles on shared article layout`
3. После Task 8–9: `refactor(scss): drop legacy CPT/blog partials and Fancybox`
