# ACF JSON Schema & Key Generation Reference

Used by SKILL.md Steps 5–7 to generate deterministic keys and build valid JSON output.

---

## 1. Deterministic Key Generation — CRC32-8

Keys must be **reproducible**: the same group title and field name always produce the same key.
This makes regeneration Git-safe — no phantom diffs from random suffixes.

### Algorithm

```
1. Input string → UTF-8 bytes
2. Initialize register: crc = 0xFFFFFFFF
3. For each byte b:
     crc = (crc >>> 8) XOR table[(crc XOR b) & 0xFF]
   where table[i] = CRC32 of byte i using polynomial 0xEDB88320
4. Finalize: crc = crc XOR 0xFFFFFFFF
5. Result: crc & 0xFFFFFFFF  (32-bit unsigned)
6. Format: zero-padded 8-character lowercase hex string
```

### Precomputed shortcuts for common cases

Rather than implementing CRC32 from scratch, use the input string as-is with any
standard CRC32 implementation (PHP `crc32()`, JS `crc32` npm, Python `binascii.crc32`).
The polynomial and byte order above match the standard IEEE 802.3 CRC32 used by all
major implementations.

> Note: PHP's `crc32()` can return negative values on 32-bit systems. Always apply
> `sprintf('%08x', crc32($input) & 0xFFFFFFFF)` to normalise.

### Input preparation

Normalise input before hashing to ensure identical results regardless of casing or whitespace:

```
group key input  = strtolower(trim(group_title))
field key input  = strtolower(trim(group_title)) . ":" . strtolower(trim(field_name))
```

For sub_fields inside a repeater or group, use the **full dotted path** as the field_name:

```
field key input  = "hero section:features.icon"
                              ↑ repeater name . sub_field name
```

### Key format

```
group_XXXXXXXX   (e.g. group_a3f2c1d8)
field_XXXXXXXX   (e.g. field_7b2e90a1)
```

### Example trace

```
group_title = "Hero Section"

group key:
  input  = "hero section"
  crc32  = 0xa3f2c1d8   (example value)
  result = "group_a3f2c1d8"

field "bg_image":
  input  = "hero section:bg_image"
  crc32  = 0x7b2e90a1   (example value)
  result = "field_7b2e90a1"

field "features.icon" (sub_field inside repeater "features"):
  input  = "hero section:features.icon"
  crc32  = 0x4d1ca832   (example value)
  result = "field_4d1ca832"
```

---

## 2. Field Group JSON Structure

The top-level JSON is an **array** containing one object per field group.
ACF/SCF reads this array when loading from `acf-json/`.

```json
[
  {
    "key": "group_XXXXXXXX",
    "title": "Hero Section",
    "fields": [ /* see §3 */ ],
    "location": [
      [
        {
          "param": "post_type",
          "operator": "==",
          "value": "page"
        }
      ]
    ],
    "menu_order": 0,
    "position": "normal",
    "style": "default",
    "label_placement": "top",
    "instruction_placement": "label",
    "hide_on_screen": "",
    "active": true,
    "description": "",
    "show_in_rest": 0,
    "modified": 1714128000
  }
]
```

**`modified`** must be the current Unix timestamp (integer seconds). ACF uses this to
detect whether the local JSON is newer than the database record and flag it for sync.

---

## 3. Field Object Templates

Every field object requires at minimum: `key`, `label`, `name`, `type`.
All other keys are optional but recommended for predictable admin UI behaviour.

The `order_no` / `menu_order` inside fields is omitted — ACF uses array order.

### text

```json
{
  "key": "field_XXXXXXXX",
  "label": "Title",
  "name": "title",
  "type": "text",
  "instructions": "",
  "required": 0,
  "conditional_logic": 0,
  "wrapper": { "width": "", "class": "", "id": "" },
  "default_value": "",
  "maxlength": "",
  "placeholder": "",
  "prepend": "",
  "append": ""
}
```

### textarea

```json
{
  "key": "field_XXXXXXXX",
  "label": "Description",
  "name": "description",
  "type": "textarea",
  "instructions": "",
  "required": 0,
  "conditional_logic": 0,
  "wrapper": { "width": "", "class": "", "id": "" },
  "default_value": "",
  "rows": 4,
  "new_lines": "wpautop",
  "maxlength": ""
}
```

### wysiwyg

```json
{
  "key": "field_XXXXXXXX",
  "label": "Content",
  "name": "content",
  "type": "wysiwyg",
  "instructions": "",
  "required": 0,
  "conditional_logic": 0,
  "wrapper": { "width": "", "class": "", "id": "" },
  "default_value": "",
  "tabs": "all",
  "toolbar": "full",
  "media_upload": 1,
  "delay": 0
}
```

### image

```json
{
  "key": "field_XXXXXXXX",
  "label": "Background Image",
  "name": "bg_image",
  "type": "image",
  "instructions": "",
  "required": 0,
  "conditional_logic": 0,
  "wrapper": { "width": "", "class": "", "id": "" },
  "return_format": "array",
  "preview_size": "medium",
  "library": "all",
  "min_width": "",
  "min_height": "",
  "min_size": "",
  "max_width": "",
  "max_height": "",
  "max_size": "",
  "mime_types": ""
}
```

### url

```json
{
  "key": "field_XXXXXXXX",
  "label": "Link Url",
  "name": "link_url",
  "type": "url",
  "instructions": "",
  "required": 0,
  "conditional_logic": 0,
  "wrapper": { "width": "", "class": "", "id": "" },
  "default_value": "",
  "placeholder": ""
}
```

### link

```json
{
  "key": "field_XXXXXXXX",
  "label": "Cta Link",
  "name": "cta_link",
  "type": "link",
  "instructions": "",
  "required": 0,
  "conditional_logic": 0,
  "wrapper": { "width": "", "class": "", "id": "" },
  "return_format": "array"
}
```

