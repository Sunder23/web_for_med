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

## SCSS Conventions
- **Mobile first:** base styles target the smallest screen; widen only through `@include breakpoint(small|mobile|tablet|laptop|desktop)` from `mixins/_breakpoint.scss` (`@use "../mixins/breakpoint" as *;` in every file that needs it). No raw `@media (max-width|min-width|width: …)`; non-width queries (`prefers-reduced-motion`, `hover`, `orientation`) are fine.
- Put the breakpoint inside the selector it modifies (`&__el { …; @include breakpoint(tablet) { … } }`), not in a block at the end of the file.
- **Nesting ≤ 3 selector levels:** `.block` → `&__el` → `&--mod` / `&:hover` / `&::before`. At-rules (`@include`, `@media`, …) do not count. Instead of a block cascading into another block (`.why .container`), use a mix (`class="container why__container"`) or a modifier.
- **BEM without prefixes:** `block`, `block__element`, `block--modifier`, kebab-case, full words — no `s-`, `l-`, `c-`, `svc-`, `br-`. One block is defined in one file.
- JS-toggled states stay `is-*` (`is-open`, `is-active`, …). Third-party classes are not renamed: `wpcf7-*`, `wp-*`, `has-children`, `editor-styles-wrapper`, `lightbox-trigger`, `scrim`, `close-button`, `aos-*`, `swiper-*`. WP menu markup gets BEM classes from `configure/theme-hooks/nav-menu-bem-classes.php`.
- Enforced by Stylelint (`.stylelintrc.json`: `max-nesting-depth`, `media-feature-name-disallowed-list`, `selector-class-pattern`). The legacy archive partials (`_archive`, `_cpt-common` — archive hero and card grid) are temporarily in `ignoreFiles` until they are replaced by new styles — remove them from the list when that happens.

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
- `npm run dev` / `npm run build` (Vite)
- `npm run lint` = `lint:js` (Biome) + `lint:css` (Stylelint); `npm run format` = Biome fixes + `format:css` (Stylelint `--fix`)
- `composer lint` = PHPCS (WordPress standard, text domain `vite-starter`) + PHPStan level 5 with `phpstan-baseline.neon`
- Docker: `docker compose -f docker/compose.yml …` (`make up|down|wp`); WP-CLI service is `wpcli`
