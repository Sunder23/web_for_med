---
name: theme-json
description: >
  Reads an HTML layout file (Figma plugin export or hand-coded), parses linked CSS for
  design tokens, and generates three outputs: theme.json (v3), a :root block in
  _variables.scss, and a PHP function in js-css.php that emits --wp--preset--* vars +
  .has-*-color/.has-*-background-color utility classes on the frontend (compensating for
  the dequeued global-styles stylesheet). Use when you have a complete HTML/CSS layout
  and want to sync design tokens into WordPress and SCSS without manual entry.
argument-hint: "<path/to/layout.html>"
allowed-tools: Read Write Edit Glob AskUserQuestion
---

# theme-json

Parse an HTML layout file, extract design tokens from linked CSS, and write two outputs:
1. `theme.json` (v3) at the theme root — tells the Gutenberg editor about the palette, font sizes, and layout
2. A `:root {}` CSS custom properties block appended to `_variables.scss` — makes the same tokens available on the frontend

No clarification round. Fully automatic.

Outputs:
1. `theme.json` (v3) — tells the Gutenberg block editor about the palette, font sizes, and font families
2. `:root {}` block in `_variables.scss` — makes all design tokens available for frontend SCSS
3. `output_theme_json_preset_vars()` in `js-css.php` — emits `--wp--preset--*` CSS custom properties + `.has-*` utility classes on the frontend (this theme dequeues `global-styles`, so WordPress never outputs these itself)

---

## Step 0 — Load Config

Read `.ai-factory/config.yaml` if it exists. Locate the theme root:
- Check `paths.description` to infer theme location, or
- Default to `theme/vite-wordpress-starter-theme/`

Resolve output paths:
- **theme.json** → `<theme-root>/theme.json`
- **_variables.scss** → `<theme-root>/assets/src/scss/abstracts/_variables.scss`

---

## Step 1 — Read HTML and Collect CSS Sources

Read the HTML file at the path provided as the skill argument.

If the file does not exist, stop:
```
File not found: <path>
Provide a valid path to an HTML file, e.g.:
  /theme-json theme/vite-wordpress-starter-theme/html-files/index.html
```

Collect CSS from two sources:

**A) External stylesheets** — find all `<link rel="stylesheet" href="...">` tags.
Resolve each `href` relative to the HTML file's directory. Read each CSS file found.

**B) Inline styles** — find all `<style>` tags. Collect their text content.

Combine all CSS text into one string for parsing.

If no CSS is found at all, stop:
```
No CSS found in <path>.
Expected either <link rel="stylesheet" href="..."> or <style> tags.
```

---

## Step 2 — Parse CSS

### 2A: Extract `:root {}` custom properties

Find the `:root { }` block(s) in the combined CSS. Extract every `--name: value;` pair.

Strip the surrounding whitespace from both name and value.

Example input:
```css
:root {
  --color-primary:    #1261a0;
  --font-main:        'Inter', system-ui, sans-serif;
  --container-max:    1440px;
}
```

Example output map:
```
--color-primary    → #1261a0
--font-main        → 'Inter', system-ui, sans-serif
--container-max    → 1440px
```

Categorise by prefix:
- `--color-*` → colors
- `--font-*` → font families
- `--container-max` (exact) → layout contentSize
- Everything else → include verbatim in the `_variables.scss` `:root` block but skip for theme.json

### 2B: Extract font sizes from element rules

Scan the CSS for these selectors and extract their `font-size` value:

| Selector | Name | Slug |
|---|---|---|
| `h1` | Heading 1 | `h1` |
| `h2` | Heading 2 | `h2` |
| `h3` | Heading 3 | `h3` |
| `h4` | Heading 4 | `h4` |
| `h5` | Heading 5 | `h5` |
| `h6` | Heading 6 | `h6` |
| `body` | Base | `base` |
| `.btn`, `button` | Button | `button` |
| `.label` | Small | `small` |

Rules:
- Use only the **desktop** (non-`@media`) values
- If the same `font-size` px value appears under multiple selectors, keep only one entry — prefer the most semantic name (heading > body > button > label order)
- If a selector has no `font-size`, skip it

