# Implementation Plan: Новая страница кейсов /cases/ (вертикальный слайдер, Figma)

Branch: feature/cases-archive-redesign (создана от feature/home-redesign: новый хедер, футер и контактная панель есть только там)
Created: 2026-11-11

## Settings
- Testing: no (проверка: `npm run lint`, `composer lint` и визуальная сверка с Figma в Chrome на 1920×979, 1440×900, 1280×720, 1024×768, 375×812)
- Logging: verbose в рамках правил проекта. JS пишет только через `utils/logDebug.js` (без новых `console.log`), PHP только `error_log( 'WARN [cases-archive] …' )` на сбоях, WP-CLI через `WP_CLI::log()` / `WP_CLI::warning()`
- Docs: yes, обязательный docs checkpoint через `/aif-docs` в конце

## Источники в Figma (fileKey `huDLClWg3dIpXyzLMuVHEs`)
| Что | node |
|---|---|
| Desktop 1920×979 «Case archive — Promedi» | `3025:141` |
| ↳ utility bar (`/*CASE ARCHIVE*/`, «Режим перегляду: 01/04», 4 индикатора 10×10 с шагом 20) | `3025:143` |
| ↳ case stage 1350×648: left 752 / arrows 41 / track 557 | `3025:153` |
| ↳ текстовый блок (CLIENT, заголовок, FINAL PRODUCT, описание, «Читати кейс») | `3094:272` |
| ↳ стрелки prev/next 41×36 | `3094:266` |
| ↳ лента каверов: каверы 522×382, зазор 16 | `3025:169` |
| ↳ нижняя полоса 40px («Next case navigation», пустая) | `3025:175` |
| Mobile 375 | `3166:3416` |

Перед вёрсткой снять `get_design_context` по `3025:141` и `3166:3416` для точной типографики (заголовок: Grunt Grotesk, лейблы капсом с трекингом) и отступов. Сверять с уже существующими токенами из `_tokens.scss` (`--font-heading`, `--c-blue`, `--c-secondary` и т. д.), новые цвета не вводить без необходимости.

## Требования дизайнера (свод)
1. Справа вертикальная лента: PREV (фрагмент сверху), ACTIVE (целиком, по центру), NEXT (фрагмент снизу).
2. Высота слайдера = доступная высота viewport минус хедер, utility bar и нижняя полоса.
3. ACTIVE всегда `aspect-ratio: 522 / 382`, изображение `object-fit: cover`, без искажений.
4. Фрагменты PREV и NEXT одинаковой высоты, считаются от свободного места, максимум **120px**.
5. Зазор между каверами всегда **16px**.
6. ACTIVE центрирован по вертикали, PREV/NEXT симметричны.
7. На низких экранах сначала уменьшаются фрагменты; ACTIVE уменьшается (с сохранением пропорций) только когда фрагменты уже на минимуме.
8. Неактивные каверы не масштабируются отдельно: все каверы одного размера, фрагменты получаются обрезкой `overflow: hidden` контейнера.
9. Переключение — плавный вертикальный сдвиг ленты (`transform`), без скачков ширины, пропорций, отступов.
10. На десктопе стрелки можно не выводить, если работает колесо.
11. Когда все кейсы пролистаны, становится доступен футер.
12. (Опционально, берём) Хедер через несколько секунд прячется, остаются utility bar и hero; возвращается при наведении на верх страницы или сильном скролле вверх.

