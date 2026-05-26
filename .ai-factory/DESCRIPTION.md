# WordPress Boilerplate

## Overview
A WordPress development boilerplate combining a custom PHP theme and plugin scaffold. The theme uses Vite for modern frontend asset bundling (JS modules + SCSS), with PHP-side integration that switches between Vite's HMR dev server and production manifest. Designed as a starter kit for building WordPress sites with a clean, opinionated structure.

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
- **Linter/Formatter:** Biome.js
- **Package Manager:** npm (Node.js), Composer (PHP)
- **Integrations:** ACF Pro, Yoast SEO, jQuery (CDN)

## Architecture Notes
- Theme lives under `theme/vite-wordpress-starter-theme/` and can be deployed directly to `wp-content/themes/`
- Plugin lives under `plugin/` and can be deployed to `wp-content/plugins/`
- Vite serves assets from `localhost:5173` in dev; PHP reads `assets/dist/.vite/manifest.json` in production
- Theme configuration is modular: separate PHP files for ACF, admin, CPT/taxonomy, JS/CSS, shortcodes, utilities
- All WordPress hooks, filters, and actions are registered from `functions.php`

## Architecture
See `.ai-factory/ARCHITECTURE.md` for detailed architecture guidelines.
**Pattern:** Layered Architecture — WordPress Modular Theme/Plugin

## Non-Functional Requirements
- **Performance:** Minimal default WordPress asset loading (block library, global styles removed)
- **Security:** WordPress standard nonces, sanitization, escaping patterns apply
- **Logging:** Standard PHP error logging via WordPress debug constants
- **Build:** `npm run dev` for HMR development, `npm run build` for production
