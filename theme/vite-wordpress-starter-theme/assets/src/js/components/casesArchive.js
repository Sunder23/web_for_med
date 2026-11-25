import { createReel } from '@js/components/casesReel.js';
import { initHeaderAutoHide } from '@js/components/headerAutoHide.js';
import { logDebug } from '@js/utils/logDebug.js';
import Swiper from 'swiper';
import { Keyboard, Mousewheel } from 'swiper/modules';

/*
 * Cases archive (/cases/): case slider on Swiper (Figma 3025:141, mobile
 * 3166:3416).
 *
 * Modes (recreated on SLIDER_QUERY change):
 * - `slider`: vertical, the active cover centred with PREV / NEXT fragments,
 *   an endless loop (1 → 2 → 3 → 1 and back) that opens on case 01. The block
 *   always fills the screen under the header; the footer stays in the normal
 *   flow below it and has nothing to do with the slider. At the page top the
 *   wheel and the arrow keys switch cases (onWheelGate / onKeyGate); scrolled
 *   down, the page scrolls natively and the case does not change. With a
 *   mouse, the header slides up ~2s after load and comes back over the bar
 *   from the top edge (headerAutoHide.js).
 *   Loop: Swiper's loop moves slides from one end to the other. With the
 *   viewport one cover high it keeps a single slide on each side, so on 3
 *   covers the moved one would vanish from the PREV / NEXT place mid-slide.
 *   Below LOOP_FILL covers the set is duplicated in the DOM (slider mode
 *   only, `data-cases-clone`): the slide being moved is then always beyond
 *   the stage edge. Indices are folded back with `realIndex % count`.
 * - `lane` (phones, low screens): horizontal, the next cover peeks in.
 * - `static`: a single case, no Swiper.
 *
 * Swiper moves the covers; the title and description reels, the «Читати
 * кейс» link, dots and the counter follow `realIndex`. Text switch as on
 * grndwrk.studio: both reels slide in step with the covers (blur-fade on the
 * items), across the loop edge too, in the covers' direction (casesReel.js).
 * Sizes live in CSS (_cases-archive.scss). Lenis is off on this page
 * (smoothScroll.js): Swiper owns the wheel over the stage.
 * Bottom zone (slider, page top): the pointer in the bottom FOOTER_ZONE of
 * the screen shows «До футера ↓» and the wheel down there scrolls the page
 * to the footer; without a fine pointer the hint is a button; End does the
 * same. Back up at the top the loop goes on from the same case.
 */

// Keep in sync with the `slider` mixin in scss/components/_cases-archive.scss.
const SLIDER_QUERY = '(min-width: 1024px) and (min-height: 520px)';
const REDUCED_MOTION_QUERY = '(prefers-reduced-motion: reduce)';

// Keep in sync with --gap in _cases-archive.scss (slider) and the lane gap.
const GAP_SLIDER = 16;
const GAP_LANE = 10;
// Slide switch as on grndwrk.studio: 0.6s, cubic-bezier(0.25, 0.2, 0, 1) (SCSS
// $ease-track). The title reel uses the same duration: keep $text-speed in sync.
const SPEED = 600;
// Lane loop needs a slide on each side of the active one plus one to move.
const LOOP_MIN = 3;
// Slider loop: covers in the DOM (real + clones). Swiper keeps loopedSlides
// = 1 + LOOP_EXTRA around the active one; 6 leaves the slide it moves
// beyond the PREV / NEXT fragments and the covers past them on tall screens.
const LOOP_FILL = 6;
const LOOP_EXTRA = 1;
// A pause in the wheel stream longer than this starts a new gesture
// (trackpad inertia keeps firing events — they belong to the same gesture).
const WHEEL_IDLE_MS = 150;

// At the page top these go to Swiper (keyboard module); scrolled down they
// scroll the page. Space, Home and End stay native (page scroll).
const SLIDER_KEYS = ['ArrowDown', 'ArrowUp', 'PageDown', 'PageUp'];

// Bottom share of the screen where the wheel down scrolls to the footer
// (keep in sync with $footer-zone in _cases-archive.scss).
const FOOTER_ZONE = 0.15;
// The hint follows the pointer only with a mouse; touch screens get a button.
const FINE_POINTER_QUERY = '(hover: hover) and (pointer: fine)';

const isOverlayOpen = () =>
	document.body.classList.contains('is-drawer-open') ||
	document.querySelector('.nav.is-open') !== null;

const isTypingTarget = (el) =>
	el instanceof HTMLElement &&
	(el.isContentEditable || /^(INPUT|TEXTAREA|SELECT)$/.test(el.tagName));

const atTop = () => window.scrollY <= 1;

const inFooterZone = (clientY) =>
	clientY >= window.innerHeight * (1 - FOOTER_ZONE);

