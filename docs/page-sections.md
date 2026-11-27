[← Getting Started](getting-started.md) · [Back to README](../README.md) · [Single Pages →](single-pages.md)

# Page Sections (`acf/section-*` blocks)

Pages (`page` post type) are assembled in the block editor from ACF/SCF section blocks `acf/section-{slug}` (inserter category "Секції сторінки"). Each block is a thin wrapper around `template-parts/blocks/section-{slug}.php`: section markup lives in one place and can also be reused directly by templates.

Single CPT, blog and archive templates (`single*.php`, `home.php`, `archive-*.php`) stay plain PHP and are **not** built from blocks. Separately, content blocks for articles (`configure/acf/acf-blocks/`, e.g. `info-block`) are registered by `configure/acf-blocks.php` — that is a different mechanism.

## How it works

1. **Block** — folder `configure/acf/section-blocks/section-{slug}/` with `block.json` (`"name": "acf/section-{slug}"`, `"category": "page-sections"`, `"acf": { "mode": "preview", "renderTemplate": "render.php" }`) and a one-line `render.php`:
   ```php
   starter_render_section_block( '{slug}', $block, $is_preview );
   ```
   `starter_register_section_blocks()` (`configure/section-blocks.php`) registers every folder via `glob()` on `init`.
2. **Fields** — local JSON `configure/acf/acf-json/group_starter_section_{slug_underscored}.json`, `location: block == acf/section-{slug}`, field keys `field_starter_section_{slug_underscored}_{name path}`.
3. **Render** — `starter_render_section_block()` passes `get_fields()` to `get_template_part( 'template-parts/blocks/section-' . $slug, null, $fields )`. The slug must be listed in `starter_get_section_slugs()`, otherwise `WARN [section-blocks] unknown section slug: …` is logged. An empty section shows a placeholder in the editor.
4. **Conditional assets** — `starter_enqueue_section_block_assets()` parses the current post content (`parse_blocks()`, recursive) and enqueues only the `section-{slug}.scss` / `.js` of blocks that are present. Styles depend on `main`; scripts depend on `jquery`.
5. **`page.php`** — `has_blocks()` ? `the_content()` : `the_title()` + `the_content()`. TOC (`configure/toc.php`) is not injected on pages.

## Files of one section

| Part | Path (relative to the theme) |
|------|------------------------------|
| Markup | `template-parts/blocks/section-{slug}.php` |
| Styles | `assets/src/scss/template-parts/blocks/section-{slug}.scss` |
| Script (optional) | `assets/src/js/template-parts/blocks/section-{slug}.js` |
| Block | `configure/acf/section-blocks/section-{slug}/{block.json,render.php}` |
| Fields | `configure/acf/acf-json/group_starter_section_{slug_underscored}.json` |

`vite.config.js` scans `template-parts/blocks/` as a second (non-recursive) level of entries, mirroring the PHP layout one-to-one.

## Editor canvas

- `starter_vite_enqueue_editor_canvas_assets()` (`configure/js-css.php`) loads, for the **page** editor only, `main.scss`, every section stylesheet (with `starter-editor-main` as dependency — the front-end `main` handle does not exist in the editor), `editor-section-blocks.scss` and `editor-section-blocks.js`; `editor-link-guard.js` is loaded for every post type so links and form submits in block previews cannot navigate the canvas away.
- `starter_strip_admin_styles_from_page_canvas()` (filter `block_editor_settings_all`) removes wp-admin stylesheets and the article editor stylesheet from the page canvas so previews match the front end. It leaves one empty `theme` style entry: with none left, the editor falls back to core default editor styles (system font, 18px body).
- Section JS (AOS, reveal animations, accordions) does not run in the editor. `editor-section-blocks.scss` resets `[data-aos]` and other pre-reveal hidden states (`.hero__title`, `.hero__button`, `.why-message`) to their final visible state — add new ones there. If a section ever gets a scroll-effect layer (`fixed` / `sticky`), put it back in flow there too, otherwise it covers neighbouring blocks and their fields cannot be selected.
- Carousels are the exception: `editor-section-blocks.js` (re)initialises them in the canvas whenever ACF re-renders a preview and destroys detached instances. To add a slider section, make its init accept a root element and register it in the `CAROUSELS` list.

## Current sections (front page)

