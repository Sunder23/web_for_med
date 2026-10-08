# Plan: WebP images — convert on upload + bulk-convert existing media

**Branch:** `feature/webp-images`
**Created:** 2026-10-08
**Type:** feature

## Settings
- **Testing:** no (no PHP test infra; verify manually via WP-CLI dry-run + browser)
- **Logging:** verbose (`WP_CLI::log` / `WP_CLI::debug` in the CLI script; `error_log` only behind `WP_DEBUG` in the upload filter)
- **Docs:** yes — mandatory docs checkpoint (`/aif-docs`)

## Roadmap Linkage
Milestone: "none"
Rationale: No `.ai-factory/ROADMAP.md` in the project.

## Research Context
Active research summary (featured-image prompts) is unrelated to this task — not carried over. Note: the 17 future cover images (PNG from ChatGPT) will be auto-converted by the upload filter from Task 1.

## Context (recon results)
- Reference implementation: `ref/promedi_block/theme/vite-wordpress-starter-theme/configure/helpers/convert-image-to-webp.php` (filter on `wp_handle_upload`) + wiring in `ref/.../configure/optimize.php` (flags `STARTER_CONVERT_UPLOADS_TO_WEBP`, `STARTER_WEBP_UPLOAD_QUALITY` = 75, `wp_editor_set_quality` filter).
- Docker WP container: `wp_image_editor_supports(['mime_type'=>'image/webp'])` = true (Imagick + GD both available).
- Media library: 21 `image/png` + 4 `image/jpeg` attachments (31 SVG — untouched). Uploads live in the `web_for_med_wordpress_data` docker volume, not in the repo.
- DB references to `.jpg/.jpeg/.png`: `_wp_attached_file` (25), `_wp_attachment_metadata` (25), `_w4m_import_source` (3 — import provenance, leave as is), `post_content` of 1 `post` + 8 revisions; options only in site transients (ignore). ACF image fields store attachment IDs → no change needed.
- Theme: `image-sizes.php` disables `large/medium/medium_large` and the `-scaled` big image → regenerated sizes are only `thumbnail` + theme sizes.
- Theme static: `static/img/noice_bg.png` — currently not referenced anywhere in PHP/SCSS/JS.

## Tasks

### Phase 1: Convert on upload

- [x] **Task 1: Port the upload WebP converter**
  - Create `theme/vite-wordpress-starter-theme/configure/helpers/convert-image-to-webp.php` with `starter_convert_image_to_webp( array $file )` ported from the reference (JPEG/PNG only; skip when editor lacks WebP support; `wp_unique_filename`; delete original via `wp_delete_file`; update `file`/`url`/`type`).
  - Small improvement over reference: replace only the basename at the end of `$file['url']` (not `str_replace` over the whole URL), and keep the original file if the saved WebP is missing.
  - Add `'convert-image-to-webp.php'` to `$starter_helpers` in `configure/utilities.php`.
  - Logging: when `WP_DEBUG` is on, `error_log( '[starter-webp] …' )` for skip reasons (unsupported mime, no WebP editor, editor/save `WP_Error` message) and success (`original → webp, bytes before/after`).

- [x] **Task 2: Wire flags and hooks in `optimize.php`**
  - In `theme/vite-wordpress-starter-theme/configure/optimize.php` add, following existing `STARTER_*` flag style: `STARTER_CONVERT_UPLOADS_TO_WEBP` (default `true`) and `STARTER_WEBP_UPLOAD_QUALITY` (default `75`).
  - When enabled: `add_filter( 'wp_handle_upload', 'starter_convert_image_to_webp' )` and `wp_editor_set_quality` returning the WebP quality for `image/webp` (named function `starter_webp_editor_quality`, not a closure — consistent with theme-hooks style).
  - ~~`image_editor_output_format`~~ dropped: core applies `wp_handle_upload` to sideloads too (`_wp_handle_upload()`), so all upload paths are already covered.
  - Hook registration at file scope (AGENTS.md rule). Depends on Task 1.
  - Logging: none at runtime (flags only); helper from Task 1 logs.

### Phase 2: Bulk-convert existing media