**px → rem conversion** (always use 16px base regardless of SCSS `$font-size-root`):
```
rem = px ÷ 16
48px → 3rem
40px → 2.5rem
20px → 1.25rem
18px → 1.125rem
16px → 1rem
15px → 0.9375rem
14px → 0.875rem
13px → 0.8125rem
```

---

## Step 3 — Build Token Map

### Colors

For each `--color-*` property:
```
Input:  --color-primary: #1261a0
Slug:   strip "--color-" prefix → "primary"
Name:   title-case each hyphen-separated word → "Primary"

Input:  --color-text-dark: #2e2f30
Slug:   "text-dark"
Name:   "Text Dark"

Output: { "name": "Text Dark", "slug": "text-dark", "color": "#2e2f30" }
```

### Font Families

For each `--font-*` property:
```
Input:  --font-main: 'Inter', system-ui, -apple-system, sans-serif
Slug:   strip "--font-" prefix → "main"
Name:   title-case → "Main"

Output: { "name": "Main", "slug": "main", "fontFamily": "'Inter', system-ui, -apple-system, sans-serif" }
```

If two `--font-*` vars have identical font stack values, include only the first.

### Font Sizes

Collected in Step 2B, already converted to rem. Example:
```
{ "name": "Heading 1", "slug": "h1", "size": "3rem" }
{ "name": "Base",      "slug": "base", "size": "1rem" }
```

### Layout

```
--container-max: 1440px → settings.layout.contentSize = "1440px"
                           settings.layout.wideSize    = "1440px"
```

If `--container-max` is not found, omit the layout section from theme.json.

---

## Step 4 — Check for Existing theme.json

```
Glob: <theme-root>/theme.json
```

If found, ask:

```
AskUserQuestion: theme.json already exists at <theme-root>/theme.json.

Options:
1. Overwrite — replace with newly generated file
2. Abort — keep existing file and stop
```

If Abort → stop:
```
Aborted. Existing theme.json kept.
```

---

## Step 5 — Write theme.json

Construct the JSON using the schema in `references/THEME-JSON-SCHEMA.md`.

Key settings:
```json
{
  "$schema": "https://schemas.wp.org/trunk/theme.json",
  "version": 3,
  "settings": {
    "color": {
      "palette": [ ...colors ],
      "custom": true,
      "customDuotone": false,
      "customGradient": false
    },
    "typography": {
      "fontSizes": [ ...fontSizes ],
      "fontFamilies": [ ...fontFamilies ],
      "customFontSize": true
    },
    "layout": {
      "contentSize": "Xpx",
      "wideSize": "Xpx"
    },
    "useRootPaddingAwareAlignments": false
  }
}
```

Write the file to `<theme-root>/theme.json`.

See `references/EXAMPLES.md` for a complete output example with real values.

---

## Step 6 — Update _variables.scss

Read the existing `_variables.scss` file.

**Check for existing generated block:**
Search the file for the comment marker: `/* theme-json: generated tokens */`

**If marker NOT found** → append to end of file:

```scss

/* theme-json: generated tokens */
:root {
  --color-primary:    #1261a0;
  --color-blue-light: #218ed6;
  /* ... all extracted :root vars verbatim, preserving original names and values */
}
```

Include ALL properties from the source `:root {}` block verbatim — colors, fonts, layout, radius, transition — everything. Preserve original formatting where possible (aligned values).

**If marker IS found** → ask:

```
AskUserQuestion: A generated :root block already exists in _variables.scss.

Options:
1. Replace — remove old block and append new one at end of file
2. Skip — leave _variables.scss unchanged
```

If Replace:
- Remove everything from `/* theme-json: generated tokens */` to the closing `}` of that `:root` block
- Append the new block at the end of the file

If Skip: leave the file as-is.

---

## Step 7 — Update js-css.php

This step ensures `--wp--preset--*` custom properties and `.has-*` utility classes reach the frontend, since this theme dequeues `global-styles` and WordPress never outputs them otherwise.

Locate `js-css.php`:
- Check `<theme-root>/configure/js-css.php` first
- Fallback: Glob `<theme-root>/**/*.php` for a file containing `wp_enqueue_scripts`