## Логика скролла (как на grndwrk.studio, зафиксировано здесь, т. к. сайт часто недоступен)
- Экран кейсов закреплён: обычная прокрутка страницы отключена (`window.lenis.stop()` + `preventDefault` на `wheel`/`touchmove`/клавишах), пока пользователь листает кейсы.
- Один «жест» = один шаг. Колесо: дельты копятся, шаг срабатывает при пороге (~50px), дальше блокировка на время анимации (~700–800ms) + антидребезг для инерции тачпада (новый шаг только после паузы в потоке wheel-событий ~150ms или по окончании анимации, что позже).
- Вниз: следующий кейс. Вверх: предыдущий.
- На последнем кейсе следующий жест вниз **снимает блокировку** (`lenis.start()`), и страница прокручивается дальше, к футеру.
- Обратно: когда страница вернулась в самый верх (`scroll <= 0`) и пользователь крутит вверх, блокировка снова включается на последнем кейсе, дальнейшие жесты вверх листают кейсы назад.
- На первом кейсе жест вверх ничего не листает (а при авто-скрытии хедера показывает хедер, см. Task 7).
- Клавиатура: ↑/↓, PageUp/PageDown, Space/Shift+Space работают как шаги, когда фокус не в поле ввода. Тач на десктопной раскладке (планшет в landscape): вертикальный свайп > 40px = шаг.
- Блокировку не включаем: на мобильной раскладке (< `tablet`), если кейс один, если доступная высота меньше минимума (≈ 520px, тогда обычный поток страницы), без JS. `prefers-reduced-motion`: шаги работают, но без анимации сдвига.

## Решения и допущения
- **Источник кейсов:** все опубликованные `cases`, сортировка `menu_order ASC, date DESC`, без пагинации (`posts_per_page = -1`). Счётчик `01/NN` и точки строятся по фактическому числу.
- **Данные карточки архива:** новые поля ACF на кейсе (вкладка «Архів»): `case_archive.title`, `case_archive.description`, `case_archive.cover`. Фолбэки: заголовок поста, `case_hero.subtitle`, featured image, нейтральный плейсхолдер (градиент `--c-banner`). Лейблы «Client» и «Final product» статичные строки (`__()`), как в макете.
- **Promedi (ID 566)** сейчас в черновике: скрипт его публикует и ставит первым; тексты берутся из макета. Для остальных опубликованных кейсов архивные поля заполняются плейсхолдером «Заголовок і опис буде пізніше» (тексты дизайнер пришлёт позже).
- **Мобайл:** без блокировки скролла. Горизонтальная лента каверов со `scroll-snap` (следующий кавер выглядывает справа, как в `3166:3416`), свайп меняет активный кейс, текст, счётчик и точки. Стрелок нет. Футер доступен обычной прокруткой.
- **Стрелки:** выводятся в разметке (доступность, клавиатура), видимы на планшете и тач-устройствах; на десктопе с `(hover: hover) and (pointer: fine)` скрыты визуально, по просьбе дизайнера.
- **Шаблон:** `archive-cases.php` больше не использует `partials/parts/archive-cpt.php`; services и directions остаются на старом общем шаблоне. Для архива кейсов `archive.scss` не подключается.
- **Расчёт размеров на CSS:** stage объявлен `container-type: size`, высоты считаются в `cqh`/`cqw`, JS только выставляет `--cases-index`. Так ресайз и показ/скрытие хедера пересчитываются без JS.

- **Количество кейсов (решено 2026-11-11):** выводим все 5 опубликованных (4 существующих + Promedi), дальше их будет больше. Счётчик и точки должны выглядеть нормально при любом N (точки в одну строку, при N > ~12 проверить переполнение на мобайле).
- **Нижняя полоса 40px (`3025:175`) (решено 2026-11-11):** это просто отступ между stage и футером, без контента и без отдельного элемента: `padding-bottom: 40px` у `.cases-archive` на десктопе (учитывается в расчёте высоты stage).

## Open questions
1. В корне темы есть untracked-дубликат `acf-json/group_6ba8f99a.json`. Проверить в `configure/acf.php`, какая папка является load point, и править только её.

## Commit Plan
- **Commit 1** (после задач 1–3): `feat(cases): archive fields, query order and content script`
- **Commit 2** (после задач 4–5): `feat(cases): new cases archive markup and styles`
- **Commit 3** (после задач 6–7): `feat(cases): vertical case slider with scroll lock and header auto-hide`
- **Commit 4** (после задач 8–9): `docs(cases): cases archive page`