- [x] **Task 3: WP-CLI bulk conversion script `scripts/convert-media-to-webp.php`**
  - Run: `wp eval-file /scripts/convert-media-to-webp.php [dry-run] [ids=1,2,3]` (args parsing like `scripts/migrate-cpt-content.php`; guard `WP_CLI`).
  - For each attachment with mime `image/jpeg|image/png` (optionally filtered by IDs):
    1. Resolve original path (`wp_get_original_image_path` / `get_attached_file`); skip with warning if missing on disk.
    2. Collect old URLs: full URL + every size URL from current `_wp_attachment_metadata`.
    3. Save WebP via `wp_get_image_editor()->save( $path, 'image/webp' )` with quality `STARTER_WEBP_UPLOAD_QUALITY`, unique filename.
    4. `update_attached_file()`, `wp_update_post( post_mime_type => image/webp )`, update `guid` via `$wpdb->update`, regenerate metadata with `wp_generate_attachment_metadata()` + `wp_update_attachment_metadata()`.
    5. Build old→new URL map (full + each size by matching size name; unmatched old size URLs map to new full URL) and replace in `post_content` of all non-attachment posts (including revisions) and in `postmeta` values that are not serialized-sensitive (use `maybe_unserialize` + recursive replace + `update_metadata` for serialized values). Skip `_w4m_import_source`.
    6. Delete old original + old size files (`wp_delete_file`) only after metadata + references were successfully updated.
  - `dry-run`: no writes, print planned conversions, URL map and number of affected posts/meta rows.
  - Idempotent: attachments already `image/webp` are skipped; summary at the end (converted / skipped / failed, total bytes saved).
  - Logging: `WP_CLI::log` per attachment (`#ID file.png → file.webp, 512 KB → 120 KB`), `WP_CLI::debug` for each URL replacement and affected post/meta ID, `WP_CLI::warning` for skips/failures, `WP_CLI::success` summary.
  - Depends on Task 2 (quality constant, output-format filter must not double-convert — script saves explicitly as WebP).

- [x] **Task 4: Run the conversion on the local environment**
  - Back up first: `make wp cmd="db export /scripts/data/backup-before-webp.sql"` (do not commit the dump; add to `.gitignore` if `scripts/data/` is tracked) and tar the uploads volume (`docker compose -f docker/compose.yml exec wordpress tar czf /tmp/uploads-before-webp.tgz wp-content/uploads`, then `docker cp` to scratch).
  - Run dry-run, review output, then real run.
  - Verify: `wp db query` shows 0 `image/jpeg|image/png` attachments; no `.jpg/.png` upload URLs left in published `post_content`; spot-check front page, a service, a case, the blog post in the browser (no broken images, Network tab shows `.webp`); `wp media regenerate` not needed.
  - Logging: keep the CLI output summary in the commit/PR description.

### Phase 3: Theme static image

- [x] **Task 5: Convert `static/img/noice_bg.png` to WebP**
  - Convert inside the container (`wp eval` with `wp_get_image_editor`) or via `cwebp` if available, quality 80, to `theme/vite-wordpress-starter-theme/static/img/noice_bg.webp`; delete the PNG.
  - Re-grep the theme (`grep -rn "noice_bg" theme/ --exclude-dir=node_modules --exclude-dir=dist`) and update any references (currently none found).
  - Logging: n/a (one-off asset change); note before/after size in the commit message.

### Phase 4: Lint & docs

- [x] **Task 6: Lint**
  - `composer lint` in the theme dir (PHPCS + PHPStan); if PHPStan complains about the script outside the theme — scripts are not in PHPStan paths, keep it that way. Fix issues; no new baseline entries.

- [ ] **Task 7: Documentation**
  - `docs/getting-started.md`: section "Images / WebP" — upload auto-conversion, flags `STARTER_CONVERT_UPLOADS_TO_WEBP` / `STARTER_WEBP_UPLOAD_QUALITY`, how to run `scripts/convert-media-to-webp.php` (dry-run first, backup).
  - `AGENTS.md`: mention `convert-image-to-webp.php` helper and the new script under `scripts/` if the structure section lists them.

## Commit Plan
- **Commit 1** (after Tasks 1–2): `feat(media): convert JPEG/PNG uploads to WebP`
- **Commit 2** (after Tasks 3–5): `feat(scripts): bulk-convert existing media library to WebP` (+ static `noice_bg.webp`)
- **Commit 3** (after Tasks 6–7): `docs(media): document WebP upload conversion and migration script`

## Implementation Notes
- First real run (Task 4) went through GD: the `wordpress:cli` image has Imagick **without** WebP, so WP picked GD; GD `imagewebp()` rejects palette PNGs and wrote 0-byte files (21 of 25). Restored DB + uploads from backup (`scripts/.backup-webp/`, git-excluded), then fixed:
  - `configure/helpers/class-starter-image-editor-gd.php` — `Starter_Image_Editor_GD` promotes palette images to truecolor on load; swapped in via `wp_image_editors` in `optimize.php`.
  - Upload helper and CLI script treat an empty output file as a failure (and delete it).
- Second run: 25/25 converted, 6 MB → 3 MB; post 278 + 8 revisions rewritten; 0 JPEG/PNG files left in uploads; front page and post 278 verified in browser (all `/uploads/` images 200 `image/webp`).
- Small 8-bit PNGs grew a bit in WebP (e.g. 34 → 59 KB); noise textures barely shrink (`noice_bg` 1.20 → 1.18 MB).
