[← Getting Started](getting-started.md) · [Back to README](../README.md)

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

- `starter_vite_enqueue_editor_canvas_assets()` (`configure/js-css.php`) loads, for the **page** editor only, `main.scss`, every section stylesheet and `editor-section-blocks.scss`; `editor-link-guard.js` is loaded for every post type so links and form submits in block previews cannot navigate the canvas away.
- `starter_strip_admin_styles_from_page_canvas()` (filter `block_editor_settings_all`) removes wp-admin stylesheets and the article editor stylesheet from the page canvas so previews match the front end.
- Section JS does not run in the editor; reveal-style hidden states must be applied by JS only, so previews stay visible.

## Current sections (front page)

| Slug | Contents | Script |
|------|----------|--------|
| `home-hero` | Hero with glitch image | `heroTitle` (title scramble) |
| `clinics` | Clinics grid + aside | — |
| `quote` | Quote with animated gears | — |
| `problems-solutions` | Problems cards + banner text + solutions (shared scroll wrapper) | — |
| `services` | Services list with image | accordion + glitch image |
| `cases` | Cases slider (Swiper) | `casesSlider` |
| `process` | Process header + steps (anchor `#process`) | — |
| `why` | Chat + reasons | `whySection` |

Anchors `#about`, `#services`, `#cases`, `#process` are part of the markup; the menu and `activeNav` rely on them.

## Footer contact form

The footer form is not a section: it comes from the ACF Options field group `footer_contact` (Footer tab: `title`, `text`, `contact_form`) via `starter_get_option()`, so it renders on every page. Analytics (GTM) is configured on the "Google / GTM" tab of the same options page.

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

## See Also

- [Getting Started](getting-started.md) — environment, commands, verification
- [Architecture](../.ai-factory/ARCHITECTURE.md) — theme layers and module layout
