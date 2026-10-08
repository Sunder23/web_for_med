# web4med — Hybrid WordPress Theme

## Overview
A hybrid WordPress theme (pages from `acf/section-*` ACF blocks, CPT/blog/archives as PHP templates) plus a plugin scaffold and a Docker dev environment. The theme uses Vite for modern frontend asset bundling (JS modules + SCSS), with PHP-side integration that switches between Vite's HMR dev server and production manifest. Designed as a starter kit for building WordPress sites with a clean, opinionated structure.

## Core Features
- Custom WordPress theme with Vite + SCSS build pipeline
- Hot Module Replacement (HMR) in development via Vite dev server
- Production build with hashed assets and manifest-driven PHP enqueuing
- Modular SCSS architecture (abstracts, base, components, layout, pages)
- ACF (Advanced Custom Fields) integration placeholder
- Custom Post Types and Taxonomies scaffold
- WordPress performance optimizations (removed default styles, emoji, jQuery migrate)
- Custom admin branding and Yoast SEO positioning
- Plugin scaffold (`plugin/`) for custom functionality

## Tech Stack
- **Language:** PHP 8+, JavaScript (ES modules)
- **CMS:** WordPress
- **Frontend Build:** Vite 8
- **CSS Preprocessor:** SCSS (Sass)
- **Linters:** Biome.js (JS/JSON), Stylelint + stylelint-scss (SCSS), PHPCS + PHPStan (PHP)
- **Package Manager:** npm (Node.js), Composer (PHP)
- **Integrations:** Secure Custom Fields (ACF-compatible), Contact Form 7, Yoast SEO, bundled WP jQuery (no migrate), Fancybox v6 (@fancyapps/ui — content image lightbox)

## Architecture Notes
- Theme lives under `theme/vite-wordpress-starter-theme/` and can be deployed directly to `wp-content/themes/`
- Plugin lives under `plugin/` and can be deployed to `wp-content/plugins/`
- Vite serves assets from `localhost:5173` in dev; PHP reads `assets/dist/.vite/manifest.json` in production
- Theme configuration is modular: `functions.php` requires `configure/*.php`; aggregators list per-item files explicitly
- Function prefix `starter_`, constants `WFB_*` / `VITE_*`, text domain `vite-starter`

## Architecture
See `.ai-factory/ARCHITECTURE.md` for detailed architecture guidelines.
**Pattern:** Hybrid Layered Architecture — WordPress Modular Theme/Plugin

## Non-Functional Requirements
- **Performance:** Minimal default WordPress asset loading (block library, global styles removed)
- **Security:** WordPress standard nonces, sanitization, escaping patterns apply
- **Logging:** Standard PHP error logging via WordPress debug constants
- **Build:** `npm run dev` for HMR development, `npm run build` for production
