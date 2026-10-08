# Research

Updated: 2026-08-03 23:15
Status: active

## Active Summary (input for /aif-plan)

<!-- aif:active-summary:start -->
Topic: Port ref design (`ref/html_ready_pages/`) onto CPT single/archive templates — services, directions, cases, blog post
Goal: Make single-services.php, single-directions.php, single-cases.php, single.php (and confirm archive-*.php) visually match the 5 bundled reference mockups. Existing post content must be reorganized (not just restyled) into new structured ACF fields.

Reference source format:
- `ref/html_ready_pages/*.html` are Dyad/Zed bundler exports — real markup is JSON-encoded inside `<script type="__bundler/template">`, and a React design-system bundle (components: ArchiveCard, CaseMetric, InfoCard, NumberedItem, ServiceCard, Button, SectionTitle, Tag, FaqAccordion, IconListItem, Breadcrumbs, Pagination) lives gzip+base64 inside `<script type="__bundler/manifest">`.
- Decode recipe (Node): regex-extract the `__bundler/template` script body, `JSON.parse()` it → real HTML. For manifest resources: base64-decode `entry.data`, gzip-inflate if `entry.compressed`. Small design-system JS resource is the one referenced via plain (non-babel) `<script src>` (~14KB after decompression); large ones (~35KB/300KB/880KB) are React/ReactDOM/Babel-standalone, skip those.
- Design tokens (`--color-brand-*`, `--font-main: 'Fixel Display'`, `--border-default`, spacing scale) are ALREADY copied 1:1 into `assets/src/scss/abstracts/_variables.scss`.
- Mapping: 01-service.html→single-services.php, 02-direction.html→single-directions.php, 03-case.html→single-cases.php, 04-blog-list.html→home.php (blog listing, already done — not in original file list), 05-blog-post.html→single.php.

Current state per template (verified via code read + live DB query + live screenshot, docker containers `web_for_med-wordpress-1`/`web_for_med-db-1`, DB `wordpress_wfmed`/`wordpress_wfmed`):

1. **single-services.php** — PHP/SCSS already implements BOTH a rich "linear" layout (matches 01-service.html: audience cards, triggers checklist, numbered included/process steps, stage cards, examples grid, formats pricing tiers, FAQ accordion) AND a plain "two-column" fallback (content-section prose + sidebar bullets). Gap is 100% DATA, not code: queried `wp_postmeta` for all 4 published services posts — none have `service_layout=linear` or populated `service_audience`/`service_triggers`/`service_included`/`service_stages`/`service_strategy`/`service_examples`/`service_formats`. All are on the old `service_sections` (generic title+wysiwyg repeater) shape; one explicitly has `service_layout=two-column`. This is why the live page (screens/localhost_8080_services_analytics_.png) renders the plain fallback instead of the target design (screens/..._01-service.html.png).

2. **single-directions.php** — structurally close to 02-direction.html (2-col grid: content 2fr + sticky sidebar 1fr) but: (a) ref's left column has 3 distinct structured blocks — numbered-steps list, a 2-col InfoCard grid (with tag), another numbered-steps list — while current ACF `direction_sections` (group_41eea81b.json) is a flat repeater of {title, wysiwyg} rendered as generic prose; (b) ref sidebar uses IconListItem (checkmark rows, sticky `position:sticky; top:90px`), current sidebar (`direction_sidebar_who`/`direction_sidebar_needs`) renders plain `<li>` bullets, not sticky. All 7 published directions posts confirmed on the old flat `direction_sections` shape (queried DB).

3. **single-cases.php** — structurally close to 03-case.html (facts as 4-col stat grid, results as CaseMetric 4-col grid — these already match well) but ref splits the narrative into "Виклик" (single prose block, italic-style challenge statement) + "Хід роботи" (numbered ordered-list of process steps), while current `case_sections` is a flat generic title+wysiwyg repeater with no numbered-list treatment. All 4 published cases posts confirmed on old flat shape.

4. **single.php** (blog post, target 05-blog-post.html) — biggest gap. Ref is a narrow single-column article (max-width 820px centered, NO sidebar): Breadcrumbs → Tag(category)+date row → H1 → bold lead paragraph → H2 sections (some on `--surface-alt` grey bg) → navy CTA section (matches existing `template-parts/cpt-cta.php` pattern). Current single.php instead renders `template-parts/content-with-toc.php`, a WIDE 2-column layout with a sticky TOC sidebar (`custom_theme_get_toc()`, `_toc.scss`) — wrong shape entirely, not a styling tweak. `content-with-toc.php` is used ONLY by single.php now (grep-confirmed) — safe to delete entirely once single.php is rebuilt.

5. **archive-cases.php / archive-directions.php / archive-services.php / home.php** — all share `template-parts/archive-cpt.php`, already using an ArchiveCard-style hover-card grid (2-col, title+excerpt+"Детальніше →") matching 04-blog-list.html's pattern (home.php already has category filter pills + this pattern, done in a prior session). Live-screenshotted `http://localhost:8080/cases/` via chrome devtools MCP — visually confirmed matching. No changes needed here.

