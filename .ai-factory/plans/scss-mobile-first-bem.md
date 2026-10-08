# План: SCSS — mobile first, вложенность ≤ 3, БЭМ-нейминг

- **Ветка:** `feature/hybrid-section-blocks` (текущая; новая ветка не создаётся — рефакторинг опирается на незакоммиченную работу в ней)
- **Создан:** 2026-10-08
- **Тема:** `theme/vite-wordpress-starter-theme` (далее `$T`), стили — `$T/assets/src/scss` (далее `$S`)

## Settings

- **Testing:** no — автотестов в теме нет. Проверка: `npm run lint` (Biome + новый Stylelint), `npm run build`, `composer lint`, grep-проверка остатков старых классов, визуальная сверка «до/после» на 375 / 768 / 1280 px.
- **Logging:** minimal — правка чисто стилевая/разметочная. Новых `error_log`/`console.log` не добавлять; в JS — только существующий `utils/logDebug.js` (если при переименовании селектора в JS элемент не найден — существующие ранние `return` оставить как есть). Прогресс по задачам фиксировать в отчёте реализации, а не в коде.
- **Docs:** yes — обязательный docs-checkpoint в конце (через `/aif-docs`): конвенции SCSS в `.ai-factory/rules/base.md`, `ARCHITECTURE.md`, `docs/getting-started.md`, `AGENTS.md`.

## Roadmap Linkage

`.ai-factory/ROADMAP.md` отсутствует — привязки нет.

## Research Context

`.ai-factory/RESEARCH.md` описывает другую тему (порт дизайна CPT-шаблонов) — к этому плану не относится. Единственное пересечение: `partials/parts/content-with-toc.php` + `_toc.scss` по-прежнему используются `single.php`, поэтому в этом плане они **переименовываются**, а не удаляются.

## Цель

1. **Mobile first.** Базовые стили — для мобильных; расширение вверх только через миксин `breakpoint()` из `$S/mixins/_breakpoint.scss` (`mobile: 766px`, `tablet: 1024px`, `min-width`). В итоговом коде нет ни одного `@media (max-width: …)` и ни одного «сырого» `@media (min-width: …)` (исключения: `prefers-reduced-motion`, `hover`, `orientation` — не ширинные запросы).
2. **Вложенность ≤ 3 уровней селекторов.** `.block { &__el { &--mod {} } }` — это 3. `@include breakpoint(...)` и прочие at-rules уровнем **не считаются** (решение пользователя).
3. **БЭМ без префиксов.** Убрать `s-`, `l-`, `c-`, `svc-`, `br-` и сокращения; элементы — через `__`, модификаторы — через `--`; составные имена через дефис в нижнем регистре.

## Текущее состояние (зафиксировано при исследовании)

- 38 SCSS-файлов, ~4800 строк. Миксин `breakpoint` **нигде не подключён**; 28 файлов используют `@media (max-width: 768px | 1024px)` (desktop first), в т.ч. `_tokens.scss` (переопределения CSS-переменных), `_base.scss`.
- Глубина вложенности фигурных скобок: `_header.scss` — 7, `_animations.scss` — 6, `section-cases.scss`/`_base.scss` — 5, все `section-*.scss` и `_footer.scss` — 4.
- Префиксы: `.s-*` (секции: `s-why`, `s-cases`, `s-cpt-hero`, `s-svc`, `s-two-col`…), `.l-wrap`/`.l-frame-x` (лейаут), `.c-tag` (дубликат `.tag`), `.svc-*` (элементы страницы услуги), `.br-desktop`. Плюс нарушения БЭМ без префиксов: `.card-title`/`.card-text` (внутри `info-card`), `.form-field-label`, `.cases-slider-wrap`/`.cases-slider-bg`, `.sections_wrapper` (snake_case), `.why-msg`, `.hero-blurb`.
- Где живут классы: PHP — `footer.php`, `home.php`, `single*.php`, `partials/parts/{archive-cpt,content-with-toc,cpt-cta,cpt-faq}.php`, `template-parts/blocks/section-*.php`; JS — `servicesAccordion.js` (`.s-services`, `.s-process`), `whySection.js` (`.s-why`), плюс селекторы без префиксов в `casesSlider.js`, `mobileNav.js`, `activeNav.js`, `contactForm.js`, `heroTitle.js`, `glitchImage.js`, `siteAnimations.js`, `toc.js`, `editor-section-blocks.js`.
- **БД чистая:** старые классы встречаются только в 2 ревизиях и в статусе ai1wm — контент/мета/опции не трогаем, миграция данных не нужна.
- `_base.scss` содержит селекторы чужих блоков в `:not(.hero, .s-quote, .s-banner, .s-process--top)` и баг `&:not(s-quote)` (нет точки).
- Stylelint не установлен; `npm run lint` = `biome check .` (SCSS не проверяет).