## Tasks

### Phase 1: Данные

- [x] **Task 1: Поля архива кейса + хелпер**
  - В field group кейсов (`configure/acf/acf-json/group_6ba8f99a.json`, сначала проверить load point в `configure/acf.php`, см. Open question 1) добавить вкладку «Архів» и группу `case_archive`: `title` (textarea, 3 строки, «Заголовок для сторінки кейсів»), `description` (textarea), `cover` (image, return format `id`, подсказка «522×382, буде обрізано по 522/382»). Ключи по конвенции группы, `modified` обновить.
  - Новый хелпер `configure/helpers/get-case-archive-item.php`: `starter_get_case_archive_item( $post )` возвращает `array( 'id', 'url', 'title', 'description', 'cover_id' )` с фолбэками (title → `get_the_title`, description → `case_hero.subtitle`, cover → `get_post_thumbnail_id`, иначе `0`). Добавить `'get-case-archive-item.php'` в массив `configure/utilities.php`.
  - Логирование: `error_log( 'WARN [cases-archive] no cover for case <ID>' )` только когда нет ни `cover`, ни миниатюры. Других логов нет.
  - Files: `configure/acf/acf-json/group_6ba8f99a.json`, `configure/helpers/get-case-archive-item.php`, `configure/utilities.php`.

- [x] **Task 2: Запрос архива кейсов**
  - Новый файл `configure/theme-hooks/cases-archive-query.php`: `pre_get_posts`, условие внутри колбэка (`! is_admin() && $query->is_main_query() && $query->is_post_type_archive( 'cases' )`): `posts_per_page = -1`, `orderby = array( 'menu_order' => 'ASC', 'date' => 'DESC' )`, `no_found_rows = true`. Хук регистрируется на уровне файла. Добавить файл в массив `configure/theme-hooks.php`.
  - В админке кейсов `supports` дополнить `page-attributes`, чтобы порядок (`menu_order`) редактировался вручную (`configure/post-types/cases.php`).
  - Логирование: не требуется.
  - Files: `configure/theme-hooks/cases-archive-query.php`, `configure/theme-hooks.php`, `configure/post-types/cases.php`.

- [x] **Task 3: Скрипт контента** (depends on 1)
  - `scripts/update-cases-archive.php` (WP-CLI, `wp eval-file /scripts/update-cases-archive.php`), по образцу `scripts/update-home-redesign.php`: опубликовать Promedi (566), `menu_order = 1`; заполнить его `case_archive` текстом из макета (заголовок «Як спроєктувати медичний сайт без галузевих шаблонів. Досвід Promedi», описание из `3025:164`); обложку взять из featured image или загрузить кавер из Figma (`3025:173`, скачать ассет через `get_design_context`, сохранить в `scripts/data/cases-archive/`). Остальным опубликованным кейсам проставить `menu_order` 2…N и плейсхолдер-тексты, только если поля пустые (скрипт идемпотентный).
  - Запуск: `make wp cmd="eval-file /scripts/update-cases-archive.php"` (в Git Bash `MSYS_NO_PATHCONV=1`).
  - Логирование: `WP_CLI::log()` на каждый обновлённый кейс (ID, что изменено), `WP_CLI::warning()` если пост не найден или загрузка обложки не удалась, в конце итог `updated / skipped`.
  - Files: `scripts/update-cases-archive.php`, `scripts/data/cases-archive/*`.
<!-- Commit checkpoint: tasks 1-3 -->

### Phase 2: Разметка и стили

