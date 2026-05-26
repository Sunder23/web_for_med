# theme-json Skill — Worked Example

End-to-end walkthrough using the real web4med project files.

Input files:
- `theme/vite-wordpress-starter-theme/html-files/index.html`
- `theme/vite-wordpress-starter-theme/html-files/css/style.css`

Invocation:
```
/theme-json theme/vite-wordpress-starter-theme/html-files/index.html
```

---

## Section 1 — Extracted Token Map

### Step 1 — CSS source discovery

`index.html` contains:
```html
<link rel="stylesheet" href="css/style.css">
```
→ Read `html-files/css/style.css`

No `<style>` tags found inline.

---

### Step 2 — `:root {}` custom properties extracted

```
--color-primary:    #1261a0
--color-blue-light: #218ed6
--color-text-dark:  #2e2f30
--color-text-body:  #3b3e41
--color-bg-dark:    #144166
--color-bg-grey:    #f8f8f8
--color-border:     #dbdbdb
--color-bg-white:   #fbfcfd
--color-btn-sec:    #d8dde0
--color-white:      #ffffff
--container-max:    1440px
--container-pad:    45px
--font-main:        'Inter', system-ui, -apple-system, sans-serif
--font-logo:        'Inter', system-ui, sans-serif
--radius:           4px
--transition:       0.25s ease
```

Categorised:
- `--color-*` (10) → colors
- `--font-*` (2) → font families; `--font-logo` has same base font as `--font-main`, so only `--font-main` is emitted
- `--container-max` → layout contentSize
- `--container-pad`, `--radius`, `--transition` → verbatim in `:root` block, skipped for theme.json

---

### Step 3 — Font sizes extracted from element rules

Desktop values only (ignoring `@media` breakpoints):

| Selector | px | rem | Name | Slug |
|---|---|---|---|---|
| `h1` | 48px | 3rem | Heading 1 | `h1` |
| `h2` | 40px | 2.5rem | Heading 2 | `h2` |
| `h3` | 20px | 1.25rem | Heading 3 | `h3` |
| `body` | 16px | 1rem | Base | `base` |
| `.btn` | 18px | 1.125rem | Button | `button` |
| `.label` | 13px | 0.8125rem | Small | `small` |

Note: `15px` appears in several card/body paragraph rules but has no dedicated semantic selector → omitted (not a distinct type scale step).

---

### Step 3 — Token map (final)

**Colors (10):**
```json
{ "name": "Primary",           "slug": "primary",        "color": "#1261a0" }
{ "name": "Blue Light",        "slug": "blue-light",     "color": "#218ed6" }
{ "name": "Text Dark",         "slug": "text-dark",      "color": "#2e2f30" }
{ "name": "Text Body",         "slug": "text-body",      "color": "#3b3e41" }
{ "name": "Background Dark",   "slug": "bg-dark",        "color": "#144166" }
{ "name": "Background Grey",   "slug": "bg-grey",        "color": "#f8f8f8" }
{ "name": "Border",            "slug": "border",         "color": "#dbdbdb" }
{ "name": "Background White",  "slug": "bg-white",       "color": "#fbfcfd" }
{ "name": "Button Secondary",  "slug": "btn-sec",        "color": "#d8dde0" }
{ "name": "White",             "slug": "white",          "color": "#ffffff" }
```

**Font families (1):**
```json
{ "name": "Main", "slug": "main", "fontFamily": "'Inter', system-ui, -apple-system, sans-serif" }
```
(`--font-logo` deduplicated — same font stack as `--font-main`)

**Font sizes (6):**
```json
{ "name": "Heading 1", "slug": "h1",     "size": "3rem"      }
{ "name": "Heading 2", "slug": "h2",     "size": "2.5rem"    }
{ "name": "Heading 3", "slug": "h3",     "size": "1.25rem"   }
{ "name": "Base",      "slug": "base",   "size": "1rem"      }
{ "name": "Button",    "slug": "button", "size": "1.125rem"  }
{ "name": "Small",     "slug": "small",  "size": "0.8125rem" }
```

**Layout:**
```
contentSize: "1440px"
wideSize:    "1440px"
```

---

## Section 2 — Output A: theme.json

Written to: `theme/vite-wordpress-starter-theme/theme.json`