| Slug | Contents | Script |
|------|----------|--------|
| `home-hero` | Hero with glitch image (`image` desktop, `image_mobile` below `mobile`, falls back to `image`; a fixed 630×380 from `notebook`, 380px high and column-wide on `tablet`); primary CTA opens the contact drawer | `heroTitle` (title scramble) |
| `clinics` | Clinics grid 3×2 + aside (2 columns on phones, aside full width) | — |
| `quote` | Centred quote text | — |
| `problems-solutions` | Problems cards + banner text + solutions with 50px icons (static flow; problems and solutions rows are 420px tall from `tablet`). Card content slides up from the card edge when it enters the viewport (`components/cardReveal.js`, as on prjctr.com: 1s `cubic-bezier(0.5, 0, 0, 1)`, 0.1s step between cards of a row, starts at 30% visible, once; skipped with reduced motion) | `section-problems-solutions.js` |
| `services` | Centred title, services accordion with image (image is not sticky on phones) | accordion + glitch image |
| `cases` | Cases slider (Swiper, dot pagination on phones) | `casesSlider` |
| `process` | Process title + steps with 50px icons (anchor `#process`); optional `title_mobile` replaces the title below 766px | — |
| `why` | Chat + reasons (title inside the reasons column) | `whySection` |

Anchors `#about`, `#services`, `#cases`, `#process` are part of the markup; the menu and `activeNav` rely on them.

### Design rules (November 2026 redesign)

- Section titles (`h1`, `h2`, `.section-title`) use **Grunt Grotesk Bold** via `--font-heading` (`static/fonts/GruntGrotesk-Bold.woff2`); body text stays Fixel Display. The problems/solutions banner text is Fixel SemiBold.
- Section titles have no decorative icons (the quote gears, services galaxy and process pulse icons were removed).
- Vertical rhythm: `--section-gap` (50px on phones, 70px from `tablet`) is the band between sections, drawn by `section:not(...)::after` in `_base.scss` together with the grid lines.
- Icons in clinics / solutions / services / process / why are SVGs from `static/images/home/*.svg`, attached to the sections' ACF image fields (solution and process icons are 50px on every breakpoint). Upload new icons to the media library rather than pointing at theme files, so editors can swap them.

## About page sections

The "Про нас" page (`/about/`, Figma desktop `3241:4188`, mobile `3255:874`) is built from three blocks, in this order:

| Slug | Contents | Script |
|------|----------|--------|
| `about-intro` | Intro text (`text`, textarea → paragraphs) + image with caption. The image comes first in the DOM (LCP, on top on phones); from `tablet` the text column moves to the left. The page title is printed as a visually hidden `<h1 class="screen-reader-text">`. The "NO FUTURE NOW*" lettering is part of the image itself. | — |
| `team` | Repeater `members` (`photo`, `name`, `role`, `label`): 2 columns on phones, 4 from `tablet`. `label` is a decorative plate (`aria-hidden`) that follows the cursor 12px right / 26px down of the pointer. | `teamHoverLabel` (only on `(hover: hover) and (pointer: fine)` from `tablet`; the label is hidden on touch and in the editor) |
| `owner-cta` | Owner's illustration, quote (`text`, line breaks kept), signature (`subtext`) and repeater `buttons` (`link` + `style`: `primary` \| `outline`). Links to other domains open in a new tab. Rows without URL or title are skipped with a `WARN`. | — |

- Rhythm: these sections are joined by a single `var(--border)` line, with no `--section-gap` strip between them (`.about-intro` and `.team` are in the `section:not(...)::after` exclusions in `_base.scss`). `owner-cta` sets `--section-gap: 40px` locally, which gives the 40px strip before the footer.
- `.button--outline` (`components/_buttons.scss`): white button with a 1px blue outline drawn as an inset `box-shadow`, so it is as tall as `.button--primary`; on hover it turns blue like primary.

## Services page sections

The "Послуги" page (`/services/`, Figma desktop `3534:2180`, mobile `3589:7251`) is a regular page built from five blocks, in this order. It replaced the removed `services` post type: old `/services/<slug>/` URLs answer 301 → `/services/` (`configure/theme-hooks/redirect-legacy-services.php`, on `template_redirect` priority 9 so it runs before the core 404 slug guess). The page is not in the main menu (the design menu is Про нас / Кейси / Блог / Контакти); the 404 screen links to it.