// Shortest signed step from `from` to `to` on a ring of `total` slots.
const ringStep = (from, to, total) => {
	let step = (((to - from) % total) + total) % total;
	if (step > total / 2) step -= total;
	return step;
};

const pad = (n) => String(n).padStart(2, '0');

export function initCasesArchive() {
	const root = document.querySelector('[data-cases-archive]');
	const stage = root?.querySelector('[data-cases-stage]');

	if (!root || !stage) {
		logDebug('[casesArchive] skipped: no stage');
		return;
	}

	const viewport = stage.querySelector('[data-cases-viewport]');
	const track = stage.querySelector('[data-cases-track]');
	const covers = [...stage.querySelectorAll('[data-cases-cover]')];
	const dots = [...root.querySelectorAll('[data-cases-dot]')];
	const reelEls = [...stage.querySelectorAll('[data-cases-reel]')];
	// «Читати кейс»: one link, the URL of each case sits on its title.
	const link = stage.querySelector('[data-cases-link]');
	const urls = [
		...stage.querySelectorAll(
			'[data-cases-reel="title"] [data-cases-reel-item]',
		),
	].map((el) => el.dataset.url);
	const current = root.querySelector('[data-cases-current]');
	const count = covers.length;

	const footerHint = root.querySelector('[data-cases-footer-hint]');

	const sliderMq = window.matchMedia(SLIDER_QUERY);
	const reducedMq = window.matchMedia(REDUCED_MOTION_QUERY);
	const finePointerMq = window.matchMedia(FINE_POINTER_QUERY);

	let swiper = null;
	let headerAuto = null;
	let mode = 'static';
	let index = 0;
	// Swiper's realIndex (0…total-1, clones included): direction of a step.
	let rawIndex = 0;

	// Wheel gesture tracking (slider mode).
	let lastWheelAt = 0;
	let gesture = 0;
	// The gesture that scrolled the page back up: its inertia must not switch
	// the case once the top is reached.
	let scrollGesture = -1;
	let lastGate = '';
	// The gesture that started the scroll to the footer (wheel in the zone).
	let footerGesture = -1;
	// Footer zone hint (slider mode, mouse).
	let hintShown = false;
	let pointerFrame = 0;
	let pointerY = -1;

	// Title and description reels (casesReel.js), in step with the covers.
	const reels =
		count > 1
			? reelEls
					.map((el) =>
						createReel(el, {
							count,
							speed: reducedMq.matches ? 0 : SPEED,
							name: el.dataset.casesReel,
						}),
					)
					.filter(Boolean)
			: [];

	// `dir`: +1 / -1 — the way the covers went (the reels follow it across
	// the loop edge), 0 — no animation (start case, mode change).
	const render = (dir = 0) => {
		reels.forEach((reel) => {
			reel.go(index, dir);
		});
		if (link && urls[index]) link.href = urls[index];

		covers.forEach((cover) => {
			cover.classList.toggle(
				'is-active',
				Number(cover.dataset.casesCover) === index,
			);
		});

		// Fragments (and clones) are previews: keyboard focus goes to the
		// cover in the centre only — a real one or a clone in the loop.
		const centre =
			mode === 'slider' ? swiper?.slides[swiper.activeIndex] : null;
		[...track.children].forEach((cover) => {
			if (mode !== 'slider' || cover === centre) {
				cover.removeAttribute('tabindex');
			} else {
				cover.setAttribute('tabindex', '-1');
			}
		});

		dots.forEach((dot, i) => {
			const active = i === index;
			dot.classList.toggle('is-active', active);
			if (active) {
				dot.setAttribute('aria-current', 'true');
			} else {
				dot.removeAttribute('aria-current');
			}
		});

		if (current) current.textContent = pad(index + 1);
	};

	const onSlideChange = (instance) => {
		const from = index;
		const raw = instance.realIndex ?? 0;
		if (raw === rawIndex) return;

		const dir = Math.sign(ringStep(rawIndex, raw, instance.slides.length));
		rawIndex = raw;
		index = raw % count;

		render(dir);
		logDebug('[casesArchive] slide', { from, to: index, raw, dir, mode });
	};

	// --- Loop clones (slider mode) --------------------------------------------

	const addLoopClones = () => {
		if (count >= LOOP_FILL) return 0;

		const sets = Math.ceil(LOOP_FILL / count) - 1;
		for (let k = 0; k < sets; k += 1) {
			covers.forEach((cover) => {
				const clone = cover.cloneNode(true);
				clone.setAttribute('data-cases-clone', '');
				clone.setAttribute('aria-hidden', 'true');
				clone.setAttribute('tabindex', '-1');
				clone.classList.remove('is-active');
				track.append(clone);
			});
		}
		return sets * count;
	};

	const removeLoopClones = () => {
		const clones = track.querySelectorAll('[data-cases-clone]');
		clones.forEach((clone) => {
			clone.remove();
		});
		if (clones.length) {
			logDebug('[casesArchive] clones removed', { n: clones.length });
		}
	};

	const options = (sliderMode) => {
		const loop = count >= LOOP_MIN;
		const base = {
			modules: [Keyboard, Mousewheel],
			wrapperClass: 'cases-archive__track',
			slideClass: 'cases-archive__cover',
			slideActiveClass: 'is-current',
			initialSlide: index,
			loop,
			rewind: !loop,
			speed: reducedMq.matches ? 0 : SPEED,
			slidesPerView: 'auto',
			keyboard: { enabled: true, onlyInViewport: true },
			on: { slideChange: onSlideChange },
		};

		if (!sliderMode) {
			return { ...base, direction: 'horizontal', spaceBetween: GAP_LANE };
		}

		return {
			...base,
			// Endless: real covers + clones (addLoopClones), always enough.
			loop: true,
			rewind: false,
			loopAdditionalSlides: LOOP_EXTRA,
			direction: 'vertical',
			centeredSlides: true,
			spaceBetween: GAP_SLIDER,
			// Mouse users switch with the wheel; dragging would fight link clicks.
			simulateTouch: false,
			// The whole page: the block fills the screen; onWheelGate decides
			// whether the event switches the case or scrolls the page.
			mousewheel: {
				eventsTarget: 'body',
				thresholdDelta: 30,
				releaseOnEdges: false,
			},
		};
	};

	// --- Gates (slider mode) --------------------------------------------------
	//
	// Who gets the event: at the page top it is Swiper (the loop); scrolled
	// down, the page scrolls natively and the case stays as it is.

	const logGate = (action, extra) => {
		const key = `${gesture}:${action}`;
		if (key === lastGate) return;
		lastGate = key;
		logDebug('[casesArchive] gate', { action, gesture, ...extra });
	};

	// Runs before Swiper (window, capture). `stopPropagation` keeps the event
	// from Swiper and leaves the native scroll; `preventDefault` swallows it.
	const onWheelGate = (e) => {
		const now = performance.now();
		if (now - lastWheelAt > WHEEL_IDLE_MS) gesture += 1;
		lastWheelAt = now;

		if (isOverlayOpen() || e.ctrlKey) {
			e.stopPropagation();
			return;
		}

		if (gesture === footerGesture && e.deltaY > 0) {
			// The rest of the gesture that started the smooth scroll to the
			// footer: a native wheel scroll would cut it short.
			e.stopPropagation();
			e.preventDefault();
			return;
		}

		if (!atTop()) {
			e.stopPropagation();
			scrollGesture = gesture;
			logGate('page', { dy: e.deltaY });
			return;
		}

		if (gesture === scrollGesture) {
			// The inertia of the scroll back up to the top: not a case switch.
			e.stopPropagation();
			e.preventDefault();
			logGate('inertia', { dy: e.deltaY });
			return;
		}

		if (e.deltaY > 0 && inFooterZone(e.clientY)) {
			// Wheel down in the bottom zone: to the footer, not the next case
			// (wheel up there switches cases as anywhere else).
			e.stopPropagation();
			e.preventDefault();
			logGate('footer', { dy: e.deltaY });
			scrollToFooter('wheel');
			return;
		}

		// The slider takes it. Swiper listens on <body>, which Chrome makes
		// passive, so its own preventDefault is ignored: stop the page here.
		e.preventDefault();
		logGate('slider', { dy: e.deltaY });
	};

	const onKeyGate = (e) => {
		if (isOverlayOpen() || isTypingTarget(e.target)) return;
		if (e.altKey || e.ctrlKey || e.metaKey) return;

		if (e.key === 'End' && atTop()) {
			e.preventDefault();
			e.stopPropagation();
			logDebug('[casesArchive] key gate', { key: e.key, action: 'footer' });
			scrollToFooter('key');
			return;
		}

		if (!SLIDER_KEYS.includes(e.key)) return; // Home, Space: native
		if (atTop()) return; // Swiper

		e.stopPropagation(); // the page scrolls natively
		logDebug('[casesArchive] key gate', { key: e.key, action: 'page' });
	};

	// --- Footer zone (slider mode) ----------------------------------------------
	//
	// The footer is in the flow: the page simply scrolls down to it. Back at
	// the top the case is the one left behind and the loop goes on.

	const setHint = (shown) => {
		if (shown === hintShown) return;
		hintShown = shown;
		root.classList.toggle('is-footer-hint', shown);
		logDebug('[casesArchive] footer zone', { inside: shown });
	};

	function scrollToFooter(reason) {
		const to = document.documentElement.scrollHeight - window.innerHeight;
		logDebug('[casesArchive] scroll to footer', {
			reason,
			from: window.scrollY,
			to,
		});
		// The rest of this wheel gesture (trackpad inertia) must not switch
		// the case if it ends back at the top.
		scrollGesture = gesture;
		footerGesture = reason === 'wheel' ? gesture : -1;
		setHint(false);
		window.scrollTo({
			top: to,
			behavior: reducedMq.matches ? 'instant' : 'smooth',
		});
	}

	const onPointerMove = (e) => {
		if (e.pointerType !== 'mouse' || !finePointerMq.matches) return;

		pointerY = e.clientY;
		if (pointerFrame) return;
		pointerFrame = window.requestAnimationFrame(() => {
			pointerFrame = 0;
			setHint(atTop() && inFooterZone(pointerY));
		});
	};

	const onPointerOut = () => setHint(false);

	const onScroll = () => {
		const scrolled = !atTop();
		root.classList.toggle('is-scrolled', scrolled);
		if (scrolled) setHint(false);
	};

	const onHintClick = () => scrollToFooter('button');

	const bindGates = (on) => {
		const method = on ? 'addEventListener' : 'removeEventListener';
		window[method]('wheel', onWheelGate, { capture: true, passive: false });
		window[method]('keydown', onKeyGate, { capture: true });
		window[method]('pointermove', onPointerMove, { passive: true });
		window[method]('scroll', onScroll, { passive: true });
		window[method]('blur', onPointerOut);
		document.documentElement[method]('pointerleave', onPointerOut);
		footerHint?.[method]('click', onHintClick);

		if (on) {
			onScroll();
			return;
		}
		window.cancelAnimationFrame(pointerFrame);
		pointerFrame = 0;
		setHint(false);
		root.classList.remove('is-scrolled');
	};

	const destroy = () => {
		if (!swiper) return;
		swiper.destroy(true, true);
		swiper = null;
	};

	const setMode = (next) => {
		if (next === mode) return;

		const from = mode;
		destroy();
		if (from === 'slider') {
			headerAuto?.destroy();
			headerAuto = null;
			bindGates(false);
			// Lane and static run on the real covers only.
			removeLoopClones();
		}
		mode = next;
		// A mode change keeps the case (initialSlide), clones come after it.
		rawIndex = index;

		if (mode === 'slider') {
			const clones = addLoopClones();
			swiper = new Swiper(viewport, options(true));
			logDebug('[casesArchive] loop', {
				count,
				total: swiper.slides.length,
				clones,
				loopFill: LOOP_FILL,
			});
		} else if (mode === 'lane') {
			swiper = new Swiper(viewport, options(false));
		}
		if (mode === 'slider') {
			scrollGesture = -1;
			footerGesture = -1;
			bindGates(true);
			// The stage grows by the header height: re-measure the covers.
			headerAuto = initHeaderAutoHide({ onCollapse: () => swiper?.update() });
		}

		// The markup marks the first case active: switch to the start case
		// without the text fade.
		root.classList.add('is-instant');
		render();
		void root.offsetHeight;
		root.classList.remove('is-instant');
		logDebug('[casesArchive] mode', {
			from,
			to: mode,
			loop: swiper?.params.loop,
			headerAuto: Boolean(headerAuto),
		});
	};

	const resolveMode = () => {
		if (count < 2) return 'static';
		return sliderMq.matches ? 'slider' : 'lane';
	};

	// The slide of case `i` closest around the ring (clones included).
	dots.forEach((dot, i) => {
		dot.addEventListener('click', () => {
			if (!swiper) return;

			const total = swiper.slides.length;
			let target = i;
			for (let r = i + count; r < total; r += count) {
				const now = Math.abs(ringStep(rawIndex, target, total));
				if (Math.abs(ringStep(rawIndex, r, total)) < now) target = r;
			}
			logDebug('[casesArchive] dot', { i, from: rawIndex, to: target });
			swiper.slideToLoop(target);
		});
	});

	// A click on a PREV / NEXT fragment (a clone too) brings that case in
	// instead of opening it; the centred cover opens the case as a link.
	viewport.addEventListener('click', (e) => {
		const cover = e.target.closest('[data-cases-cover]');
		if (mode !== 'slider' || !swiper || !cover) return;
		if (cover.classList.contains('is-current')) return;

		e.preventDefault();
		const to = Number(cover.dataset.swiperSlideIndex);
		swiper.slideToLoop(to);
		logDebug('[casesArchive] fragment click', { to });
	});

	sliderMq.addEventListener('change', () => setMode(resolveMode()));
	reducedMq.addEventListener('change', () => {
		const speed = reducedMq.matches ? 0 : SPEED;
		if (swiper) swiper.params.speed = speed;
		reels.forEach((reel) => {
			reel.setSpeed(speed);
		});
	});

	setMode(resolveMode());

	logDebug('[casesArchive] init', {
		count,
		mode,
		index,
		lenis: Boolean(window.lenis),
	});
}
