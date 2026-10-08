# Project Base Rules

> Conventions of the hybrid theme. Edit as needed.

## Naming Conventions
- PHP files: `kebab-case.php` inside `configure/` sub-folders (e.g. `theme-hooks/register-menus.php`, `helpers/get-option.php`)
- PHP functions: `snake_case` with the `starter_` prefix (e.g. `starter_get_option`, `starter_vite_register_style`)
- PHP constants: `UPPER_SNAKE_CASE` — theme `WFB_THEME_PATH/URI/VERSION`, Vite `VITE_*`
- Text domain: `vite-starter`
- Blocks: `acf/section-{slug}` (page sections), `acf/{slug}` (content blocks); ACF field keys for sections `field_starter_section_{slug_underscored}_{name path}`
- JS files: `camelCase.js` for components; entries `kebab-case.js` (`single-cpt.js`, `section-cases.js`)
- SCSS: partials `_kebab-case.scss`; entries without underscore (`main.scss`, `archive.scss`, `block-{slug}.scss`, `template-parts/blocks/section-{slug}.scss`)
- Imports in JS/SCSS go through the aliases `@js`, `@scss`, `@src`, `@`

## Module Structure
- `theme/vite-wordpress-starter-theme/functions.php` — composition root, explicit `$starter_modules` list
- `configure/{post-types,taxonomies,theme-hooks,utilities,shortcodes,ajax}.php` — aggregators with explicit arrays of per-item files in sub-folders (no `glob()`)
- `configure/acf/{acf-json,acf-blocks,section-blocks}` — field groups and block definitions
- `template-parts/blocks/section-{slug}.php` — section markup (takes everything via `$args`)
- `partials/` — breadcrumbs, header, shared parts
- `assets/src/` — flat SCSS/JS; `assets/dist/` is generated and gitignored
- `docker/`, `mu-plugins/`, `plugin/`, `scripts/` — environment, local mail, plugin scaffold, WP-CLI scripts

## Error Handling & Logging
- WordPress-style checks (`file_exists`, `is_array`, `isset`) before acting on data; no exceptions in theme code
- Missing Vite manifest → degrade silently; missing entries log once: `error_log( 'WARN [vite] manifest entry missing: <key>' )`
- WARN-only logging with an `[area]` tag (`[vite]`, `[acf-blocks]`, `[section-blocks]`, `[footer]`); WP-CLI scripts use `WP_CLI::log()` / `WP_CLI::warning()`
- JS: only `utils/logDebug.js`, no new `console.log`

## WordPress Conventions
- Hooks are registered at file scope at the bottom of each function file; conditions go inside the callback
- Assets via `starter_vite_register_style()` / `starter_vite_register_script()`; per-template and per-section handles depend on `main` and are enqueued on `wp_enqueue_scripts` priority 110
- Escape all output (`esc_html`, `esc_attr`, `esc_url`, `wp_kses_post`); section templates start with `if ( empty( $args ) || ! is_array( $args ) ) { return; }`
- Footer / site-wide data comes from ACF Options via `starter_get_option()`, never from a specific page
- Single CPT, blog and archive templates are not built from section blocks

## Tooling
- `npm run dev` / `npm run build` (Vite), `npm run lint` / `npm run format` (Biome)
- `composer lint` = PHPCS (WordPress standard, text domain `vite-starter`) + PHPStan level 5 with `phpstan-baseline.neon`
- Docker: `docker compose -f docker/compose.yml …` (`make up|down|wp`); WP-CLI service is `wpcli`
