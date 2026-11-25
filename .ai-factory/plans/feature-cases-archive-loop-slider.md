# Implementation Plan: /cases/ — зацикленный слайдер, зона «До футера», барабан описания

Branch: feature/cases-archive-loop-slider (от `main`; незакоммиченные правки `casesArchive.js` / `_cases-archive.scss` / `cases-archive.php` из рабочей копии перенесены в ветку как есть)
Created: 2026-11-25

## Settings
- Testing: no (проверка: `npm run lint`, `npm run build`, `composer lint` и визуальная проверка через Claude in Chrome на 1920×979, 1440×900, 1280×720, 1024×768, 1024×600, 375×812)
- Logging: verbose в рамках правил проекта — JS только через `utils/logDebug.js` (`logDebug('[casesArchive] …')`, `logDebug('[casesReel] …')`), никаких новых `console.*`; PHP без логов (чистая разметка)
- Docs: yes — обязательный docs checkpoint через `/aif-docs` в конце (`docs/single-pages.md`, строка `archive-cases.php` + описание слайдера)

## Roadmap Linkage
Milestone: "none"
Rationale: нет `.ai-factory/ROADMAP.md`

## Research Context
Source: .ai-factory/RESEARCH.md (Active Summary)

Goal: в режиме slider десктопный слайдер /cases/ становится бесконечной вертикальной петлёй, не связанной с футером; секция слайдера всегда занимает экран; нижняя hover-зона показывает подсказку и по колесу вниз прокручивает к футеру; описание переключается тем же «барабаном», что и заголовок.

Constraints:
- Только режим `slider` (`SLIDER_QUERY` = min-width 1024px и min-height 520px). Режимы `lane` (телефоны / низкие экраны) и `static` не трогаем (исключение — барабан описания, он общий для всех режимов, как уже общий барабан заголовков).
- Сейчас 3 опубликованных кейса (Doctor Blago, Promedi, «великий сайт»); у Doctor Blago нет описания.
- Swiper 12.1.4; loop переставляет слайды (без клонов); у слайдера `overflow: visible`, `centeredSlides`, `slidesPerView: 'auto'`.
- Lenis на /cases/ выключен (`smoothScroll.js`); `headerAutoHide.js` не меняем.
- SCSS: mobile first через `breakpoint()`, вложенность ≤ 3, BEM, `is-*` для состояний из JS.

Decisions (пользователь, 2026-11-25):
- Петля: тот же вертикальный слайдер, просто зациклен (1 → 2 → 3 → 1 и обратно). Никакой связи с футером.
- Футер всегда в обычном потоке под секцией. Секция не сжимается: удалить `setFooter` / `settleFooter` / `cancelFooterAnim`, `is-footer-hidden` / `is-footer-moving`, анимацию `padding-bottom` у `.site-content`, inline-добавление `is-footer-hidden` в `cases-archive.php` и все «end»-гейты по `atLastCase`.
- Нижняя зона: **15%** высоты экрана. Курсор в зоне → подсказка **«До футера ↓»** (типографика подписи как у `/*CASE ARCHIVE*/`, стрелка вниз). Колесо ВНИЗ в зоне → плавная прокрутка страницы к футеру вместо смены кейса. Колесо ВВЕРХ в зоне листает кейсы как обычно.
- Оверлей зоны только визуальный (`pointer-events: none`), зона определяется по Y курсора, NEXT-фрагмент и «Читати кейс» в зоне остаются кликабельными.
- Футер → вверх: обычная прокрутка страницы; индекс кейса не меняется, пользователь возвращается к последнему просмотренному кейсу, петля работает дальше. Защита от инерции тачпада (`scrollGesture`) остаётся.
- Старт с кейса 01 (`SLIDER_START` / `SLIDER_START_MIN` удалить: в петле PREV-фрагмент сверху есть всегда).
- Без точного указателя (iPad landscape в режиме slider): подсказка видна всегда и работает как кнопка; `End` → футер, стрелки листают кейсы; `focusin` внутри футера работает (футер в потоке, браузер докручивает сам).
- Описание: такой же барабан, как заголовок (трек едет вместе с каверами, blur-fade на элементах, та же скорость / easing). Высота окна барабана следует за активным элементом. Пустой элемент для кейса без описания. Кнопка «Читати кейс» под барабаном (не внутри), меняет только `href`.
- Переход через край барабана (3 → 1, 1 → 3): клоны (последний элемент перед первым, первый после последнего) в обоих барабанах; анимировать один шаг до клона, затем без transition прыгнуть на настоящий элемент — текст едет в ту же сторону, что каверы. Один общий хелпер для обоих барабанов.
- Фиксированная высота заголовка («как у Promedi») — ОТЛОЖЕНО, не в этой задаче.