## Конвенции (обязательны для всех задач)

### Mobile first

| Было (desktop first) | Стало (mobile first) |
|---|---|
| desktop-значение в базе + `@media (max-width: 768px) { mobile }` | mobile-значение в базе + `@include breakpoint(mobile) { desktop/tablet }` |
| `@media (max-width: 1024px) { tablet }` | значение для ≤1023 в базе/`breakpoint(mobile)` + `@include breakpoint(tablet) { desktop }` |
| три состояния (desktop / ≤1024 / ≤768) | база = mobile → `breakpoint(mobile)` = tablet → `breakpoint(tablet)` = desktop |

- Подключение в **каждом** файле, где нужен миксин (модульная система Sass, глобально он не виден): `@use "@scss/mixins/breakpoint" as *;` — если алиас не резолвится в `@use`, использовать относительный путь (`../mixins/breakpoint`, `../../mixins/breakpoint`).
- Медиазапрос пишется **внутри** селектора, к которому относится (`&__el { …; @include breakpoint(tablet) { … } }`), а не отдельными блоками в конце файла.
- **Сдвиг границ на 1–2 px принят:** раньше mobile = `≤768`, теперь desktop-стили с `≥766`; раньше tablet = `≤1024`, теперь desktop с `≥1024` (ширина ровно 1024 теперь получает desktop). Миксин не меняем; при визуальной сверке это не считается регрессией.

### Вложенность

- Максимум 3 уровня селекторов: `.block` → `&__el` → `&--mod` / `&:hover` / `&::before`.
- Псевдоклассы/псевдоэлементы считаются уровнем. 4-й уровень разворачивать: `&__el--mod:hover` на уровне 2, либо вынести элемент на верхний уровень (`.block__el { … }`).
- Каскад «блок в блоке» (`.s-why .l-wrap { … }`) заменить **миксом** (`class="container why__container"`) или модификатором (`.container--flush`).

### БЭМ-нейминг

- Блок — полное слово(а) через дефис, без префиксов и сокращений; элемент `block__element`; модификатор `block--mod` / `block__element--mod`.
- Состояния, переключаемые из JS, остаются в формате `is-*` (`is-open`, `is-active`, `is-visible`, `is-focused`, `is-filled`, `is-error`, `is-sub-open`) — это допустимая практика БЭМ, JS не трогаем.
- Чужие классы **не переименовываем**: `wpcf7-*`, `wp-*`, `has-children` (WP-меню), `editor-styles-wrapper`, `fancybox*`, `aos-*`, `[data-aos=…]`, `js-css`.
- Каждый блок — в своём файле (или в файле своей секции); один селектор не определяется в двух файлах (сейчас `.s-banner`, `.clinics-grid`, `.hero` размазаны по нескольким).

### Таблица переименований (базовая; при реализации дополнить, не отклоняясь от принципов)