```json
{
  "$schema": "https://schemas.wp.org/trunk/theme.json",
  "version": 3,
  "settings": {
    "color": {
      "palette": [
        { "name": "Primary",          "slug": "primary",   "color": "#1261a0" },
        { "name": "Blue Light",       "slug": "blue-light", "color": "#218ed6" },
        { "name": "Text Dark",        "slug": "text-dark",  "color": "#2e2f30" },
        { "name": "Text Body",        "slug": "text-body",  "color": "#3b3e41" },
        { "name": "Background Dark",  "slug": "bg-dark",    "color": "#144166" },
        { "name": "Background Grey",  "slug": "bg-grey",    "color": "#f8f8f8" },
        { "name": "Border",           "slug": "border",     "color": "#dbdbdb" },
        { "name": "Background White", "slug": "bg-white",   "color": "#fbfcfd" },
        { "name": "Button Secondary", "slug": "btn-sec",    "color": "#d8dde0" },
        { "name": "White",            "slug": "white",      "color": "#ffffff" }
      ],
      "custom": true,
      "customDuotone": false,
      "customGradient": false
    },
    "typography": {
      "fontSizes": [
        { "name": "Heading 1", "slug": "h1",     "size": "3rem"      },
        { "name": "Heading 2", "slug": "h2",     "size": "2.5rem"    },
        { "name": "Heading 3", "slug": "h3",     "size": "1.25rem"   },
        { "name": "Base",      "slug": "base",   "size": "1rem"      },
        { "name": "Button",    "slug": "button", "size": "1.125rem"  },
        { "name": "Small",     "slug": "small",  "size": "0.8125rem" }
      ],
      "fontFamilies": [
        {
          "name": "Main",
          "slug": "main",
          "fontFamily": "'Inter', system-ui, -apple-system, sans-serif"
        }
      ],
      "customFontSize": true
    },
    "layout": {
      "contentSize": "1440px",
      "wideSize": "1440px"
    },
    "useRootPaddingAwareAlignments": false
  }
}
```

---

## Section 3 — Output B: _variables.scss addition

Appended to: `theme/vite-wordpress-starter-theme/assets/src/scss/abstracts/_variables.scss`

The existing SCSS variables (`$primary`, `$font-size-base`, etc.) are left untouched.
The following block is added at the end of the file:

```scss

/* theme-json: generated tokens */
:root {
  --color-primary:    #1261a0;
  --color-blue-light: #218ed6;
  --color-text-dark:  #2e2f30;
  --color-text-body:  #3b3e41;
  --color-bg-dark:    #144166;
  --color-bg-grey:    #f8f8f8;
  --color-border:     #dbdbdb;
  --color-bg-white:   #fbfcfd;
  --color-btn-sec:    #d8dde0;
  --color-white:      #ffffff;
  --container-max:    1440px;
  --container-pad:    45px;
  --font-main:        'Inter', system-ui, -apple-system, sans-serif;
  --font-logo:        'Inter', system-ui, sans-serif;
  --radius:           4px;
  --transition:       0.25s ease;
}
```

All `:root` properties from the source CSS are included verbatim — including `--container-pad`, `--radius`, and `--transition` which are useful in SCSS even though they have no theme.json equivalent.

---

## Section 4 — Output C: js-css.php update

Appended to: `theme/vite-wordpress-starter-theme/configure/js-css.php`

The `output_theme_json_preset_vars()` function is added, hooked at `wp_enqueue_scripts` priority 101.

It does two things on every frontend page load:
1. Emits a `:root {}` block with `--wp--preset--color--*`, `--wp--preset--font-family--*`, and `--wp--preset--font-size--*` CSS custom properties read directly from `theme.json`
2. Emits `.has-<slug>-color`, `.has-<slug>-background-color`, `.has-<slug>-font-size`, and `.has-<slug>-font-family` utility classes that mirror what WordPress would normally output in `global-styles`

This is necessary because the theme dequeues `global-styles` in two places (`configure.php` and `js-css.php`), so WordPress never outputs these vars or classes on the frontend.

Example of the generated inline CSS (for the web4med palette):
```css
:root {
  --wp--preset--color--primary: #1261a0;
  --wp--preset--color--blue-light: #218ed6;
  /* ... */
  --wp--preset--font-family--main: 'Inter', system-ui, -apple-system, sans-serif;
  --wp--preset--font-size--h1: 3rem;
  /* ... */
}
.has-primary-color { color: var(--wp--preset--color--primary) !important; }
.has-primary-background-color { background-color: var(--wp--preset--color--primary) !important; }
.has-blue-light-color { color: var(--wp--preset--color--blue-light) !important; }
.has-blue-light-background-color { background-color: var(--wp--preset--color--blue-light) !important; }
.has-h1-font-size { font-size: var(--wp--preset--font-size--h1) !important; }
.has-main-font-family { font-family: var(--wp--preset--font-family--main) !important; }
/* ... */
```

---

## Summary Output (what the skill prints)

```
theme.json written: theme/vite-wordpress-starter-theme/theme.json
  Colors:        10
  Font families:  1
  Font sizes:     6
  Layout:        contentSize = 1440px

_variables.scss updated: theme/vite-wordpress-starter-theme/assets/src/scss/abstracts/_variables.scss
  Added :root block with 16 custom properties

js-css.php updated: theme/vite-wordpress-starter-theme/configure/js-css.php
  output_theme_json_preset_vars() added — outputs --wp--preset--* vars + .has-* utility classes
  Hook: wp_enqueue_scripts @ priority 101

ℹ️  This theme dequeues global-styles, so WordPress never outputs --wp--preset--* vars or
    .has-*-color utility classes on the frontend. The PHP function added to js-css.php
    compensates for this. theme.json serves the block editor only.
```
