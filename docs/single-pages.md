[← Page Sections](page-sections.md) · [Back to README](../README.md)

# Single Pages (articles)

Services, directions, cases and blog posts share one article layout. These templates are plain PHP — they are **not** built from `acf/section-*` blocks. The body text is regular block-editor content (`the_content()`).

## Page structure

| Order | Part | Partial |
|-------|------|---------|
| 1 | Hero: breadcrumbs, H1, meta, lead, note, buttons | `partials/parts/article-layout.php` |
| 2 | Featured image (`large`, eager, `fetchpriority="high"`) | ″ |
| 3 | Blurb cards | ″ |
| 4 | Sidebar: table of contents + quick-contact CTA | `partials/parts/toc.php` |
| 5 | Post content in `.entry-content` | ″ (`the_content()`) |
| 6 | FAQ accordion (CPT only) | `partials/parts/cpt-faq.php` |
| 7 | "Схожі …" — 3 posts of the same type | `partials/parts/related-posts.php` + `post-card.php` |
| 8 | Navy CTA banner | `partials/parts/cpt-cta.php` |

Layout: one column on phones (hero → image → blurbs → sidebar → content). From `tablet` (1024px) up there are two columns: content up to 885px and a 295px sticky sidebar. The sidebar is rendered once and positioned with grid areas. `wp_is_mobile()` is not used because it breaks page caching.

## Data per template

| Template | `lead` | Other args | FAQ / CTA groups |
|----------|--------|------------|------------------|
| `single-services.php` | `service_hero.subtitle` | `note` = `text`, `buttons`, `blurbs` | `service_faq` / `service_cta` |
| `single-directions.php` | `direction_hero.description` | `blurbs` | `direction_faq` / `direction_cta` |
| `single-cases.php` | `case_hero.subtitle` | — | `case_faq` / `case_cta` |
| `single.php` | post excerpt (if set) | `meta` = first category tag + date `d.m.Y` | default CTA strings |

Empty fields are skipped. The FAQ heading falls back to "Часті запитання". The related row shows nothing when there are no other posts. Blog posts are matched by first category, with the latest posts as a fallback.

## Table of contents

- Built from the content H2s by `starter_get_toc()` (`configure/toc.php`). Anchors are injected by a `the_content` filter.
- `assets/src/js/components/toc.js` marks the last heading scrolled past as active (`is-active`). One `.toc__indicator` frame slides to it. Clicking a link scrolls through Lenis with the sticky header offset.
- When the list is taller than the viewport, only `.toc__nav` scrolls.
- CTA button: `#contacts` (footer form, `footer_contact` option). If that option is empty, the `header_button` link is used and `WARN [toc] footer_contact option is empty…` is logged. The e-mail comes from the `mail` option.

## Content styles and images

- `components/_entry-content.scss` covers headings, paragraphs, square/numbered lists, links, quotes, tables, captions and core buttons. `style-editor.css` mirrors the same rules for the block editor.
- Article editors (posts and CPTs) show an 800px column centred in the canvas. The page editor drops `style-editor.css`.
- Image lightbox: core `core/image` lightbox (`theme.json` → `settings.blocks.core/image.lightbox`). It is on by default and can be turned off per image. Brand colours for the overlay and trigger are in `_entry-content.scss`.

## Assets

| Entry | Enqueued on | Includes |
|-------|-------------|----------|
| `single-cpt.scss` + `single-cpt.js` (deps `jquery`) | `is_singular( services, directions, cases )` | article, entry-content, toc, faq, post-card, related-posts, cta-banner; `initToc`, `initFaqAccordion` |
| `single-post.scss` + `single-post.js` | `is_singular( 'post' )` | same, without FAQ; `initToc` |

Both are enqueued from `configure/theme-hooks/enqueue-listing-templates-assets.php` (priority 110, style depends on `main`).

## See Also

- [Page Sections](page-sections.md) — how `page` posts are built from section blocks
- [Getting Started](getting-started.md) — commands, linting, verifying a refactor