| Было | Стало | Файлы |
|---|---|---|
| `.l-wrap` | `.container` | `_grid.scss`, ~все шаблоны |
| `.l-frame-x` | `.frame` (рамки слева/справа) | `_grid.scss`, шаблоны |
| `.c-tag` + `.tag` | `.tag` (один класс) | `_tag.scss`, `home.php`, `section-process.php`, `section-problems-solutions.php` |
| `.btn`, `.btn--*` | `.button`, `.button--*` | `_buttons.scss`, PHP, JS |
| `.br-desktop` | `.line-break--desktop` | `section-quote.scss/.php` |
| `.transition` | проверить использование; при отсутствии — удалить | `_base.scss` |
| `.s-home-hero`/`.hero`, `.hero-blurb` | `.hero`, `.hero__*`; `.hero-blurb` → `.cpt-hero__blurb` | `section-home-hero.*`, `_animations.scss`, `_cpt-common.scss` |
| `.s-quote` | `.quote` | `section-quote.*` |
| `.s-clinics`, `.clinics-aside`, `.clinics-grid` | `.clinics`, `.clinics__aside`, `.clinics__grid` | `section-clinics.*`, `_grid.scss` |
| `.sections_wrapper`, `.s-problems`, `.s-banner`, `.s-solutions`, `.problem-card`, `.solution-card` | `.problems-solutions`, `.problems`, `.banner`, `.solutions`, `.problem-card`, `.solution-card` | `section-problems-solutions.*`, `_grid.scss` |
| `.s-process`, `.s-process--top/--bottom`, `.process-step` | `.process`, `.process--top/--bottom`, `.process-step` | `section-process.*`, `servicesAccordion.js` |
| `.s-services`, `.services-img`, `.services-list` | `.services`, `.services__image`, `.services-list` | `section-services.*`, `servicesAccordion.js` |
| `.s-cases`, `.cases-slider-wrap`, `.cases-slider-bg`, `.cases-slide`, `.cases-dots`, `.cases-dot` | `.cases`, `.cases-slider__wrap`, `.cases-slider__background`, `.cases-slider__slide`, `.cases-slider__dots`, `.cases-slider__dot` | `section-cases.*`, `casesSlider.js` |
| `.s-why`, `.why-chat`, `.why-msg`, `.why-reasons` | `.why`, `.why-chat`, `.why-message`, `.why-reasons` | `section-why.*`, `whySection.js` |
| `.card-title`, `.card-text` | `.info-card__title`, `.info-card__text` | `_info-card.scss`, PHP |
| `.form-field-label` | `.form-field__label` | `_forms.scss`, `contactForm.js`, CF7-разметка в PHP |
| `.s-cpt-hero`, `.s-cpt-cta` | `.cpt-hero`, `.cpt-cta` | `_cpt-common.scss`, `single-*.php`, `cpt-cta.php` |
| `.s-svc`, `.s-svc--formats/--stages`, `.svc-card(s)`, `.svc-list`, `.svc-notice`, `.svc-numbered`, `.svc-stage(s)`, `.svc-prose` | `.service-section` (+ `--formats/--stages`), `.service-card(s)`, `.service-list`, `.service-notice`, `.numbered-list` (`__item/__title/__text`), `.service-stage(s)`, `.prose` | `_service.scss`, `_cpt-common.scss`, `single-services.php`, `single-directions.php` |
| `.s-two-col` | `.two-col` | `_two-col.scss`, шаблоны |
| `.s-case-content`, `.s-case-facts`, `.s-case-results` | `.case-content`, `.case-facts-section`→ слить с `.case-facts`, `.case-results` | `_case.scss`, `single-cases.php` |
| `.s-archive` | `.archive` | `_archive.scss`, `archive-cpt.php`, `home.php` |
| `.s-post` | `.post` | `_single-post.scss`, `single.php` |
| `.s-content-toc` | `.content-toc` | `_toc.scss`, `content-with-toc.php`, `toc.js` |
| `.section-block-placeholder` | без изменений (уже БЭМ-совместимо) | `editor-section-blocks.scss` |

## Tasks

### Phase 0 — Инструменты и эталон

