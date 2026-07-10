# Implementation Plan: New Pages & CPTs — Services, Directions, Cases, Blog (Web4Med)

Branch: feature/services-directions-cpt
Created: 2026-07-10
Source content: ref/plan_new_pages_and_cpt_Web4Med.md (all page copy, UA language, use verbatim)

## Settings
- Testing: no
- Logging: minimal (errors only + one summary line per import script)
- Docs: no  # WARN [docs] only, no mandatory checkpoint

## Research Context
Source: .ai-factory/RESEARCH.md (Active Summary — different topic, but its project-wide conventions apply here)

- SCF (Secure Custom Fields) is the ACF fork in use; field groups live as JSON in `theme/vite-wordpress-starter-theme/acf-json/`
- Deterministic keys: `group_XXXXXXXX` / `field_XXXXXXXX` (CRC32-style 8-hex hash) — matches existing `group_57587b53` (Front Page)
- `flexible_content` is excluded project-wide — use group/repeater/tab only
- The installed `/acf-from-html` skill can assist with JSON generation

## User Decisions (2026-07-10)
- Mode: full; branch created from main (uncommitted main changes carried into working tree — do not revert them)
- «Напрямки» = separate CPT `directions` (not pages, not taxonomy)
- «Кейси» = CPT `cases` + import of all 3 cases
- URL slugs: EN latin — `/services/`, `/directions/`, `/cases/`, `/blog/`
- Archives: yes — simple hero + card grid for services, directions, cases
- SCF JSON should carry `default_value` for recurring structural labels (sidebar titles, CTA button captions, FAQ section title)
- Import via wp-cli (Docker: add `cli` service, `wordpress:cli` image)
- Styles identical to the existing site (front page is the reference: reuse `info-card`, `c-tag`, `btn`, `l-wrap`, section patterns, SCSS variables)

## Content Inventory (from ref doc)
- Services (4): Веб розробка, SEO, Контекстна реклама, Аналітика і моніторинг
- Directions (7): Приватний кабінет, Реабілітація та санаторії, Багатопрофільна клініка, Репродуктологія, Естетична медицина, Лікування залежностей, Стоматологія
- Cases (3): KhmilClinic (редизайн, KPI: 15.8M показів / 448K кліків / CTR 2.8% / позиція 9.9), Lviv Medical Center (редизайн головної), Compliment (естетичний центр)
- Blog posts (3): «SEO без ілюзій: позиції ≠ прибуток» (2025-10-30, images ref/images/image_1..3.png), «Соцмережі й месенджери є. Навіщо лікарю сайт» (use the SECOND rewritten version from the doc), «Особливість просування закладів лікування залежностей» (2025-03-25; text mentions "WebForBiz" — flag to user, keep verbatim unless told otherwise)

## Layout Notes
- Services pages «Веб розробка / SEO / Контекстна реклама» share a full-width sectioned layout (hero → cards → lists → stages → FAQ → CTA)
- Service «Аналітика і моніторинг» and ALL Directions pages share a two-column layout (left content sections, right sidebar «Кому підходить» / «Що потрібно») — factor shared two-column SCSS
- FAQ accordion: reuse/extend existing `assets/src/js/components/servicesAccordion.js` (check selector contract before reuse)

## Commit Plan
- **Commit 1** (after tasks 1–2): `feat: add wp-cli service and register services/directions/cases CPTs`
- **Commit 2** (after tasks 3–5): `feat: add SCF field groups for services, directions and cases`
- **Commit 3** (after tasks 6–9): `feat: add single templates and styles for services, directions, cases`
- **Commit 4** (after tasks 10–11): `feat: add archive and blog templates`
- **Commit 5** (after tasks 12–14): `feat: add wp-cli content import scripts and navigation setup`

## Tasks

### Phase 1: Infrastructure & CPTs
- [x] Task 1: Add wp-cli `cli` service to docker-compose.yml (+ `./scripts:/scripts:ro` and `./ref/images:/import-images:ro` mounts) and Makefile `wp` / `import` targets
- [x] Task 2: Register CPTs `services`, `directions`, `cases` in `configure/cpt-taxonomy.php` (has_archive, EN rewrite slugs, UA labels, show_in_rest)
<!-- Commit checkpoint: tasks 1-2 -->

### Phase 2: SCF Field Groups (depends on 2)
- [x] Task 3: Services field group JSON (tabs: hero, audience, triggers, included, stages, strategy, examples, formats, faq, cta; defaults on structural labels)
- [x] Task 4: Directions field group JSON (hero+blurbs, content_sections repeater [title+wysiwyg], two sidebar groups with default titles, cta)
- [x] Task 5: Cases field group JSON (facts repeater, content_sections, results with metrics repeater, cta)
<!-- Commit checkpoint: tasks 3-5 -->

### Phase 3: Templates & Styles
- [x] Task 6: Breadcrumbs helper in `configure/utilities.php` + `components/_breadcrumbs.scss`
- [x] Task 7: `single-services.php` + `pages/_service.scss` + FAQ accordion wiring (depends on 3, 6)
- [x] Task 8: `single-directions.php` + `pages/_direction.scss` — two-column layout shared with analytics service page (depends on 4, 6)
- [x] Task 9: `single-cases.php` + `pages/_case.scss` with KPI metric tiles (depends on 5, 6)
<!-- Commit checkpoint: tasks 6-9 -->
- [ ] Task 10: `archive-services.php`, `archive-directions.php`, `archive-cases.php` + shared `pages/_archive.scss` (depends on 2, 6)
- [ ] Task 11: Blog templates `home.php` + `single.php` + `pages/_single-post.scss` (depends on 6)
<!-- Commit checkpoint: tasks 10-11 -->

### Phase 4: Content Import (wp-cli)
- [ ] Task 12: Transcribe ALL copy from ref doc into `scripts/import/data/{services,directions,cases,posts}.php` (UA verbatim, HTML strings for wysiwyg sections)
- [ ] Task 13: Idempotent `wp eval-file` import scripts per CPT + posts (update_field by FIELD KEYS, media sideload for blog images, rewrite flush) (depends on 1, 3, 4, 5, 12)
- [ ] Task 14: Menu setup script (Головна / Послуги▾ / Напрямки роботи / Кейси / Блог), `page_for_posts`, run full import, verify all pages in browser (depends on 7–11, 13)
<!-- Commit checkpoint: tasks 12-14 -->

## Verification Criteria (task 14)
- `wp post list --post_type=services|directions|cases` → 4 / 7 / 3 published posts
- 3 blog posts with correct dates and inline images
- Front-page untouched; all new pages render with site styles (Vite build passes), FAQ accordion works, no PHP notices in container logs
- Re-running import scripts creates no duplicates
