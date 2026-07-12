# Plan: Replace CPT sidebar cards with a single ACF/SCF "Info Block"

**Branch:** `feature/acf-info-block`
**Created:** 2026-07-12
**Base:** `main`

## Goal

Remove the `service_sidebar_who` / `service_sidebar_needs` (and matching `direction_sidebar_*`)
ACF sidebar fields and their rendering. Replace them with a single block-editor block —
**`acf/info-block`** (title + text) — registered via `block.json` through SCF (Secure Custom
Fields, ACF fork). Editors insert it directly into post content where needed.

## Settings

- **Testing:** no (manual verification only — theme has no test infrastructure)
- **Logging:** standard (guard checks + silent graceful degradation, matching `vite_manifest()` style)
- **Docs:** no — warn-only (`WARN [docs]`), no mandatory checkpoint

## Roadmap Linkage

- Milestone: "none"
- Rationale: no ROADMAP.md exists in this project

## Research Context

`.ai-factory/RESEARCH.md` Active Summary covers an unrelated topic (`acf-from-html` skill) and was
not used, except one carried-over convention: **deterministic 8-hex CRC32 keys** for ACF
group/field JSON (`group_XXXXXXXX` / `field_XXXXXXXX`), which this plan reuses for the new field group.

## Key Design Decisions

1. **Block convention:** `theme/vite-wordpress-starter-theme/acf-blocks/info-block/` holding
   `block.json`, `render.php`, `info-block.scss`. Registered with `register_block_type()` on
   `init` from a new `configure/blocks.php` (scans `acf-blocks/*/block.json` so future blocks
   auto-register).
2. **Styles in editor AND frontend:** `block.json` references a registered style handle
   (`"style": "info-block"`). `configure/blocks.php` registers that handle with a
   manifest-aware URI — dev: `VITE_SERVER . '/acf-blocks/info-block/info-block.scss'`
   (Vite serves compiled CSS for `Accept: text/css` requests, same mechanism as `main.scss` today);
   prod: manifest lookup via `vite_manifest_uri()`. WordPress then loads it on the frontend and
   inside the editor iframe automatically. Registration must NOT be behind `is_admin()`.
3. **SCSS pipeline:** `vite.config.js` gets a second entry helper scanning
   `acf-blocks/*/*.scss` (non-underscore), so block SCSS compiles to hashed CSS with a
   manifest key of `acf-blocks/info-block/info-block.scss`.
4. **Self-contained block styles:** `.info-block` BEM styles must not depend on `main.scss`
   classes (`.info-card` etc.) because `main.scss` is not loaded in the editor. Use
   `--wp--preset--*` vars from `theme.json` (available in editor natively, on frontend via
   `output_theme_json_preset_vars()`).
5. **Fields:** new acf-json group ("Block: Info Block"), location `block == acf/info-block`,
   fields `title` (text) + `text` (wysiwyg, basic toolbar, no media). Same JSON format for
   SCF and ACF Pro.
6. **Scope includes Directions:** `single-directions.php` uses the identical sidebar pattern —
   both singles and `content-with-toc.php` are cleaned. `single.php` / `single-cases.php`
   already call the part without args.
7. **No data migration:** existing `*_sidebar_*` postmeta stays in the DB harmlessly;
   editors re-enter content as blocks. (A migration script exists as precedent if ever needed —
   out of scope.)

## Tasks

### Phase 1 — Block infrastructure & the block itself

- [x] **Task 1:** Add ACF block infrastructure
  - `vite.config.js`: entry helper scanning `acf-blocks/*/` for non-underscore `.scss`
  - New `configure/blocks.php`: `register_block_type()` loop on `init` + `wp_register_style('info-block', …)`
    with dev/prod (manifest) URI, silent skip when manifest entry missing
  - `functions.php`: unconditional `include( 'configure/blocks.php' );` after `js-css.php`
- [x] **Task 2:** Create `acf-blocks/info-block/` *(blocked by 1)*
  - `block.json` — `acf/info-block`, `"style": "info-block"`, `acf.renderTemplate: render.php`, mode preview, anchor support
  - `render.php` — `.info-block` / `__title` (h3) / `__text` markup; `esc_html()` title, `wp_kses_post()` text; `$is_preview` placeholder when empty
  - `info-block.scss` — self-contained BEM styles using `--wp--preset--*` vars
- [x] **Task 3:** Create acf-json field group for the block *(blocked by 2)*
  - `acf-json/group_XXXXXXXX.json`, deterministic CRC32 key, title/text fields, block location rule

### Phase 2 — Remove old sidebars & verify

- [x] **Task 4:** Remove sidebar fields from templates
  - `single-services.php:6-7,52-58`, `single-directions.php:6-7,36-43` — drop fields + args
  - `template-parts/content-with-toc.php:16-17,42-60` — drop args handling + sidebar-card loop, update doc comment
- [x] **Task 5:** Clean field definitions & SCSS
  - `acf-json/group_9033ee91.json` — remove `field_be8bbd50`, `field_142218cc` (+ empty Sidebar tab if any)
  - `acf-json/group_41eea81b.json` — remove `direction_sidebar_who` / `direction_sidebar_needs`
  - `assets/src/scss/pages/_direction.scss` — remove `.sidebar-card` styles
- [x] **Task 6:** Build & manual verification *(blocked by 1-5)*
  - `npm run build` → manifest contains `acf-blocks/info-block/info-block.scss`
  - Editor: block in inserter, fields editable, styled preview (dev + prod modes)
  - Frontend: block renders styled in `.entry-content`; no sidebar cards; TOC intact; no PHP notices
  - `grep` confirms no `service_sidebar_*` / `direction_sidebar_*` references remain

## Commit Plan

- **Commit 1** (after Tasks 1-3): `feat(theme): add acf/info-block block with block.json registration and Vite SCSS pipeline`
- **Commit 2** (after Tasks 4-6): `refactor(theme): replace CPT sidebar who/needs cards with info-block content block`

## Verification Checklist (Task 6 detail)

1. Dev mode (`npm run dev`, no manifest): editor iframe loads block CSS from Vite server; frontend too
2. Prod mode (`npm run build`): hashed CSS enqueued via manifest on both surfaces
3. Services + Directions singles render without sidebar cards; `single.php` / `single-cases.php` unaffected
4. SCF admin shows the new "Block: Info Block" field group synced from acf-json
