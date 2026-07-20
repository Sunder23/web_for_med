# Implementation Plan: Mobile Menu Grid-Flash on Scan Line

Branch: feature/mobile-menu-grid-scan-animation
Created: 2026-07-20

## Settings
- Testing: no
- Logging: verbose
- Docs: no  # WARN [docs] only, no mandatory docs checkpoint

## Roadmap Linkage
Milestone: "none"
Rationale: No `.ai-factory/ROADMAP.md` exists in this project.

## Context

The mobile nav (`.nav--mobile-overlay`, `theme/vite-wordpress-starter-theme/assets/src/scss/layout/_header.scss:191-344`)
already has a "scan line" that sweeps down the panel once per open — currently a pure CSS
`::after` pseudo-element on `.is-open` (`_header.scss:220-239`) animated by `@keyframes nav-scan`
(`_header.scss:454-462`), driven from `openNav()`/`closeNav()` in
`theme/vite-wordpress-starter-theme/assets/src/js/components/mobileNav.js`.

**Requested feature:** when that line passes through a `.menu-item`, a hologram-style "grid"
flash should play on that specific item, using the reference markup/CSS the user supplied
(`.hologram-container` / `.hologram-glitch` — a crossed-linear-gradient grid texture,
`background-size: 10px 10px`, animated). The grid color must match the mobile menu's own
background color (`#F4F6F8`, `_header.scss:200`), not the reference's cyan.

**Chosen approach** (confirmed with user):
- Convert the scan line from a CSS-only pseudo-element into a real DOM node driven by a
  **GSAP timeline** (GSAP is already a project dependency, used the same way in
  `heroAnimations.js`). The timeline's `onUpdate` callback compares the line's live position
  against each `.menu-item`'s pre-measured bounding box and toggles a `.menu-item--grid-flash`
  class at the exact moment of crossing — this guarantees the grid flash is perfectly
  synchronized to the line's real position instead of relying on precomputed CSS delays that
  could drift if the line's duration/easing changes later.
- The grid effect renders on the `.menu-item` itself (a `::before` pseudo-element sized to the
  item), not as a band that follows the line across the full nav width.
- No automated tests (no test infra exists for this WP theme's frontend; verify manually in
  browser at mobile width).
- Grid cell size / flash duration / opacity are left to implementer judgment, based on the
  reference snippet, as long as the color stays tied to `#F4F6F8`.

## Tasks

### Phase 1: SCSS
- [x] Task 1: Replace legacy CSS scan-line with JS-driven line base styles (`_header.scss`)
- [x] Task 2: Add hologram-style grid-flash effect on mobile menu items (`_header.scss`, depends on 1)

### Phase 2: JS
- [x] Task 3: Drive scan line with GSAP and trigger per-item grid flash (`mobileNav.js`, depends on 1, 2)

Full task descriptions (deliverables, exact code sketches, logging requirements) are tracked via
`TaskCreate`/`TaskList` in this session — see tasks #1-#3.

## Commit Plan
Fewer than 5 tasks — single commit at the end covering all three tasks, e.g.:
`feat(theme): sync grid-flash animation to mobile menu scan line`

## Manual Verification (not a task — do this after implementation)
1. `npm run dev` in `theme/vite-wordpress-starter-theme/`, open the site at a ≤768px viewport.
2. Open the mobile menu (burger) and confirm the scan line still sweeps down once per open.
3. Confirm each `.menu-item` flashes with a light grid texture in `#F4F6F8`-based color exactly
   as the line passes over it, and the flash does not linger or loop.
4. Close and reopen the menu repeatedly — confirm the effect resets cleanly every time (no
   stuck `.menu-item--grid-flash` classes, no duplicate/orphaned scan-line elements).
5. Check the console with `localStorage.setItem('LOG_LEVEL','debug')` to confirm the verbose
   `[web_for_med] [mobileNav] ...` logs fire with correct item labels and Y positions.
