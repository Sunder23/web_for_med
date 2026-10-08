[Back to README](../README.md) · [Page Sections →](page-sections.md)

# Getting Started

## Prerequisites

- Docker Desktop (or Docker Engine + Compose v2)
- Node.js 20+ and npm (theme frontend)
- Composer and PHP 8.2+ (PHPCS / PHPStan on the host)

## Setup

```bash
cp docker/.env.example docker/.env      # first time only
docker compose -f docker/compose.yml up -d
cd theme/vite-wordpress-starter-theme
npm install
composer install
npm run build                           # or: npm run dev (HMR)
```

| Service | URL |
|---------|-----|
| WordPress | http://localhost:8080 |
| phpMyAdmin | http://localhost:8081 |
| Mailpit (outgoing mail catcher) | http://localhost:8025 |

The compose project is pinned to `name: web_for_med`, so the existing `web_for_med_db_data` / `web_for_med_wordpress_data` volumes are reused regardless of the compose file location.

## Daily commands

| Command | Description |
|---------|-------------|
| `make up` / `make down` | Start / stop the containers |
| `make logs` | Follow WordPress logs |
| `make wp cmd="plugin list"` | Run WP-CLI in the `wpcli` service |
| `make migrate-front-page args="apply"` | One-time front page → section blocks migration (dry run without `args`) |
| `npm run dev` | Vite dev server with HMR (used when `assets/dist` is absent and `WP_ENVIRONMENT_TYPE=local`) |
| `npm run build` | Production build into `assets/dist/` |
| `composer lint` | PHPCS + PHPStan (level 5, baseline in `phpstan-baseline.neon`) |
| `npm run lint` | `lint:js` (Biome) + `lint:css` (Stylelint for `assets/src/scss`) |
| `npm run lint:css` / `npm run format:css` | Stylelint check / check with fixes |
| `npm run format` | Biome fixes + `format:css` |

SCSS rules checked by Stylelint (`.stylelintrc.json`): mobile first only through `@include breakpoint(...)` (no raw width media queries), at most 3 nested selector levels, BEM class names without the old `s-`/`l-`/`c-`/`svc-`/`br-` prefixes. The legacy archive partials (`_archive`, `_cpt-common`) are temporarily listed in `ignoreFiles`.

On Windows (Git Bash) prefix raw `docker compose ... run wpcli wp eval-file /scripts/...` calls with `MSYS_NO_PATHCONV=1`.

## Dev vs production assets

- `VITE_BUILD` — `assets/dist/.vite/manifest.json` exists → hashed files from the manifest.
- `VITE_DEV` — no manifest and `wp_get_environment_type() === 'local'` → assets from `http://localhost:5173`.
- To test the dev server while a build exists, move `assets/dist` away first.

## Verifying a refactor

1. Take screenshots before (desktop 1440 / mobile 390) of `/`, `/services/`, one service, direction, case, `/blog/` and a blog post.
2. Rebuild and compare after. Heights must match exactly; remaining differences should be limited to animation phases (footer glitch cover, gears, text scramble).
3. Check `wp-content/debug.log` for notices.

## See Also

- [Page Sections](page-sections.md) — building pages from `acf/section-*` blocks
- [Architecture](../.ai-factory/ARCHITECTURE.md) — theme layers and module layout
