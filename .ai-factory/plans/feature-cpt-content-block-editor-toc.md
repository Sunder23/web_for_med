# Implementation Plan: CPT Singles Refactor — Block Editor Content + TOC Sidebar

Branch: feature/cpt-content-block-editor-toc
Created: 2026-07-12

## Settings
- Testing: no
- Logging: minimal (WARN/ERROR only; PHP `error_log()` guarded by `WP_DEBUG`, migration script uses `WP_CLI::log/warning`)
- Docs: no  # warn-only, no mandatory docs checkpoint

## Goal

Refactor single templates for all CPTs (`services`, `directions`, `cases`) and the blog (`single.php`):

1. **Main body moves into `post_content`** (Gutenberg block editor). ACF-driven middle sections (audience, triggers, included, stages, strategy, examples, formats, sections, facts, results) are removed from templates and their data migrated into block markup.
2. **Hero stays ACF/template-driven** (decision: title + breadcrumbs + ACF hero fields remain in the template).
3. **FAQ and CTA stay ACF/template-driven at the end of the page** for every CPT. FAQ becomes a shared template part; Direction and Case field groups gain an FAQ field (Service already has one).
4. **Sidebar with sticky TOC** (auto-generated from `h2`/`h3` headings in content) on every CPT single and blog single. Existing who/needs ACF sidebar cards render **below** the TOC when filled.
5. **Enable block editor** for the three CPTs (`show_in_rest` is already `true`; add `editor` to `supports`).

**Perspective:** this is groundwork for custom Gutenberg blocks built with ACF/SCF (`acf_register_block_type`). Content becomes block-based now; bespoke section designs return later as custom blocks instead of hardcoded template sections.

## Key Technical Facts (from exploration)

- `configure/cpt-taxonomy.php` — all 3 CPTs: `'supports' => ['title','thumbnail']`, `show_in_rest: true`.
- `configure/js-css.php:166-170` dequeues `wp-block-library`, `global-styles`, `classic-theme-styles` globally — must become conditional (keep dequeue except on singular views that render `the_content()`).
- `configure/configure.php:35-37` removes `render_block` filters incl. `wp_render_layout_support_flag` — this **breaks core group/columns blocks**; must be reverted (or made safe) when the block editor is in play.
- `template-parts/two-column-page.php` already implements the two-column layout with who/needs sidebar cards — evolve it into the content+TOC layout rather than building from scratch.
- FAQ markup currently lives inline in `single-services.php:242-267` (accordion, `data-faq`, JS in `assets/src/js/components/faqAccordion.js`).
- CTA is already a shared part: `template-parts/cpt-cta.php`.
- ACF JSON groups: `group_9033ee91` (Service Page), `group_41eea81b` (Direction Page), `group_6ba8f99a` (Case Page) in `theme/vite-wordpress-starter-theme/acf-json/`.
- WP-CLI import scripts already exist under `scripts/import/` — migration script follows the same pattern.
- Deterministic ACF key convention (CRC32-style 8-hex hash) per RESEARCH.md applies to any new fields.

## Decisions (user-confirmed)

- Hero: keep in template as ACF fields.
- Sidebar: sticky TOC on top + existing who/needs cards below.
- Existing ACF content: migrate to `post_content` blocks via WP-CLI script (non-destructive — postmeta is not deleted).
- No tests; minimal logging; docs warn-only; no roadmap linkage (no ROADMAP.md in project).

## Commit Plan
- **Commit 1** (after tasks 1-3): `feat: enable block editor for CPTs and add TOC infrastructure`
- **Commit 2** (after tasks 4-7): `refactor: move CPT and blog singles to content-with-TOC layout`
- **Commit 3** (after tasks 8-10): `feat: add TOC styles/scrollspy, ACF migration script and trimmed field groups`
- Final verification (task 11) commits only if fixes were needed.

## Tasks

### Phase 1: Block editor enablement + TOC infrastructure
- [x] Task 1: Add `editor` support to `services`, `directions`, `cases` CPTs (`configure/cpt-taxonomy.php`)
- [x] Task 2: Make block-asset stripping conditional (`configure/js-css.php`, `configure/configure.php`) so core blocks render correctly on singular views
- [x] Task 3: Create `configure/toc.php` — heading anchor-ID injection filter on `the_content` + `custom_theme_get_toc()` helper; include from `functions.php`

### Phase 2: Templates
- [x] Task 4: Create `template-parts/content-with-toc.php` (evolve `two-column-page.php`) and `template-parts/cpt-faq.php` (extract from `single-services.php`) (depends on 3)
- [x] Task 5: Refactor `single-services.php`: hero + content-with-TOC + FAQ + CTA (depends on 4)
- [x] Task 6: Refactor `single-directions.php` and `single-cases.php` to the same layout (depends on 4)
- [x] Task 7: Refactor `single.php` (blog): wrap `the_content()` in content-with-TOC layout (depends on 4)
<!-- Commit checkpoint: tasks 4-7 -->

### Phase 3: Frontend, fields, migration
- [x] Task 8: SCSS `components/_toc.scss` + layout tweaks; JS `components/toc.js` scrollspy (IntersectionObserver), register in `main.js`
- [x] Task 9: WP-CLI migration script `scripts/migrate-acf-to-blocks.php` — convert ACF section fields to Gutenberg block markup in `post_content` (depends on 1)
- [x] Task 10: Update ACF JSON groups: remove migrated content fields; keep hero/sidebar/FAQ/CTA; add FAQ field to Direction and Case groups (depends on 5, 6, 9)
<!-- Commit checkpoint: tasks 8-10 -->

### Phase 4: Verification
- [x] Task 11: `npm run build` + manual browser verification of all four page types and Gutenberg editor (depends on all)
