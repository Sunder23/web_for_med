# Implementation Plan: Страница «Послуги» (`/services/`, Figma, ноябрь 2026)

Branch: feature/services-page (от main)
Created: 2026-11-27

## Settings
- Testing: no (проверка — визуально 1920 / 1440 / 1024 / 375 через Claude in Chrome, сверка с макетом через Figma plugin; `npm run lint`, `composer lint`)
- Logging: verbose в рамках правил проекта — PHP только `error_log( 'WARN [area] …' )` на сбоях/пустых данных; JS только через `utils/logDebug.js` (без новых `console.log`); WP-CLI — `WP_CLI::log()` / `WP_CLI::warning()`
- Docs: yes — обязательный docs checkpoint через `/aif-docs` в конце

## Источники в Figma (fileKey `huDLClWg3dIpXyzLMuVHEs`)
| Что | Desktop (`3534:2180`, 1920, контейнер 1350) | Mobile (`3589:7251`, 375) |
|---|---|---|
| Hero: подпись `/*services*/` + H1 | `3537:2555` | `3589:7272` |
| Дашборд «Клініка · огляд» на сетке 45px | `3537:2570` (панель `3537:2617`, 1280×448) | `3589:7275` (та же панель, пропорционально уменьшена до 375×150) |
| Список услуг (интро + 5 услуг + «Перейти до кейсів») | `3534:2296` (активна SEO) | `3589:7383` (раскрыта «Реклама в Google») |
| Состояния списка с другой активной услугой (превью-фото у каждой своё) | Розробка сайтів `3589:7642` (часы), SEO `3534:2296` (дорога/фонарь), Реклама в Google `3589:7688` (футболист), Аналітика та моніторинг `3589:7734` (рубка корабля), Комунікація з пацієнтами `3589:7780` (бумажный самолёт) — превью в каждом кадре: дочерний «Прев’ю обраної послуги» (`3589:7645` для `7642`, в остальных — тот же слой) | — |
| «Якщо у вас вже є хороший сайт…» + список компетенций + панель задач | `3534:2404` (панель `3534:2424`) | `3589:7440` (панель `3589:7469`) |
| «Підключаємось на будь-якому етапі» (сетка-полоса + 4 этапа) | `3539:2727` | `3589:7492` |
| FAQ «Поширені запитання» | `3534:2487` | `3589:7573` |

Хедер / футер — общие, уже соответствуют макету (не трогаем). Меню в макете: Про нас / Кейси / Блог / Контакти — пункт «Послуги» **не добавляем**.

## Спецификация по макету (кратко; точные размеры брать из `get_design_context` по node)

**Hero** — caption `/*SERVICES*/` (как `/*CASE ARCHIVE*/`, `--c-blue`), H1 Grunt Grotesk ≈ 40px / 990px шириной; рельс `.container.frame`. Mobile: H1 ≈ 30px, padding 40px 25px.

**Дашборд** — декоративная полоса 540px: сетка 45×45 линий `#dbdbdb` (CSS `repeating-linear-gradient`, без 40+ DOM-линий), поверх панель 1280×448: шапка «Клініка · огляд» / «Останні 30 днів», левое меню (Огляд — активный, Звернення, Джерела пацієнтів, Аналітика, Комунікації), заголовок «Ефективність залучення пацієнтів», 3 KPI-карточки (248 ↑18%, 176 ↑12%, ₴ 184 ↓9%), столбчатый график «Динаміка звернень» (18 столбцов, высоты из макета, последние 4–6 темнее), «Звідки приходять пацієнти» (Пошук Google 46%, Реклама Google 35%, Прямі переходи 19% — прогресс-бары 4px). На mobile/tablet панель **масштабируется целиком** (дизайн-ширина 1280px → `transform: scale(var(--dashboard-scale))`, высота обёртки через `aspect-ratio`), как в мобильном макете (375×150).
Анимация (при входе во viewport, один раз): столбцы растут `scaleY` с каскадом, полосы источников заполняются `scaleX`, KPI-числа count-up; `prefers-reduced-motion` → сразу финальное состояние. Данные захардкожены в шаблоне (декор), `aria-hidden="true"` + визуально скрытое текстовое описание не нужно (чисто декоративно).