> `return_format: "array"` gives `url`, `title`, `target` keys. Use `"url"` to get just the URL string.

### file

```json
{
  "key": "field_XXXXXXXX",
  "label": "Video File",
  "name": "video_file",
  "type": "file",
  "instructions": "",
  "required": 0,
  "conditional_logic": 0,
  "wrapper": { "width": "", "class": "", "id": "" },
  "return_format": "array",
  "library": "all",
  "min_size": "",
  "max_size": "",
  "mime_types": ""
}
```

### oembed

```json
{
  "key": "field_XXXXXXXX",
  "label": "Video Embed",
  "name": "video_embed",
  "type": "oembed",
  "instructions": "",
  "required": 0,
  "conditional_logic": 0,
  "wrapper": { "width": "", "class": "", "id": "" },
  "width": "",
  "height": ""
}
```

### date_picker

```json
{
  "key": "field_XXXXXXXX",
  "label": "Event Date",
  "name": "event_date",
  "type": "date_picker",
  "instructions": "",
  "required": 0,
  "conditional_logic": 0,
  "wrapper": { "width": "", "class": "", "id": "" },
  "display_format": "d/m/Y",
  "return_format": "d/m/Y",
  "first_day": 1
}
```

### true_false

```json
{
  "key": "field_XXXXXXXX",
  "label": "Show Section",
  "name": "show_section",
  "type": "true_false",
  "instructions": "",
  "required": 0,
  "conditional_logic": 0,
  "wrapper": { "width": "", "class": "", "id": "" },
  "message": "",
  "default_value": 0,
  "ui": 1,
  "ui_on_text": "",
  "ui_off_text": ""
}
```

### select

```json
{
  "key": "field_XXXXXXXX",
  "label": "Theme Color",
  "name": "theme_color",
  "type": "select",
  "instructions": "",
  "required": 0,
  "conditional_logic": 0,
  "wrapper": { "width": "", "class": "", "id": "" },
  "choices": {
    "light": "Light",
    "dark": "Dark",
    "auto": "Auto"
  },
  "default_value": [],
  "return_format": "value",
  "multiple": 0,
  "allow_null": 0,
  "ui": 0,
  "ajax": 0,
  "placeholder": ""
}
```

> Parse `<option value="X">Label</option>` → `"X": "Label"` in `choices`.

### number

```json
{
  "key": "field_XXXXXXXX",
  "label": "Item Count",
  "name": "item_count",
  "type": "number",
  "instructions": "",
  "required": 0,
  "conditional_logic": 0,
  "wrapper": { "width": "", "class": "", "id": "" },
  "default_value": "",
  "placeholder": "",
  "prepend": "",
  "append": "",
  "min": "",
  "max": "",
  "step": ""
}
```

### email

```json
{
  "key": "field_XXXXXXXX",
  "label": "Contact Email",
  "name": "contact_email",
  "type": "email",
  "instructions": "",
  "required": 0,
  "conditional_logic": 0,
  "wrapper": { "width": "", "class": "", "id": "" },
  "default_value": "",
  "placeholder": ""
}
```

### repeater

```json
{
  "key": "field_XXXXXXXX",
  "label": "Features",
  "name": "features",
  "type": "repeater",
  "instructions": "",
  "required": 0,
  "conditional_logic": 0,
  "wrapper": { "width": "", "class": "", "id": "" },
  "layout": "block",
  "button_label": "Add Row",
  "min": 0,
  "max": 0,
  "sub_fields": [
    /* field objects for each sub-field */
  ]
}
```

### group

```json
{
  "key": "field_XXXXXXXX",
  "label": "Card",
  "name": "card",
  "type": "group",
  "instructions": "",
  "required": 0,
  "conditional_logic": 0,
  "wrapper": { "width": "", "class": "", "id": "" },
  "layout": "block",
  "sub_fields": [
    /* field objects for each sub-field */
  ]
}
```

---

## 4. Location Rule Params Reference

Location rules determine which admin screens show the field group.
The `location` array is an array of **OR** rule groups; each group is an array of **AND** conditions.

### Common params

| `param` | `operator` | `value` example | When it shows |
|---|---|---|---|
| `post_type` | `==` | `"page"`, `"post"`, `"project"` | Editing a post of that type |
| `post_type` | `!=` | `"post"` | Any post type except post |
| `page_template` | `==` | `"templates/home.php"` | Page using that template |
| `post_status` | `==` | `"publish"`, `"draft"` | Post in that status |
| `current_user_role` | `==` | `"administrator"` | Current user has that role |
| `taxonomy` | `==` | `"category"` | Editing a term in that taxonomy |

### Most common — single post type

```json
"location": [[{ "param": "post_type", "operator": "==", "value": "page" }]]
```

### OR — multiple post types

```json
"location": [
  [{ "param": "post_type", "operator": "==", "value": "page" }],
  [{ "param": "post_type", "operator": "==", "value": "project" }]
]
```

### AND — post type + template

```json
"location": [[
  { "param": "post_type",      "operator": "==", "value": "page" },
  { "param": "page_template",  "operator": "==", "value": "templates/home.php" }
]]
```

---

## 5. File Naming Convention

```
acf-json/group_<8hexkey>.json
```

- Always in `<theme-root>/acf-json/`
- Filename uses the group key without the `group_` prefix: `group_a3f2c1d8.json`
  (note: the filename IS `group_a3f2c1d8.json` — the full key string including the `group_` prefix)
- ACF/SCF scans the folder on every page load and auto-registers any valid JSON files found
- No need to import via admin Tools — just place the file and reload
