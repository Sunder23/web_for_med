[← Page Sections](page-sections.md) · [Back to README](../README.md)

# Single Pages (articles)

Services, directions and blog posts share one article layout; cases have their own layout (see [Single case](#single-case)). These templates are plain PHP — they are **not** built from `acf/section-*` blocks. The body text is regular block-editor content (`the_content()`).

## Page structure

| Order | Part | Partial |
|-------|------|---------|
| 1 | Hero: breadcrumbs, H1, meta, lead, note, buttons | `partials/parts/article-layout.php` |
| 2 | Featured image (`large`, eager, `fetchpriority="high"`) | ″ |
| 3 | Blurb cards | ″ |
| 4 | Sidebar: table of contents + quick-contact CTA | `partials/parts/toc.php` |
| 5 | Post content in `.entry-content` | ″ (`the_content()`) |
| 6 | FAQ accordion (CPT only) | `partials/parts/cpt-faq.php` |
| 7 | Previous / next post of the same type (loops at the ends) | `partials/parts/post-nav.php` |
| 8 | "Схожі …" — 3 posts of the same type | `partials/parts/related-posts.php` + `post-card.php` |

Layout: one column on phones (hero → image → blurbs → sidebar → content). From `tablet` (1024px) up there are two columns: content up to 885px and a 295px sticky sidebar. The sidebar is rendered once and positioned with grid areas. `wp_is_mobile()` is not used because it breaks page caching.

## Data per template

| Template | `lead` | Other args | FAQ group |
|----------|--------|------------|------------------|
| `single-services.php` | `service_hero.subtitle` | `note` = `text`, `buttons`, `blurbs` | `service_faq` |
| `single-directions.php` | `direction_hero.description` | `blurbs` | `direction_faq` |
| `single.php` | post excerpt (if set) | `meta` = first category tag + date `d.m.Y` | — |

The `*_cta` ACF groups are no longer rendered. There is no form before the footer any more: every contact CTA opens the contact drawer (see [Page Sections → Contact drawer and footer](page-sections.md#contact-drawer-and-footer)), and the footer of inner pages has no "Далі буде…" cover. Empty fields are skipped. The FAQ heading falls back to "Часті запитання". The related row shows nothing when there are no other posts. Blog posts are matched by first category, with the latest posts as a fallback.

## Single case

`single-cases.php` does not use the article layout (Figma `3061:891` desktop, `3124:1383` mobile). No breadcrumbs, TOC, FAQ, CTA or related cases.

| Order | Part | Partial |
|-------|------|---------|
| 1 | Utility bar: `/*FILE: {client} */` label + `Section: NN/MM` counter | `partials/parts/case-single.php` |
| 2 | Intro: H1 (`the_title()`), lead, image (`full`, eager, `fetchpriority="high"`) | ″ |
| 3 | Project details sidebar (`dl`) | ″ |
| 4 | Post content in `.entry-content.case-single__content` | ″ (`the_content()`) |
| 5 | Prev / next strip (loops at the ends, titles hidden on phones) | `partials/parts/case-nav.php` |

Layout: one column on phones (intro → details → content). From `tablet` (1024px) the details are a 180px sticky column on the left and the intro and content share an 880px column. The sidebar is rendered once and placed with grid areas.

Data (`starter_get_case_single_data()` in `configure/helpers/get-case-single-data.php`):

| Field | Source | Fallback |
|-------|--------|----------|
| Label `client` | ACF `case_intro.client` | post title |
| Lead | ACF `case_intro.text` (wysiwyg) | `case_hero.subtitle` |
| Intro image | ACF `case_intro.image` | featured image |
| Deliverables / Technology / Status | taxonomies `case_deliverable`, `case_technology`, `case_status` (`configure/taxonomies/`, hierarchical, no public URLs); terms by name, Status — first term | skipped |
| Duration, Live Project | ACF `case_details.duration`, `case_details.live_url` + `live_label` (default "View Website") | skipped |

The counter total is 1 (intro) + the content H2s. `assets/src/js/components/caseSectionCounter.js` updates the current section on scroll: the last section whose top passed the upper third of the viewport, the last one at the page bottom. Content images and galleries with a caption sit on a light panel; images in a gallery share one height (`caseGalleries.js` sets `--case-ratio` per image). `case_faq` and `case_cta` are kept in the field group but not rendered; `case_hero.subtitle` is still used by the cases archive.

Prev / next for both strips comes from `starter_get_adjacent_posts()` (`configure/helpers/get-adjacent-posts.php`).

## Table of contents

- Built from the content H2s by `starter_get_toc()` (`configure/toc.php`). Anchors are injected by a `the_content` filter.
- `assets/src/js/components/toc.js` marks the last heading scrolled past as active (`is-active`). One `.toc__indicator` frame slides to it. Clicking a link scrolls through Lenis with the header height as offset (the header is a fixed smart header: hidden while scrolling down, shown while scrolling up).
- When the list is taller than the viewport, only `.toc__nav` scrolls.
- CTA button: `#contact-form` (opens the contact drawer, form from the `footer_contact` option). If that option is empty, the `header_button` link is used and `WARN [toc] footer_contact option is empty…` is logged. The e-mail comes from the `mail` option.

## Content styles and images

- `components/_entry-content.scss` covers headings, paragraphs, square/numbered lists, links, quotes, tables, captions and core buttons. `style-editor.css` mirrors the same rules for the block editor.
- Article editors (posts and CPTs) show an 800px column centred in the canvas. The page editor drops `style-editor.css`.
- Image lightbox: core `core/image` lightbox (`theme.json` → `settings.blocks.core/image.lightbox`). It is on by default and can be turned off per image. Brand colours for the overlay and trigger are in `_lightbox.scss`.

## Assets

| Entry | Enqueued on | Includes |
|-------|-------------|----------|
| `single-cpt.scss` + `single-cpt.js` (deps `jquery`) | `is_singular( services, directions )` | article, entry-content, lightbox, toc, faq-section, faq, post-card, related-posts, post-nav; `initToc`, `initFaqAccordion` |
| `single-post.scss` + `single-post.js` | `is_singular( 'post' )` | same, without FAQ; `initToc` |
| `single-case.scss` + `single-case.js` | `is_singular( 'cases' )` | entry-content, case-single; `initCaseSectionCounter`, `initCaseGalleries` |

All are enqueued from `configure/theme-hooks/enqueue-listing-templates-assets.php` (priority 110, style depends on `main`).

## Blog listings and 404

| Template | Layout | Styles |
|----------|--------|--------|
| `home.php` (posts page), `archive.php` (category, tag, date) | `partials/parts/blog-archive.php`: breadcrumbs, H1, term description, category chips (`category-nav.php`, shown with 2+ non-empty categories), `post-card` grid | `archive.scss` |
| `archive-{services,directions}.php` | `partials/parts/archive-cpt.php` | `archive.scss` |
| `archive-cases.php` | `partials/parts/cases-archive.php`: utility bar + vertical case slider | `cases-archive.scss` |
| `404.php` | Big "404", heading, home/blog buttons, list of services | `error-404.scss` (`starter_enqueue_404_assets`) |

Breadcrumbs (`starter_get_breadcrumb_items()`): post archives show Головна → Блог → term; 404 shows Головна → Сторінку не знайдено.

## See Also

- [Page Sections](page-sections.md) — how `page` posts are built from section blocks
- [Getting Started](getting-started.md) — commands, linting, verifying a refactor