- [ ] **Task 4: Шаблон и подключение ассетов** (depends on 1, 2)
  - `archive-cases.php` → `get_template_part( 'partials/parts/cases-archive' )`.
  - Новый `partials/parts/cases-archive.php`, блок `.cases-archive` (BEM, без префиксов):
    - `__bar`: `/*CASE ARCHIVE*/`, `__counter` («Режим перегляду: <span data-cases-current>01</span>/NN», на мобайле только `01/NN`), `__dots` (кнопки-индикаторы с `aria-label` «Кейс N», `aria-current` у активного).
    - `__stage` (`data-cases-stage`): слева `__panels`, где каждый кейс отдельная `__panel` (`h1` у первого, `h2` у остальных — или единый `h1` «Кейси» визуально скрыт + `h2` у всех; выбрать вариант с одним `h1`), лейблы Client / Final product, описание, кнопка `button button--secondary` «Читати кейс» со ссылкой на кейс. Неактивные панели `hidden`/`inert`, активная `is-active`.
    - `__arrows`: prev/next `button` с `aria-controls`, SVG-шевроны из макета.
    - `__viewport` (overflow hidden) → `__track` → `__cover` на каждый кейс (`<a>` на кейс с `wp_get_attachment_image( $cover_id, 'large', false, array( 'class' => 'cases-archive__image', 'loading' => первый ? 'eager' : 'lazy', 'sizes' => '(min-width: 1024px) 522px, 80vw' ) )`, плейсхолдер, если `cover_id = 0`).
    - Отдельной нижней полосы нет: 40px снизу — это отступ (`padding-bottom`) блока на десктопе.
    - `aria-live="polite"` на счётчике; весь блок `role="region"` с `aria-roledescription="carousel"`.
    - Пустое состояние: «Кейсів поки немає.» как в старом архиве.
  - `configure/theme-hooks/enqueue-listing-templates-assets.php`: `starter_enqueue_archive_assets()` пропускает `is_post_type_archive( 'cases' )`; новая функция `starter_enqueue_cases_archive_assets()` → `starter_enqueue_template_assets( 'starter-cases-archive', 'cases-archive.scss', 'cases-archive.js' )`, приоритет 110.
  - Фильтр `body_class`: `is-cases-archive` (в том же файле хуков или отдельный `theme-hooks/body-class-cases-archive.php` + регистрация в агрегаторе).
  - Логирование: не требуется (WARN только из хелпера).
  - Files: `archive-cases.php`, `partials/parts/cases-archive.php`, `configure/theme-hooks/enqueue-listing-templates-assets.php`, (опц.) `configure/theme-hooks.php`.

- [ ] **Task 5: Стили страницы** (depends on 4)
  - Entry `assets/src/scss/cases-archive.scss` → `@use "components/cases-archive"`; партиал `components/_cases-archive.scss`. Mobile first, только `@include breakpoint(...)`, вложенность ≤ 3, Stylelint чистый.
  - **Мобайл (база):** обычный поток. Bar 40px с границами сетки; текст; горизонтальная лента `__viewport` со `scroll-snap-type: x mandatory`, кавер ~ `calc(100% - 64px)` с `aspect-ratio: 522/382`, gap 16px, следующий выглядывает справа; затем описание и кнопка (порядок как в `3166:3416`, перестановка через `order`/grid-areas).
  - **Десктоп (`laptop`+):** `.cases-archive` занимает `height: calc(100svh - var(--header-offset))`, где `--header-offset` = высота хедера (0, когда хедер скрыт, см. Task 7; переход `transition` на `height`/`padding`), плюс `padding-bottom: 40px` (отступ до футера из макета). Сетка: контейнер 1350 с линиями как в хедере; колонки `minmax(0, 752px) 41px 557px` (или пропорционально на 1280–1440). `__stage` с `container-type: size`.
  - Математика (в `__stage`, `--gap: 16px`, `--frag-max: 120px`, `--frag-min: 24px`, `--ratio: 382 / 522`):
    - `--cover-w: min(522px, 100cqw /* колонки трека */, (100cqh - 2 * var(--gap) - 2 * var(--frag-min)) * 522 / 382)`.
    - `--cover-h: calc(var(--cover-w) * 382 / 522)`.
    - `--frag: clamp(var(--frag-min), (100cqh - var(--cover-h) - 2 * var(--gap)) / 2, var(--frag-max))`.
    - `__viewport`: `height: calc(var(--cover-h) + 2 * (var(--frag) + var(--gap)))`, `overflow: hidden`, по центру stage (`align-self: center`), значит PREV/NEXT симметричны.
    - `__track`: колонка каверов высотой `--cover-h` с `gap: var(--gap)`, `transform: translateY(calc(var(--frag) + var(--gap) - var(--cases-index, 0) * (var(--cover-h) + var(--gap))))`, `transition: transform .75s cubic-bezier(.65, 0, .35, 1)`; в `prefers-reduced-motion` без transition.
    - Ширина трека задаётся контейнером трека (`cqw` считать от колонки: вынести трек во вложенный контейнер `container-type: inline-size` или задать ширину колонки через переменную). Проверить на 1920×979 (кавер 522×382, фрагменты ~117–120), 1440×900, 1280×720 (фрагменты меньше), 1366×600 (сжимается ACTIVE).
  - Панели текста: смена через opacity + небольшой translateY (~16px) за 400ms, без скачков высоты (панели в одной grid-ячейке `grid-area: 1 / 1`).
  - Стрелки: видимы на `tablet`; в `@media (hover: hover) and (pointer: fine)` на `laptop`+ `visibility: hidden`, но фокусируемые через `:focus-visible` (показывать при фокусе).
  - Без JS (`html:not(.js)` или атрибут, который ставит JS): каверы обычным столбцом, все панели видимы, без высоты 100svh.
  - Логирование: не требуется.
  - Files: `assets/src/scss/cases-archive.scss`, `assets/src/scss/components/_cases-archive.scss`.
