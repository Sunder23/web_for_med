# Implementation Plan: Редизайн главной и правки по сайту (Figma, ноябрь 2026)

Branch: feature/home-redesign (создана от feature/webp-images с незакоммиченными изменениями)
Created: 2026-11-11

## Settings
- Testing: no (проверка — визуально в браузере 1440 / 1024 / 375 + `npm run lint`, `composer lint`)
- Logging: verbose в рамках правил проекта — PHP только `error_log( 'WARN [area] …' )` на сбоях; JS только через `utils/logDebug.js` (без новых `console.log`); WP-CLI — `WP_CLI::log()` / `WP_CLI::warning()`
- Docs: yes — обязательный docs checkpoint через `/aif-docs` в конце

## Источники в Figma (fileKey `huDLClWg3dIpXyzLMuVHEs`)
| Что | node |
|---|---|
| Главная desktop 1440 | `3061:1443` (hero+header `3287:1415`, 02 clinics `3061:1458`, 03 quote/problems/banner/solutions `3287:1416`, 04 services `3061:1551`, 05 cases `3061:1592`, 06 process `3061:1660`, 07 why `3061:1707`, footer+cover `3287:1417`) |
| Главная mobile 375 + заметки к мобайлу | `3025:225` (заметки — текстовые ноды справа, x=4956: `3178:3487…3178:3495`, `3287:1424`) |
| Контактная панель desktop / mobile | `3155:2962` / `3155:2916` |
| Состояния контролов формы | `3155:2785` |
| Отправка: success / retry / loading | `3155:2617` |
| Список правок заказчика | `3166:3486` |
| Страница кейса (референс внутренней страницы: футер без иллюстрации и без формы) | `3061:891` |

Токены из макета: dark blue `#092a4a`, blue main `#1261a0`, white on dark `#f8f9f9`, grey line `#dbdbdb`, header/main black `#2e2f30`, text `#3b3e41`, **secondary btn bg `#f4f3f2`**, hover картки `#edf6f8`, icon `#aebbc4`, banner `#144166`, tag `#218ed6`.
Шрифты: заголовки — **Grunt Grotesk Bold** (исходник `GruntGrotesk-Bold.otf` в корне репо, 51 KB), текст — Fixel Display 400/500/600 (уже есть).
Отступ между секциями на desktop — **70px** (по координатам фреймов).

## Решения и допущения
- **П.8 (форма):** подтверждено заказчиком — панель выезжает **слева** на desktop (макет `3155:2962`), на мобайле — снизу (bottom sheet, `3155:2916`).
- **П.9 (хедер):** не sticky; smart header — прячется при скролле вниз, появляется при скролле вверх (как на кейсе Promedi).
- **П.7:** `.footer__form-section` из `footer.php` удаляется **на всех страницах** — форма теперь только в панели. Подтверждено макетом страницы кейса `3061:891`: перед футером формы нет.
- **П.6:** иллюстрация «Далі буде…» (`.footer__cover`) выводится только на главной.
- **П.13:** «две секции с новыми иконками» — solutions (03_4, ноды `3287:1398/1375/1408`) и process (06, ноды `3287:1367/1297/1279/1311`); иконки 50px и на mobile такого же размера.
- **П.11:** удаляем декоративные иконки заголовков: `.quote__gears` (quote), `.galaxy-icon` (services), `.pulse-icon` (process).
- Неиспользуемые untracked папки `scss/abstracts|base|layout|pages|vendors` и `js/components/heroAnimations.js` не трогаем и не подключаем — работаем в действующих `_tokens/_fonts/_base/components/*`.

## Open questions
1. Заметка mobile «будуть зміни, дам пізніше контент» (`3178:3492`, секция cases) — согласовано: контент позже, секцию cases меняем только по стилям.
2. Редизайн самого шаблона кейса (`3061:891`) вне этого плана — здесь он только референс для футера и хедера внутренних страниц.

## Commit Plan
- **Commit 1** (после задач 1–3): `feat(theme): heading font, button and spacing tokens, smart header`
- **Commit 2** (после задач 4–5): `feat(contact): off-canvas contact drawer and new footer`
- **Commit 3** (после задач 6–9): `feat(home): restyle hero, clinics, quote, problems and solutions`
- **Commit 4** (после задач 10–12): `feat(home): restyle services, cases, process and why sections`
- **Commit 5** (после задач 13–14): `chore(home): content migration script and docs`

