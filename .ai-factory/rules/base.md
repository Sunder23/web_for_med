# Project Base Rules

> Auto-detected conventions from codebase analysis. Edit as needed.

## Naming Conventions
- PHP files: `snake_case.php` (e.g., `cpt-taxonomy.php`, `js-css.php`)
- PHP functions: `snake_case` prefixed with context (e.g., `_custom_theme_register_menu`, `custom_setup`, `add_vite_assets`)
- PHP constants: `UPPER_SNAKE_CASE` (e.g., `DIST_DIR`, `VITE_SERVER`, `VITE_BUILD`)
- JS files: `camelCase.js` (e.g., `_general.js`, `main.js`)
- SCSS files: `_kebab-case.scss` with leading underscore for partials (e.g., `_variables.scss`, `_buttons.scss`)
- SCSS entry points: no underscore prefix (e.g., `main.scss`)

## Module Structure
- `theme/vite-wordpress-starter-theme/` — WordPress theme root (deploy to `wp-content/themes/`)
  - `assets/src/js/` — JavaScript source files
  - `assets/src/scss/` — SCSS source (abstracts, base, components, layout, pages, vendors)
  - `assets/dist/` — Vite build output (gitignored, generated)
  - `configure/` — Modular PHP configuration files (one concern per file)
  - `functions.php` — Theme entry point, includes all configure files
- `plugin/` — WordPress plugin scaffold (deploy to `wp-content/plugins/`)
- `.ai-factory/` — AI agent context and artifacts

## Error Handling
- PHP: WordPress-style checks (`file_exists`, `is_array`, `isset`) before acting on data
- No throwing exceptions in theme/plugin code — use WordPress logging patterns
- Graceful degradation: if Vite manifest is missing, fall back silently (return null)

## WordPress Conventions
- Register hooks/filters with `add_action()` / `add_filter()` at the bottom of each function file
- Use `wp_enqueue_script()` and `wp_enqueue_style()` for all assets
- Escape all output: `esc_url()`, `esc_attr()`, `esc_html()` as appropriate
- Prefix all custom function names to avoid collisions

## Frontend Build
- Development: `npm run dev` (starts Vite dev server on port 5173 with HMR)
- Production: `npm run build` (outputs hashed assets + manifest to `assets/dist/`)
- SCSS is compiled by Vite via the `sass` package; no separate SCSS build step
- Biome.js handles JS/TS linting and formatting