Open questions (закрываются в Task 1):
- Swiper loop всего на 3 слайдах + centered + auto width + visible overflow: вероятны видимые скачки / «not enough slides for loop». Рабочая гипотеза — в режиме slider дублировать каверы в DOM, пока их < 6, и сводить индекс как `realIndex % count`.

Success signals: колесо бесконечно листает кейсы в обе стороны; заголовок и описание всегда едут вместе с каверами (и через край тоже); высота секции не меняется; наведение на нижнюю зону показывает подсказку, колесо вниз там прокручивает к футеру, прокрутка вверх возвращает к тому же кейсу и петля работает дальше; нет двойного переключения от инерции; iPad / клавиатура добираются до футера.

## Текущее состояние (что меняем)
- `assets/src/js/components/casesArchive.js` (487 строк): Swiper в `slider` с `loop: false, rewind: false`, старт с `SLIDER_START = 1`; футер привязан к последнему кейсу (`setFooter`, `settleFooter`, `cancelFooterAnim`, `footerShown`, `FOOTER_FALLBACK_MS`); гейты `onWheelGate` / `onKeyGate` (capture на `window`) решают, кому отдать событие, с «end» на последнем кейсе; `onPageScroll` и `onFooterFocus` показывают футер; барабан заголовков — `syncReel` / `resyncReel` (`--reel-y` / `--reel-h`); описания — панели `[data-cases-panel]` с crossfade.
- `assets/src/scss/components/_cases-archive.scss` (592 строки): правила `.is-cases-archive` для `.site` / `.site-content` / `.footer` (flex-колонка на экран, transition `padding-bottom`, `is-footer-hidden` / `is-footer-moving`), `.is-header-collapsed`; `.cases-archive.is-enhanced` — барабан заголовков и crossfade панелей.
- `partials/parts/cases-archive.php`: inline-скрипт ставит `is-enhanced` и `body.is-footer-hidden`; `__heads` с барабаном заголовков; `__details` с панелями (описание + ссылка на каждый кейс); `__viewport` / `__track` / `__cover`.
- Футер: `footer.php` → `<footer class="footer" id="contacts">`.
- Высота хедера: `--header-h` (`_tokens.scss`), при авто-скрытии хедера `body.is-header-collapsed` и `.site { padding-top: 0 }`.

## Commit Plan
- **Commit 1** (после задач 1–2): `feat(cases): endless vertical loop on the cases archive, footer back in the flow`
- **Commit 2** (после задач 3–4): `feat(cases): bottom zone hint and wheel-down scroll to the footer`
- **Commit 3** (после задач 5–6): `feat(cases): description reel with edge clones shared with the title reel`
- **Commit 4** (после задачи 7): `fix(cases): slider rework polish after visual check` (только если были правки)

## Tasks

### Phase 1: Петля и отвязка от футера

