# Architecture: Layered — WordPress Modular Theme/Plugin

## Overview
This project follows a **Layered Architecture** adapted to WordPress conventions. The structure separates concerns into distinct horizontal layers: WordPress core as the framework, modular PHP configuration files for theme behaviour, PHP templates for presentation, and a Vite-compiled frontend layer for all assets.

WordPress's hook system (actions and filters) acts as the communication bus between layers. Each concern is isolated in its own PHP file under `configure/`, and the theme's `functions.php` acts as the composition root that wires everything together via `include`.

This pattern was chosen because WordPress enforces its own structural conventions, the project has no complex domain business logic, the team is small, and the existing codebase already naturally follows this layout.

## Decision Rationale
- **Project type:** WordPress classic theme + plugin scaffold (boilerplate/starter kit)
- **Tech stack:** PHP/WordPress, Vite 8, SCSS, Biome.js, vanilla JS (ES modules)
- **Key factor:** WordPress dictates the outer structure (hooks, templates, `functions.php`); modular PHP files within `configure/` map cleanly to layered separation by concern

## Folder Structure
```
wp-boilerplate/
├── plugin/                              # Plugin layer
│   └── index.php                        # Plugin bootstrap (registers hooks, includes)
│
└── theme/vite-wordpress-starter-theme/  # Theme root
    │
    ├── functions.php                    # Composition root — includes all configure files
    │
    ├── configure/                       # Configuration/business layer
    │   ├── configure.php                # Theme setup (menus, image support, cleanup)
    │   ├── js-css.php                   # Asset registration & Vite integration
    │   ├── acf.php                      # ACF field group registration
    │   ├── cpt-taxonomy.php             # Custom Post Types and Taxonomies
    │   ├── shortcodes.php               # Shortcode definitions
    │   ├── utilities.php                # Shared helper functions
    │   └── admin.php                    # Admin-only hooks (loaded only when is_admin())
    │
    ├── *.php                            # Template layer (WordPress templates)
    │   ├── index.php                    # Default template
    │   ├── header.php / footer.php      # Layout partials
    │   └── 404.php                      # Error template
    │
    └── assets/                          # Frontend layer
        └── src/
            ├── js/
            │   ├── main.js              # JS entry point (imported by Vite)
            │   └── _general.js          # Shared JS utilities (prefixed with _)
            └── scss/
                ├── main.scss            # SCSS entry point
                ├── abstracts/           # Variables, mixins — no output
                ├── base/                # Reset, fonts, global base
                ├── components/          # Reusable UI (buttons, modals)
                ├── layout/              # Structural (header, footer, grid)
                ├── pages/               # Page-specific overrides
                └── vendors/             # Third-party style overrides
```

## Dependency Rules
Communication flows top-down through layers. Lower layers must not reference higher layers.

```
WordPress Core (framework)
    ↓
configure/ files (hook registration, feature setup)
    ↓
PHP templates (*.php — consume registered hooks and functions)
    ↓
assets/src (JS/SCSS — compiled by Vite, enqueued by js-css.php)
```

- ✅ `functions.php` may include any file in `configure/`
- ✅ `configure/` files may call WordPress core functions and register hooks
- ✅ Template files may call functions defined in `configure/utilities.php`
- ✅ `configure/js-css.php` may read `assets/dist/.vite/manifest.json` to locate compiled assets
- ✅ SCSS partials (prefixed `_`) may be imported by other SCSS files
- ❌ `configure/` files must NOT directly include or call template files
- ❌ JS/SCSS source files must NOT reference PHP — communication happens only via `wp_localize_script()`
- ❌ Admin-only logic must NOT be included outside the `is_admin()` guard in `functions.php`
- ❌ Do NOT add business logic directly to `functions.php` — keep it as pure composition root

