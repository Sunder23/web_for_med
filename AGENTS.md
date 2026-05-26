# AGENTS.md

> Keep this file up to date as the project structure evolves. It is the primary navigation map for AI agents working in this repository.

## Project Overview
A WordPress development boilerplate combining a custom PHP theme (Vite + SCSS build pipeline) and a plugin scaffold. Designed as a starter kit for WordPress sites with a modern frontend workflow and opinionated PHP structure.

## Tech Stack
- **Language:** PHP 8+, JavaScript (ES modules)
- **CMS:** WordPress
- **Frontend Build:** Vite 8
- **CSS Preprocessor:** SCSS (Sass)
- **Linter/Formatter:** Biome.js
- **Package Manager:** npm (frontend), Composer (PHP)
- **Integrations:** ACF Pro, Yoast SEO, jQuery (CDN)

## Project Structure
```
wp-boilerplate/
├── plugin/                          # WordPress plugin scaffold
│   └── index.php                    # Plugin entry point (placeholder)
├── theme/
│   ├── index.php                    # Theme directory placeholder
│   └── vite-wordpress-starter-theme/  # WordPress classic theme
│       ├── functions.php            # Theme entry — includes all configure files
│       ├── configure/               # Modular theme configuration
│       │   ├── configure.php        # Theme setup (menus, image sizes, cleanup)
│       │   ├── js-css.php           # Vite asset enqueuing (dev + prod)
│       │   ├── acf.php              # ACF custom field hooks
│       │   ├── admin.php            # Admin-only customizations
│       │   ├── cpt-taxonomy.php     # Custom Post Types and Taxonomies
│       │   ├── shortcodes.php       # Custom shortcodes
│       │   └── utilities.php        # Helper functions
│       ├── assets/
│       │   └── src/
│       │       ├── js/              # JavaScript source (main.js entry point)
│       │       └── scss/            # SCSS source (main.scss entry point)
│       │           ├── abstracts/   # Variables, mixins, functions
│       │           ├── base/        # Reset, fonts, base styles
│       │           ├── components/  # Buttons, modals, reusable UI
│       │           ├── layout/      # Header, footer, grid, forms
│       │           ├── pages/       # Page-specific styles
│       │           └── vendors/     # Third-party overrides
│       ├── assets/dist/             # Vite build output (gitignored)
│       ├── vite.config.js           # Vite build configuration
│       ├── package.json             # Node dependencies
│       ├── biome.json               # Biome linter/formatter config
│       ├── composer.json            # PHP package definition
│       ├── 404.php                  # 404 template
│       ├── header.php               # Theme header template
│       ├── footer.php               # Theme footer template
│       └── index.php                # Default theme template
├── .ai-factory/                     # AI agent context and artifacts
│   ├── DESCRIPTION.md               # Project specification
│   ├── ARCHITECTURE.md              # Architecture guidelines
│   ├── config.yaml                  # AI Factory configuration
│   └── rules/
│       └── base.md                  # Detected coding conventions
├── .mcp.json                        # MCP server configuration
└── .ai-factory.json                 # AI Factory skills registry
```

## Key Entry Points
| File | Purpose |
|------|---------|
| `theme/vite-wordpress-starter-theme/functions.php` | Theme bootstrap — registers all hooks and includes configure files |
| `theme/vite-wordpress-starter-theme/configure/js-css.php` | Vite asset integration (dev server vs production manifest) |
| `theme/vite-wordpress-starter-theme/vite.config.js` | Vite build config — input entries, output paths, HMR for PHP files |
| `theme/vite-wordpress-starter-theme/assets/src/js/main.js` | JavaScript entry point |
| `theme/vite-wordpress-starter-theme/assets/src/scss/main.scss` | SCSS entry point |
| `plugin/index.php` | Plugin entry point (scaffold) |

## Documentation
| Document | Path | Description |
|----------|------|-------------|
| AI Context | .ai-factory/DESCRIPTION.md | Full project specification and tech stack |
| Architecture | .ai-factory/ARCHITECTURE.md | Architecture patterns and decisions |
| Base Rules | .ai-factory/rules/base.md | Detected coding conventions |

## AI Context Files
| File | Purpose |
|------|---------|
| AGENTS.md | This file — project map for AI agents |
| .ai-factory/DESCRIPTION.md | Full project description and tech stack |
| .ai-factory/ARCHITECTURE.md | Architecture guidelines and patterns |
| .ai-factory/config.yaml | AI Factory run configuration |
| .ai-factory/rules/base.md | Coding conventions detected from codebase |

## Agent Rules
- Decompose multi-step shell commands into separate steps — do not chain with `&&` in a single command
  - Incorrect (combined): `git checkout main && git pull`
  - Correct (decomposed): First `git checkout main`, then `git pull origin main`
- When modifying theme PHP, always check `functions.php` to understand which configure files are loaded and in what order
- Vite asset paths differ between dev and prod — always check `VITE_BUILD` constant before assuming asset URLs
- WordPress hooks must be registered at file scope (not inside conditionals) unless using `is_admin()` guards