- [ ] **Task 1. Эталонные скриншоты «до».** ⏭ **Пропущено:** состояние «до» для главной потеряно, потому что секции уже переписаны в рабочем дереве. Вместо сверки «до/после» — проверка «после» в Task 15.
  - Через chrome devtools MCP (`http://localhost:8080`, контейнеры подняты `make up`, ассеты собраны `npm run build`) снять full-page скриншоты на 375 / 768 / 1280 px: главная `/`, `/blog/`, одна запись блога, архивы `/services/`, `/directions/`, `/cases/`, по одной single-странице каждого CPT, `404`. Дополнительно — открытое мобильное меню (375) и экран редактора страницы с секциями.
  - Сохранить в `.ai-factory/qa/scss-mobile-first-bem/before/{page}-{width}.png` (не коммитить — добавить путь в `.gitignore`, если он не игнорируется).
  - Логирование: список снятых URL × ширин записать в `.ai-factory/qa/scss-mobile-first-bem/README.md`.

- [x] **Task 2. Stylelint для SCSS.**
  - `$T/package.json`: devDependencies `stylelint`, `stylelint-scss`, `postcss-scss`; скрипты `"lint:css": "stylelint \"assets/src/scss/**/*.scss\""`, `"lint:js": "biome check ."`, `"lint": "npm run lint:js && npm run lint:css"`, `"format:css": "stylelint \"assets/src/scss/**/*.scss\" --fix"`.
  - `$T/.stylelintrc.json` (`customSyntax: postcss-scss`, плагин `stylelint-scss`), правила:
    - `max-nesting-depth: [2, { "ignoreAtRules": ["include", "media", "supports", "container"] }]` (Stylelint считает корневое правило уровнем 0 → 2 = три уровня селекторов);
    - `media-feature-name-disallowed-list: ["max-width", "min-width", "width"]` — ширинные запросы только через миксин (в `mixins/_breakpoint.scss` — `/* stylelint-disable-next-line */` у `@media`);
    - `selector-class-pattern`: БЭМ + запрет старых префиксов, например `^(?!(s|l|c|svc|br)-)[a-z][a-z0-9]*(-[a-z0-9]+)*(__[a-z0-9]+(-[a-z0-9]+)*)?(--[a-z0-9]+(-[a-z0-9]+)*)?$`; через `ignoreSelectors`/доп. ветки регулярки разрешить `is-*`, `has-*`, `wpcf7-*`, `wp-*`, `aos-*`, `fancybox*`, `editor-styles-wrapper`;
    - `scss/at-use-no-unnamespaced` выключить (миксин подключается `as *`).
  - `ignoreFiles`: `assets/dist/**`, `vendors/**` (fancybox — сторонние классы).
  - На этом шаге `npm run lint:css` **ожидаемо падает** — зафиксировать стартовое число ошибок в отчёте; задачи 3–14 доводят его до нуля.
  - Логирование: нет (тулинг).

### Phase 1 — Фундамент

- [x] **Task 3. Базовый слой: `_tokens.scss`, `_base.scss`, `_fonts.scss`, `_grid.scss`, `main.scss`.**
  - `_tokens.scss`: блоки `@media (max-width: 1024px/768px)` с переопределением CSS-переменных (`--pad-x`, `--header-h` и т.п.) перевернуть: мобильные значения в `:root`, планшет/десктоп — в `@include breakpoint(mobile|tablet) { :root { … } }`. Bootstrap-наследие `$grid-breakpoints`/`$container-max-widths` не трогать (если не используется — отметить в отчёте, не удалять в этом плане).
  - `_base.scss`: развернуть вложенность `section { @media { &:not(…) { &::after { @media … }}}}` (глубина 5) до ≤3; заменить ссылки на чужие блоки в `:not(.hero, .s-quote, .s-banner, .s-process--top)` на новые имена (`.hero, .quote, .banner, .process--top`) — а лучше на модификатор-маркер (например `.section--no-divider`, проставить его в шаблонах этих секций), чтобы база не знала про конкретные блоки. Исправить баг `&:not(s-quote)` (нет точки) → `&:not(.quote)` или тот же модификатор.
  - `_grid.scss`: `.l-wrap` → `.container`, `.l-frame-x` → `.frame`; убрать из файла чужие селекторы `.s-banner__inner` и `.clinics-grid` (перенести/заменить миксом `container`/`frame` в разметке соответствующих секций — задачи 7–8).
  - Глобальная замена `l-wrap`/`l-frame-x` во всех PHP (`footer.php`, `home.php`, `single-services.php`, `partials/parts/archive-cpt.php`, `template-parts/blocks/section-*.php`) и JS.
  - Логирование: нет.

