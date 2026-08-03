# Implementation Plan: Structured CPT Pages (Services / Directions / Cases)

Branch: feature/structured-cpt-pages
Created: 2026-08-03

## Settings
- Testing: no
- Logging: verbose
- Docs: no

## Research Context
Source: .ai-factory/RESEARCH.md (Active Summary) — not present for this feature; context instead comes from an /aif-explore session in this conversation.

Goal: Rebuild the Services/Directions/Cases single templates to render structured, section-by-section content per `Web4Med_структура_сторінок.md`, instead of the current raw `the_content()` blob wrapped in a "Зміст" (TOC) sidebar. Add a final CTA + category filters to the blog.

Constraints:
- CSS for every target section already exists and is unused (`_service.scss`, `_direction.scss`, `_case.scss`, `_cpt-common.scss`) — reuse it, don't invent new classes.
- Real page copy already exists, pre-structured, in `scripts/import/data/{services,directions,cases,posts}.php` — written for an ACF schema that doesn't exist yet. No manual re-typing of content; only ACF field-name alignment + running the existing idempotent importers (`scripts/import/import-*.php`, via `wp eval-file` in the `cli` docker-compose service).
- TOC (`content-with-toc.php` / "Зміст" sidebar) is removed from Services/Directions/Cases only. Blog (`single.php`) keeps it — long-form articles benefit from it, and `scripts/import/data/posts.php` confirms blog content is plain post_content, not ACF-driven.
- "Аналітика і моніторинг" service uses the existing `.s-two-col` two-column layout instead of the linear `.s-svc` layout used by the other 3 services — controlled by a new `layout` ACF select field (default `linear`), not a separate template file.

Decisions (confirmed via AskUserQuestion during /aif-explore + /aif-plan):
- Migrate real content now (not placeholder-then-manual-entry) — via the existing import scripts.
- Layout variant selection: ACF toggle field, not a dedicated template file.
- TOC removed from CPT singles only; blog single keeps it.

Open questions: none — resolved during exploration/planning.

## Commit Plan
- **Commit 1** (after tasks 1-4): "feat(acf): extend CPT field groups with structured section fields"
- **Commit 2** (after tasks 5-8): "feat(theme): render structured CPT sections, drop content-with-toc for services/directions/cases"
- **Commit 3** (after tasks 9-10): "feat(blog): add category filters and final CTA to blog templates"
- **Commit 4** (after tasks 11-12): "fix: reconcile CPT content import with new field schema"

## Tasks

### Phase 1: ACF Field Schema
- [x] Task 1: Extend Service Page ACF field group (`group_9033ee91`) with a `layout` toggle (linear default | two-column) plus linear-only fields (`audience`, `triggers`, `included`, `stages`, `strategy`, `examples`, `formats`) and two-column-only fields (`sections`, `sidebar_who`, `sidebar_needs`), using conditional_logic on the `layout` field. Update `scripts/import/data/services.php`'s `analytics` entry with `'layout' => 'two-column'`.
- [x] Task 2: Extend Direction Page ACF field group (`group_41eea81b`) with `sections` (repeater: title, wysiwyg content), `sidebar_who`, `sidebar_needs`.
- [x] Task 3: Extend Case Page ACF field group (`group_6ba8f99a`) with `facts` (repeater: label, value), `sections` (repeater: title, wysiwyg content), `results` (title, optional metrics repeater, wysiwyg text).
- [x] Task 4: Add a new "Post CTA" ACF field group scoped to `post_type=post` (`post_cta`: text, button_label, button_url). Extract/write CTA data into `scripts/import/data/posts.php` for all 3 posts and wire it into `scripts/import/import-posts.php`.
<!-- Commit checkpoint: tasks 1-4 -->

### Phase 2: Templates
- [ ] Task 5: Rewrite `single-services.php` (depends on 1) — branch on `layout`, render all linear/two-column sections with the existing `.svc-*` / `.s-two-col` SCSS, drop `content-with-toc`.
- [ ] Task 6: Rewrite `single-directions.php` (depends on 2) — render `sections` + `sidebar_who`/`sidebar_needs` via `.s-two-col`, drop `content-with-toc`.
- [ ] Task 7: Rewrite `single-cases.php` (depends on 3) — render `facts`/`sections`/`results` via `.case-facts`/`.s-case-content`/`.s-case-results`/`.case-metrics`, drop `content-with-toc`.
- [ ] Task 8: Scope `custom_theme_is_block_content_view()` to `post` only (depends on 5, 6, 7); verify `js-css.php` block-library enqueue and `toc.php` heading-anchor injection are unaffected for blog.
<!-- Commit checkpoint: tasks 5-8 -->

### Phase 3: Blog
- [ ] Task 9: Add a category filter section to `home.php` (blog list) + client-side filter JS + category assignment in the importer.
- [ ] Task 10: Wire the `post_cta` field into `single.php` via the shared `cpt-cta.php` partial (depends on 4).
<!-- Commit checkpoint: tasks 9-10 -->

### Phase 4: Import & Verify
- [ ] Task 11: Run all 4 importers (`wp eval-file` via the `cli` docker-compose service) against the local stack; fix any field-name mismatches until all report 0 errors (depends on 1-10).
- [ ] Task 12: Visual QA across all 4 services, 7 directions, 3 cases, blog list, and one blog single (depends on 11).
<!-- Commit checkpoint: tasks 11-12 (final) -->