## Tasks

### Phase 1: Глобальные токены, шрифт, хедер

- [x] **Task 1: Шрифт заголовков и токены (п.1, 4, 10)**
  - Конвертировать `GruntGrotesk-Bold.otf` (корень репо) в woff2: `pip install fonttools brotli`, затем `python -c "from fontTools.ttLib import TTFont; f=TTFont('GruntGrotesk-Bold.otf'); f.flavor='woff2'; f.save('theme/vite-wordpress-starter-theme/static/fonts/GruntGrotesk-Bold.woff2')"`. Проверить, что в шрифте есть кириллица и украинские буквы (і, ї, є, ґ, ’); если какой-то нет, сообщить заказчику. После конвертации убрать `.otf` из корня (не коммитить исходник в корень).
  - `assets/src/scss/_fonts.scss`: `@font-face` Grunt Grotesk 700 из `@/fonts/GruntGrotesk-Bold.woff2` (`font-display: swap`); добавить `<link rel="preload">` для шрифта, если так уже сделано для Fixel.
  - `assets/src/scss/_tokens.scss`: `--font-heading: 'Grunt Grotesk', var(--font-main), sans-serif`; `--c-secondary: #f4f3f2`; `--c-card-hover: #edf6f8`; `--c-icon: #aebbc4`; `--c-tag: #218ed6`; `--c-banner: #144166`. Удалить мёртвые токены `--font-heading` (Roboto Condensed) / `--font-body` (IBM Plex), чтобы не было конфликта имён.
  - `_base.scss`: `h1, h2` и `.section-title` → `font-family: var(--font-heading)`, `font-weight: 700` (по макету: hero 40/1.01, заголовки секций 40/1.15; на баннере 03_3 остаётся Fixel SemiBold — проверить по макету).
  - `components/_buttons.scss`: `.button--secondary` — фон `var(--c-secondary)` (#f4f3f2), текст `var(--c-blue)`; hover — по состояниям `3155:2785`, иначе текущий (синий фон / белый текст).
  - Логирование: не требуется (только CSS и разовая конвертация).
  - Files: `_fonts.scss`, `_tokens.scss`, `_base.scss`, `components/_buttons.scss`, `static/fonts/GruntGrotesk-Bold.woff2`.

- [x] **Task 2: Расстояние между секциями (п.12)**
  - Ввести `--section-gap` (mobile — по фрейму `3025:225`, ≈50px; desktop 70px) в `_tokens.scss`.
  - `_base.scss:48-67`: заменить 90px-полосу `section:not(...)::after` на `--section-gap` (сохранить border-top линии сетки, как в макете).
  - Учесть mobile-заметку «додати відстань між секціями» (`3178:3491`).
  - Сверить итоговые Y-позиции секций с макетом 1440 (557, 815, 2295, 3300, 4127, 4858, 5646).
  - Files: `_tokens.scss`, `_base.scss`, при необходимости `template-parts/blocks/section-*.scss` с хардкодом отступов.

- [x] **Task 3: Хедер — новый вид + smart header (п.9, 15)**
  - Разметка/стили по `3287:1415` (инстанс `2963:4`): высота 57px desktop, контейнер 1350 с боковыми линиями, padding 8px 35px; лого Grunt 20px `#092a4a`; меню Fixel 14px, letter-spacing .7px, gap 40px; CTA «Форма зв'язку» `button--primary button--sm` 15px, padding 10/14. Mobile — `3180:3733` (46px, burger 34×26).
  - `_header.scss:30`: убрать `position: sticky`; новая модель — `position: fixed` + `padding-top` на `body`/`.site` = `--header-h`, состояния `.is-hidden` (translateY(-100%)) и `.is-scrolled`, `transition: transform .3s`; `prefers-reduced-motion` — без анимации.
  - Новый `assets/src/js/components/smartHeader.js` (подключить в `main.js`): слушает `window.lenis.on('scroll')` (fallback — `scroll` с rAF), порог 8px, у верха страницы всегда показан, не прятать при открытом мобильном меню (`.nav.is-open`) и открытой контакт-панели (`body.is-drawer-open`).
  - Проверить зависимости от `--header-h`: `section-problems-solutions.scss` (`.problems { top: var(--header-h) }`), `activeNav.js`, якорные скроллы Lenis (offset).
  - Логирование: `logDebug('[smartHeader] init')`, `logDebug('[smartHeader] state', { hidden, y })` при смене состояния.
  - Files: `partials/header/header.php`, `partials/header/logo.php`, `components/_header.scss`, `js/components/smartHeader.js`, `js/main.js`.
<!-- Commit checkpoint: tasks 1-3 -->

### Phase 2: Контактная панель и футер

- [ ] **Task 4: Off-canvas панель с формой (п.8)** (depends on 1)
  - Разметка: новый `partials/parts/contact-drawer.php` — `<div class="contact-drawer" id="contact-drawer" role="dialog" aria-modal="true" aria-labelledby=… hidden>` + `.contact-drawer__overlay`, `__panel`, `__close`, `__title` («Обговорити задачі»), форма CF7 из `starter_get_option('footer_contact')['contact_form']`. Подключить один раз в `footer.php` (глобально). Если опция пустая — не выводить и `error_log( 'WARN [contact-drawer] footer_contact.contact_form is empty' )`.
  - Стили `components/_contact-drawer.scss` (подключить в `main.scss` вместо пустого `_modal.scss`): desktop — панель слева, ширина ≈640px (по `3155:2962`), белый фон, overlay `#2e2f30` ~50%; mobile — bottom sheet снизу, max-height ~85vh, скролл внутри. Сторона — модификатор `contact-drawer--left` / `--right`.
  - Поля по макету: Ім'я*, «Як з вами зв'язатися?*» — чипы Телефон/Email/Telegram/WhatsApp (radio, `is-active` с галочкой), поле контакта меняет label/placeholder/маску по выбору (`+380 (__) ___ __ __` для телефона), «Що хотіли б обговорити? · необов'язково» textarea, «Відправити» full width, «* Обов'язкові поля». Состояния loading / success / retry — `3155:2617`.
  - JS `components/contactDrawer.js` (в `main.js`): открытие по делегированному клику на `[data-contact-open]` и `a[href$="#contact-form"]`; закрытие — крестик, overlay, Esc; focus trap, возврат фокуса на триггер; `window.lenis.stop()/start()` и `body.is-drawer-open`; после `wpcf7mailsent` — success-экран. Обновить `contactForm.js` под новые имена полей (`your-name`, `contact-method`, `your-contact`, `your-message`).
  - Триггеры: header CTA, `.nav__cta-button`, hero primary, кнопка футера — через `data-contact-open` в PHP (для ACF-ссылок: если URL = `#contact-form`, добавить атрибут).
  - CF7-форму с новой разметкой создать/обновить WP-CLI скриптом (Task 13).
  - Логирование: `logDebug('[contactDrawer] open', { trigger })`, `close`, `cf7 event`, `method change`; PHP — WARN при пустой опции.
  - Files: `partials/parts/contact-drawer.php`, `footer.php`, `components/_contact-drawer.scss`, `components/_forms.scss`, `main.scss`, `js/components/contactDrawer.js`, `js/components/contactForm.js`, `js/main.js`.

- [ ] **Task 5: Новый футер (п.5, 6, 7)** (depends on 4)
  - Разметка по инстансу `3139:1682` (mobile `3180:3732`): 3 колонки внутри контейнера с линиями — (1) лого Grunt 32px + меню Fixel 14px gap 30px (включить footer-меню, сейчас закомментировано); (2) «Обговорити ваш запит» 15px + кнопка «Заповнити форму» (`data-contact-open`, 52px); (3) соцсети 96×40 + email Fixel SemiBold 22px `#1261a0` underline. Padding колонок 48px/40px.
  - `.footer__cover` («Далі буде…», Grunt 48px, белый, высота 454) — только при `is_front_page()` (п.6).
  - `.footer__form-section` удалить из `footer.php` полностью, на всех страницах (п.7). Вместе с ней убрать `initFooterFormAOS` из `siteAnimations.js` и стили `&__form-section`, `&__form-wrap`, `&__form-title` из `_footer.scss`. Опция `footer_contact` остаётся: её `contact_form` использует панель (Task 4), а `title`/`text` — заголовок панели, если заполнены.
  - Внутренние страницы сверить с футером макета кейса `3061:891`: та же полоса из 3 колонок, без cover.
  - Логирование: WARN `[footer]` о пустой `footer_contact` перенести в `contact-drawer.php` (Task 4).
  - Files: `footer.php`, `components/_footer.scss`, `js/components/siteAnimations.js`, при необходимости `configure/theme-hooks/register-menus.php`.
<!-- Commit checkpoint: tasks 4-5 -->

### Phase 3: Секции главной (каждая — по своему desktop-фрейму и mobile `3025:225`)

- [ ] **Task 6: Hero** (depends on 1, 3)
  - `section-home-hero.php/.scss`: контейнер padding 50px 35px 0 (desktop), gap 100px, контент 550px; заголовок Grunt 40/1.01 `#2e2f30`, подзаголовок Fixel 500 20/1.5; кнопки 18px padding 18/24, gap 24px; изображение 630×380.
  - Mobile (`3180:3523` + заметка `3178:3487`): высота изображения 260px; размытое изображение — выводить `image_mobile` в нужном размере (srcset / не растягивать маленький файл), кнопки full width 50px.
  - Primary CTA — триггер панели (`data-contact-open`).
  - Логирование: только PHP WARN при отсутствии обязательных `$args` (по шаблону секций).
  - Files: `template-parts/blocks/section-home-hero.php`, `scss/template-parts/blocks/section-home-hero.scss`.

- [ ] **Task 7: Clinics (02)** (depends on 1)
  - Сетка 3×2 (325×94) + aside 375 справа; ячейки padding 30/32, gap 8; иконки 28px; текст Fixel 500 16/1.05; aside — иконка-пазл 40px + текст 20px `#1261a0`.
  - Mobile (`3180:3534`, заметки `3178:3488`, `3287:1424`): 2 колонки по 177.5, последний ряд — aside на всю ширину с иконкой 28px; исправить «приплюснутые» отступы ячеек; скрыть отмеченную иконку.
  - Files: `section-clinics.php/.scss`.

- [ ] **Task 8: Quote (03_1) + Problems/Banner/Solutions (03_2–03_4) (п.2, 3, 11, 13, 14)** (depends on 1, 2, 3)
  - Quote: убрать inline SVG `.quote__gears` (п.11); текст «Цифрові процеси мають працювати в унісон з медичним бізнесом» Grunt 40/1.15 по центру, ширина 990, padding 80px 151px.
  - Problems: карточки 450×420, padding 48/40, `justify-content: space-between`; тег `/*problem*/` Fixel 600 13px uppercase `#218ed6`; заголовок 20/1.2, текст 16/1.55.
  - Banner: фон `#144166`, текст Fixel 600 40/1.15 `#f8f9f9`, ширина 849, padding 90/40.
  - Solutions: **убрать фон** (п.3); заменить ACF-картинки 200×308 на новые stroke-иконки 50px (п.13: magnet, yarn 120×50, funnel) — на mobile того же размера; карточки 450×420, gap 37.
  - Анимация «наезда» (`.problems` fixed / `.solutions` sticky, `bottom: -90px`): пересчитать под новые высоты блоков (п.14), новый `--section-gap` и не-sticky хедер; проверить в браузере и отметить, что заказчик проверит анимацию после.
  - Mobile (`3180:3567`–`3180:3596`): тексты/отступы по фрейму.
  - Files: `section-quote.php/.scss`, `section-problems-solutions.php/.scss`, `editor-section-blocks.scss` (если меняются слои), иконки → `static/images/home/`.

- [ ] **Task 9: Загрузка ассетов из Figma** (параллельно 7–8, до 11)
  - Скачать через `get_design_context` SVG иконок clinics (6 + пазл), solutions (3), process (4), services (5), why (4), social icons футера; положить в `static/images/home/` с kebab-case именами. Не использовать временные URL Figma.
  - Files: `static/images/home/*.svg`.
<!-- Commit checkpoint: tasks 6-9 -->

- [ ] **Task 10: Services (04) (п.2, 11)** (depends on 1, 2)
  - Заголовок «За кожною послугою стоїть розуміння того, як працює клініка зсередини» по центру, Grunt 40/1.15; убрать `.galaxy-icon` (п.11, заметка `3178:3495`).
  - Фото 666×683 слева, список 684: пункты 120px, padding 48/52, иконка ~22px + gap 20, раскрытый пункт — фон `#edf6f8`, текст 16/1.55.
  - Mobile (заметка `3178:3490`): высота изображения 212px, **убрать эффект «залипания» картинки** при скролле списка (sticky/JS в `section-services.js` / glitchImage — отключить на mobile).
  - Логирование: `logDebug('[services] accordion toggle', { index })` в существующем JS при необходимости.
  - Files: `section-services.php/.scss/.js`.

- [ ] **Task 11: Cases (05) и Process (06) (п.11, 13)** (depends on 1, 2, 9)
  - Cases: заголовок Grunt 40, padding 80/33; стрелки 31×33; слайды 450×551 (текст 171 + обложка 380). Заметка `3178:3489` — отступы ячеек на mobile; mobile-пагинация точками 8px. Контент — позже (Open question 3).
  - Process: убрать `.pulse-icon` (п.11, заметка `3178:3493`); заголовок «Починаємо проєкт із визначення актуальних задач клініки, а не з дизайну» (mobile — «…задач клініки»); шаги 337.5, padding 48/40, новые иконки шагов 50px (п.13, на mobile того же размера) вместо ACF-картинок 200×173; caption `/*step N*/` 13px `#218ed6`, заголовок 20, текст 16.
  - Files: `section-cases.php/.scss`, `section-process.php/.scss`.

- [ ] **Task 12: Why (07) (п.2)** (depends on 1, 2)
  - Заголовок «Чому з нами співпрацюють роками» Grunt 40 внутри правой колонки (item 188px, padding 48/52); пункты 120–144px с иконками 26px; чат слева 666×718.
  - Mobile (заметка `3178:3494`): убрать задержку появления блока с отзывами при скролле (`section-why.js` / AOS delay).
  - Логирование: `logDebug('[why] reveal')` если меняется логика JS.
  - Files: `section-why.php/.scss/.js`.
<!-- Commit checkpoint: tasks 10-12 -->

### Phase 4: Контент и документация

- [ ] **Task 13: WP-CLI миграция контента главной** (depends on 4, 6–12)
  - `scripts/update-home-redesign.php` (идемпотентный, `--dry-run`): обновить в блоках главной тексты трёх заголовков (п.2), подставить новые иконки (sideload из `static/images/home/` в медиатеку; если загрузка SVG не разрешена — шаблоны выводят иконки из темы по ключу, а скрипт только чистит старые поля), ссылки CTA → `#contact-form`; опция `header_button` → `#contact-form`; создать/обновить CF7-форму с новыми полями и записать шорткод в `footer_contact.contact_form`.
  - Логирование: `WP_CLI::log()` на каждый обновлённый блок/опцию, `WP_CLI::warning()` при отсутствии блока/вложения, итоговая сводка.
  - Запуск: `MSYS_NO_PATHCONV=1 make wp cmd="eval-file /scripts/update-home-redesign.php"`.
  - Files: `scripts/update-home-redesign.php`, при необходимости `configure/acf/acf-json/group_starter_section_*.json`.

- [ ] **Task 14: Документация и линтеры** (depends on all)
  - Обновить `docs/page-sections.md` (секции без иконок заголовков, новые иконки, spacing), `docs/getting-started.md`/`docs/single-pages.md` при изменении футера/форм, `AGENTS.md` и `.ai-factory/ARCHITECTURE.md` (contact drawer, smart header, шрифт заголовков, убрать упоминание sticky).
  - `npm run lint`, `composer lint`, `npm run build`; визуальная проверка главной и одной внутренней страницы на 1440/1024/375.
  - Files: `docs/*.md`, `AGENTS.md`, `.ai-factory/ARCHITECTURE.md`.
<!-- Commit checkpoint: tasks 13-14 -->