- [x] **Task 4. Глобальные компоненты-примитивы.**
  - Файлы: `components/_buttons.scss` (`.btn` → `.button`), `_tag.scss` (`.c-tag` слить в `.tag`), `_section-title.scss`, `_info-card.scss` (`.card-title/.card-text` → `.info-card__title/__text`), `_icon-list.scss`, `_breadcrumbs.scss`, `_modal.scss` (пустой — оставить), `block-info-block.scss`.
  - Для каждого: mobile first через миксин, вложенность ≤3, переименование + замена в PHP (`grep -rn "btn\b\|c-tag\|card-title\|card-text"` по `$T` без `node_modules`/`dist`) и JS (`contactForm.js`, `mobileNav.js` и др., где есть `.btn`). Кнопки CF7 (`.wpcf7-submit`) и `.wp-element-button` — оставить как есть, только перевести их медиазапросы.
  - Проверить разметку CF7-формы (шаблон формы хранится в БД в `wpcf7_contact_form`): если в ней есть `btn`/`form-field-label` — зафиксировать в отчёте и обновить форму через WP-CLI (`wp post update <id> --post_content=…`) после подтверждения пользователя. На момент исследования совпадений в БД не найдено.
  - Логирование: нет.
  - Зависит от Task 3.

- [x] **Task 5. Header + мобильное меню (`components/_header.scss`, `partials/header/*.php`, `mobileNav.js`, `activeNav.js`).**
  - Самый глубокий файл (7 уровней, 17 медиазапросов). Блоки: `.logo`, `.header`, `.nav`, `.burger` — имена уже без префиксов; проверить элементы/модификаторы на БЭМ (никаких `.nav li a` — выдать элементы `nav__item`, `nav__link`; если разметку меню генерирует `wp_nav_menu`, добавить классы через фильтры `nav_menu_css_class` / `nav_menu_link_attributes` в существующем файле `configure/theme-hooks/*` или новом `configure/theme-hooks/nav-menu-classes.php` с регистрацией в `theme-hooks.php`).
  - Mobile first: мобильная панель/бургер — база, десктопная горизонтальная навигация — `breakpoint(tablet)` (сверить, где сейчас граница: 768 или 1024).
  - `$nav-mobile-bg` и grid-flash анимацию мобильного меню сохранить без визуальных изменений.
  - Логирование: нет; в JS не добавлять `console.log`.
  - Зависит от Task 4.

- [x] **Task 6. Footer + формы (`_footer.scss`, `_forms.scss`, `footer.php`, `contactForm.js`).**
  - `_footer.scss`: 8 медиазапросов, глубина 4 → mobile first, ≤3.
  - `_forms.scss`: `.form-field-label` → `.form-field__label`, `.form-success` проверить на БЭМ (`.contact-form__success`, если это элемент формы); `wpcf7-*` не переименовывать, но их правила тоже ≤3 уровня и mobile first.
  - Обновить PHP-разметку футера/формы и селекторы в `contactForm.js`.
  - Логирование: нет.
  - Зависит от Task 4.

### Phase 2 — Секции главной (`template-parts/blocks/section-*`)

Для каждой задачи фазы: SCSS-файл секции + её PHP-шаблон + её JS (если есть) + стили редактора, если они ссылаются на классы. Медиазапросы — mobile first; вложенность ≤3; имена по таблице.