Read the file and search for the string `output_theme_json_preset_vars`.

**If NOT found** — append the function to the end of the file:

```php

function output_theme_json_preset_vars() {
    $theme_json_path = get_template_directory() . '/theme.json';
    if ( ! file_exists( $theme_json_path ) ) {
        return;
    }

    $data = json_decode( file_get_contents( $theme_json_path ), true );
    if ( empty( $data['settings'] ) ) {
        return;
    }

    $s     = $data['settings'];
    $lines = [];

    foreach ( $s['color']['palette'] ?? [] as $item ) {
        $lines[] = '  --wp--preset--color--' . $item['slug'] . ': ' . $item['color'] . ';';
    }

    foreach ( $s['typography']['fontFamilies'] ?? [] as $item ) {
        $lines[] = '  --wp--preset--font-family--' . $item['slug'] . ': ' . $item['fontFamily'] . ';';
    }

    foreach ( $s['typography']['fontSizes'] ?? [] as $item ) {
        $lines[] = '  --wp--preset--font-size--' . $item['slug'] . ': ' . $item['size'] . ';';
    }

    if ( empty( $lines ) ) {
        return;
    }

    $css = ':root {' . "\n" . implode( "\n", $lines ) . "\n}";

    $utilities = [];
    foreach ( $s['color']['palette'] ?? [] as $item ) {
        $slug        = $item['slug'];
        $var         = 'var(--wp--preset--color--' . $slug . ')';
        $utilities[] = '.has-' . $slug . '-color { color: ' . $var . ' !important; }';
        $utilities[] = '.has-' . $slug . '-background-color { background-color: ' . $var . ' !important; }';
    }
    foreach ( $s['typography']['fontSizes'] ?? [] as $item ) {
        $slug        = $item['slug'];
        $utilities[] = '.has-' . $slug . '-font-size { font-size: var(--wp--preset--font-size--' . $slug . ') !important; }';
    }
    foreach ( $s['typography']['fontFamilies'] ?? [] as $item ) {
        $slug        = $item['slug'];
        $utilities[] = '.has-' . $slug . '-font-family { font-family: var(--wp--preset--font-family--' . $slug . ') !important; }';
    }

    if ( ! empty( $utilities ) ) {
        $css .= "\n" . implode( "\n", $utilities );
    }

    wp_add_inline_style( 'main', $css );
}
add_action( 'wp_enqueue_scripts', 'output_theme_json_preset_vars', 101 );
```

Hook priority `101` ensures it runs after `add_vite_assets` (100) so the `main` stylesheet handle is registered before `wp_add_inline_style` is called.

**If found** — the function already exists. Ask:

```
AskUserQuestion: output_theme_json_preset_vars() already exists in js-css.php.

Options:
1. Replace — overwrite with updated function body
2. Skip — leave js-css.php unchanged
```

If Replace: locate the function from `function output_theme_json_preset_vars()` to its closing `}` (and the following `add_action` line), replace with the new version above.

If Skip: leave the file as-is.

---

## Step 8 — Output Summary

```
theme.json written: <theme-root>/theme.json
  Colors:        N
  Font families: N
  Font sizes:    N
  Layout:        contentSize = Xpx  (omitted if no --container-max found)

_variables.scss updated: <theme-root>/assets/src/scss/abstracts/_variables.scss
  Added :root block with N custom properties

js-css.php updated: <theme-root>/configure/js-css.php
  output_theme_json_preset_vars() added — outputs --wp--preset--* vars + .has-* utility classes
  Hook: wp_enqueue_scripts @ priority 101

ℹ️  This theme dequeues global-styles, so WordPress never outputs --wp--preset--* vars or
    .has-*-color utility classes on the frontend. The PHP function added to js-css.php
    compensates for this. theme.json serves the block editor only.
```

---

## Reference Files

- `references/THEME-JSON-SCHEMA.md` — annotated theme.json v3 structure, field rules, slug naming
- `references/EXAMPLES.md` — end-to-end worked example: web4med CSS → token map → theme.json + _variables.scss
