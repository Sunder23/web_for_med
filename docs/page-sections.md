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
- Section JS (AOS, reveal animations, accordions) does not run in the editor. `editor-section-blocks.scss` resets `[data-aos]` and other pre-reveal hidden states (`.hero__title`, `.hero__button`, `.why-message`) to their final visible state — add new ones there. Scroll-effect layers (`fixed` / `sticky`, currently `.problems`) are put back in flow there too, otherwise they cover neighbouring blocks and their fields cannot be selected.
- Carousels are the exception: `editor-section-blocks.js` (re)initialises them in the canvas whenever ACF re-renders a preview and destroys detached instances. To add a slider section, make its init accept a root element and register it in the `CAROUSELS` list.

## Current sections (front page)

| Slug | Contents | Script |
|------|----------|--------|
| `home-hero` | Hero with glitch image (`image` desktop, `image_mobile` below `mobile`, falls back to `image`); primary CTA opens the contact drawer | `heroTitle` (title scramble) |
| `clinics` | Clinics grid 3×2 + aside (2 columns on phones, aside full width) | — |
| `quote` | Centred quote text | — |
| `problems-solutions` | Problems cards + banner text + solutions with 50px icons (shared scroll wrapper, `.problems` sticky from `tablet`) | — |
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

## See Also

- [Getting Started](getting-started.md) — environment, commands, verification
- [Single Pages](single-pages.md) — article layout of CPT and blog singles
- [Architecture](../.ai-factory/ARCHITECTURE.md) — theme layers and module layout