- [x] **Task 7. Hero, quote, clinics + `_animations.scss`.**
  - `section-home-hero.scss` (15 media), `section-quote.scss` (5; `.br-desktop` → `.line-break--desktop`, `.gear` → `.quote__gear`), `section-clinics.scss` (13; `.clinics-aside/.clinics-grid` → `.clinics__aside/__grid`, убрать дубль `.clinics-grid` из `_grid.scss`).
  - `_animations.scss` (глубина 6): развернуть вложенность миксинов `glitch`/`rgb-shift` в генерируемом CSS до ≤3; `.hero__image--revealed` и `.glitch-image` привести к новым именам; `[data-aos=…]`, `@keyframes` — без изменений. Правило `.hero…` перенести в `section-home-hero.scss`, если оно относится только к hero.
  - JS: `heroTitle.js`, `glitchImage.js`, `siteAnimations.js` — обновить селекторы.
  - Логирование: нет.
  - Зависит от Task 3.

- [x] **Task 8. Problems/solutions + process.**
  - `section-problems-solutions.scss` (27 media — самый «медийный» файл): `.sections_wrapper` → `.problems-solutions`, `.s-problems/.s-banner/.s-solutions` → `.problems/.banner/.solutions`; `.s-banner__inner` из `_grid.scss` заменить миксом `class="banner__inner container"`.
  - `section-process.scss` (12 media): `.s-process*` → `.process*`; `.tag` вместо `.c-tag`.
  - JS: `servicesAccordion.js` (ссылается на `.s-process`).
  - Логирование: нет.
  - Зависит от Task 3, Task 4.

- [x] **Task 9. Services + cases.**
  - `section-services.scss` (9 media): `.s-services` → `.services`, `.services-img` → `.services__image`.
  - `section-cases.scss` (14 media, глубина 5): `.s-cases` → `.cases`, `.cases-slider-*`/`.cases-slide`/`.cases-dot(s)` → элементы блока `.cases-slider`.
  - JS: `servicesAccordion.js` (`.s-services`), `casesSlider.js` (в рабочем дереве уже есть незакоммиченные правки — не затирать их, переименовывать поверх).
  - Логирование: нет.
  - Зависит от Task 8 (общий `servicesAccordion.js`).

- [x] **Task 10. Why.**
  - `section-why.scss` (18 media): `.s-why` → `.why`; `.why-msg` → `.why-message`; каскад `.s-why .l-wrap { padding: 0; border: 0 }` заменить модификатором `.container--flush` или миксом `why__container`; `.why-chat__screen--entered .why-msg--visible` развернуть до ≤3 уровней.
  - JS: `whySection.js`.
  - Логирование: нет.
  - Зависит от Task 3.

- [x] **Task 11. Редактор блоков.**
  - `editor-section-blocks.scss` (в рабочем дереве незакоммиченные правки — сохранить), `assets/src/js/editor-section-blocks.js` (новый, untracked), `configure/js-css.php` (editor canvas assets), `configure/section-blocks.php` — проверить, что превью секций в Gutenberg использует новые классы и что `.editor-styles-wrapper` правила ≤3 уровня.
  - Логирование: нет.
  - Зависит от Task 7–10.

### Phase 3 — CPT, блог, архивы

- [ ] **Task 12. Services/directions single (`_cpt-common.scss`, `_service.scss`, `_two-col.scss`, `_icon-list.scss`).** ⏭ **Пропущено по решению пользователя:** для CPT-страниц будут новые стили.
  - Переименования: `.s-cpt-hero` → `.cpt-hero` (`.hero-blurb` → `.cpt-hero__blurb`), `.s-cpt-cta` → `.cpt-cta`, `.s-svc*` → `.service-section*`, `.svc-*` → `.service-*` / `.numbered-list` / `.prose`, `.s-two-col` → `.two-col`, `.faq` — проверить элементы.
  - Шаблоны: `single-services.php`, `single-directions.php`, `partials/parts/cpt-cta.php`, `partials/parts/cpt-faq.php`.
  - SCSS-энтри `single-cpt.scss` — проверить `@use`.
  - Логирование: нет.
  - Зависит от Task 4.

