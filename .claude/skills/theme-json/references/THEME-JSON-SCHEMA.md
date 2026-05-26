# theme.json v3 Schema Reference

Used by the `theme-json` skill to construct the output file.

---

## Full Annotated Skeleton

```json
{
  "$schema": "https://schemas.wp.org/trunk/theme.json",
  "version": 3,
  "settings": {
    "color": {
      "palette": [
        {
          "name": "Primary",
          "slug": "primary",
          "color": "#1261a0"
        }
      ],
      "custom": true,
      "customDuotone": false,
      "customGradient": false
    },
    "typography": {
      "fontSizes": [
        {
          "name": "Heading 1",
          "slug": "h1",
          "size": "3rem"
        }
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

## Field Reference

### `$schema`
Always: `"https://schemas.wp.org/trunk/theme.json"`

### `version`
Always: `3` (requires WordPress 6.6+)

---

### `settings.color.palette[]`

| Field | Rule |
|---|---|
| `slug` | Lowercase, hyphen-separated. Stripped from `--color-` prefix of the CSS var name. Example: `--color-text-dark` → `"text-dark"` |
| `name` | Title-case of the slug parts. `"text-dark"` → `"Text Dark"` |
| `color` | Hex value verbatim from the CSS custom property value |

WordPress generates `--wp--preset--color--<slug>` from each entry.

### `settings.color.custom`
`true` — editors can pick any color beyond the defined palette.
Set to `false` only to lock the editor to palette-only colors.

### `settings.color.customDuotone`
`false` — hides the duotone filter picker. Classic themes almost never use it.

### `settings.color.customGradient`
`false` — hides the gradient picker. Classic themes almost never use it.

---

### `settings.typography.fontSizes[]`

| Field | Rule |
|---|---|
| `slug` | Short semantic identifier: `h1`, `h2`, `h3`, `base`, `button`, `small` |
| `name` | Human-readable label shown in editor: `"Heading 1"`, `"Base"`, etc. |
| `size` | Always in `rem`. Conversion base: **16px** (independent of SCSS `$font-size-root`). Example: 48px → `"3rem"` |

WordPress generates `--wp--preset--font-size--<slug>` from each entry.

### `settings.typography.fontFamilies[]`

| Field | Rule |
|---|---|
| `slug` | Stripped from `--font-` prefix: `--font-main` → `"main"` |
| `name` | Title-case: `"Main"` |
| `fontFamily` | Full font stack string, quotes preserved: `"'Inter', system-ui, sans-serif"` |

WordPress generates `--wp--preset--font-family--<slug>` from each entry.

### `settings.typography.customFontSize`
`true` — editors can type any font size. Matches the open-editor policy.

---

### `settings.layout.contentSize`
Max width of content blocks in the editor. Set from `--container-max` CSS var.

### `settings.layout.wideSize`
Max width of "wide" aligned blocks. Set equal to `contentSize` unless the design has a distinct wide breakpoint.

### `settings.useRootPaddingAwareAlignments`
`false` — prevents WordPress from adding root-level padding custom properties, which would conflict with the theme's own container/padding system.

---

## WordPress CSS Custom Property Names Generated

From theme.json settings, WordPress generates these CSS custom properties **in the editor and in global-styles output**:

```
--wp--preset--color--primary
--wp--preset--color--text-dark
--wp--preset--font-size--h1
--wp--preset--font-size--base
--wp--preset--font-family--main
--wp--preset--layout--content-size   (from contentSize)
--wp--preset--layout--wide-size      (from wideSize)
```

### Important: these vars do NOT reach the frontend in this boilerplate

`js-css.php` dequeues the `global-styles` stylesheet:
```php
wp_dequeue_style('global-styles');
```

WordPress only outputs the `--wp--preset--*` vars inside the `global-styles` stylesheet.
When that stylesheet is dequeued, the vars exist only in the **block editor admin**, not on the frontend.

**Use the `:root {}` block added to `_variables.scss` for all frontend SCSS references.**