<!-- Commit checkpoint: tasks 4-5 -->

### Phase 3: Поведение

- [ ] **Task 6: Слайдер и блокировка скролла** (depends on 4, 5)
  - Entry `assets/src/js/cases-archive.js` → `initCasesArchive()` из `components/casesArchive.js` (не путать с существующим `casesSlider.js` главной).
  - Состояние: `index`, `count`, `mode` (`'desktop' | 'mobile' | 'static'`, через `matchMedia` на брейкпоинт `laptop` из `mixins/_breakpoint.scss` + проверка высоты stage ≥ минимума), `locked`, `animating`.
  - `goTo(i, source)`: clamp, `stage.style.setProperty('--cases-index', i)`, переключает `is-active`/`hidden`/`inert` у панелей и `aria-current` у точек, обновляет счётчик (`01`), `disabled` у стрелок на краях; ждёт `transitionend` трека (с таймаутом-страховкой) и снимает `animating`.
  - Десктоп: логика из раздела «Логика скролла» выше: lock/release/relock через `window.lenis.stop()/start()` (если Lenis нет, через `overflow: hidden` на `html`); слушатели `wheel` (`passive: false`), `keydown`, `touchstart/touchmove/touchend`. На старте блокировка включена, только если верх экрана кейсов в верху страницы (`scrollY <= 0`), иначе ждём возврата наверх.
  - Мобайл: без блокировки; `scroll`-синхронизация горизонтальной ленты (rAF, ближайший кавер к левому краю → `goTo` без прокрутки ленты); тап по точке → `scrollTo({ left, behavior })` ленты.
  - Клики по точкам и стрелкам → `goTo` (на десктопе и при блокировке, и без).
  - `resize`/`matchMedia change`: пересчитать режим, при уходе с десктопа снять блокировку (`lenis.start()`, снять обработчики), при возврате снова навесить. Вся логика с `destroy()` для чистого переключения.
  - Логирование (`logDebug`, префикс `[casesArchive]`): init (count, mode, есть ли Lenis), смена режима, каждый `goTo` (from, to, source: wheel/key/touch/arrow/dot/swipe), lock / release / relock с `scrollY`, игнорирование жеста (`animating`, порог не набран — только в debug-режиме, без спама на каждое wheel-событие), фолбэк, если `transitionend` не пришёл.
  - Files: `assets/src/js/cases-archive.js`, `assets/src/js/components/casesArchive.js`.

