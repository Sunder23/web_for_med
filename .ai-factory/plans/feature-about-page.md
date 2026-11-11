# Implementation Plan: Страница «Про нас» (Figma, ноябрь 2026)

Branch: feature/about-page (от main)
Created: 2026-11-11

## Settings
- Testing: no (проверка — визуально в браузере 1440 / 1024 / 375 через Claude in Chrome, сверка с макетом через Figma plugin; `npm run lint`, `composer lint`)
- Logging: verbose в рамках правил проекта — PHP только `error_log( 'WARN [area] …' )` на сбоях/пустых данных; JS только через `utils/logDebug.js` (без новых `console.log`); WP-CLI — `WP_CLI::log()` / `WP_CLI::warning()`
- Docs: yes — обязательный docs checkpoint через `/aif-docs` в конце

## Источники в Figma (fileKey `huDLClWg3dIpXyzLMuVHEs`)
| Что | node |
|---|---|
| About desktop (1920, контейнер 1350) | `3241:4188` — hero `3241:4190`, team `3241:4199`, cta `3241:4222`, полоса 40px перед футером `3241:4234` |
| About mobile 375 | `3255:874` — hero `3255:883`, team `3255:896`, cta `3255:920` |
| Hover-плашки команды (только desktop) | `3241:4277` (курсор + плашка: смещение плашки от острия курсора ≈ 12px вправо / 26px вниз) |
| Картинка hero с надписью «NO FUTURE NOW*» | кадр `3241:4195` (802×810) — экспортировать **целиком, с надписью** |
| Фото команды | `3241:4203`, `3241:4208`, `3241:4213`, `3241:4218` (305×400) |
| Иллюстрация «OPTIMISM: 99%» | `3241:4225` (177×153, рамка `#dbdbdb`) |

Страница уже создана: `/about/` = page **ID 700**, `post_content` пустой. Хедер и футер — общие, уже соответствуют макету (не трогаем).

## Спецификация по макету

**Hero (`about-intro`)** — desktop: `.container.frame`, padding 32px 35px, `gap: 100px`; слева колонка 378px, текст 328px — Fixel 500 16/1.55 `#3b3e41`, абзацы через 10px; справа `flex: 1` — картинка 802×810 (`aspect-ratio: 802/810`, `object-fit: cover`), под ней через 10px подпись Fixel 500 13/1.4 `--c-dark-blue` по центру «[*Inspired by “No Future Now”, Dmitry Dulfan, 1997]». Mobile: картинка **сверху** (порядок: картинка+подпись, потом текст), рельс padding 40px 10px, `gap: 24px`, картинка 325×328, абзацы через 16px.
Секция без видимого заголовка → визуально скрытый `<h1>` с заголовком страницы (SEO/a11y).

**Team (`team`)** — desktop: padding 48px 35px, 4 колонки `gap: 20px`; карточка: фото `aspect-ratio: 305/400` cover, `gap: 20px`, `padding-bottom: 24px`; имя Fixel 500 20/1.05 `#2e2f30`, роль Fixel 500 14/1.24 `#3b3e41`, между ними 12px. Mobile: 2 колонки `gap: 12px`, ряды через 32px, padding 40px 10px; фото той же пропорции (≈156×203); имя 16/1.25, роль 13/1.25, gap 8, фото→текст 14px. Tablet — 2 колонки (или 4, если влезает ≥ 200px на фото — решить при проверке 1024).
**Hover-плашка** (desktop only): `--c-dark-blue` `#092a4a` фон, padding 10px, radius 2px, Fixel 600 12px uppercase, letter-spacing 0.48px, line-height 1.25, цвет `#f8f9f9`, `white-space: nowrap`. Следует за курсором (позиция = курсор + 12px / 26px), появляется при наведении на карточку, скрыта на touch/mobile. Тексты: Валерій — «Іронічний прагматик», Іра — «Володарка коду», Оля — «Адвокат деталей», Макс — «Верстає силою думки».

**Owner CTA (`owner-cta`)** — desktop: padding 48px 35px, `display: flex; justify-content: space-between; align-items: flex-end`; слева иллюстрация 177×153 (border `var(--border)`) + через 16px текстовый столбец 545px высотой с картинку (`justify-content: space-between`): цитата Fixel 500 20/1.25 `#2e2f30`, подпись Fixel 500 16/1.15; справа две кнопки `gap: 24px` — `button--primary` («Написати в Telegram») и **новый** `button--outline` («Зв’язатися у Whatsapp»: белый фон, 1px `--c-blue` border, текст `--c-blue`). Mobile: колонка `gap: 24px`, иллюстрация на всю ширину высотой 153 (`object-fit: contain`), цитата 17/1.25, подпись Fixel **400** 16/1.15, кнопки full width 50px, `gap: 12px`.

