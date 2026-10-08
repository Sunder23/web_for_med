# AGENTS.md

> Keep this file up to date as the project structure evolves. It is the primary navigation map for AI agents working in this repository.

## Project Overview
A hybrid WordPress theme (Vite + SCSS frontend) with a plugin scaffold and a Docker dev environment. Pages are built from ACF section blocks (`acf/section-*`); single CPT, blog and archive views are plain PHP templates that reuse partials directly.

## Tech Stack
- **Language:** PHP 8.2+, JavaScript (ES modules)
- **CMS:** WordPress (Secure Custom Fields as ACF replacement, Contact Form 7, Yoast SEO)
- **Frontend Build:** Vite 8, SCSS (Sass), `postcss-pxtorem` on build
- **Linters:** Biome.js (JS/JSON), Stylelint + stylelint-scss (SCSS), PHPCS (WordPress standard) + PHPStan level 5
- **Package Manager:** npm (frontend), Composer (PHP tooling)
- **Environment:** Docker Compose (`docker/compose.yml`: db, wordpress, wpcli, phpmyadmin, mailpit)

## Project Structure
```
wp-boilerplate/
├── docker/                          # compose.yml (name: web_for_med), .env(.example), uploads.ini
├── mu-plugins/mailpit.php           # Local mail catcher (local env only)
├── plugin/                          # WordPress plugin scaffold
├── scripts/                         # WP-CLI import/migration scripts (mounted at /scripts)
├── theme/vite-wordpress-starter-theme/
│   ├── functions.php                # Composition root ($starter_modules list)
│   ├── configure/
│   │   ├── post-types.php + post-types/       # services, directions, cases
│   │   ├── taxonomies.php + taxonomies/       # (empty)
│   │   ├── theme-hooks.php + theme-hooks/     # one hook concern per file
│   │   ├── utilities.php + helpers/           # starter_get_* helpers
│   │   ├── shortcodes.php, ajax.php           # empty aggregators (+ folders)
│   │   ├── js-css.php               # Vite integration + editor canvas assets
│   │   ├── analytics.php            # GTM via ACF Options
│   │   ├── optimize.php             # Optional front-end trims (flags)
│   │   ├── acf.php                  # Local JSON save/load point
│   │   ├── acf-blocks.php           # Content blocks (configure/acf/acf-blocks/*)
│   │   ├── section-blocks.php       # Page section engine (configure/acf/section-blocks/*)
│   │   ├── toc.php, admin.php
│   │   └── acf/{acf-json,acf-blocks,section-blocks}/
│   ├── template-parts/blocks/       # section-{slug}.php section markup
│   ├── partials/                    # breadcrumbs.php, header/{header,logo}.php, parts/*
│   ├── page.php, single*.php, archive-*.php, home.php, header.php, footer.php, 404.php
│   ├── assets/src/js/               # main.js, single-cpt.js, single-post.js, components/, template-parts/blocks/
│   ├── assets/src/scss/             # main.scss + per-template/section entries, components/, mixins/
│   ├── assets/dist/                 # Vite build output (gitignored)
│   ├── vite.config.js, package.json, biome.json, .stylelintrc.json
│   └── composer.json, phpcs.xml.dist, phpstan.neon, phpstan-baseline.neon, phpstan-bootstrap.php
├── docs/                            # getting-started.md, page-sections.md
├── .ai-factory/                     # AI agent context and plans
├── .mcp.json
└── Makefile                         # docker compose -f docker/compose.yml shortcuts
```

## Key Entry Points
| File | Purpose |
|------|---------|
| `theme/vite-wordpress-starter-theme/functions.php` | Theme bootstrap — defines `WFB_THEME_*`, requires modules |
| `.../configure/js-css.php` | Vite integration (`VITE_BUILD` / `VITE_DEV`, manifest, register helpers) |
| `.../configure/section-blocks.php` | Section slugs, rendering, conditional assets |
| `.../configure/theme-hooks/enqueue-listing-templates-assets.php` | Per-template entries (archive, single-cpt, single-post) |
| `.../vite.config.js` | Flat SCSS/JS entries + `template-parts/blocks/` level |
| `.../assets/src/js/main.js`, `.../scss/main.scss` | Global entries |
| `docker/compose.yml` | Dev environment |

## Documentation
| Document | Path | Description |
|----------|------|-------------|
| README | README.md | Project landing page |
| Getting Started | docs/getting-started.md | Environment, commands, asset modes |
| Page Sections | docs/page-sections.md | `acf/section-*` blocks and migration |
| Single Pages | docs/single-pages.md | Article layout for CPT and blog singles |
| AI Context | .ai-factory/DESCRIPTION.md | Project specification and tech stack |
| Architecture | .ai-factory/ARCHITECTURE.md | Layers, module layout, asset pipeline |
| Base Rules | .ai-factory/rules/base.md | Naming and coding conventions |

## AI Context Files
| File | Purpose |
|------|---------|
| AGENTS.md | This file — project map for AI agents |
| .ai-factory/DESCRIPTION.md | Full project description and tech stack |
| .ai-factory/ARCHITECTURE.md | Architecture guidelines and patterns |
| .ai-factory/config.yaml | AI Factory run configuration |
| .ai-factory/rules/base.md | Coding conventions |
| .ai-factory/plans/ | Implementation plans |

## Commands
- `docker compose -f docker/compose.yml up -d` (or `make up`) — start the environment (WP :8080, phpMyAdmin :8081, Mailpit :8025)
- `make wp cmd="…"` — WP-CLI (`wpcli` service); on Windows Git Bash prefix raw calls with `MSYS_NO_PATHCONV=1`
- `npm run dev` / `npm run build` / `npm run lint` (Biome + Stylelint) — in the theme directory; `npm run lint:css` / `format:css` for SCSS only
- `composer lint` — PHPCS + PHPStan

## Agent Rules
- Decompose multi-step shell commands into separate steps — do not chain with `&&` in a single command
  - Incorrect (combined): `git checkout main && git pull`
  - Correct (decomposed): First `git checkout main`, then `git pull origin main`
- When modifying theme PHP, check `functions.php` and the aggregator (`theme-hooks.php`, `utilities.php`, …) to see which files are loaded; new per-item files must be added to the aggregator array
- Vite asset paths differ between dev and prod — check `VITE_BUILD` / `VITE_DEV` before assuming asset URLs
- WordPress hooks must be registered at file scope (not inside conditionals) unless using `is_admin()` guards
- Use the `starter_` prefix and the `vite-starter` text domain; run `composer lint` and `npm run lint` before committing
- Page-specific styles/scripts depend on `main` and are enqueued at `wp_enqueue_scripts` priority 110
- Do not assemble single CPT / blog / archive templates from section blocks
- SCSS: mobile first only via `@include breakpoint(...)`, nesting ≤ 3 selector levels (at-rules don't count), BEM without `s-`/`l-`/`c-`/`svc-`/`br-` prefixes, `is-*` for JS states — see `.ai-factory/rules/base.md`
