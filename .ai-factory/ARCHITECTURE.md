# Architecture: Hybrid Layered — WordPress Modular Theme

## Overview
The theme is a **hybrid**: `page` content is assembled from ACF section blocks (`acf/section-{slug}`), while single CPT, blog and archive views are plain PHP templates that reuse partials directly. The structure stays layered: WordPress core, modular PHP configuration under `configure/`, PHP templates/partials, and a Vite-compiled frontend.

WordPress hooks (actions/filters) are the communication bus. `functions.php` is a pure composition root: it defines `WFB_THEME_*` constants and requires modules from an explicit list. Aggregator files (`post-types.php`, `taxonomies.php`, `theme-hooks.php`, `utilities.php`, `shortcodes.php`, `ajax.php`) each hold an explicit array of per-item files in a sub-folder — no `glob()` there. The only `glob()` calls are the block registrars (`acf-blocks.php`, `section-blocks.php`).

## Folder Structure
```
wp-boilerplate/
├── docker/                              # compose.yml (name: web_for_med), .env(.example), uploads.ini
├── mu-plugins/mailpit.php               # local mail catcher (WP_ENVIRONMENT_TYPE=local only)
├── plugin/                              # plugin scaffold
├── scripts/                             # WP-CLI import / migration scripts
└── theme/vite-wordpress-starter-theme/
    ├── functions.php                    # composition root ($starter_modules)
    ├── configure/
    │   ├── post-types.php  + post-types/{services,directions,cases}.php
    │   ├── taxonomies.php  + taxonomies/
    │   ├── theme-hooks.php + theme-hooks/*.php   # one hook concern per file
    │   ├── utilities.php   + helpers/*.php       # starter_get_* helpers
    │   ├── shortcodes.php, ajax.php (+ folders)
    │   ├── js-css.php                   # Vite integration, editor canvas assets
    │   ├── analytics.php, optimize.php  # GTM (ACF Options), optional trims
    │   ├── acf.php                      # local JSON save/load point
    │   ├── acf-blocks.php               # content blocks (glob)
    │   ├── section-blocks.php           # page section engine (glob)
    │   ├── toc.php, admin.php
    │   └── acf/
    │       ├── acf-json/                # field groups (group_starter_section_*, options, CPT groups)
    │       ├── acf-blocks/<slug>/       # content blocks (block.json, render.php)
    │       └── section-blocks/section-<slug>/   # page section blocks
    ├── template-parts/blocks/section-<slug>.php # section markup (blocks + direct reuse)
    ├── partials/                        # breadcrumbs, header/{header,logo}, parts/*
    ├── page.php, single*.php, archive-*.php, home.php, header.php, footer.php, 404.php
    └── assets/src/
        ├── js/        main.js, single-cpt.js, single-post.js, editor-link-guard.js,
        │              components/, utils/, template-parts/blocks/section-<slug>.js
        └── scss/      main.scss, single-cpt.scss, single-post.scss, archive.scss,
                       block-<slug>.scss, editor-section-blocks.scss, _tokens/_fonts/_base/_animations,
                       components/, mixins/, vendors/, template-parts/blocks/section-<slug>.scss
```

## Dependency Rules
```
WordPress Core
    ↓
configure/ modules (hooks, helpers, block registration)
    ↓
templates (page.php, single*.php …) and partials / template-parts
    ↓
assets/src (compiled by Vite, enqueued by js-css.php and per-template hooks)
```

- ✅ `functions.php` requires modules; aggregators require their per-item files from an explicit array
- ✅ Templates call `starter_*` helpers and `get_template_part()` with `$args`
- ✅ Section templates (`template-parts/blocks/section-*.php`) take all data through `$args` — usable by a block or by hand-built args
- ✅ Page-specific assets are enqueued with `array( 'main' )` as dependency, on `wp_enqueue_scripts` priority 110 (after `main` at 100)
- ❌ No logic in `functions.php`; no `glob()` in aggregators
- ❌ Single CPT / blog / archive templates are not assembled from section blocks
- ❌ JS/SCSS must not reference PHP; data goes through `wp_localize_script()`
- ❌ Imports in JS/SCSS use `@js` / `@scss` aliases, not relative climbing

## Asset Pipeline
- **Entries** (`vite.config.js`): every non-underscore `.scss` / `.js` in `assets/src/{scss,js}/` plus one flat level `template-parts/blocks/`. Manifest keys are `assets/src/{js,scss}/<path>`.
- **Global** (`main.scss`, `main.js`): tokens, fonts, base, header/footer/forms/grid, shared components, smooth scroll, mobile nav, footer animations.
- **Per template**: `archive.scss`, `single-cpt.scss/js`, `single-post.scss/js` (see `theme-hooks/enqueue-listing-templates-assets.php`).
- **Per section block**: `section-{slug}.scss/js`, enqueued only when the block is on the page (`section-blocks.php`).
- **JS-imported CSS** (vendors) is resolved through manifest `css` + `imports` (`starter_vite_entry_css_files()`).
- **Modes**: `VITE_BUILD` (manifest exists) → hashed files; `VITE_DEV` (no manifest, local env) → `localhost:5173`.
- `postcss-pxtorem` runs on `vite build` only.

## Key Principles
1. **One concern per file** under `configure/`; per-item files hold one hook/helper/post type.
2. **functions.php is a composition root.**
3. **Prefix**: functions `starter_*`, constants `WFB_*` / `VITE_*`, text domain `vite-starter`, block names `acf/section-*`.
4. **Escape at output**; WARN-level `error_log( 'WARN [area] … ' )` only on failures.
5. **Footer data lives in ACF Options** (`footer_contact`), never on a particular page.

## Code Examples

### Adding a helper
```php
// configure/helpers/get-thing.php
function starter_get_thing( $id ) { /* … */ }
```
Then append `'get-thing.php'` to the array in `configure/utilities.php`.

### Adding a section block
See [docs/page-sections.md](../docs/page-sections.md) — template part, scss/js entries, block folder, field group, slug in `starter_get_section_slugs()`.

## Anti-Patterns
- ❌ Hardcoding asset URLs — use `starter_vite_register_style()` / `starter_vite_register_script()`
- ❌ Enqueueing page-specific assets at priority < 101 with a `main` dependency (WP 6.9 notice: dependency not registered)
- ❌ Reading front-page ACF fields from other templates — use Options
- ❌ Hooks registered inside conditionals at include time (apply the condition inside the callback)
