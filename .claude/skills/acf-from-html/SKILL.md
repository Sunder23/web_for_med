---
name: acf-from-html
description: >
  Parses an HTML layout file and generates an ACF/SCF field group JSON file in the
  theme's acf-json/ folder. Use when you have an HTML mockup or design export and
  want to scaffold the corresponding ACF field group without manual data entry.
argument-hint: "<path/to/layout.html>"
allowed-tools: Read Write Glob AskUserQuestion
---

# acf-from-html

Parse an HTML layout file and generate an ACF/SCF-compatible field group JSON file
saved directly to `acf-json/` in the theme root. Supports text, image, link, repeater,
group, and all standard field types. Uses deterministic CRC32 keys for Git-safe
reproducibility.

## Step 0 — Load Config

Read `.ai-factory/config.yaml` if it exists. Locate the theme root:
- Check `paths.description` to infer theme location, or
- Default to `theme/vite-wordpress-starter-theme/`

The output directory is always `<theme-root>/acf-json/`. Verify it exists:

```
Glob: <theme-root>/acf-json/
```

If `acf-json/` does not exist, stop and tell the user:

```
acf-json/ not found at <theme-root>/acf-json/
Create it first: mkdir theme/vite-wordpress-starter-theme/acf-json
ACF/SCF will auto-load field groups from this folder.
```

## Step 1 — Plugin Selection

Ask the user which plugin is active:

```
AskUserQuestion: Which ACF plugin are you using?

Options:
1. ACF Pro — commercial plugin by WP Engine
2. SCF (Secure Custom Fields) — open-source community fork
```

Store the answer as `plugin`. Both plugins use identical JSON format; this is recorded
in the output file comment and used for field label guidance only.

## Step 2 — Read HTML File

Read the file at the path provided as the skill argument.

If the file does not exist, stop with a clear error:

```
File not found: <provided path>
Provide a valid path to an HTML file, e.g.:
  /acf-from-html theme/vite-wordpress-starter-theme/templates/hero.html
```

If the file exists but is empty, stop:

```
File is empty: <provided path>
```

## Step 3 — Parse HTML & Build Draft Field Map

Analyse every element in the HTML using the rules in `references/HTML-FIELD-MAP.md`.

**Auto-mapped elements** (no clarification needed):
- `<img>` → `image`
- `<h1>`–`<h6>` → `text`
- `<p class~=content|body|description|text|copy|wysiwyg>` → `wysiwyg`
- `<a>` with no visible child text (href only) → `url`
- `<video>`, `<audio>` → `file`
- `<iframe>` → `oembed`
- `<time>` or element whose class contains `date` or `time` → `date_picker`
- `<input type="checkbox">` → `true_false`
- `<select>` → `select` (parse `<option>` values as the `choices` array)
- `<input type="number">` → `number`
- `<input type="email">` → `email`

**Field name extraction** (apply to every element):
- Use the element's `class` or `id` attribute as the field name hint
- Strip BEM modifiers: keep only the final segment after `__` as a suffix; ignore `--` modifiers
- Convert hyphens to underscores, lowercase everything
- Example: `class="hero__cta-link"` → field name `hero__cta_link` → simplified to `cta_link`
  when the parent group already carries the `hero` prefix
- Example: `class="bg-image"` → `bg_image`
- If no class or id, fall back to the element tag name + an auto-incrementing index: `img_1`

**Structural detection**:
- **Repeater:** 2 or more sibling elements sharing the same class → map as `repeater`;
  children of one representative element become `sub_fields`
- **`<ul><li>` with structured children** (children contain more than plain text) → `repeater`
- **Group:** a `<div>` or `<section>` whose children map to 2+ distinct field types,
  not already a repeater → map as `group` with `sub_fields`

**Ambiguous elements** (always flag, never auto-decide):
- `<p>` without a semantic content class → mark as `?type` (text / textarea / wysiwyg)
- `<a>` with both href and visible child text → mark as `?type` (url / link)
- See full table in `references/HTML-FIELD-MAP.md`

