# web4med — WordPress Theme

> Hybrid WordPress theme: pages are built from ACF section blocks, CPT/blog templates stay plain PHP. Vite + SCSS frontend, Docker dev environment.

A custom theme for a medical-marketing agency site (services, directions, cases, blog). Pages are assembled in the block editor from `acf/section-*` blocks; single/archive templates reuse PHP partials directly.

## Quick Start

```bash
cp docker/.env.example docker/.env
docker compose -f docker/compose.yml up -d
cd theme/vite-wordpress-starter-theme && npm install && composer install && npm run build
```

- WordPress: http://localhost:8080 · phpMyAdmin: http://localhost:8081 · Mailpit: http://localhost:8025

## Key Features

- **Hybrid theme** — `page` = `acf/section-*` blocks with per-block conditional CSS/JS; CPT, blog and archives = PHP templates
- **Modular `configure/`** — `functions.php` composition root, aggregator files with explicit module lists, `starter_` function prefix
- **Vite 8 + SCSS** — flat SCSS, per-template entries, HMR in dev, manifest-driven enqueue in prod, `postcss-pxtorem` on build
- **Tooling** — PHPCS (WordPress standard), PHPStan level 5, Biome
- **Docker** — MySQL, WordPress (PHP 8.3), WP-CLI, phpMyAdmin, Mailpit
- **Analytics** — GTM configured from ACF Options

## Structure

```
├── docker/            # compose.yml, .env(.example), uploads.ini
├── mu-plugins/        # mailpit.php (local mail catcher)
├── plugin/            # custom plugin scaffold
├── scripts/           # WP-CLI import / migration scripts
├── theme/vite-wordpress-starter-theme/
└── Makefile
```

---

## Documentation

| Guide | Description |
|-------|-------------|
| [Getting Started](docs/getting-started.md) | Environment, commands, dev vs prod assets |
| [Page Sections](docs/page-sections.md) | `acf/section-*` blocks, editor canvas, migration |
| [Single Pages](docs/single-pages.md) | Article layout of CPT and blog singles, TOC, lightbox |

## License

GPL-2.0-or-later