**Вертикальный ритм:** между hero / team / cta **нет** полосы `--section-gap` — только линия `var(--border)`. Перед футером — полоса **40px** (desktop и mobile, `3241:4234`, mobile footer `pt 40`).

## Решения и допущения
- Три новые секции-блока `acf/section-{about-intro,team,owner-cta}` по существующему движку (`docs/page-sections.md`), а не отдельный `page-about.php` — страница редактируется в блок-редакторе, как главная.
- Надпись «NO FUTURE NOW*» **запекается в картинку** (экспорт кадра `3241:4195` с текстом) — без шрифта Manrope; редактор меняет одну картинку. Отдельной mobile-картинки нет (пропорция почти 1:1, та же картинка).
- Ссылки Telegram / WhatsApp — **поля в самой секции** (repeater кнопок: link + стиль `primary|outline`), не ACF Options. Реальных URL пока нет → заглушки `https://t.me/` / `https://wa.me/` в миграции, заказчик подставит; пустая ссылка → кнопка не выводится + WARN.
- Hover-плашка — текст из поля `label` участника; включается только при `(hover: hover) and (pointer: fine)` и от брейкпоинта `tablet`; разметка `aria-hidden="true"` (декоративная). Без JS плашка просто не показывается.
- Полосу-разрыв убираем для `about-intro` и `team` через список исключений в `_base.scss` (`section:not(...)`), для `owner-cta` — локальное `--section-gap: 40px` на секции.
- Фото и иллюстрации — в медиатеку через WP-CLI скрипт (как `update-home-redesign.php`), не из темы, чтобы редактор мог менять. Конвертация в WebP — существующий механизм загрузки (`optimize.php`).

## Open questions
1. Реальные ссылки Telegram / WhatsApp Валерия (сейчас заглушки).
2. Должен ли «Про нас» в меню получать активное состояние (`activeNav.js` работает по якорям главной) — проверить, что пункт меню ведёт на `/about/`, а не на `/#about`.

## Commit Plan
- **Commit 1** (после задач 1–3): `feat(about): about-intro and team section blocks with hover labels`
- **Commit 2** (после задач 4–5): `feat(about): owner CTA section and page rhythm`
- **Commit 3** (после задач 6–7): `chore(about): content script and docs`

## Tasks

### Phase 1: Ассеты и секции

- [x] **Task 1: Ассеты из Figma**
  - Скачать через `get_design_context` / `download_assets` (не временные URL в коде): картинка hero — экспорт кадра `3241:4195` **с надписью** в 2x (≈1604×1620) → `scripts/data/about/about-hero-2x.png`; 4 фото команды 2x (`3241:4203`, `4208`, `4213`, `4218`) → `scripts/data/about/team-{syschuk,buhutska,unhurian,kravchuk}-2x.png`; иллюстрация `3241:4225` 2x → `scripts/data/about/owner-optimism-2x.png`.
  - Проверить, что файлы непустые и пропорции совпадают с макетом (802×810, 305×400, 177×153).
  - Логирование: не требуется (разовая загрузка).
  - Files: `scripts/data/about/*`.

- [x] **Task 2: Секция `about-intro` (hero)**
  - `template-parts/blocks/section-about-intro.php`: guard `$args`; поля `text` (WYSIWYG/textarea с абзацами → `wp_kses_post`), `image` (ID, `wp_get_attachment_image( …, 'large', false, [ 'class' => 'about-intro__image', 'sizes' => '(min-width: 1024px) 802px, 100vw', 'loading' => 'eager', 'fetchpriority' => 'high' ] )`), `caption`. Визуально скрытый `<h1 class="screen-reader-text">` с `get_the_title()`. Если нет ни текста, ни картинки — `error_log( 'WARN [section-about-intro] text and image are empty' )` и return; если нет картинки — WARN и рендер только текста.
  - Разметка: `<section class="about-intro"><div class="about-intro__wrap container"><div class="about-intro__inner frame">` → `__text` + `__media` (`__image`, `__caption`). Mobile-порядок «картинка → текст» через `order`/`flex-direction: column-reverse` или порядок в DOM (картинка первая в DOM, на desktop `order` для текста — выбрать вариант с картинкой первой в DOM для LCP).
  - `assets/src/scss/template-parts/blocks/section-about-intro.scss` — по спецификации выше, mobile first, `@include breakpoint(tablet|laptop)`; desktop-раскладка (колонки 378 + flex:1, gap 100) с `laptop`, на `tablet` — gap поменьше (≈40px), колонка текста ~40%.
  - Если в теме нет `.screen-reader-text` — добавить утилиту в `_base.scss` (стандартный WP-вариант clip-path).
  - Блок: `configure/acf/section-blocks/section-about-intro/{block.json,render.php}` (title «About intro», icon `id`); поля `configure/acf/acf-json/group_starter_section_about_intro.json` (ключи `field_starter_section_about_intro_*`); slug в `starter_get_section_slugs()`.
  - Логирование: PHP WARN как описано.
  - Files: `section-about-intro.php`, `section-about-intro.scss`, `section-blocks/section-about-intro/*`, `group_starter_section_about_intro.json`, `configure/section-blocks.php`, при необходимости `_base.scss`.

