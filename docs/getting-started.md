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

## Images: WebP

New JPEG/PNG uploads (admin uploads and sideloads) are converted to WebP before WordPress creates the attachment. The original file is removed and the sizes are generated from the WebP file. If conversion fails (no editor with WebP support, or the editor writes an empty file), the original is kept and `WARN [webp] …` is logged.

The flags in `configure/optimize.php` can be overridden in `wp-config.php`:

| Constant | Default | Purpose |
|----------|---------|---------|
| `STARTER_CONVERT_UPLOADS_TO_WEBP` | `true` | Turns upload conversion on or off |
| `STARTER_WEBP_UPLOAD_QUALITY` | `75` | WebP quality, also used for generated sizes |

If Imagick has no WebP support, WordPress falls back to GD. GD cannot write palette (8-bit) PNGs to WebP, so the theme uses `Starter_Image_Editor_GD` (`configure/helpers/class-starter-image-editor-gd.php`) in its place, which converts those images to truecolor first. The `wordpress:cli` image has no Imagick WebP support, so CLI runs always use this GD path.

To convert images already in the media library, run `scripts/convert-media-to-webp.php`. It converts each attachment in place and keeps its ID, so ACF image fields keep working. It also regenerates sizes and rewrites old file URLs in `post_content` (including revisions) and in postmeta. The originals are deleted only after the update succeeded. The script skips attachments that are already WebP, so it is safe to run it again.

```bash
# 1. Back up first: the originals are deleted
docker compose -f docker/compose.yml exec -T db sh -c 'mysqldump -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" --no-tablespaces "$MYSQL_DATABASE"' > backup.sql
docker compose -f docker/compose.yml exec -T wordpress tar czf - -C /var/www/html/wp-content uploads > uploads.tgz

# 2. Preview, then run (add ids=12,34 to limit the run, --debug to list every URL replacement)
MSYS_NO_PATHCONV=1 docker compose -f docker/compose.yml run --rm wpcli wp eval-file /scripts/convert-media-to-webp.php dry-run
MSYS_NO_PATHCONV=1 docker compose -f docker/compose.yml run --rm wpcli wp eval-file /scripts/convert-media-to-webp.php
```

`wp db export` from the `wpcli` container cannot connect to MySQL 8 (`caching_sha2_password`), so the backup step calls `mysqldump` inside the `db` container. Small 8-bit PNGs and noise textures can end up the same size or slightly larger as WebP.

## Placeholder featured images

Until the real covers are ready, `scripts/assign-placeholder-thumbnails.php` gives each published `services`, `directions`, `cases` and `post` entry a random featured image. It picks from three images in the media library (`2026/10/web_site_development.webp`, `2026/10/seo_medicine_web_site.webp`, `2026/05/hero_image.webp`), which it finds by file path. Posts that already have a thumbnail are skipped. Each placeholder is marked with the `_w4m_placeholder_thumb` postmeta, so `reset` removes only placeholders and keeps real covers.

```bash
make placeholder-thumbs args="dry-run"   # preview
make placeholder-thumbs                  # assign to posts without a thumbnail
make placeholder-thumbs args="force"     # also replace existing thumbnails
make placeholder-thumbs args="reset"     # remove placeholders
```

Add `types=services,cases` to limit the post types.

## Verifying a refactor

1. Take screenshots before (desktop 1440 / mobile 390) of `/`, `/services/`, one service, direction, case, `/blog/` and a blog post.
2. Rebuild and compare after. Heights must match exactly; remaining differences should be limited to animation phases (footer glitch cover on the front page, glitch images, text scramble).
3. Check `wp-content/debug.log` for notices.

## See Also

- [Page Sections](page-sections.md) — building pages from `acf/section-*` blocks
- [Architecture](../.ai-factory/ARCHITECTURE.md) — theme layers and module layout