- [x] **Task 1: Спайк + реализация петли Swiper на 3 каверах (режим slider)**
  Файл: `theme/vite-wordpress-starter-theme/assets/src/js/components/casesArchive.js`.
  - В `options(true)`: `loop: true`, убрать `rewind: false` / `loop: false`, остальное (`direction: 'vertical'`, `centeredSlides`, `slidesPerView: 'auto'`, `spaceBetween: GAP_SLIDER`, `simulateTouch: false`, `mousewheel` на `body`) оставить.
  - Спайк (сначала, в браузере на 1920×979 и 1280×720): включить loop на 3 реальных каверах, листать вниз/вверх по 5+ шагов колесом, клавишами, точками, кликом по фрагментам. Смотреть: есть ли предупреждение Swiper о нехватке слайдов, пропадает ли PREV/NEXT во время перехода, видны ли перестановки слайдов (кавер «телепортируется» с края на край в зоне видимости stage).
  - Ожидаемое решение: клоны каверов в DOM только в режиме slider. Константа `LOOP_FILL = 6`; если `count < LOOP_FILL`, дописать в `__track` копии набора (`k = Math.ceil(LOOP_FILL / count) - 1` раз) через `cover.cloneNode(true)`, с `data-cases-clone`, `aria-hidden="true"`, `tabindex="-1"`, `loading` у img не менять (тот же src, из кеша). Клоны создаются в `setMode` перед `new Swiper` в режиме slider и удаляются после `destroy()` при уходе из slider (lane и static работают только на реальных каверах, как сейчас). Если спайк покажет, что нужно иначе (`loopAdditionalSlides`, другой `LOOP_FILL`) — зафиксировать фактическое значение в комментарии у константы.
  - Сведение индексов: `total` = число слайдов в Swiper; `index = instance.realIndex % count`; хранить `rawIndex = instance.realIndex` для направления. Направление шага: `step = (raw - prevRaw + total) % total; if (step > total / 2) step -= total` → `dir = Math.sign(step)` (нужно барабанам в Task 6; пока просто логировать).
  - Точки: `slideToLoop(r)`, где `r` — ближайший по кругу `raw` с `r % count === i` (кратчайший путь от текущего `rawIndex`).
  - Клик по фрагменту: делегировать на `viewport` (`closest('[data-cases-cover]')`), если это не текущий слайд (`is-current`) — `preventDefault()` и `swiper.slideToLoop(Number(el.dataset.swiperSlideIndex))`. Работает и для клонов. Активный кавер (в т. ч. клон) открывает кейс как ссылка.
  - `render()`: `is-active` / `tabindex` для реальных каверов по `index`; клоны всегда `tabindex="-1"`.
  - Удалить `SLIDER_START`, `SLIDER_START_MIN` и ветку в `setMode`, которая ставит `index = SLIDER_START`; первая загрузка в slider открывается на кейсе 01.
  - Итог (2026-11-25): выбраны клоны каверов (`LOOP_FILL = 6`, `loopAdditionalSlides: 1`); по коду Swiper `slidesPerViewDynamic()` при окне в один кавер = 1 → `loopedSlides = 2`, предупреждения о нехватке слайдов нет. Визуальный спайк не проводился (Claude in Chrome не был подключён) — перенесён в Task 7.
  - Fallback, если loop Swiper на клонах всё равно дёргается: классическая «бесконечная лента» без Swiper loop — 3 набора каверов, после `transitionEnd` прыжок `slideTo(…, 0)` в средний набор. Описать выбранный вариант в комментарии в шапке файла.
  - Логи: `logDebug('[casesArchive] loop', { count, total, clones, loopFill })` при создании Swiper; `logDebug('[casesArchive] slide', { from, to: index, raw, dir, mode })` в `onSlideChange`; `logDebug('[casesArchive] clones removed', { n })` при уходе из slider; `logDebug('[casesArchive] dot', { i, from: rawIndex, to: r })`.