- [ ] **Task 13. Cases single + архивы + блог.** ⏭ **Пропущено по решению пользователя:** для кейсов, архивов и блога будут новые стили.
  - `_case.scss`: `.s-case-*` → `.case-content`, `.case-results`, факты — объединить `.s-case-facts` и `.case-facts` в один блок `.case-facts` с элементами. Шаблон `single-cases.php`.
  - `_archive.scss`: `.s-archive` → `.archive`; `.archive-card`, `.archive-grid`, `.archive-empty`, `.pagination` — проверить элементы. Шаблоны `partials/parts/archive-cpt.php`, `home.php` (фильтр категорий, `.c-tag`). JS: `blogFilter.js` удалён в рабочем дереве — не восстанавливать.
  - `_single-post.scss`, `_toc.scss`, `_content-with-toc.scss`: `.s-post` → `.post`, `.s-content-toc` → `.content-toc`; `.entry-content` (WP-класс контента) оставить, вложенные правила для контента (`.entry-content h2 a`) — ≤3 уровня. Шаблоны `single.php`, `partials/parts/content-with-toc.php`; JS `toc.js`; `configure/toc.php` — если генерирует классы в разметке, привести к БЭМ (`toc__item`, `toc__link`).
  - Энтри `archive.scss`, `single-post.scss`.
  - Логирование: нет.
  - Зависит от Task 12.

### Phase 4 — Проверка и документация

- [x] **Task 14. Глобальная зачистка и линт.**
  - `grep -rnE "\b(s|l|c|svc|br)-[a-z]" $T --include=*.php --include=*.js --include=*.scss --include=*.json` (без `node_modules`, `dist`, `vendor`) — должен вернуть 0 совпадений по классам (ложные срабатывания вроде `wp-`, `aria-`, переменных `--c-*` разобрать вручную).
  - `grep -rn "max-width" $S | grep "@media"` — 0; `grep -rn "@media" $S` — только `mixins/_breakpoint.scss` и не-ширинные запросы.
  - Скрипт глубины (см. «Текущее состояние») или `npm run lint:css` — 0 ошибок `max-nesting-depth`, `selector-class-pattern`, `media-feature-name-disallowed-list`.
  - `npm run lint`, `npm run build` (без warning `Unknown breakpoint`), `composer lint` — зелёные.
  - Логирование: результаты команд — в отчёт реализации.
  - Зависит от Task 1–13.

- [ ] **Task 15. Визуальная сверка «после».**
  - Повторить набор Task 1 → `.ai-factory/qa/scss-mobile-first-bem/after/`. Сравнить попарно; допустимые отличия — только в диапазонах 766–768 px и ровно 1024 px (сдвиг границ миксина). Любые другие расхождения исправить в соответствующем SCSS и перепроверить.
  - Отдельно проверить: открытие/закрытие мобильного меню, аккордеон услуг/процесса, слайдер кейсов, чат «Почему мы», отправку CF7 (письмо в Mailpit :8025), TOC в записи блога, превью секций в редакторе.
  - Логирование: список найденных/исправленных расхождений — в `.ai-factory/qa/scss-mobile-first-bem/README.md`.
  - Зависит от Task 14.

- [x] **Task 16. Документация (docs-checkpoint).**
  - `.ai-factory/rules/base.md` → раздел SCSS: mobile first только через `breakpoint()`, вложенность ≤3 (at-rules не считаются), БЭМ без префиксов, `is-*` для состояний, список чужих классов-исключений, `npm run lint:css`.
  - `.ai-factory/ARCHITECTURE.md` (asset pipeline / SCSS), `docs/getting-started.md` (команды `lint`, `lint:css`, `format:css`), `AGENTS.md` (Commands, Agent Rules — «SCSS: mobile first через миксин, вложенность ≤3, БЭМ»; упомянуть `.stylelintrc.json` в структуре).
  - Логирование: нет.
  - Зависит от Task 14.

## Implementation Notes (2026-10-08)