- [ ] **Task 7: Авто-скрытие хедера на /cases/** (depends on 6)
  - `components/smartHeader.js`: опциональный режим auto-hide, включается, если `body.is-cases-archive` и `matchMedia('(min-width: laptop) and (hover: hover) and (pointer: fine)')`. Поведение: через 2500ms после загрузки хедер прячется (`is-hidden`); показывается, когда курсор в верхних ~80px окна (`mousemove`/`mouseleave` у верхнего края), при фокусе внутри хедера, при открытом меню или контактной панели, и по «сильному скроллу вверх». Для этого `casesArchive.js` диспатчит `CustomEvent('cases:reveal-header')`, когда на первом кейсе или в верху страницы накоплен wheel вверх > ~300px за 500ms; smartHeader его слушает. Повторное скрытие через 1500ms после ухода курсора из зоны / снятия фокуса. Обычный режим smart header (скролл вниз/вверх) на остальных страницах не меняется.
  - smartHeader дополнительно ставит на `document.documentElement` класс `is-header-hidden` (или пишет `--header-offset`), чтобы `.cases-archive` расширял stage на высоту хедера: utility bar поднимается на место хедера. Учесть, что `.site` резервирует высоту хедера в `_base.scss`: для `is-cases-archive` отступ берётся из `--header-offset` с transition.
  - Логирование (`[smartHeader]`): включение auto-hide, причина показа (`pointer-top`, `focus`, `reveal-event`, `menu`) и скрытия (`idle-timeout`, `pointer-leave`).
  - Files: `assets/src/js/components/smartHeader.js`, `assets/src/js/components/casesArchive.js`, `assets/src/scss/components/_header.scss` и/или `_base.scss`, `assets/src/scss/components/_cases-archive.scss`.
<!-- Commit checkpoint: tasks 6-7 -->

### Phase 4: Проверка и документация

- [ ] **Task 8: Визуальная сверка и линтеры** (depends on 3, 7)
  - `npm run build`, затем Chrome (Claude in Chrome): `localhost:8080/cases/` на 1920×979 (сравнить со скриншотом `3025:141`: ACTIVE 522×382, фрагменты по 120px или меньше, зазоры 16, симметрия), 1440×900, 1280×720, 1366×600, 1024×768 (стрелки видны), 375×812 (сравнить с `3166:3416`, свайп ленты).
  - Сценарии: колесо мышью и тачпадом (нет двойных шагов от инерции), последний кейс → футер → обратно вверх → relock; клавиатура; ресайз через брейкпоинт; `prefers-reduced-motion`; авто-скрытие хедера и появление при наведении/скролле вверх; открытие контактной панели из хедера, пока страница заблокирована; ссылки «Читати кейс» и клики по каверам.
  - `npm run lint`, `composer lint` без новых ошибок.
  - Логирование: проверить, что в консоли нет спама от `logDebug` при прокрутке.
  - Files: правки по результатам в файлах задач 4–7.

- [ ] **Task 9: Документация** (depends on 8, обязательный docs checkpoint через `/aif-docs`)
  - `docs/cases-archive.md` (или раздел в `docs/single-pages.md`): данные (`case_archive`, фолбэки, порядок `menu_order`), скрипт контента, математика высот, логика скролла (копия раздела grndwrk из этого плана), авто-скрытие хедера, как добавить кейс.
  - Обновить `AGENTS.md` (структура: `partials/parts/cases-archive.php`, `cases-archive.scss/js`, `components/casesArchive.js`, скрипт), `.ai-factory/ARCHITECTURE.md` (Asset Pipeline: per-template `cases-archive`; smartHeader auto-hide).
  - Логирование: не требуется.
  - Files: `docs/cases-archive.md`, `AGENTS.md`, `.ai-factory/ARCHITECTURE.md`.
<!-- Commit checkpoint: tasks 8-9 -->
