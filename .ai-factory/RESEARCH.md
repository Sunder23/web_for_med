# Research

Updated: 2026-10-08 18:38
Status: active

## Active Summary (input for /aif-plan)

<!-- aif:active-summary:start -->
Topic: ChatGPT prompts for cover images (featured images) of posts, services, directions, cases
Goal: Generate a consistent series of 17 cover images in the web4med hero style (`screens/image copy 3.png`) via ChatGPT image generation, then attach them as Featured Image per post ID.
Constraints:
- Style: blue monochrome (#0A1F44 / #13407F / #1F5FA8 + cyan glow #7FD3FF), semi-realistic painterly illustration, holographic dashboards, NO text/logos/numbers.
- Format 3:2 (1536x1024), negative space on the left third (theme has no `add_image_size` / fixed `aspect-ratio` — cards may crop).
- Sensitive topics (addiction: 273, 285): no substances, no people in distress.
- Real brands in cases (Lviv Medical Center, Compliment): no logos; better to composite a real site screenshot into the mockup later.
Decisions:
- One master style prompt + one short "Scene:" prompt per post (full texts in Session 2026-10-08 18:38).
- Visual language per type: services = specialist + service-specific hologram; directions = clinic interior of that profile + digital layer; cases = device mockups with abstract site; posts = conceptual metaphor.
- Workflow: new ChatGPT chat → send master prompt → one Scene per message → name files by post ID (264.png…) → if style drifts send "Keep exactly the same style…"; after ~8–10 images start a new chat with master prompt + one good image as style reference.
Open questions: final card crop ratio (3:2 vs 16:9); whether to replace AI case covers with real site screenshots.
Success signals: 17 covers in one consistent style, no text artifacts, uploaded as Featured Images to the matching post IDs.
Next step: generate images in ChatGPT; optionally a WP-CLI script in `scripts/` to attach `{ID}.png` as featured images.
(Previous summary — ref/html_ready_pages port onto single templates — was implemented and merged in 1bfc42c; details remain in Session 2026-08-03 23:15.)
<!-- aif:active-summary:end -->

## Sessions

<!-- aif:sessions:start -->
### 2026-04-26 12:00 — acf-from-html skill exploration

What changed:
- Explored skill concept, HTML→ACF mapping rules, output format options
- Settled all major design decisions through user Q&A
- Confirmed SCF supports same JSON format + field types as ACF Pro (full fork)
- Confirmed acf-json/ folder already exists in theme

Key notes:
- JSON-only output (no PHP) — acf-json/ auto-loaded by ACF/SCF, no admin import needed
- Input is file path (design-first workflow: HTML mockup → ACF config)
- Plugin question at startup is for context/comments, not for gating field types
- Deterministic keys (CRC32 hash) are critical for Git-safe reproducible regeneration
- Optional template snippet output is a nice V1 bonus

Links (paths):
- theme/vite-wordpress-starter-theme/acf-json/  (output directory, already exists)
- configure/acf.php                             (read for context, not written to)
- .ai-factory/DESCRIPTION.md
### 2026-08-03 23:15 — Design/structure port from ref/html_ready_pages

What changed:
- Decoded the 5 bundled reference HTML files (Dyad/Zed bundler format — real markup hidden as JSON inside `<script type="__bundler/template">`) to see the actual target design and the React design-system component source (ArchiveCard, InfoCard, NumberedItem, CaseMetric, FaqAccordion, IconListItem, etc.)
- Initially assumed single-services/directions/cases.php + archives were already fully done (code/SCSS reads matched design tokens closely) — this was WRONG, corrected after the user shared before/after screenshots
- Queried the live docker DB (`web_for_med-db-1`, `wordpress_wfmed`) directly and found: 100% of existing services/directions/cases content (15 posts) is stored in the OLD generic prose-repeater ACF shape (`*_sections`), none use the new structured fields the "linear" services template already supports — this is *why* the live page falls back to a plain layout instead of the rich target design
- Live-screenshotted `localhost:8080/cases/` via chrome-devtools MCP and visually confirmed archive pages already match the target ArchiveCard pattern — no work needed there
- Walked through per-template gaps (services=data only; directions=data+ACF schema extension+sidebar markup fix; cases=data+ACF schema split; single.php=full rebuild, wrong layout entirely; archives=done)

Key notes:
- The `__bundler/template` decode trick (Node: regex-extract + JSON.parse) is reusable if more ref HTML bundles show up later
- Don't trust "the SCSS/PHP looks structurally right" as proof of a finished feature when ACF-driven CPT templates are involved — always check what shape the actual post data is in, since a template can support the new design in code while every real post still renders the old fallback
- `content-with-toc.php` is now single-purpose (blog single only) and will become dead code once single.php is rebuilt

Links (paths):
- ref/html_ready_pages/*.html (bundled ref designs — decode via scratchpad Node script, not readable directly)
- theme/vite-wordpress-starter-theme/single-services.php, single-directions.php, single-cases.php, single.php
- theme/vite-wordpress-starter-theme/template-parts/archive-cpt.php, content-with-toc.php
- theme/vite-wordpress-starter-theme/acf-json/group_9033ee91.json (Service Page), group_41eea81b.json (Direction Page), group_6ba8f99a.json (Case Page)
- theme/vite-wordpress-starter-theme/assets/src/scss/pages/_service.scss, _cpt-common.scss, _archive.scss, _single-post.scss
- screens/localhost_8080_services_analytics_.png (current live render), screens/..._01-service.html.png (target)
### 2026-10-08 18:38 — Cover image prompts for ChatGPT

What changed:
- Pulled published titles from DB (`wp_posts`, need `--default-character-set=utf8mb4` for Cyrillic): 3 posts, 4 services, 7 directions, 3 cases.
- Read style from hero screenshot `screens/image copy 3.png` (blue monochrome, glowing holographic dashboards, operator at monitors).
- Wrote master style prompt + 17 scene prompts; agreed workflow for feeding them to ChatGPT.

Key notes:

**Master prompt (send once, first message):**
```
You are generating a consistent series of cover images for a Ukrainian digital-marketing agency
that builds websites, SEO, Google Ads and analytics for private medical clinics (brand "web4med").

STYLE — apply to EVERY image in this chat:
- Semi-realistic digital illustration with a soft painterly finish, cinematic, calm, professional.
- Strict blue monochrome palette: deep navy #0A1F44, cobalt #13407F, royal blue #1F5FA8,
  highlights in light sky-blue/cyan #7FD3FF with a soft glow. No warm colors, no red, no green.
- Recurring motif: glowing holographic UI panels, line charts trending up, thin connecting lines
  with light dots, subtle geometric grid / window frames in the background.
- Evening or dim interior lighting, light coming mostly from screens and holograms.
- Clean composition, one clear focal point, generous negative space on the left third
  (covers may be cropped for cards).
- Landscape 3:2 format (1536x1024).
- People: adults, seen from behind or in three-quarter view, faces not in focus, no patients
  in distress.
- ABSOLUTELY NO readable text, letters, numbers, logos, watermarks or brand marks anywhere —
  UI elements must be abstract shapes, bars and icons only.
- No blood, syringes, pills close-ups, surgical gore or anything frightening.

Confirm you understand, then wait for my scene descriptions. For each one, generate one image.
```

**Scenes (one per message):**

services
- 264 Розробка сайтів для медичних закладів та приватних практик — `Scene: a web designer at a wide desk assembling a clinic website — floating holographic wireframe blocks (header, cards, booking form shapes) snapping together in mid-air in front of large monitors, a soft glowing cursor path connecting the blocks.`
- 265 SEO для медичних сайтів та клінік — `Scene: a specialist in front of a giant translucent search-results-like panel made of abstract bars; one bar glows brighter and rises to the top, a magnifying-glass hologram and an upward line chart beside it, network nodes linking to a small glowing medical cross icon.`
- 266 Контекстна реклама для медичних закладів (Google Ads) — `Scene: a marketer controlling a holographic campaign dashboard — abstract ad cards fanning out, a glowing target/bullseye icon, funnels of light particles flowing from the cards into a single clinic building silhouette. Dynamic, focused mood.`
- 267 Аналітика та моніторинг: бачити не трафік, а пацієнтів — `Scene: a dark control room with a big holographic dashboard; anonymous crowd of tiny light dots (traffic) filters through a glowing funnel and turns into a few distinct human silhouettes walking toward a clinic door. Metaphor: from traffic to real patients.`

directions
- 268 Приватні кабінети: лікар, масаж, остеопатія — `Scene: a cozy small private practice room with a massage/treatment table and a desk, warm-but-blue dim light; a compact floating holographic panel shows an online booking calendar shape and a phone icon with a gentle notification glow. Intimate, personal, trustworthy.`
- 269 Реабілітаційні центри та санаторії — `Scene: a calm rehabilitation hall with large windows overlooking pine trees at dusk, parallel bars and a lounge area; translucent holographic cards float near the window showing abstract room cards and a heart/trust icon. Serene, spacious, hopeful.`
- 270 Багатопрофільні клініки: фокус замість хаосу — `Scene: a modern multi-department clinic lobby; many scattered small holographic icons (tooth, heart, eye, bone, brain) on one side are being organized by light lines into a neat, structured grid on the other side. Metaphor: chaos to order.`
- 271 Репродуктологія — `Scene: a quiet, refined reproductive-medicine consultation room; a delicate glowing hologram of a stylized cell/embryo shape made of light dots floats above the desk, surrounded by soft analytics curves. Tender, careful, high-trust mood, nothing clinical or graphic.`
- 272 Естетична медицина: утримувати клієнтів надовго — `Scene: an elegant aesthetic-medicine studio with a cosmetology chair, mirror and soft rim-lighting; a looping circular holographic ring of light around the scene with small returning-client icons along it. Metaphor: loyalty and retention. Premium, minimal.`
- 273 Центри лікування залежностей — `Scene: a calm, welcoming counseling room at dawn, two empty armchairs facing each other, first sunlight (still in blue tones) entering through a window; a discreet holographic panel with a gently rising line and a shield/privacy icon. Mood: confidentiality, support, hope. No substances, no bottles, no distressed people.`
- 274 Стоматологія: сайт + реклама + аналітика — `Scene: a modern dental cabinet with a dental chair and overhead lamp; three linked holographic panels float above it — website layout, ad card with target icon, analytics chart — connected by glowing lines into one system, a stylized tooth icon at the center.`

cases
- 275 Клініка репродуктивної медицини: переробка сайту на WordPress — `Scene: a large monitor and laptop on a desk showing an abstract, clean clinic website (blocks and soft imagery shapes, no text); behind them a holographic "before/after" transition — messy fragmented panels on the left morphing into an orderly layout on the right, an upward growth chart for inquiries.`
- 276 Редизайн головної Lviv Medical Center — `Scene: a laptop showing an abstract redesigned homepage hero (big image block, buttons as shapes), placed on a desk with a softly blurred view of an old European city at dusk through the window (Lviv-like rooftops and spires, generic, no recognizable landmarks text). Holographic layout grid floating above the screen. No logos.`
- 277 Compliment: редизайн сайту естетичного центру — `Scene: a tablet and smartphone on a marble surface in an aesthetic clinic, both showing an abstract elegant website layout; soft holographic color-swatch circles (only blue shades) and layout blocks float around them. Premium, airy, refined. No logos or brand names.`

posts
- 278 SEO без ілюзій: позиції ≠ прибуток — `Scene: conceptual — a tall glowing podium/ranking ladder with a bright "number one" shape at the top, but its light reflection on the floor reveals a nearly empty wallet/coin stack shape; next to it a smaller ladder leads to a real clinic door with people. Metaphor: rankings are not revenue.`
- 283 Соцмережі й месенджери є. Навіщо лікарю сайт — `Scene: a doctor in a white coat seen from behind, holding a smartphone with floating abstract chat bubbles and social-like cards drifting away; in front of them a solid, glowing website "house" made of layout blocks stands on a stable foundation. Metaphor: rented space vs own home.`
- 285 Особливість просування закладів лікування залежностей — `Scene: conceptual — a winding path of light across a dark landscape leading to a softly lit building on a hill; along the path, discreet holographic signposts (shield, lock, heart icons). Mood: careful, ethical, confidential promotion. No people in distress, no substances.`

**Style drift fix:** `Keep exactly the same style, palette and rendering as the first image. No text.`

Links (paths):
- screens/image copy 3.png (style reference — hero)
- wp_posts (post / services / directions / cases, post_status=publish)
<!-- aif:sessions:end -->