- [x] **Task 2: Футер в потоке, секция на весь экран, новые гейты колеса/клавиш (без зоны)** (depends on 1)
  Файлы: `.../js/components/casesArchive.js`, `.../scss/components/_cases-archive.scss`, `partials/parts/cases-archive.php`.
  - JS: удалить `setFooter`, `settleFooter`, `cancelFooterAnim`, `footerShown`, `footerAnim`, `FOOTER_FALLBACK_MS`, `atLastCase`, `pageScrolls`, `onPageScroll`, `onFooterFocus`, вызов `setFooter` в `onSlideChange`, переключение `is-footer-hidden` в `bindGates`, переменную `content`. `footer` оставить (нужен в Task 4).
  - Новый `onWheelGate` (window, capture, `passive: false`), без «end»:
    1. новый жест по паузе `WHEEL_IDLE_MS` (как сейчас);
    2. открыт оверлей / `ctrlKey` → `stopPropagation()`, нативное поведение;
    3. `!atTop()` → `stopPropagation()`, `scrollGesture = gesture` (страница скроллится нативно);
    4. `gesture === scrollGesture` → `stopPropagation()` + `preventDefault()` (инерция возврата наверх не листает кейс);
    5. иначе — событие Swiper'у, `preventDefault()` (Chrome делает слушатель Swiper на body пассивным).
    Ветку «колесо вниз в нижней зоне» добавит Task 4 между шагами 4 и 5 — оставить там комментарий-заглушку.
  - Новый `onKeyGate`: оверлей / поле ввода / модификаторы → выйти; не наверху → `stopPropagation()` (нативный скролл); наверху стрелки и PageUp/PageDown идут в Swiper (петля), `Space` — нативная прокрутка страницы вниз (ещё один путь к футеру, не трогаем), `End` / `Home` пока не обрабатывать (Task 4). Пересобрать `KEYS_DOWN` / `KEYS_UP` под это.
  - `bindGates` оставляет только `wheel` и `keydown`; в `setMode` убрать `footerShown = …`, `scrollGesture = -1` оставить.
  - SCSS: удалить правила `.is-cases-archive.is-footer-hidden`, `.is-cases-archive.is-footer-hidden, .is-cases-archive.is-footer-moving`, transition `transform` у `.footer`, transition `padding-bottom` и flex у `.site-content`, flex/min-height у `.site`, переменную `$ease-footer`. Оставить transition `padding-top` у `.site` (синхронно с хедером) и `.is-header-collapsed { .site { padding-top: 0 } }`.
  - Высота секции в slider: у `.cases-archive.is-enhanced` (миксин `slider`) вместо `flex: 1 0 auto` — `height: calc(100svh - var(--wp-admin--admin-bar--height, 0px) - var(--header-h))`, `transition: height 0.3s ease` (в такт хедеру); при `.is-cases-archive.is-header-collapsed` — `calc(100svh - var(--wp-admin--admin-bar--height, 0px))`. `display: flex; flex-direction: column; padding-bottom: 40px` оставить, `__wrap` (`flex: 1 1 0`, `container-type: size`, `min-height: $stage-min-h`) не трогать. Проверить, что `headerAutoHide` → `onCollapse: swiper.update()` по-прежнему пересчитывает каверы после роста секции.
  - PHP: inline-скрипт оставляет только `classList.add('is-enhanced')`; обновить комментарий над ним и PHPDoc файла (футер всегда в потоке, слайдер зациклен).
  - Обновить шапки-комментарии `casesArchive.js` (описание режима `slider`) и `_cases-archive.scss` (блок «One screen…» и комментарий у `.cases-archive__viewport` про «footer sliding in»).
  - Логи: `logDebug('[casesArchive] gate', { action: 'page' | 'inertia' | 'slider', dy: e.deltaY, gesture })` — только при смене action внутри жеста (не на каждое wheel-событие); `logDebug('[casesArchive] key gate', { key, action })`.

<!-- Commit checkpoint: tasks 1-2 -->

### Phase 2: Нижняя зона «До футера»

