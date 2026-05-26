# Research

Updated: 2026-04-26 12:30
Status: active

## Active Summary (input for /aif-plan)

<!-- aif:active-summary:start -->
Topic: `acf-from-html` — custom AI Factory skill
Goal: Generate ACF/SCF field group JSON from an HTML layout file (design-first workflow)
Constraints:
- JSON output only (no PHP configure/acf.php output)
- Output target: `theme/vite-wordpress-starter-theme/acf-json/` (folder already exists)
- Input: file path to an HTML file (not pasted HTML)
- Skill must ask upfront: ACF Pro or SCF (Secure Custom Fields fork)
- Key generation: deterministic (CRC32-style hash of group_title + field_name)
- Workflow: Design-first — HTML mockup/export → ACF JSON config
- ACF Pro and SCF both support the same JSON format and field types (repeater included in both)
- flexible_content is excluded entirely — not used in this project at all

Decisions:
- No PHP output (acf_add_local_field_group) — JSON only via acf-json/ folder
- acf-json/ folder already created by user in theme root
- Plugin choice (ACF Pro vs SCF) asked at skill startup, affects field suggestions/comments only
- Deterministic key generation confirmed
- Template PHP snippet (get_field() calls) is optional output — offer, don't force

HTML → ACF Field Type Mapping (confirmed rules):
  <img>                            → image (return_format: array)
  <h1>–<h6>                        → text
  <p> short / no content class     → text or textarea (ask)
  <p class="content|body|desc..."> → wysiwyg
  <a href> (href only)             → url
  <a href> (href + visible text)   → link (ACF link field stores both)
  <video>, <audio>                 → file
  <iframe>                         → oembed
  <time>, .date                    → date_picker
  <input type="checkbox">          → true_false
  <select> + <option>s             → select (choices parsed from options)
  Repeated elements (N × same cls) → repeater
  Grouped sub-fields in one block  → group
  (flexible_content: excluded — not used in this project)

Key naming: CSS class/ID → snake_case field name, Title Cased label
Key format: group_XXXXXXXX / field_XXXXXXXX (CRC32 hash, 8 hex chars)

V1 scope:
  ✅ File path input to HTML
  ✅ Plugin choice: ACF Pro vs SCF
  ✅ Element → field type mapping with class/id name hints
  ✅ Repeater for repeated same-structure sections
  ✅ Group for nested sub-field blocks
  ✅ One round of AskUserQuestion clarifications (ambiguous elements + location rules)
  ✅ JSON output → acf-json/group_XXXXXXXX.json
  ✅ Deterministic key generation
  ✅ Optional template PHP snippet (get_field calls with esc_*)
  ❌ PHP acf_add_local_field_group() output (out of scope)
  ❌ Multi-file or directory analysis
  ❌ flexible_content — excluded by design, not used in this project

Open questions: none — all decisions settled

Success signals:
- User provides an HTML file path, skill outputs a valid importable JSON in acf-json/
- Field names match class/id hints from the HTML
- No duplicate keys across runs (deterministic keys solve this)

Overwrite behavior: detect existing group_XXXXXXXX.json by deterministic key → offer to overwrite (don't silently replace)

Next step: /aif-plan fast "acf-from-html skill: generate ACF field group JSON from HTML file"
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
<!-- aif:sessions:end -->