- **Tasks 2–11** выполнены в рабочем дереве до запуска `/aif-implement`. Сверка: `npm run build` — ок; `biome check` — ок (форматирование 7 JS-файлов и `.stylelintrc.json` исправлено через `biome check --write`); stylelint — 0 ошибок вне файлов CPT/архивов/блога; в разметке главной нет классов со старыми префиксами; в браузере (1280 / 375) — все 8 секций и футер с CF7 на месте, мобильное меню открывается, горизонтального скролла и ошибок в консоли нет.
- **Отступления от таблицы переименований** (оба имени проходят `selector-class-pattern`):
  - `.card-title` / `.card-text` оставлены общими классами-миксами для `problem-card`, `solution-card`, `process-step` (вместо `.info-card__title` / `__text`);
  - `.cases-slide` оставлен отдельным блоком (вместо `.cases-slider__slide`).
- **Tasks 12–13 пропущены** → в `_cpt-common`, `_service`, `_two-col`, `_case`, `_archive`, `_single-post`, `_toc` остаются 87 ошибок stylelint (74 `selector-class-pattern`, 13 `media-feature-name-disallowed-list`). Эти 7 файлов временно добавлены в `ignoreFiles` в `.stylelintrc.json` — убрать оттуда, когда их заменят новые стили.
- **Task 14:** `npm run lint` (Biome + Stylelint) — 0 ошибок; `npm run build` — без warning; `composer lint` (PHPCS + PHPStan) — 0 ошибок; grep по старым префиксам и ширинным `@media` вне CPT-файлов — пусто. PHPCS падал только на CRLF в 9 PHP-файлах CPT/блога (рабочая копия при `core.autocrlf=true`, в индексе LF) — исправлено `phpcbf --sniffs=Generic.Files.LineEndings`, содержимое не менялось.
- **Task 15** (только «после», эталона «до» нет — см. Task 1; только главная, CPT-страницы вне объёма):
  - 1280 / 768 / 375 — все 8 секций и футер на месте, горизонтального скролла нет, ошибок в консоли нет.
  - Интерактив: аккордеон услуг переключает `services-list__item--active`; слайдер кейсов (Swiper инициализирован, 4 слайда, точки `.cases__dot` переключают слайд); чат «Почему мы» (триггер → `aria-expanded`, активный экран, сообщения появляются); мобильное меню открывается; CF7 отправляется («It has been sent»), письмо пришло в Mailpit.
  - Известное (не регрессия): на 766–~860 px десктопное меню переносится в 2 строки (header 93 px вместо 63). В исходных стилях на 769–1024 были те же `gap: 24px` + `flex-wrap`, т.е. перенос был и раньше; при желании — отдельной задачей (бургер до `tablet` или меньший gap).
  - Не проверено: превью секций в редакторе Gutenberg (нужна авторизация в wp-admin).

## Commit Plan

1. После **Task 1–3**: `chore(scss): add stylelint, convert base layer to mobile-first`
2. После **Task 4–6**: `refactor(scss): BEM + mobile-first for global components, header, footer, forms`
3. После **Task 7–11**: `refactor(blocks): BEM + mobile-first for section blocks and editor styles`
4. После **Task 12–13**: `refactor(templates): BEM + mobile-first for CPT, archive and blog styles`
5. После **Task 14–16**: `docs: document SCSS conventions (mobile-first, nesting, BEM)`

Перед каждым коммитом: `npm run lint`, `npm run build`, `composer lint`. Незакоммиченные изменения, уже лежавшие в ветке до начала плана (`casesSlider.js`, `editor-section-blocks.*`, `js-css.php`, `section-blocks.php`, удалённые `blogFilter.js` и `group_9d4a1b2c.json`, docs) — уточнить у пользователя, коммитить ли их отдельным коммитом **до** Task 1, чтобы не смешивать с рефакторингом.

## Риски

- **Массовое переименование ломает JS/PHP молча** — нет тестов; страхуют grep-зачистка (Task 14) и функциональная проверка интерактива (Task 15).
- **Сдвиг брейкпоинтов на 1–2 px** — принят осознанно (миксин не меняем).
- **CF7-форма в БД** может содержать классы — проверить перед Task 6; при изменении формы спросить пользователя.
- **Кэш браузера/Vite** при сверке — пересобирать `npm run build` и делать hard reload.