- [x] **Task 3: Разметка и стили подсказки** (depends on 2)
  Файлы: `partials/parts/cases-archive.php`, `.../scss/components/_cases-archive.scss`.
  - PHP: внутри `<main class="cases-archive">`, после `__wrap`, только при `$starter_count > 1`: `<button type="button" class="cases-archive__footer-hint" data-cases-footer-hint aria-controls="contacts">` с `<span>` «До футера» (`esc_html_e( 'До футера', 'vite-starter' )`) и стрелкой ↓ (inline SVG `aria-hidden="true"` или символ в `span aria-hidden`), `aria-label` не нужен — текст видимый.
  - SCSS (BEM, вложенность ≤ 3, mobile first):
    - База (lane / static / без JS): `display: none`.
    - `slider` + `.is-enhanced`: `.cases-archive` получает `position: relative`; подсказка — `position: absolute; inset: auto 0 0; height: $footer-zone` (`$footer-zone: 15svh`, синхронно с `FOOTER_ZONE = 0.15` в JS — секция у верха страницы кончается ровно у нижнего края экрана), flex по центру внизу (`align-items: flex-end; padding-bottom` ≈ 10px, чтобы текст встал в нижнюю 40px-полосу), лёгкий градиент фона от прозрачного к `var(--c-bg)`, `opacity: 0; transition: opacity 0.3s ease`, `pointer-events: none`, `z-index` выше stage, ниже хедера.
    - Текст: как `__label` (12px, 500, `letter-spacing: 0.04em`, uppercase, `var(--c-blue-light)`), стрелка рядом, при показе — короткое «покачивание» стрелки вниз (`@keyframes`, выключено в `prefers-reduced-motion`).
    - `.cases-archive.is-footer-hint .cases-archive__footer-hint { opacity: 1 }`.
    - Без точного указателя (`@media not ((hover: hover) and (pointer: fine))` внутри миксина): всегда `opacity: 1`, `pointer-events: auto`, курсор `pointer`, `:focus-visible` outline как у точек; высоту зоны уменьшить до самой кнопки (не перекрывать NEXT-фрагмент зоной нажатия).
    - `.cases-archive.is-scrolled .cases-archive__footer-hint { opacity: 0; pointer-events: none }` (страница прокручена — подсказка не нужна).
  - Логи: нет (разметка и CSS).

- [x] **Task 4: JS зоны: подсказка, колесо вниз → футер, кнопка, `End`/`Home`** (depends on 2, 3)
  Файл: `.../js/components/casesArchive.js`.
  - Константы: `FOOTER_ZONE = 0.15` (синхронно с `$footer-zone`), `FINE_POINTER_QUERY = '(hover: hover) and (pointer: fine)'`.
  - `inFooterZone(clientY) = clientY >= window.innerHeight * (1 - FOOTER_ZONE)`.
  - `scrollToFooter(reason)`: `window.scrollTo({ top: document.documentElement.scrollHeight - window.innerHeight, behavior: reducedMq.matches ? 'instant' : 'smooth' })`, `scrollGesture = gesture` (хвост инерции жеста не листает кейс), снять `is-footer-hint`.
  - Подсказка (только в режиме slider и только для точного указателя): `pointermove` на `window` (passive, `pointerType === 'mouse'`, через rAF) → `root.classList.toggle('is-footer-hint', atTop() && inFooterZone(e.clientY))`; `pointerleave` на `document.documentElement` / `blur` окна → снять класс.
  - `scroll` на `window` (passive) → `root.classList.toggle('is-scrolled', !atTop())`.
  - `onWheelGate`: новая ветка после проверки инерции — `e.deltaY > 0 && inFooterZone(e.clientY)` (у `WheelEvent` есть `clientY`) → `stopPropagation()`, `preventDefault()`, `scrollToFooter('wheel')`. Колесо вверх в зоне — как обычно в Swiper.
  - Кнопка `[data-cases-footer-hint]`: `click` → `scrollToFooter('button')` (работает на тач-устройствах; на мыши кнопка некликабельна из-за `pointer-events: none`).
  - `onKeyGate`: наверху `End` → `preventDefault()`, `stopPropagation()`, `scrollToFooter('key')`; `Home` не наверху → нативно (прокрутка вверх, кейс не меняется).
  - Все новые слушатели вешать/снимать в `bindGates(on)`; при уходе из slider снимать `is-footer-hint` / `is-scrolled`.
  - Возврат: после прокрутки к футеру и обратно наверх индекс не трогается, петля продолжает работать; проверить, что инерция жеста вверх у верха страницы не переключает кейс (`scrollGesture`).
  - Логи: `logDebug('[casesArchive] footer zone', { inside })` только при смене состояния; `logDebug('[casesArchive] scroll to footer', { reason, from: window.scrollY, to })`.

<!-- Commit checkpoint: tasks 3-4 -->

### Phase 3: Барабан описания