**Список услуг** — desktop: слева колонка 375px — превью 375×375 (фон `#edf6f8`, `object-fit: cover`) + описание активной услуги (Fixel 500 16/1.55 `#3b3e41`, отступ 12px); справа 900px — интро-текст (Fixel 500 20/1.5, 614px, padding-left 52px), ниже через 48px строки: padding 28px 52px, border-bottom `#dbdbdb`, иконка 22–26px + название Fixel 500 20/1.2 `#2e2f30`, справа «+» 36px Fixel Light `--c-blue`. Активная строка — фон `#edf6f8`, radius 8px. Последняя строка «Перейти до кейсів» — ссылка `--c-blue` + стрелка `›` 36px, без border.
Поведение desktop (≥ tablet): наведение (fine pointer) / клик / фокус активирует услугу → превью и описание слева переключаются (crossfade; можно glitch как на главной). Mobile: аккордеон — тап по строке раскрывает под ней описание + картинку 325×325, «+» → «−»; одна открытая за раз; по умолчанию открыта одна (в макете — «Реклама в Google»).

**Компетенции + задачи** — заголовок-секция (H2 Grunt Grotesk ≈ 40px, 990px). Ниже 2 колонки 684 / 664: слева 6 строк (иконка 36px + текст Fixel 500 ≈ 18px, padding ≈ 36px 35px, border-bottom); справа панель с радиальным градиентом (`#e3effb` → `#c0dff7`), padding 48px 40px: точка 19px + «Яке завдання стоїть перед вами?» Fixel 600 32/1.15, теги (белый фон, radius 4, padding 12px 18px, Fixel 500 16/1.3, gap 16), кнопка «Обговоримо?» `button--primary` справа внизу. Mobile: колонкой, панель на всю ширину, кнопка full width.
Теги — **переключатели** (`<button type="button" aria-pressed>`; выбранный — `--c-blue` фон/рамка, белый текст — уточнить у дизайна, иначе по общим стилям `tag`). «Обговоримо?» открывает contact drawer (`data-contact-open`) и подставляет выбранные задачи в `textarea[name="your-message"]` CF7: `Завдання: <tag1>; <tag2>` (не затирая уже набранный пользователем текст — дописать первой строкой, повторный клик заменяет только эту строку).

**Этапы** — сверху декоративная полоса сетки 112px (тот же grid-компонент), H2 «Підключаємось на будь-якому етапі роботи над проєктом», 4 колонки по 337px с вертикальными разделителями: иконка 32px (rocket / puzzle / refresh / handshake 54×32), заголовок Fixel 500 20 (2 строки), текст Fixel 400 14–16. Mobile: колонкой, разделители горизонтальные.

**FAQ** — 2 колонки 713 / 634: слева H2 «Поширені запитання», справа аккордеон (строки padding 28px 32px, border-bottom, «+»/«−» 36px). Первый пункт раскрыт. Без блока «Не знайшли відповідь?» (отличие от `cpt-faq`). Перед футером — полоса 40px (как на about).

**Ритм:** между секциями — линии `var(--border)` и полосы `--section-gap` как в макете (проверить по скриншоту: hero→дашборд без разрыва, после списка услуг и после компетенций — полоса ≈ 40px).

## Решения и допущения
- Страница — обычный `page` (slug `services`, заголовок «Послуги»), собирается из **новых section-блоков** `acf/section-*` по движку `docs/page-sections.md` (как главная и about). Отдельный `page-services.php` не делаем.
- **CPT `services` удаляется полностью** вместе с 4 записями (seo, ppc, analytics, web-development) — решение пользователя. Освобождает URL `/services/` для страницы. `directions` и `cases` не трогаем.
- Старые URL `/services/<slug>/` после удаления → 301 на `/services/` (маленький хук на `template_redirect` при 404, только для префикса `services/`), чтобы не плодить 404 из индекса / внутренних ссылок.
- Новые блоки (BEM без префиксов; **не** использовать класс `.services` — его ищет `servicesAccordion.js` главной):
  - `services-hero` — caption + H1 + дашборд (разметка дашборда в `partials/parts/clinic-dashboard.php`, данные — константы в partial);
  - `service-list` — интро, repeater услуг (`icon` image, `title`, `text`, `image`), ссылка (`link` ACF link, по умолчанию `/cases/`);
  - `capabilities` — `title`, repeater `items` (`icon`, `text`), `tasks_title`, repeater `tasks` (`label`), `button_label`;
  - `stages` — `title`, repeater `items` (`icon`, `title`, `text`);
  - `faq` — `title`, repeater `items` (`question`, `answer` wysiwyg); переиспользует `.faq` + `components/faqAccordion.js` (`data-faq`), своя раскладка `.faq-block`.
