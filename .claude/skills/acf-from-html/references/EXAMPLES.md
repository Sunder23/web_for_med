# acf-from-html — Worked Example

End-to-end example: hero section HTML → draft field map → clarifications → JSON output → PHP snippet.

---

## Input HTML

File: `theme/vite-wordpress-starter-theme/templates/hero.html`

```html
<section class="hero">
  <img class="hero__bg" src="placeholder.jpg" alt="Hero background">
  <h1 class="hero__title">Welcome to Our Site</h1>
  <p class="hero__description">Short introductory text goes here.</p>
  <a class="hero__cta" href="#">Learn more</a>
  <ul class="hero__features">
    <li class="hero__feature">
      <img class="feature__icon" src="icon.svg" alt="Feature icon">
      <span class="feature__label">Fast delivery</span>
    </li>
    <li class="hero__feature">
      <img class="feature__icon" src="icon.svg" alt="Feature icon">
      <span class="feature__label">24/7 support</span>
    </li>
  </ul>
</section>
```

---

## Step 3 Output — Draft Field Map

```
hero (section — top-level wrapper, likely ignore)

  hero__bg         → image          (auto: <img>)
  hero__title      → text           (auto: <h1>)
  hero__description → ??? ambiguous (<p> without content class)
  hero__cta        → ??? ambiguous  (<a> with visible text "Learn more")
  hero__features   → repeater       (auto: 2× <li class="hero__feature">)
    feature__icon  → image          (auto: <img>)
    feature__label → text           (auto: <span>)
```

The `<section class="hero">` is the top-level wrapper and is skipped (not mapped to a field).

---

## Step 4 — Clarification Round

**AskUserQuestion** (one call, batched):

> **Draft field map for `hero.html`:**
>
> - `bg_image` → image
> - `title` → text
> - `description` → **?** (ambiguous `<p>`)
> - `cta` → **?** (ambiguous `<a>` with text)
> - `features` → repeater
>   - `icon` → image
>   - `label` → text
>
> **Q1:** What is the field group title?
> _(e.g. "Hero Section")_
>
> **Q2:** Where should this group appear?
> - post_type == page
> - post_type == post
> - page_template == templates/home.php
> - Other
>
> **Q3:** `hero__description` — `<p>` without content class. Which field type?
> - text (single line)
> - textarea (multi-line plain text)
> - wysiwyg (rich text editor)
>
> **Q4:** `hero__cta` — `<a>` with visible text "Learn more". Which field type?
> - url (stores href only)
> - link (stores href + label + target)

**User answers:**
- Q1: `Hero Section`
- Q2: `post_type == page`
- Q3: `textarea`
- Q4: `link`

---

## Step 5 — Key Computation Trace

```
group_title = "hero section"   (lowercased trim of "Hero Section")

group key:
  crc32("hero section")        → 0xb6d23f9c  → "group_b6d23f9c"

field keys:
  crc32("hero section:bg_image")        → "field_XXXXXXXX"
  crc32("hero section:title")           → "field_XXXXXXXX"
  crc32("hero section:description")     → "field_XXXXXXXX"
  crc32("hero section:cta")             → "field_XXXXXXXX"
  crc32("hero section:features")        → "field_XXXXXXXX"
  crc32("hero section:features.icon")   → "field_XXXXXXXX"  ← dotted path for sub_field
  crc32("hero section:features.label")  → "field_XXXXXXXX"
```

> The actual hex values depend on your CRC32 implementation.
> The example uses `XXXXXXXX` as a placeholder — real output will be 8-char hex.

---

## Step 7 Output — group_b6d23f9c.json

Written to `theme/vite-wordpress-starter-theme/acf-json/group_b6d23f9c.json`:

```json
[
  {
    "key": "group_b6d23f9c",
    "title": "Hero Section",
    "fields": [
      {
        "key": "field_a1b2c3d4",
        "label": "Bg Image",
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
      },
      {
        "key": "field_e5f6a7b8",
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
      },
      {
        "key": "field_c9d0e1f2",
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
      },
      {
        "key": "field_03a4b5c6",
        "label": "Cta",
        "name": "cta",
        "type": "link",
        "instructions": "",
        "required": 0,
        "conditional_logic": 0,
        "wrapper": { "width": "", "class": "", "id": "" },
        "return_format": "array"
      },
      {
        "key": "field_d7e8f9a0",
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
          {
            "key": "field_b1c2d3e4",
            "label": "Icon",
            "name": "icon",
            "type": "image",
            "instructions": "",
            "required": 0,
            "conditional_logic": 0,
            "wrapper": { "width": "", "class": "", "id": "" },
            "return_format": "array",
            "preview_size": "thumbnail",
            "library": "all",
            "min_width": "",
            "min_height": "",
            "min_size": "",
            "max_width": "",
            "max_height": "",
            "max_size": "",
            "mime_types": ""
          },
          {
            "key": "field_f5a6b7c8",
            "label": "Label",
            "name": "label",
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
        ]
      }
    ],
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

---

## Step 8 Output — Optional PHP Template Snippet

```php
<?php
// Hero Section — ACF field consumption
// Generated by /acf-from-html

$bg_image    = get_field('bg_image');
$title       = get_field('title');
$description = get_field('description');
$cta         = get_field('cta');
?>

<section class="hero">

  <?php if ($bg_image): ?>
    <img class="hero__bg"
         src="<?= esc_url($bg_image['url']) ?>"
         alt="<?= esc_attr($bg_image['alt']) ?>"
         width="<?= esc_attr($bg_image['width']) ?>"
         height="<?= esc_attr($bg_image['height']) ?>">
  <?php endif; ?>

  <?php if ($title): ?>
    <h1 class="hero__title"><?= esc_html($title) ?></h1>
  <?php endif; ?>

  <?php if ($description): ?>
    <p class="hero__description"><?= esc_html($description) ?></p>
  <?php endif; ?>

  <?php if ($cta): ?>
    <a class="hero__cta"
       href="<?= esc_url($cta['url']) ?>"
       <?= $cta['target'] ? 'target="' . esc_attr($cta['target']) . '"' : '' ?>>
      <?= esc_html($cta['title']) ?>
    </a>
  <?php endif; ?>

  <?php if (have_rows('features')): ?>
    <ul class="hero__features">
      <?php while (have_rows('features')): the_row(); ?>
        <?php $icon = get_sub_field('icon'); ?>
        <li class="hero__feature">
          <?php if ($icon): ?>
            <img class="feature__icon"
                 src="<?= esc_url($icon['url']) ?>"
                 alt="<?= esc_attr($icon['alt']) ?>">
          <?php endif; ?>
          <span class="feature__label">
            <?= esc_html(get_sub_field('label')) ?>
          </span>
        </li>
      <?php endwhile; ?>
    </ul>
  <?php endif; ?>

</section>
```

---

## Confirmation Output (Step 7)

```
✅ Field group written: acf-json/group_b6d23f9c.json

  Group:  Hero Section  (key: group_b6d23f9c)
  Fields: 5 fields generated (including 2 sub_fields in repeater "features")
  Plugin: ACF Pro
  Target: post_type == page

ACF/SCF will auto-load this group on next page request.
To sync via admin: Tools → Sync → click Sync for this group.
```