- [x] **Task 3: Секция `team` + hover-плашка** (depends on 2 — общая схема блока)
  - Поля (`group_starter_section_team.json`): repeater `members` → `photo` (image ID), `name` (text), `role` (text), `label` (text, «плашка при наведении»). Пустой repeater → WARN `[section-team] members is empty` и return; участник без имени пропускается с WARN.
  - `template-parts/blocks/section-team.php`: `<section class="team"><div class="team__wrap container"><ul class="team__grid frame">` → `<li class="team__member" data-team-label>` (`__photo` через `wp_get_attachment_image( …, 'medium_large', …, ['sizes' => '(min-width: 1024px) 305px, 50vw', 'loading' => 'lazy'] )`, `__name`, `__role`, `<span class="team__label" aria-hidden="true">` если `label` заполнен).
  - `section-team.scss`: сетка 2 колонки (mobile, gap 12 / row-gap 32) → 4 колонки с `laptop` (gap 20); на `tablet` проверить 4 колонки при 1024 (фото ≥ 220px) — иначе 2. Плашка: `position: fixed; left: 0; top: 0; transform: translate3d(var(--x), var(--y), 0)`, `pointer-events: none`, `opacity: 0`, `transition: opacity .2s`; `.team__member.is-hovered .team__label { opacity: 1 }`; всё это только внутри `@media (hover: hover) and (pointer: fine)` + `@include breakpoint(tablet)`, иначе `display: none`. `prefers-reduced-motion` — без transition. Курсор над карточкой — обычный (в макете стрелка — системный курсор).
  - Если `position: fixed` внутри секции ломается из-за `transform`/Lenis у предков — использовать `position: absolute` относительно `.team__member` и координаты относительно карточки (`clientX - rect.left`). Выбрать по факту проверки.
  - JS: `assets/src/js/components/teamHoverLabel.js` — `initTeamHoverLabels(root = document)`: `matchMedia('(hover: hover) and (pointer: fine)')` + ширина ≥ tablet, иначе ничего не делает; на `pointerenter` — `is-hovered`, `pointermove` — запись `--x/--y` (курсор + 12 / 26px) через `requestAnimationFrame`, на `pointerleave` — снять класс; не выходить плашкой за правый край вьюпорта (сдвиг влево при переполнении). Entry `assets/src/js/template-parts/blocks/section-team.js` только импортирует и вызывает init.
  - Логирование: `logDebug('[teamHoverLabel] init', { count, enabled })`, `logDebug('[teamHoverLabel] skip: no fine pointer')`; без логов на каждый `pointermove`.
  - Блок: `section-blocks/section-team/*`, slug `team` в `starter_get_section_slugs()`.
  - Files: `section-team.php`, `section-team.scss`, `section-team.js`, `components/teamHoverLabel.js`, `section-blocks/section-team/*`, `group_starter_section_team.json`, `configure/section-blocks.php`.
<!-- Commit checkpoint: tasks 1-3 -->

- [x] **Task 4: Секция `owner-cta` + кнопка `button--outline`**
  - Поля (`group_starter_section_owner_cta.json`): `image` (ID), `text` (textarea, цитата), `subtext` (text), repeater `buttons` → `link` (ACF link: url/title/target), `style` (select `primary|outline`, default `primary`). Пустые `text` и `buttons` → WARN `[section-owner-cta] text is empty` и return; кнопка без url/title — пропуск с WARN.
  - `template-parts/blocks/section-owner-cta.php`: `<section class="owner-cta"><div class="owner-cta__wrap container"><div class="owner-cta__inner frame">` → `__person` (`__image`, `__body` с `__text` / `__subtext`) + `__actions` (`<a class="button button--{style} owner-cta__button">`, внешним ссылкам `target="_blank" rel="noopener"`).
  - `section-owner-cta.scss`: по спецификации; на `laptop` — ряд `space-between / flex-end`, на `tablet` — person сверху, кнопки снизу в ряд; mobile — колонка, кнопки на всю ширину. Локально `--section-gap: 40px` (полоса перед футером 40px, `3241:4234`).
  - `components/_buttons.scss`: `.button--outline` — фон `var(--c-white)`, `border: 1px solid var(--c-blue)`, цвет `var(--c-blue)`; hover — как у primary (синий фон, белый текст). Проверить, что высота совпадает с primary (border учесть: `padding` −1px или `box-shadow: inset`).
  - Блок: `section-blocks/section-owner-cta/*`, slug `owner-cta`.
  - Логирование: PHP WARN как описано.
  - Files: `section-owner-cta.php`, `section-owner-cta.scss`, `components/_buttons.scss`, `section-blocks/section-owner-cta/*`, `group_starter_section_owner_cta.json`, `configure/section-blocks.php`.