- [x] **Task 5: Разметка и стили барабана описания, одна кнопка «Читати кейс»** (depends on 2)
  Файлы: `partials/parts/cases-archive.php`, `.../scss/components/_cases-archive.scss`.
  - PHP: в `__details` вместо панелей `[data-cases-panel]`:
    - подпись «Final product» — как сейчас (только если есть хотя бы одно описание);
    - `<div class="cases-archive__reel cases-archive__reel--text" data-cases-reel="description"><div class="cases-archive__reel-track" data-cases-reel-track>` с элементом на каждый кейс: `<p class="cases-archive__description cases-archive__reel-item" data-cases-reel-item="{i}">` (пустой `<p>` для кейса без описания — элемент остаётся, чтобы индексы совпадали; `aria-hidden` ставит JS);
    - после барабана одна `<a class="button button--secondary cases-archive__link" href="{url кейса 0}" data-cases-link>` «Читати кейс»; URL каждого кейса — `data-url="<?php echo esc_url( … ) ?>"` на его элементе барабана заголовков (без JSON).
  - Барабан заголовков: заменить `data-cases-panel` на `data-cases-reel-item`, `data-cases-reel` → `data-cases-reel="title"` (единые атрибуты для общего хелпера).
  - Без JS: заголовки и описания списками (как сейчас заголовки); кнопка «Читати кейс» скрыта (`.cases-archive:not(.is-enhanced) .cases-archive__link { display: none }`), так как каверы в ленте сами ссылки. Пустые описания без JS — `display: none` (`:empty`).
  - SCSS: стили `.is-enhanced` барабана (`__reel` высота `--reel-h` + `overflow: hidden`, `__reel-track` `translateY(--reel-y)`, `__reel-item` blur-fade, `:not(.is-ready)`) применяются к обоим барабанам — они уже по классам, лишь проверить, что `__description` внутри трека не теряет типографику и что `gap: 12px` трека без JS подходит описаниям (иначе модификатор `__reel--text`). Пустой элемент: `min-height: 0`, окно барабана схлопывается до 0 (анимация высоты как у заголовка).
  - Удалить `__panel` и всё его оформление (база, `& + &`, crossfade в `.is-enhanced`, `is-instant`, `prefers-reduced-motion`), добавить `__reel` в эти списки вместо него. `__link` margin-top пересчитать от барабана (в макете 52px от описания: сейчас 44 + gap 8 → проверить, что gap `__details` по-прежнему 8).
  - Логи: нет.

- [x] **Task 6: Общий хелпер барабана с клонами на краях + подключение** (depends on 1, 5)
  Файлы: новый `.../js/components/casesReel.js`, `.../js/components/casesArchive.js`.
  - `createReel(reel, { count, speed, name })` → `{ go(index, dir), resync(reason), setSpeed(ms), destroy() }`:
    - находит реальные элементы `[data-cases-reel-item]`; при `count > 1` добавляет клоны: копию последнего в начало трека, копию первого в конец (`data-reel-clone`, `aria-hidden="true"`);
    - позиционирует по `offsetTop` / `offsetHeight` целевого элемента (`--reel-y`, `--reel-h`, класс `is-ready`), трек `position: relative` (как сейчас);
    - `is-active` только на целевом элементе (клоне во время перехода через край), реальные неактивные элементы — `aria-hidden`;
    - переход через край: `dir > 0` и `from === count - 1`, `to === 0` → анимировать к клону-после-последнего; `dir < 0` и `from === 0`, `to === count - 1` → к клону-перед-первым; по `transitionend` (`transform` на треке) или по таймеру `speed + 50` — прыжок без анимации на реальный элемент (`is-instant` на самом барабане, reflow, снять), `is-active` переносится с клона на реальный без transition (иначе мигнёт blur-fade);
    - если новый `go()` пришёл, пока ждём прыжка, — сначала мгновенно доделать прыжок, потом анимировать новый шаг;
    - многошаговый переход через край (точка через 2+ кейса при `count ≥ 4`) — анимировать напрямую, без клона;
    - `speed === 0` (reduced motion) → всегда мгновенно;
    - `resync()` — пересчёт без анимации (resize ширины трека, `document.fonts.ready`); `ResizeObserver` перенести внутрь хелпера;
    - `destroy()` — убрать клоны, наблюдатели, таймер.
  - `casesArchive.js`: заменить `syncReel` / `resyncReel` / код панелей в `render()` и `ResizeObserver` на два экземпляра `createReel` (title, description); в `onSlideChange` вызывать `reel.go(index, dir)` с направлением из Task 1 (в режиме lane направление тоже считать по `realIndex`, клонов каверов там нет — та же формула с `total = count`); при `is-instant`-рендере (`setMode`) — `go(index, 0)` без анимации. Кнопка `[data-cases-link]`: `href` = `data-url` активного заголовка. CSS-переменная/константа скорости: `SPEED` = `$text-speed`.
  - Обновить комментарии в шапке `casesArchive.js` («the title reel…, the details crossfade» → оба барабана) и в `_cases-archive.scss` (блок «Enhanced»).
  - Логи: `logDebug('[casesReel] go', { name, from, to, dir, viaClone })`, `logDebug('[casesReel] jump', { name, to, reason: 'transitionend' | 'timeout' | 'interrupted' })`, `logDebug('[casesReel] resync', { name, reason, width })`, `logDebug('[casesReel] init', { name, count, clones: 2 })`.