- Сетка-декор 45px — общий компонент `components/_grid-lines.scss` (`.grid-lines`), используется в `services-hero` и `stages`.
- Иконки — SVG из Figma, загружаются в медиатеку как attachments (прецедент: `static/images/home/process-*.svg`), чтобы редактор мог менять. Картинка услуги — из Figma `3534:2301` (2x WebP через существующий механизм загрузки).
- Картинки всех 5 услуг есть в макете (кадры состояний `3534:2296`, `3589:7642`, `7688`, `7734`, `7780`). **Описания услуг берём с главной** (решение дизайнера): скрипт контента читает блок `acf/section-services` страницы `page_on_front` (`items_N_text` по совпадению порядка услуг: web, seo, ads, analytics, communication) и копирует тексты в `service-list` при создании страницы. В рантайме блок с главной **не читает** (анти-паттерн «front-page ACF из других шаблонов») — это одноразовое копирование; если на главной текст пустой → WARN и общий текст-фолбэк из `scripts/data/services/content.php` (тот же «Пошукові кампанії…»). Сейчас у всех 5 услуг один текст — это принято (уникальных текстов пока нет); когда появятся — поправить на главной и перезапустить скрипт с `force` или поправить в блоке вручную.
- Ответы FAQ: у всех 4 вопросов один текст — ответ первого вопроса из макета (`3543:2990`).
- Иконки услуг — **те же, что на главной**: `static/images/home/service-{web,seo,ads,analytics,communication}.svg` (в медиатеке уже есть attachments 659–671 с `_starter_source`) — переиспользовать через тот же `_starter_source`, не загружать повторно; сверить с иконками макета (`3547:2992` и др.), новые качать только если отличаются. Иконка «Перейти до кейсів» (`3571:3330`) — новая.
- Активная по умолчанию — поле блока `default_active` (число, по умолчанию 2 = SEO, как в основном кадре desktop); на mobile этот же пункт раскрыт при загрузке. Тексты этапов / FAQ-ответов — брать из `get_design_context` (на десктопе у этапов в слоях стоит заглушка «Інтеграція CRM…», реальные тексты видны на скриншоте — сверить с mobile-нодами).

## Open questions
1. ~~Описания услуг и ответы FAQ~~ — решено (2026-11-27): реальных текстов пока нет, **ставим один текст на всё**. Описания всех 5 услуг — текст с главной («Пошукові кампанії з урахуванням обмежень у медичній тематиці…»); ответы на 3 закрытых вопроса FAQ — тот же текст, что у открытого первого вопроса в макете («Так, звичайно. Впродовж 1 місця після здачі…»). Никаких `TODO(client)` и уникальных черновиков; заменяются позже в редакторе блоков.
2. Вид выбранного тега в «Яке завдання…» — в макете нет состояния; делаем по общим стилям (синяя рамка/фон), показать дизайнеру.
3. Был ли `/services/<slug>/` где-то проиндексирован на проде — если сайт ещё не в проде, 301-хук можно потом убрать.

## Commit Plan
- **Commit 1** (после задачи 1): `refactor(services): remove services CPT, templates and ACF group`
- **Commit 2** (после задач 2–4): `feat(services): services hero with animated dashboard and service list blocks`
- **Commit 3** (после задач 5–7): `feat(services): capabilities with task picker, stages and FAQ blocks`
- **Commit 4** (после задач 8–9): `chore(services): services page content script and docs`

## Tasks

### Phase 1: Освободить `/services/`