- [x] **Task 5: Ритм страницы и редактор** (depends on 2–4)
  - `_base.scss`: добавить `.about-intro, .team` в исключения `section:not(.hero, .quote, …)::after` — между секциями только линия, без полосы. Проверить, что линии рельса (`.frame` border-left/right) непрерывны от хедера до футера, а горизонтальные линии не двоятся (`margin-top: -1px` на mobile).
  - `editor-section-blocks.scss`: плашка `.team__label` в редакторе скрыта; секции корректно выглядят в canvas (стили подключаются автоматически через `starter_vite_enqueue_editor_canvas_assets`).
  - Проверить в браузере `http://localhost:8080/about/` на 1440 / 1024 / 375 против `3241:4188` и `3255:874` (Claude in Chrome); hover-плашка на desktop, отсутствие на mobile-эмуляции touch.
  - Логирование: не требуется (CSS).
  - Files: `assets/src/scss/_base.scss`, `assets/src/scss/editor-section-blocks.scss`.
<!-- Commit checkpoint: tasks 4-5 -->

### Phase 2: Контент и документация

- [x] **Task 6: WP-CLI скрипт наполнения страницы** (depends on 1–4)
  - `scripts/update-about-page.php` по образцу `update-home-redesign.php`: dry run по умолчанию, `apply` — запись; ищет страницу по slug `about` (не по ID); sideload файлов из `scripts/data/about/` один раз с метой `_starter_source = about/<file>` (повторный запуск переиспользует вложения); alt-тексты (имена участников, «Команда web4med — No Future Now»).
  - Собирает `post_content` из трёх блоков `acf/section-about-intro`, `acf/section-team`, `acf/section-owner-cta` с данными (`acf_register_block` формат `<!-- wp:acf/section-… {"name":…,"data":{…}} /-->`, ключи полей через `_field` ↔ `field_starter_section_*`), тексты — из макета (5 абзацев, 4 участника с ролями и плашками, цитата Валерия, подпись, кнопки с заглушками `https://t.me/` и `https://wa.me/`).
  - Если в странице уже есть `acf/section-*` блоки — не перезаписывать без аргумента `force`; непустой старый контент — бэкап в мету `_starter_pre_blocks_content`.
  - Проверить пункт меню «Про нас» в header/footer-меню: ведёт на страницу 700 (`/about/`); если на `/#about` — сообщить в логе (WARN), не менять меню автоматически.
  - Логирование: `WP_CLI::log()` на каждый шаг (найдена страница, загружен/переиспользован файл, блок собран, dry run/apply), `WP_CLI::warning()` — нет файла, нет страницы, пункт меню ведёт не туда.
  - Запуск: `MSYS_NO_PATHCONV=1 make wp cmd="eval-file /scripts/update-about-page.php"` → затем `… apply`.
  - Files: `scripts/update-about-page.php`, `scripts/data/about/*`, при необходимости цель `update-about` в `Makefile`.

- [x] **Task 7: Документация** (depends on 1–6)
  - `docs/page-sections.md`: таблица секций — новый подраздел «About page» (`about-intro`, `team` + `teamHoverLabel`, `owner-cta`), правило ритма (без полосы между ними, 40px перед футером), `button--outline`, раздел про `scripts/update-about-page.php`.
  - `AGENTS.md`: `scripts/` — добавить `update-about-page.php (+ data/about/)`; `assets/src/js/components/` — `teamHoverLabel`.
  - Через `/aif-docs` (docs checkpoint).
  - Files: `docs/page-sections.md`, `AGENTS.md`.
<!-- Commit checkpoint: tasks 6-7 -->

## Проверка перед коммитами
- `npm run lint` и `npm run build` (в каталоге темы), `composer lint`.
- Визуально 1440 / 1024 / 375 против макетов; hover-плашка: появляется у курсора, не выходит за край экрана, нет на touch.
- `debug.log` без новых WARN на заполненной странице.
