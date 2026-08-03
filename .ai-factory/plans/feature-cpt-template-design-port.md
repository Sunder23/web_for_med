# Implementation Plan: Port ref/html_ready_pages design onto CPT single/archive templates

Branch: feature/cpt-template-design-port
Created: 2026-08-03

## Settings
- Testing: no (no PHPUnit/Jest test infra exists in this project; verification is visual, via chrome-devtools screenshots against the reference mockups)
- Logging: verbose (extend the existing `error_log('[W4M <template>] ...')` pattern already used across single-services.php/single-directions.php/single-cases.php)
- Docs: no — warn-only, no mandatory docs checkpoint (internal template/content redesign, no new public API/fixture)

## Roadmap Linkage
<!-- No .ai-factory/ROADMAP.md exists in this project -->
Milestone: "none"
Rationale: No roadmap file configured for this project.

## Research Context
Source: .ai-factory/RESEARCH.md (Active Summary)

Goal: Make single-services.php, single-directions.php, single-cases.php, single.php (and confirm archive-*.php) visually match the 5 bundled reference mockups in `ref/html_ready_pages/`. Existing post content must be reorganized (not just restyled) into new structured ACF fields.

Constraints:
- Reference HTML files are Dyad/Zed bundler exports — real markup is JSON-encoded inside `<script type="__bundler/template">`; decode via Node (regex-extract + `JSON.parse()`) before reading.
- `flexible_content` ACF field type is not used in this project (established convention) — new structured fields must use named `group`/`repeater` fields, mirroring the pattern already established in `group_9033ee91.json` (Service Page).
- Docker dev stack (`web_for_med-wordpress-1`, `web_for_med-db-1`, DB `wordpress_wfmed`) is the source of truth for current post content — no wp-cli available in the container.

Decisions:
- Services: template code already supports the rich "linear" design; gap is 100% content — migrate existing posts' data into structured fields, no ACF/template changes needed.
- Directions: extend ACF schema (services-level flexibility) — add `direction_included`/`direction_formats`/`direction_process`/hero highlights; update template to render them reusing existing `.svc-numbered`/`.tag`/`.icon-list__item` CSS components.
- Cases: no ACF schema change — the existing `case_sections` field already fits "Виклик"/"Хід роботи"; only needs CSS for numbered-list rendering, plus content migration.
- Content strategy: AI restructures each existing post's current text into the new field shapes, preserving meaning/facts — not asking the user to manually re-enter everything.
- Blog post (single.php): full rebuild as a narrow single-column article (05-blog-post.html) — no comments count, no TOC sidebar.
- `content-with-toc.php` + `custom_theme_get_toc()` + `_toc.scss`: delete as dead code once single.php no longer uses them.
- Archives (archive-cases/directions/services.php + home.php): confirmed already matching the target ArchiveCard pattern (verified via live screenshot) — out of scope, not touched by this plan.

Open questions: none blocking.

## Commit Plan
- **Commit 1** (after tasks 1-2): "feat(acf): add content-migration tooling and extend Direction Page schema"
- **Commit 2** (after tasks 3-7): "feat(cpt): render structured direction fields, add case numbered-list styling, migrate all CPT content to new design"
- **Commit 3** (after tasks 8-10): "feat(blog): rebuild single.php as narrow article layout, remove dead TOC code"

## Tasks

### Phase 1: Migration tooling & schema
- [x] Task 1: Build ACF content-migration runner script
- [x] Task 2: Extend ACF "Direction Page" field group with structured section fields
<!-- Commit checkpoint: tasks 1-2 -->

### Phase 2: Templates & content migration
- [x] Task 3: Update single-directions.php to render new structured direction fields (depends on 2)
- [ ] Task 4: Migrate content for all 7 published "directions" posts (depends on 1, 2)
- [ ] Task 5: Add numbered ordered-list styling for case "process" content
- [ ] Task 6: Migrate content for all 4 published "cases" posts into Challenge/Process shape (depends on 1, 5)
- [ ] Task 7: Migrate content for all 4 published "services" posts to the "linear" layout (depends on 1)
<!-- Commit checkpoint: tasks 3-7 -->

### Phase 3: Blog post rebuild & cleanup
- [ ] Task 8: Rebuild single.php as narrow single-column blog article
- [ ] Task 9: Remove dead TOC code after single.php rebuild (depends on 8)
- [ ] Task 10: Build assets and visually verify all rebuilt templates against reference mockups (depends on 3, 4, 6, 7, 9)
<!-- Commit checkpoint: tasks 8-10 -->