Build the draft field map as an ordered list. Show it to the user before asking
clarification questions, so they can see what was detected.

## Step 4 — One Clarification Round

Ask all questions in a single `AskUserQuestion` call (max 4 questions). Batch related
ambiguous elements into one question using "Other" for free-form responses when needed.

**Always ask:**
1. What is the field group title? (e.g., "Hero Section")
2. Where should this field group appear?
   - By post type: `page`, `post`, or a custom CPT slug
   - By page template: template file name (e.g. `templates/home.php`)

**Ask for each ambiguous element** (batch into question 3 if multiple):
- `<p class="hero__description">` → text / textarea / wysiwyg?
- `<a class="hero__cta">` → url (href only) / link (href + label)?

Display the full draft field map in the question context so the user can reference it.

## Step 5 — Generate Deterministic Keys

Use the CRC32-8 algorithm defined in `references/ACF-JSON-SCHEMA.md`.

```
group_key  = "group_"  + crc32_8(lower(trim(group_title)))
field_key  = "field_"  + crc32_8(lower(trim(group_title)) + ":" + lower(trim(field_name)))
```

For `sub_fields` inside a repeater or group, the field name used in the hash is the
**full dotted path**: `repeater_name.sub_field_name`.

Example:
```
group_title = "Hero Section"
field_name  = "bg_image"

group_key = "group_" + crc32_8("hero section")        → group_a3f2c1d8
field_key = "field_" + crc32_8("hero section:bg_image") → field_7b2e90a1
```

## Step 6 — Conflict Check

```
Glob: <theme-root>/acf-json/group_<computed-key>.json
```

If a file is found, ask:

```
AskUserQuestion: group_<key>.json already exists in acf-json/.
This file was likely generated from a previous run on the same HTML.

Options:
1. Overwrite — replace the existing file
2. Abort — keep the existing file and stop
```

If the user chooses Abort, stop with:

```
Aborted. Existing file kept: acf-json/group_<key>.json
```

## Step 7 — Write JSON

Construct the field group array following the schema in `references/ACF-JSON-SCHEMA.md`.

Set `"modified"` to the current Unix timestamp (seconds since epoch).

Write the file to `<theme-root>/acf-json/group_<key>.json`.

Confirm:

```
✅ Field group written: acf-json/group_<key>.json

  Group:  <group_title>  (key: group_<key>)
  Fields: <N> fields generated
  Plugin: <ACF Pro | SCF>
  Target: <post_type == page | page_template == ...>

ACF/SCF will auto-load this group on next page request.
To sync via admin: Tools → Sync → click Sync for this group.
```

## Step 8 — Optional PHP Template Snippet

```
AskUserQuestion: Generate a PHP template snippet showing how to use these fields?

Options:
1. Yes — output get_field() calls with proper escaping
2. No — done
```

If yes, output a PHP code block using:
- `esc_html(get_field('name'))` for text, textarea, number, email
- `esc_url(get_field('name'))` for url
- Image array pattern: `$img = get_field('name'); if ($img): echo '<img src="' . esc_url($img['url']) . '" alt="' . esc_attr($img['alt']) . '">';`
- Link pattern: `$link = get_field('name'); if ($link): echo '<a href="' . esc_url($link['url']) . '">' . esc_html($link['title']) . '</a>';`
- Repeater pattern: `if (have_rows('name')): while (have_rows('name')): the_row(); the_sub_field('sub_name'); endwhile; endif;`
- WYSIWYG: `the_field('name')` (ACF already applies `wpautop`)
- Date: `esc_html(get_field('name'))` (format configured in ACF admin)

See `references/EXAMPLES.md` for a complete worked example.

## Reference Files

- `references/HTML-FIELD-MAP.md` — complete element → field type mapping table, name rules, ambiguity table, repeater heuristics
- `references/ACF-JSON-SCHEMA.md` — CRC32-8 algorithm, field group JSON structure, all field object templates, location rule params
- `references/EXAMPLES.md` — end-to-end worked example: HTML in → draft map → clarifications → JSON out → PHP snippet