- [x] **Task 1: Удалить CPT `services` и всё, что на него завязано**
  - Удалить: `configure/post-types/services.php` (+ убрать `'services.php'` из `configure/post-types.php`), `single-services.php`, `archive-services.php`, `configure/acf/acf-json/group_9033ee91.json` (Service Page; untracked-дубль `theme/.../acf-json/group_9033ee91.json` тоже), мёртвый `configure/cpt-taxonomy.php` (не подключён в `functions.php` — проверить и удалить).
  - Почистить ссылки на тип: `configure/helpers/get-breadcrumb-items.php`, `configure/helpers/is-block-content-view.php`, `configure/theme-hooks/enqueue-listing-templates-assets.php` (`is_singular( array( 'directions' ) )`), `partials/parts/post-card.php`, `partials/parts/post-nav.php`, `partials/parts/archive-cpt.php`, `template-parts/archive-cpt.php`, комментарий в `template-parts/cpt-faq.php`.
  - `404.php`: блок «послуги» сейчас берёт `get_post_type_archive_link( 'services' )` + `get_posts( services )` → ссылка на страницу `get_page_by_path( 'services' )` (если нет — блок не выводится + WARN `[404] services page not found`); список карточек услуг убрать или заменить статичными ссылками — по факту разметки (минимально, без новой вёрстки).
  - Новый хук `configure/theme-hooks/redirect-legacy-services.php` (+ в массив `theme-hooks.php`): на `template_redirect`, если `is_404()` и путь начинается с `services/` и не равен `services/` → `wp_safe_redirect( home_url( '/services/' ), 301 )`. Регистрация на уровне файла, условие внутри callback.
  - Скрипт `scripts/remove-services-cpt.php` (dry run / `apply`): удаляет все посты `post_type = services` (`wp_delete_post( $id, true )`, вместе с meta), пункты меню с `object = services`, потом `flush_rewrite_rules()`. Логи: `WP_CLI::log` на каждый пост, `WP_CLI::warning` на сбой, итог `WP_CLI::success`. Запуск — до создания страницы.
  - Legacy-скрипты (`scripts/import/*`, `migrate-*`, `scripts/data/services-content.php`, `assign-placeholder-thumbnails.php`) — не трогаем, кроме удаления `'services'` из массива в `assign-placeholder-thumbnails.php`.
  - Проверка: `grep -rn "'services'" theme/` — остались только секция главной (`section-services`) и её slug; `composer lint` чистый.
  - Files: перечисленные выше.

### Phase 2: Hero + список услуг

- [x] **Task 2: Ассеты из Figma**
  - Через `get_design_context` на ноды из таблицы скачать (не оставлять временные URL): иконку «Перейти до кейсів» (иконки 5 услуг — из `static/images/home/service-*.svg`, см. «Решения»), иконки компетенций (6), иконки этапов (4), точка панели задач, стрелка `›`; **5 фото услуг** в 2x (750×750) — слой «Прев’ю обраної послуги» из кадров `3534:2296` (`3534:2301`, SEO), `3589:7642` (`3589:7645`, сайты), `3589:7688` (реклама), `3589:7734` (аналитика), `3589:7780` (коммуникации).
  - Иконки → `scripts/data/services/icons/*.svg` (имена по смыслу: `service-web.svg`, `cap-local-seo.svg`, `stage-rocket.svg` …); фото → `scripts/data/services/service-{web,seo,ads,analytics,communication}-2x.png`. Стрелка `›` и точка — в `static/images/icons/` (используются шаблоном, не контентом).
  - Проверить непустые файлы и root width/height SVG как в макете.
  - Логирование: не требуется (разовая загрузка).
  - Files: `scripts/data/services/*`, `static/images/icons/*`.

- [x] **Task 3: Блок `services-hero` + анимированный дашборд**
  - Новый блок по `docs/page-sections.md`: `configure/acf/section-blocks/section-services-hero/{block.json,render.php}`, `configure/acf/acf-json/group_starter_section_services_hero.json` (`caption` text, `title` textarea), slug `services-hero` в `starter_get_section_slugs()`, `template-parts/blocks/section-services-hero.php`, `assets/src/scss/template-parts/blocks/section-services-hero.scss`, `assets/src/js/template-parts/blocks/section-services-hero.js`.
  - Разметка дашборда — `partials/parts/clinic-dashboard.php` (данные массивами в начале файла: KPI, 18 высот столбцов в %, источники), `aria-hidden="true"`. Сетка — новый `assets/src/scss/components/_grid-lines.scss` (`.grid-lines`, шаг через `--grid-step: 45px`), подключить в `main.scss`.
  - Масштаб: `assets/src/js/components/dashboardScale.js` — `ResizeObserver` на обёртку, ставит `--dashboard-scale = width / 1280` (≤ 1); без JS — `overflow: hidden` обёртка, панель не ломает раскладку.
  - Анимация: `assets/src/js/components/dashboardReveal.js` — `IntersectionObserver` (threshold ~0.3, один раз) ставит `is-revealed`; CSS-переходы `scaleY` / `scaleX` с `transition-delay: calc(var(--i) * 40ms)`; count-up чисел (`requestAnimationFrame`, 900ms, `data-count-to`, формат `₴ 184` через `data-prefix`); `prefers-reduced-motion` → сразу финал. `logDebug('[services-hero] dashboard revealed', …)`, `logDebug` при отсутствии разметки.
  - Пусто `title` → WARN `[section-services-hero] title is empty` и рендер без H1 не делаем (return).
  - Files: перечисленные выше.