## Layer Communication
- **PHP → Frontend:** `wp_localize_script('main', 'siteVars', [...])` in `js-css.php` passes PHP data to JS as a global
- **Frontend → PHP:** AJAX requests target `admin-ajax.php` (or REST API endpoints); the URL is passed via `siteVars.ajaxUrl`
- **PHP → PHP:** WordPress `add_action()` / `add_filter()` hooks; direct function calls for utilities
- **Dev vs Prod assets:** `VITE_BUILD` constant (set from manifest existence) switches between Vite dev server URLs and hashed production asset URLs

## Key Principles
1. **One concern per configure file** — `configure/cpt-taxonomy.php` handles CPTs only, `configure/acf.php` handles ACF only, etc. Do not mix concerns across files.
2. **functions.php is a composition root, not a logic file** — All logic lives in `configure/`; `functions.php` only includes files.
3. **SCSS follows 7-1 light pattern** — Partials are prefixed with `_`; only `main.scss` is a Vite entry point; `abstracts/` must produce no CSS output.
4. **Vite integration is the single source for all theme assets** — Never hardcode asset paths; always use the manifest-aware enqueue functions in `js-css.php`.
5. **WordPress escaping at output** — Always use `esc_html()`, `esc_url()`, `esc_attr()` when rendering any user-supplied or database-sourced content in templates.

## Code Examples

### Adding a new feature area (e.g., custom REST endpoints)
Create a new file in `configure/`:

```php
<?php
// configure/rest-api.php

function my_theme_register_rest_routes() {
    register_rest_route( 'my-theme/v1', '/posts', [
        'methods'             => WP_REST_Server::READABLE,
        'callback'            => 'my_theme_get_posts',
        'permission_callback' => '__return_true',
    ]);
}
add_action( 'rest_api_init', 'my_theme_register_rest_routes' );

function my_theme_get_posts( WP_REST_Request $request ) {
    // query and return data
}
```

Then include it in `functions.php`:
```php
include( 'configure/rest-api.php' );
```

### Passing PHP data to JavaScript via wp_localize_script
In `configure/js-css.php`, extend `siteVars`:

```php
$vars = array(
    'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
    'restBase' => esc_url_raw( rest_url() ),
    'nonce'    => wp_create_nonce( 'wp_rest' ),
);
wp_localize_script( 'main', 'siteVars', $vars );
```

Consume in `assets/src/js/main.js`:
```js
const { ajaxUrl, restBase, nonce } = window.siteVars ?? {};
```

### Adding a new SCSS component
Create `assets/src/scss/components/_card.scss` (note the `_` prefix):

```scss
// _card.scss — component styles for card UI
.card {
  padding: var(--spacing-md);
  border-radius: var(--radius-sm);
}
```

Then import it in `main.scss`:
```scss
@use 'components/card';
```

### Registering a Custom Post Type
In `configure/cpt-taxonomy.php`:

```php
function my_theme_register_project_cpt() {
    register_post_type( 'project', [
        'label'    => __( 'Projects', 'textdomaintomodify' ),
        'public'   => true,
        'supports' => [ 'title', 'editor', 'thumbnail' ],
        'rewrite'  => [ 'slug' => 'projects' ],
    ]);
}
add_action( 'init', 'my_theme_register_project_cpt' );
```

## Anti-Patterns
- ❌ **Writing logic in `functions.php`** — it should only contain `include` statements; logic belongs in `configure/`
- ❌ **Hardcoding asset URLs** — always use `DIST_URI` + manifest lookup from `js-css.php`, never raw file paths
- ❌ **Skipping the `is_admin()` guard** for admin-only code — loading admin hooks on the frontend adds unnecessary overhead
- ❌ **Importing `main.scss` from another SCSS partial** — `main.scss` is an entry point, not a partial; only `@use` other partials from it
- ❌ **Adding business logic to PHP templates** — templates should only call pre-registered functions; keep templates as thin presentational wrappers
- ❌ **Registering hooks inside conditional blocks at include time** (e.g., `if (condition) { add_action(...) }`) — register hooks unconditionally and apply conditions inside the callback
