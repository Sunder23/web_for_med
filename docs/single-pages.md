[← Page Sections](page-sections.md) · [Back to README](../README.md)

# Single Pages (articles)

Services and directions share one article layout; blog posts (see [Blog post](#blog-post)) and cases (see [Single case](#single-case)) have their own layouts. These templates are plain PHP — they are **not** built from `acf/section-*` blocks. The body text is regular block-editor content (`the_content()`).

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

The `*_cta` ACF groups are no longer rendered. There is no form before the footer any more: every contact CTA opens the contact drawer (see [Page Sections → Contact drawer and footer](page-sections.md#contact-drawer-and-footer)), and the footer of inner pages has no "Далі буде…" cover. Empty fields are skipped. The FAQ heading falls back to "Часті запитання". The related row shows nothing when there are no other posts.

## Blog post

`single.php` (Figma `3453:479` desktop, `3493:839` mobile) uses `partials/parts/post-single.php`, not the article layout. No sidebar, FAQ or prev/next.

| Order | Part | Partial |
|-------|------|---------|
| 1 | Utility bar (sticky, shared `mixins/_utility-bar.scss` with the case page): breadcrumbs `home / blog / title` in one line (`partials/breadcrumbs.php` with `class` `breadcrumbs--bar` and `labels`) + `section: NN/MM` counter | `partials/parts/post-single.php` |
| 2 | 880px column: H1 (centred from `tablet`), author line, lead = post excerpt, cover = featured image (`full`, eager, `fetchpriority="high"`, crop 880/583) | ″ + `partials/parts/post-author.php` |
| 3 | «Зміст»: `[ 01 ]` + H2 text, anchor links, built by `starter_get_toc()` | ″ |
| 4 | Post content in `.entry-content.post-single__content` | ″ (`the_content()`) |
| 5 | «Актуальні теми»: Swiper row of compact blog cards | `partials/parts/post-related.php` + `blog-card.php` |

- **Author:** `starter_get_post_author()` (`configure/helpers/get-post-author.php`) → name, author archive URL, avatar from the user ACF field `user_avatar` (`group_starter_user_profile`); without it `get_avatar()`. A post without an author logs `WARN [blog] post #ID has no author`.
- **Counter:** total = 1 (intro, incl. «Зміст») + content H2s. `assets/src/js/components/sectionCounter.js` (shared with the case page; markup `[data-section-counter]` > `[data-section-current]` / `[data-section-total]`, selectors passed to `initSectionCounter({ intro, headings })`). H2 have `scroll-margin-top` = header + bar + 24px, so «Зміст» anchors are not hidden under the sticky bar.
- **Content styles** live only under `.post-single__content` (`_post-single.scss`), so CPT singles and cases are untouched: H2 Grunt 36/1.18 (phones 26/32), paragraphs 17/1.55 (16/25), `core/pullquote` without citation (lines above and below, Fixel Medium 32 → 20 on phones), block styles on `core/group` — `is-style-figure` (light panel, two pictures + one caption paragraph `article-figure__caption`) and `is-style-callout` (light panel, H3 so it stays out of «Зміст», paragraphs, `core/button` → `#contact-form` opens the contact drawer; full width on phones). Helpers `is-hidden-mobile` / `is-hidden-desktop` switch at `tablet` (the figure shows a horizontal scheme on desktop and a vertical one on phones). Styles are registered in `configure/theme-hooks/register-block-styles.php` (list of `[ block, style ]` pairs). They are not mirrored in `style-editor.css`.
- **«Актуальні теми»:** up to 8 latest posts of the same category, topped up with the latest posts when fewer than 2; nothing without other posts. `postRelatedSlider.js`: `slidesPerView: 'auto'`, cards 361px from `tablet` / 335px from `mobile` / full width below, arrows from `tablet` only, hidden when everything fits (`watchOverflow`). Swiper CSS comes with the JS entry, only on single posts.

Content script: `scripts/add-blog-post-private-practice.php` (+ `scripts/data/blog/`) creates or updates the post «Від приватної практики до власного центру: коли потрібен сайт» (texts verbatim from the design, media uploaded once by `_starter_source`, old content kept in `_starter_content_backup`). `scripts/update-blog-author.php` creates the author user `valerii-syshchuk` with the avatar and sets it as the author of all posts. Both: dry run by default, `apply` to write.

## Single case

`single-cases.php` does not use the article layout (Figma `3061:891` / `3402:462` desktop, `3124:1383` mobile). No breadcrumbs, TOC, FAQ, CTA or related cases.

| Order | Part | Partial |
|-------|------|---------|
| 1 | Utility bar (sticky): `/*CASE: {project_name} */` label + `Section: NN/MM` counter | `partials/parts/case-single.php` |
| 2 | Intro: H1 (`the_title()`), lead, image (`full`, eager, `fetchpriority="high"`) or the theme placeholder | ″ |
| 3 | Project details sidebar (`dl`) | ″ |
| 4 | Post content in `.entry-content.case-single__content` | ″ (`the_content()`) |
| 5 | Prev / next strip (loops at the ends, titles hidden on phones) | `partials/parts/case-nav.php` |

Layout: one column on phones (intro → details → content). From `tablet` (1024px) the details are a 180px column on the left (not sticky, it scrolls with the page) and the intro and content share an 880px column. The sidebar is rendered once and placed with grid areas.

Data (`starter_get_case_single_data()` in `configure/helpers/get-case-single-data.php`):

| Field | Source | Fallback |
|-------|--------|----------|
| Label `project_name` | ACF `case_intro.project_name` | `case_intro.client` → post title |
| Lead | ACF `case_intro.text` (wysiwyg) | `case_hero.subtitle` |
| Intro image | ACF `case_intro.image` | featured image → placeholder `starter_get_case_placeholder_image()` (`static/images/case-placeholder.webp`, 783×573, "Case file is rendering"), only when the case has a lead or content. The cases archive slider uses the same placeholder for a case without `case_archive.cover` and featured image |
| Deliverables / Technology / Status | taxonomies `case_deliverable`, `case_technology`, `case_status` (`configure/taxonomies/`, hierarchical, no public URLs); terms by name, Status — first term | skipped |
| Duration, Live Project | ACF `case_details.duration`, `case_details.live_url` + `live_label` (default "View Website") | skipped |

The counter total is 1 (intro) + the content H2s. `assets/src/js/components/sectionCounter.js` (shared with the blog post) updates the current section on scroll: the last section whose top passed the upper third of the viewport, the last one at the page bottom. Content headings from `tablet`: H2 36px, H2 with the block style `chapter` 38px, H3 36px (50px above, 28px below; 28px between a chapter and its first H3). Content images and galleries with a caption sit on a light panel; images in a gallery share one height (`caseGalleries.js` sets `--case-ratio` per image). `case_faq` and `case_cta` are kept in the field group but not rendered; `case_hero.subtitle` is still used by the cases archive.

Content scripts: `scripts/update-promedi-case.php` (block styles, phone 404 image) and `scripts/update-khmilclinic-case.php` (full refill of the Khmilclinic case from Figma `3402:462`: media, ACF intro/details, terms, block content; dry run by default, `apply` to write, original content in `_khmilclinic_content_backup`).

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
| `single-post.scss` + `single-post.js` | `is_singular( 'post' )` | entry-content, post-author, post-single, blog-card, post-related (+ Swiper CSS); `initSectionCounter`, `initPostRelatedSlider` |
| `single-case.scss` + `single-case.js` | `is_singular( 'cases' )` | entry-content, case-single; `initSectionCounter`, `initCaseGalleries` |
| `blog-archive.scss` + `blog-archive.js` | `starter_is_blog_listing()`: `is_home()`, category, tag, author, date | post-author, blog-card, blog-archive; `initBlogLoadMore` |

All are enqueued from `configure/theme-hooks/enqueue-listing-templates-assets.php` (priority 110, style depends on `main`).

## Blog listings and 404

| Template | Layout | Styles |
|----------|--------|--------|
| `home.php` (posts page), `archive.php` (category, tag, date, author) | `partials/parts/blog-archive.php` (Figma `3446:656` / `3493:1082`): hero `/*blog*/` + H1 (posts page: «Досвід, помилки та несподівані відкриття»; archives: term or author name + description), grid of `blog-card` (1 → 2 columns from `mobile` → 3 from `tablet`), «Показати ще» | `blog-archive.scss` |
| `archive-{services,directions}.php` | `partials/parts/archive-cpt.php` | `archive.scss` |
| `archive-cases.php` | `partials/parts/cases-archive.php`: utility bar + vertical case slider (see [Cases archive slider](#cases-archive-slider)) | `cases-archive.scss` |
| `404.php` | Big "404", heading, home/blog buttons, list of services | `error-404.scss` (`starter_enqueue_404_assets`) |

Blog listing details:

- 6 posts per page in the main query (`configure/theme-hooks/blog-query.php`).
- `partials/parts/blog-card.php`: cover in the `blog-card` image size (822×500, 2× of 411×250; after changing it run `wp media regenerate --image_size=blog-card --yes`), placeholder without a featured image; title up to two lines (two lines reserved so the author lines of a row align), its link stretched over the card; author line on top. `variant` `compact` is used by «Актуальні теми». CPT cards keep `post-card.php`.
- «Показати ще» is a link to the next page (`/blog/page/2/`) and works without JS. `blogLoadMore.js` fetches that page, appends its `.blog-archive__item` cards (short fade-in), moves focus to the first new card, takes the next link or removes the button; on an error it falls back to normal navigation.
- The hero has no `section::after` divider strip; the grid section ends with a 40px strip above the footer (`--section-gap: 40px`).

### Cases archive slider

`casesArchive.js` picks a mode by `(min-width: 1024px) and (min-height: 520px)`:

- **slider** — an endless vertical loop (1 → 2 → 3 → 1 and back) that opens on case 01, the active cover centred with PREV / NEXT fragments. Below 6 covers the set is cloned in the DOM (`data-cases-clone`, slider mode only) so Swiper's loop always moves a slide beyond the stage edge. The block always takes the screen under the header (`height: 100svh − admin bar − --header-h`; when the header auto-hides, the full screen); the footer stays in the normal flow below. At the page top the wheel and ↑ / ↓ / PageUp / PageDown switch cases; scrolled down, the page scrolls natively and the case stays (trackpad inertia of the scroll back up does not switch it).
- **Bottom zone** (15% of the screen, `FOOTER_ZONE` / `$footer-zone`): with a mouse, the pointer in it shows «До футера ↓» (visual only, `pointer-events: none`), the wheel down there scrolls smoothly to the footer, the wheel up switches cases as usual. Without a fine pointer the hint is always shown and works as a button. `End` at the top also scrolls to the footer; Space / Home stay native. Back at the top the loop goes on from the same case.
- **lane** (phones, low screens) — a horizontal Swiper, the next cover peeks in; **static** — one case, no Swiper.

Text switch as on grndwrk.studio: static captions (Client / Final product), the titles and descriptions form two reels (`casesReel.js`: the track slides 0.6s in step with the covers, items blur-fade, the window takes the active item's height via `--reel-y` / `--reel-h`; an empty description closes it to 0). Across the loop edge each reel slides one step to a clone (last item before the first, first after the last) and jumps back to the real item without a transition, so the text moves the same way as the covers. One «Читати кейс» link under the reels points at the active case (`data-url` on each title). Without JS the covers form a scroll-snap lane, titles and descriptions are listed and the link is hidden.

Breadcrumbs (`starter_get_breadcrumb_items( $labels )`): post archives show Головна → Блог → term; 404 shows Головна → Сторінку не знайдено. `$labels` (`home`, `blog`) overrides the first two labels (single post bar: `home / blog`). The blog listing itself shows no breadcrumbs.

## See Also

- [Page Sections](page-sections.md) — how `page` posts are built from section blocks
- [Getting Started](getting-started.md) — commands, linting, verifying a refactor