- [x] **Task 4: Блок `service-list` (hover-превью на desktop, аккордеон на mobile)**
  - Блок `service-list` (те же 6 файлов + field group `group_starter_section_service_list.json`: `intro` textarea, repeater `items` {`icon` image ID, `title` text, `text` textarea, `image` image ID}, `link` (ACF link)).
  - Разметка: `<section class="service-list">` → `__preview` (desktop: все картинки + тексты стопкой, видна активная, `aria-hidden="true"`) и `__items` — каждая услуга `<button class="service-list__toggle" aria-expanded aria-controls>` + `__details` (текст + картинка, для mobile). Последняя строка — `<a class="service-list__link">` с иконкой и стрелкой. Активная по умолчанию — `default_active` (поле `number` в field group, default 2; если больше числа услуг — первая + WARN). Превью-картинки неактивных услуг — `loading="lazy"`, активной — `eager`.
  - JS `assets/src/js/components/serviceList.js` (без jQuery): desktop `(min-width: 1024px)` — `pointerenter` (только `(hover: hover)`), `click`, `focus` → `setActive(i)`; mobile — клик toggles аккордеон (одна открыта), высота через `grid-template-rows: 0fr → 1fr`; пересчёт при смене `matchMedia`. `logDebug('[service-list] active', { index, mode })`.
  - SCSS mobile first, вложенность ≤ 3, состояния `is-active` / `is-open`. «+» → «−» через CSS.
  - WARN в шаблоне: пустые `items` → `[section-service-list] items are empty`; у услуги нет `image` → `[section-service-list] item N has no image`.
  - Files: `configure/acf/section-blocks/section-service-list/*`, `configure/acf/acf-json/group_starter_section_service_list.json`, `template-parts/blocks/section-service-list.php`, `assets/src/{scss,js}/template-parts/blocks/section-service-list.*`, `assets/src/js/components/serviceList.js`, `configure/section-blocks.php`.

### Phase 3: Компетенции, этапы, FAQ

- [x] **Task 5: Блок `capabilities` с выбором задач → форма**
  - Блок `capabilities` (6 файлов + `group_starter_section_capabilities.json`: `title`, repeater `items` {`icon`, `text`}, `tasks_title`, repeater `tasks` {`label`}, `button_label`).
  - Панель: градиент из макета, точка-маркер, теги `<button type="button" class="task-chip" aria-pressed="false">` (компонент `components/_task-chip.scss` или модификатор существующего `_tag.scss` — выбрать по факту стилей `tag`), кнопка `<a class="button button--primary" href="#contact-form" data-contact-open data-task-picker-submit>`.
  - JS `assets/src/js/components/taskPicker.js`: toggle `aria-pressed` / `is-selected`; на клик submit (обработчик в capture-фазе, до `contactDrawer`) находит в drawer `textarea[name="your-message"]`, пишет/обновляет первую строку `Завдання: a; b` (если ничего не выбрано — строку убирает), диспатчит `input`. Если textarea нет — `logDebug('[task-picker] message field not found')`, drawer всё равно открывается.
  - Проверить с CF7 + Turnstile: подставленный текст уходит в письмо (Mailpit :8025).
  - Files: блок, `assets/src/js/components/taskPicker.js`, SCSS блока/компонента.