<!-- Commit checkpoint: tasks 5-6 -->

### Phase 4: Проверка

- [x] **Task 7: Линтеры, сборка, визуальная проверка** (depends on 1–6)
  - `npm run lint`, `npm run build` (в `theme/vite-wordpress-starter-theme`), `composer lint` — без новых ошибок.
  - Claude in Chrome на `http://localhost:8080/cases/` (по памяти проекта — не DevTools MCP и не Playwright), включить DEBUG-логи `logDebug`:
    - 1920×979, 1440×900, 1280×720, 1024×768: колесом 7+ шагов вниз и 7+ вверх — петля без скачков каверов, PREV/NEXT всегда на месте, заголовок и описание едут в ту же сторону, что каверы, в том числе 3 → 1 и 1 → 3; у Doctor Blago пустое описание (окно схлопнулось), кнопка «Читати кейс» ведёт на активный кейс; счётчик и точки синхронны, клик по точке и по фрагменту (в т. ч. по клону) работает.
    - Высота секции не меняется при листании; хедер прячется через ~2s, секция растёт на его высоту, каверы пересчитаны.
    - Курсор в нижних 15% → «До футера ↓»; колесо вниз там → плавно к футеру; колесо вверх там → предыдущий кейс; обратно вверх тачпадом с инерцией → тот же кейс, без лишнего переключения; дальше петля работает.
    - Клавиатура: ↑/↓, PageUp/PageDown листают; `End` → футер; `Tab` в футер — футер в зоне видимости.
    - 1024×600 (низко, но slider) и эмуляция тача (iPad landscape): подсказка видна всегда, по нажатию — к футеру.
    - 375×812 и 1024×500 (lane): лента как раньше, оба барабана работают, подсказки нет.
    - `prefers-reduced-motion: reduce`: без анимаций, прокрутка к футеру мгновенная.
  - Найденные расхождения — исправить в рамках задач 1–6 и отметить в этом плане.
  - Логи: нет новых; убедиться, что в консоли нет предупреждений Swiper о loop.
  - Итог (2026-11-25, DevTools MCP на Chrome :9222): 1920×979 — 7 шагов вниз и 7 вверх, каверы стабильны (±398), барабаны через край едут через клоны и прыгают на реальный элемент; высота секции постоянна; зона 15% → подсказка, колесо вверх в зоне листает, вниз → футер, возврат наверх с инерцией не меняет кейс; 1280×720 — секция до низа экрана; 1024×600 touch — подсказка-кнопка ведёт к футеру; 375×812 lane — без клонов и подсказки; reduced motion — мгновенно, `End` → футер; slider ↔ lane — клоны убираются/создаются. Предупреждение Swiper loop только в lane на 1440×500 (3 слайда в широкой ленте) — существовало до задачи, lane вне скоупа.

<!-- Commit checkpoint: task 7 (только если были правки) -->