Decisions (confirmed with user):
- Content strategy: AI restructures the EXISTING text of all 15 published posts (4 services + 7 directions + 4 cases) into the new structured field shapes, preserving meaning/facts — not asking the user to manually re-enter everything, and not just building empty schema.
- Extend ACF schema for directions and cases to get services-level flexibility: directions needs typed sections (prose vs numbered-steps vs card-grid) analogous to service_audience/triggers/stages; cases needs the sections repeater split into a dedicated Challenge field + a numbered-Process field (mirroring "Виклик"/"Хід роботи").
- Blog post comments count ("6 коментарів" in ref) — drop entirely, not wiring real WP comments.
- `content-with-toc.php` + `custom_theme_get_toc()` + `_toc.scss` — delete as dead code once single.php no longer uses them (nothing else references TOC).
- Archives (archive-cases/directions/services.php + home.php) — confirmed sufficient as-is, out of scope for the upcoming plan.

Open questions: none blocking — ready to plan. Still to decide during /aif-plan: exact new ACF field names/shapes for directions (typed sections) and cases (Challenge/Process split); whether services' existing `service_layout` field should just be forced to always resolve to "linear" going forward (dropping two-column) or kept as an option.

Success signals:
- All published services/directions/cases posts render the rich card/grid design matching their ref mockup with no visible fallback-to-plain-prose sections.
- single.php matches 05-blog-post.html's narrow single-column layout with tag+date meta, no comment count, no TOC sidebar.
- `content-with-toc.php`, `custom_theme_get_toc()`, TOC SCSS removed with no remaining references.

Next step: /aif-plan full "Port ref/html_ready_pages design onto single-services/directions/cases/single.php: extend ACF schema for directions+cases, AI-restructure existing post content into new structured fields, rebuild single.php as narrow blog-article layout, remove dead TOC code"
<!-- aif:active-summary:end -->

## Sessions

<!-- aif:sessions:start -->
### 2026-04-26 12:00 — acf-from-html skill exploration

What changed:
- Explored skill concept, HTML→ACF mapping rules, output format options
- Settled all major design decisions through user Q&A
- Confirmed SCF supports same JSON format + field types as ACF Pro (full fork)
- Confirmed acf-json/ folder already exists in theme

Key notes:
- JSON-only output (no PHP) — acf-json/ auto-loaded by ACF/SCF, no admin import needed
- Input is file path (design-first workflow: HTML mockup → ACF config)
- Plugin question at startup is for context/comments, not for gating field types
- Deterministic keys (CRC32 hash) are critical for Git-safe reproducible regeneration
- Optional template snippet output is a nice V1 bonus

Links (paths):
- theme/vite-wordpress-starter-theme/acf-json/  (output directory, already exists)
- configure/acf.php                             (read for context, not written to)
- .ai-factory/DESCRIPTION.md
### 2026-08-03 23:15 — Design/structure port from ref/html_ready_pages

What changed:
- Decoded the 5 bundled reference HTML files (Dyad/Zed bundler format — real markup hidden as JSON inside `<script type="__bundler/template">`) to see the actual target design and the React design-system component source (ArchiveCard, InfoCard, NumberedItem, CaseMetric, FaqAccordion, IconListItem, etc.)
- Initially assumed single-services/directions/cases.php + archives were already fully done (code/SCSS reads matched design tokens closely) — this was WRONG, corrected after the user shared before/after screenshots
- Queried the live docker DB (`web_for_med-db-1`, `wordpress_wfmed`) directly and found: 100% of existing services/directions/cases content (15 posts) is stored in the OLD generic prose-repeater ACF shape (`*_sections`), none use the new structured fields the "linear" services template already supports — this is *why* the live page falls back to a plain layout instead of the rich target design
- Live-screenshotted `localhost:8080/cases/` via chrome-devtools MCP and visually confirmed archive pages already match the target ArchiveCard pattern — no work needed there
- Walked through per-template gaps (services=data only; directions=data+ACF schema extension+sidebar markup fix; cases=data+ACF schema split; single.php=full rebuild, wrong layout entirely; archives=done)

Key notes:
- The `__bundler/template` decode trick (Node: regex-extract + JSON.parse) is reusable if more ref HTML bundles show up later
- Don't trust "the SCSS/PHP looks structurally right" as proof of a finished feature when ACF-driven CPT templates are involved — always check what shape the actual post data is in, since a template can support the new design in code while every real post still renders the old fallback
- `content-with-toc.php` is now single-purpose (blog single only) and will become dead code once single.php is rebuilt

Links (paths):
- ref/html_ready_pages/*.html (bundled ref designs — decode via scratchpad Node script, not readable directly)
- theme/vite-wordpress-starter-theme/single-services.php, single-directions.php, single-cases.php, single.php
- theme/vite-wordpress-starter-theme/template-parts/archive-cpt.php, content-with-toc.php
- theme/vite-wordpress-starter-theme/acf-json/group_9033ee91.json (Service Page), group_41eea81b.json (Direction Page), group_6ba8f99a.json (Case Page)
- theme/vite-wordpress-starter-theme/assets/src/scss/pages/_service.scss, _cpt-common.scss, _archive.scss, _single-post.scss
- screens/localhost_8080_services_analytics_.png (current live render), screens/..._01-service.html.png (target)
<!-- aif:sessions:end -->