- [x] **Task 6: Блок `stages`**
  - Блок `stages` (6 файлов + `group_starter_section_stages.json`: `title`, repeater `items` {`icon`, `title`, `text`}). Сверху полоса `.grid-lines` 112px (desktop) / ≈31px (mobile). 4 колонки с вертикальными разделителями от `notebook`, 2 — от `mobile` (проверить 1024), 1 — mobile. Иконка handshake шире (54×32) — `width: auto; height: 32px`.
  - Появление карточек — существующий `data-aos` / `cardReveal` (не новый JS).
  - WARN на пустые `items`.
  - Files: блок + SCSS.

- [x] **Task 7: Блок `faq`**
  - Блок `faq` (6 файлов + `group_starter_section_faq.json`: `title`, repeater `items` {`question`, `answer` wysiwyg}, `open_first` true/false, default true).
  - Разметка аккордеона — та же, что в `partials/parts/cpt-faq.php` (`.faq` / `.faq__item` / `data-faq`), чтобы работал `components/faqAccordion.js`; вынести общий цикл в `partials/parts/faq-list.php` и использовать в обоих местах (без изменения вывода `cpt-faq`). Обёртка `.faq-block` 2 колонки 713 / 634, без «Не знайшли відповідь?». id ответов — с префиксом блока, чтобы не конфликтовать с `cpt-faq`.
  - `open_first` → первый пункт `aria-expanded="true"` + класс открытия, который понимает `faqAccordion.js` (проверить его API; если нет — добавить поддержку `data-faq-open` без изменения поведения других страниц).
  - Полоса 40px перед футером — как на about (`--section-gap` локально).
  - Files: блок, `partials/parts/faq-list.php`, `partials/parts/cpt-faq.php`, при необходимости `components/faqAccordion.js`, `_faq.scss`.

### Phase 4: Контент и документация

- [x] **Task 8: Скрипт контента `scripts/update-services-page.php`**
  - По образцу `update-about-page.php`: dry run / `apply` / `force`; sideload ассетов из `scripts/data/services/` с `_starter_source = services/<file>` (повторный запуск переиспользует). Создаёт (или обновляет) page slug `services`, заголовок «Послуги», `post_content` из блоков: `services-hero` → `service-list` → `capabilities` → `stages` → `faq` — тексты из Figma (`get_design_context`); описания услуг — копия `items_N_text` блока `acf/section-services` с главной (`get_option( 'page_on_front' )` → `parse_blocks()`), фолбэк — общий текст из `scripts/data/services/content.php` + `WP_CLI::warning( '[services-page] home description N is empty, using fallback' )`; ответы FAQ — один общий текст (ответ первого вопроса из макета) для всех 4; иконки услуг — существующие attachments по `_starter_source` главной.
  - Перед созданием: если существует пост `services` CPT — `WP_CLI::error` «run remove-services-cpt.php first». Конфликт slug (`services-2`) → warning.
  - Yoast: title/description страницы — из H1 и интро (если Yoast активен, `update_post_meta` `_yoast_wpseo_metadesc`).
  - Логи: `WP_CLI::log` по каждому блоку/ассету, `WP_CLI::warning` на сбои sideload, итоговая статистика uploaded / reused / warnings.
  - Прогнать: `remove-services-cpt.php apply` → `update-services-page.php apply` → `wp rewrite flush`; проверить `/services/`, `/services/seo/` (301), 404-страницу, главную (секция services не сломалась).
  - Files: `scripts/update-services-page.php`, `scripts/data/services/content.php`.

- [x] **Task 9: Визуальная сверка и документация**
  - Claude in Chrome: 1920 / 1440 / 1024 / 375 против `3534:2180` / `3589:7251`; дашборд-анимация, hover/клик списка услуг, mobile-аккордеон, выбор задач → текст в форме, FAQ. Lighthouse не нужен; проверить отсутствие горизонтального скролла на 375 (масштаб дашборда).
  - `npm run lint`, `composer lint` (PHPStan baseline не расширять).
  - Docs (`/aif-docs`): `docs/page-sections.md` — 5 новых блоков, страница «Послуги», скрипты; `docs/single-pages.md` — убрать `single-services.php`; `AGENTS.md` — структура (services CPT удалён, новые блоки/скрипты/компоненты); `.ai-factory/ARCHITECTURE.md` — `post-types/{directions,cases}`, `single-{directions}`.
  - Files: docs, AGENTS.md, ARCHITECTURE.md.