| Slug | Contents | Script |
|------|----------|--------|
| `services-hero` | `caption` (`/*services*/`, shown uppercase) + `title` (h1; empty → `WARN`, nothing rendered), then the decorative "Клініка · огляд" dashboard (`partials/parts/clinic-dashboard.php`, `aria-hidden`; its numbers are constants in that file, not fields). The 1350×540 stage (grid + 1280×448 panel) is drawn at design size and scaled to the column width via `--dashboard-scale`; phones show it edge to edge, as the mobile frame does. The panel uses Fixel instead of the mock-up's Inter. | `dashboardScale` (ResizeObserver), `dashboardReveal` (once at 30% in view; the timeline is set in the partial as `--d` / `data-delay` per element: texts float up, menu items slide in one by one, KPI numbers and source percentages count up, chart rules draw from the left, bars rise one after another with a small overshoot, source bars fill; then `.is-live` — the bars keep moving gently, paused off screen. Transitions live on `.is-revealed` only, so the pending state hides elements at once; skipped with reduced motion) |
| `service-list` | `intro`, repeater `items` (`icon`, `title`, `text`, `image` 375×375 @2x), `default_active` (1-based, default 2; out of range → first + `WARN`), `link_icon` + `link` (last row, e.g. «Перейти до кейсів»; empty link → no row). From `tablet`: preview column (image + description of the active service, crossfade) next to the list; hover (fine pointer), focus or click makes a row active. Phones: accordion, one row open at a time, «+» → «−». Class is `.service-list`, not `.services` (that one belongs to the front page `servicesAccordion.js`). | `serviceList` |
| `capabilities` | `title`, repeater `items` (`icon`, `text`), `tasks_title`, repeater `tasks` (`label`), `button_label`. Task chips (`.task-chip`, `components/_task-chip.scss`) are toggles (`aria-pressed`; selected = blue fill — the mock-up has no selected state). «Обговоримо?» opens the contact drawer and writes `Завдання: a; b` as the first line of the CF7 `your-message` (a repeated click replaces only that line; nothing selected removes it; the visitor's own text stays). | `taskPicker` (listens on `window` capture, before `contactDrawer`) |
| `stages` | `title`, repeater `items` (`icon` 32px high, `title`, `text`). A band of the decorative grid on top (112px; 31px with a 12.5px step on phones), cards in 1 / 2 (`mobile`) / 4 (`notebook`) columns separated by 1px rules. | `cardReveal` (`.stages__item`) |
| `faq` | `title`, repeater `items` (`question`, `answer` wysiwyg), `open_first` (default on). Accordion markup is the shared `partials/parts/faq-list.php` (also used by `cpt-faq.php`), modifier `.faq--block`; no "Не знайшли відповідь?" box. | `faqAccordion` (an item rendered with `faq__item--open` starts open) |

- Decorative grid: `.grid-lines` (`components/_grid-lines.scss`, in `main.scss`) — two repeating gradients every `--grid-step` (45px), lines at 48% `#dbdbdb`, solid frame lines on both edges.
- Rhythm: `.services-hero` and `.service-list` are in the `section:not(...)::after` exclusions (joined by single rules); `capabilities` and `stages` close with a 40px strip on phones / 80px from `tablet`, `faq` with 40px before the footer.
- The editor canvas does not run section scripts, so the dashboard is not scaled there (it is clipped at its design size).

## Contact drawer and footer

The contact form is not a section and is no longer part of the footer. `partials/parts/contact-drawer.php` (rendered once from `footer.php`) shows the CF7 form from the ACF Options group `footer_contact` (Footer tab: `contact_form`; `title` / `text` override the drawer heading when filled) in an off-canvas panel: from the left on desktop, a bottom sheet on phones. `contactDrawer.js` opens it on any `[data-contact-open]` element or link ending in `#contact-form` (header CTA, mobile menu CTA, hero primary link, footer button, TOC and FAQ CTAs) and on page load when the URL hash is `#contact-form`; it closes on the close button, overlay or Esc, traps focus and pauses Lenis. If `contact_form` is empty the drawer is not rendered and `WARN [contact-drawer] footer_contact.contact_form is empty` is logged.

The footer is a three-column strip (logo + menu, "Заповнити форму" button, socials + e-mail). The "Далі буде…" cover illustration is rendered on the front page only. Analytics (GTM) is configured on the "Google / GTM" tab of the same options page.

## Adding a section

1. Create `template-parts/blocks/section-{slug}.php`. Guard: `if ( empty( $args ) || ! is_array( $args ) ) { return; }`; read fields with `starter_get_array_value( $args, 'name' )` and escape on output.
2. Optionally add `section-{slug}.scss` / `section-{slug}.js` under `assets/src/{scss,js}/template-parts/blocks/` (the JS file is only an entry — logic lives in `assets/src/js/components/`; imports via `@js` / `@scss`).
3. Create the block folder (`block.json` + `render.php`) next to the existing ones.
4. Add `group_starter_section_{slug_underscored}.json` (or build the group in the SCF admin — local JSON is saved automatically).
5. Add the slug to `starter_get_section_slugs()`.

## Content migration (front page)

`scripts/migrate-front-page-to-blocks.php` moved the old `front_page_*` fields into blocks by reading raw postmeta and translating field keys against the old group JSON. It also copied `front_page_contact` to the `footer_contact` option.

```bash
make migrate-front-page                      # dry run
make migrate-front-page args="apply"         # write post_content + footer option
make migrate-front-page args="apply cleanup" # additionally delete front_page_* meta (page + revisions)
# test on a copy: args="apply page=ID"
```

Pages that already contain `acf/section-*` are skipped (re-running is safe). A non-empty old `post_content` is backed up to `_starter_pre_blocks_content`. Take a `mysqldump` first. The script needs the removed `group_57587b53.json` (restore it from git history to re-run).

### Redesign content update (November 2026)

`scripts/update-home-redesign.php` moves the front page to the redesign content: new quote / services / why titles, icons from `static/images/home/` (sideloaded once and tagged with the `_starter_source` meta `home-redesign/<file>`, reused on re-runs), 2x hero images from `scripts/data/home-redesign/`, the hero primary link and the `header_button` option pointing to `#contact-form`. It then runs `update-cf7-contact-form.php` for the drawer form markup. Values that already match are skipped, so re-running is safe.

```bash
MSYS_NO_PATHCONV=1 make wp cmd="eval-file /scripts/update-home-redesign.php"        # dry run
MSYS_NO_PATHCONV=1 make wp cmd="eval-file /scripts/update-home-redesign.php apply"  # upload media + write
```

### About page content (November 2026)

`scripts/update-about-page.php` fills the page with slug `about` with the `about-intro`, `team` and `owner-cta` blocks and the texts from the design. Media from `scripts/data/about/` (hero image with the lettering, four team photos, the "Optimism" illustration, all 2x) is sideloaded once with alt texts and tagged `_starter_source` = `about/<file>`, so re-runs reuse the attachments. A page that already has `acf/section-*` blocks is left alone unless `force` is passed; non-empty old content is backed up to `_starter_pre_blocks_content`. The script also reports menu items named "Про нас" that do not point to the page (it does not change menus).

The Telegram / WhatsApp buttons are created with placeholder URLs (`https://t.me/`, `https://wa.me/`); replace them in the page editor.

```bash
MSYS_NO_PATHCONV=1 make wp cmd="eval-file /scripts/update-about-page.php"              # dry run
MSYS_NO_PATHCONV=1 make wp cmd="eval-file /scripts/update-about-page.php apply"        # upload media + write
MSYS_NO_PATHCONV=1 make wp cmd="eval-file /scripts/update-about-page.php apply force"  # overwrite existing blocks
```

### Services page content (November 2026)

`scripts/remove-services-cpt.php` deletes what is left of the removed `services` post type (posts with their meta, menu items pointing to them) and flushes rewrite rules. Run it first: `scripts/update-services-page.php` stops while such posts exist.

`scripts/update-services-page.php` creates (or refills) the page with slug `services` from the five blocks above with the texts of `scripts/data/services/content.php`. Service descriptions are copied once from the front page `acf/section-services` block (`items_N_text`, same order); an empty one falls back to the shared text in `content.php` with a warning. Service icons reuse the front page attachments (`_starter_source` `home-redesign/service-*.svg`); the service images and the other icons are sideloaded from `scripts/data/services/` once and tagged `services/<file>`. The FAQ has one shared answer for every question for now — replace answers and descriptions in the block editor when real texts are ready. The Rank Math (or Yoast) description is set when the plugin is active. A page that already has section blocks is left alone unless `force` is passed.

```bash
MSYS_NO_PATHCONV=1 make wp cmd="eval-file /scripts/remove-services-cpt.php"               # dry run
MSYS_NO_PATHCONV=1 make wp cmd="eval-file /scripts/remove-services-cpt.php apply"         # delete posts + flush rewrites
MSYS_NO_PATHCONV=1 make wp cmd="eval-file /scripts/update-services-page.php"              # dry run
MSYS_NO_PATHCONV=1 make wp cmd="eval-file /scripts/update-services-page.php apply"        # upload media + create the page
MSYS_NO_PATHCONV=1 make wp cmd="eval-file /scripts/update-services-page.php apply force"  # overwrite existing blocks
```

## See Also

- [Getting Started](getting-started.md) — environment, commands, verification
- [Single Pages](single-pages.md) — article layout of CPT and blog singles
- [Architecture](../.ai-factory/ARCHITECTURE.md) — theme layers and module layout
