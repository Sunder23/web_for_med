# HTML → ACF Field Type Mapping Reference

Used by SKILL.md Step 3 to build the draft field map from an HTML layout file.

---

## 1. Element → Field Type Mapping Table

| HTML Pattern | ACF Field Type | Notes |
|---|---|---|
| `<img>` | `image` | `return_format: "array"` — gives URL, alt, width, height |
| `<h1>`, `<h2>`, `<h3>`, `<h4>`, `<h5>`, `<h6>` | `text` | Short single-line string |
| `<span>`, `<strong>`, `<em>`, `<b>`, `<i>` | `text` | Short inline string |
| `<p>` with class matching `content\|body\|description\|text\|copy\|wysiwyg\|richtext` | `wysiwyg` | Rich text editor |
| `<p>` without a semantic content class | **ambiguous** — ask user | See §3 |
| `<a>` with `href` attribute only, no visible child text | `url` | Stores URL string only |
| `<a>` with `href` AND visible child text or `<span>` child | **ambiguous** — ask user | Could be `url` or `link` |
| `<video>`, `<audio>` | `file` | Returns file array or URL |
| `<iframe>` | `oembed` | oEmbed URL — ACF renders embed |
| `<time>` | `date_picker` | Date value in admin date picker |
| Element whose class contains `date` or `time` | `date_picker` | Class-based detection |
| `<input type="checkbox">` | `true_false` | Boolean toggle |
| `<input type="number">` | `number` | Numeric value, optional min/max |
| `<input type="email">` | `email` | Email address with validation |
| `<input type="url">` | `url` | URL string |
| `<select>` with `<option>` children | `select` | Parse option values as `choices` |
| `<textarea>` | `textarea` | Multi-line plain text |
| `<svg>` or `<picture>` | `image` | Treat as image field |
| `<figure>` | `image` | Usually wraps `<img>` |

---

## 2. Structural Detection Rules

### Repeater

A **repeater** is detected when:

1. **2 or more sibling elements share the same class**
   ```html
   <div class="team__member">...</div>
   <div class="team__member">...</div>  ← same class, 2+ siblings
   ```
   → Map as `repeater` named from the shared class. Use one representative element's children as `sub_fields`.

2. **`<ul><li>` with structured children** (children are not plain text only)
   ```html
   <ul class="features">
     <li class="features__item">
       <img class="features__icon" ...>
       <span class="features__label">...</span>
     </li>
   </ul>
   ```
   → Map `features` as `repeater`; `features__icon` and `features__label` as `sub_fields`.

3. **Single `<li>` or single element** — do NOT create a repeater; map the single element directly.

### Group

A **group** is detected when:

- A `<div>` or `<section>` contains 2+ children that map to distinct field types and
  is NOT a repeater (no sibling duplication).
- Example: a card `<div>` with an `<img>`, `<h3>`, and `<p>` inside.
- Map as `group` with `sub_fields` = the children's field definitions.
- Name the group from the container element's class or id.

### Nested structures

Repeaters and groups may be nested (repeater containing groups, group containing repeater).
Map recursively up to 2 levels deep. Beyond 2 levels, flatten and note the limitation.

---

## 3. Ambiguity Table — Elements That Always Require Clarification

Never auto-decide these — always include in the Step 4 question:

| Element | Why ambiguous | Options to offer |
|---|---|---|
| `<p>` without content class | Could be a short label (text), multi-line plain text (textarea), or rich content (wysiwyg) | text / textarea / wysiwyg |
| `<a>` with href + child text | Could store just the URL (url) or both URL and label (link) | url / link |
| `<div class="content">` with mixed children | Could be a wysiwyg field or a group of sub-fields | wysiwyg / group |
| `<section>` wrapping the whole layout | Probably a group, but may just be the HTML container — confirm | group / ignore (top-level wrapper) |

---

## 4. Field Name Extraction Rules

Given an element's `class` or `id`, derive a clean snake_case field name:

### Priority order
1. Use `id` attribute if present (usually more semantic)
2. Use the most specific `class` (last class in the list if multiple)
3. Fall back to `tagname_N` (e.g. `img_1`, `p_2`) if no class or id

### Transformation steps
1. Take the raw class/id string: `hero__cta-link`
2. Split on `__` (BEM element separator): `["hero", "cta-link"]` → use the last segment: `cta-link`
3. Remove `--modifier` suffixes: `cta-link--active` → `cta-link`
4. Replace hyphens and spaces with underscores: `cta_link`
5. Lowercase: `cta_link`
6. Strip leading/trailing underscores

### Examples

| Raw class/id | Derived field name |
|---|---|
| `hero__bg-image` | `bg_image` |
| `hero__title` | `title` |
| `hero__cta-link--primary` | `cta_link` |
| `features__item` | `item` (repeater row) |
| `feature__icon` | `icon` (sub_field) |
| `bg-image` | `bg_image` |
| `section-intro` | `section_intro` |
| `card` | `card` |

### Label generation
Derive the ACF label by title-casing the field name with spaces:
- `bg_image` → `Bg Image`
- `cta_link` → `Cta Link`
- `hero_title` → `Hero Title`

> Tip: The user can rename labels in the ACF admin at any time; the name (slug) cannot be changed without losing saved data.

---

## 5. Field Type Configuration Defaults

Used in Step 7 when building the JSON. These are the recommended defaults per type:

| Type | Key settings |
|---|---|
| `text` | `default_value: ""`, `maxlength: ""`, `placeholder: ""` |
| `textarea` | `rows: 4`, `new_lines: "wpautop"` |
| `wysiwyg` | `tabs: "all"`, `toolbar: "full"`, `media_upload: 1` |
| `image` | `return_format: "array"`, `preview_size: "medium"`, `library: "all"` |
| `url` | `default_value: ""`, `placeholder: ""` |
| `link` | `return_format: "array"` (gives url, title, target) |
| `file` | `return_format: "array"`, `library: "all"` |
| `oembed` | `width: ""`, `height: ""` |
| `date_picker` | `display_format: "d/m/Y"`, `return_format: "d/m/Y"`, `first_day: 1` |
| `true_false` | `message: ""`, `default_value: 0`, `ui: 1` |
| `select` | `multiple: 0`, `allow_null: 0`, `return_format: "value"` |
| `number` | `default_value: ""`, `min: ""`, `max: ""`, `step: ""` |
| `email` | `default_value: ""`, `placeholder: ""` |
| `repeater` | `layout: "block"`, `min: 0`, `max: 0`, `button_label: "Add Row"` |
| `group` | `layout: "block"` |
