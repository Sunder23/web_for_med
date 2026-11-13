import { initHeaderAutoHide } from '@js/components/headerAutoHide.js';
import { logDebug } from '@js/utils/logDebug.js';
import Swiper from 'swiper';
import { Keyboard, Mousewheel, Navigation } from 'swiper/modules';

/*
 * Cases archive (/cases/): looped case slider on Swiper (Figma 3025:141,
 * mobile 3166:3416).
 *
 * Modes (recreated on SLIDER_QUERY change):
 * - `slider`: vertical, the active cover centred with PREV / NEXT fragments.
 *   First the block fills the screen under the header; the footer is out of
 *   the flow, below the viewport edge (`body.is-footer-hidden`). The wheel
 *   and the arrow keys switch cases; the slider loops, so the footer comes in
 *   after one full pass: once every case has been shown, the next downward
 *   gesture slides it up while the block shrinks by its height, as in the
 *   mockup ("release"; on screens too low to fit, the page also scrolls on).
 *   It stays visible. A new gesture at the top hands the wheel back to
 *   the slider and the pass restarts. Scrolling the page or tabbing into the
 *   footer reveals it as well. With a mouse, the header slides up ~2s after
 *   load and comes back over the bar from the top edge (headerAutoHide.js).
 * - `lane` (phones, low screens): horizontal, the next cover peeks in.
 * - `static`: a single case, no Swiper.
 *
 * Swiper moves the covers; text panels, dots and the counter follow
 * `realIndex`. Sizes live in CSS (_cases-archive.scss). Lenis is off on this
 * page (smoothScroll.js): Swiper owns the wheel over the stage.
 */

// Keep in sync with the `slider` mixin in scss/components/_cases-archive.scss.
const SLIDER_QUERY = '(min-width: 1024px) and (min-height: 520px)';
const REDUCED_MOTION_QUERY = '(prefers-reduced-motion: reduce)';

// Keep in sync with --gap in _cases-archive.scss (slider) and the lane gap.
const GAP_SLIDER = 16;
const GAP_LANE = 10;
const SPEED = 750;
// Loop needs a slide on each side of the active one plus one to move.
const LOOP_MIN = 3;
// A pause in the wheel stream longer than this starts a new gesture
// (trackpad inertia keeps firing events — they belong to the same gesture).
const WHEEL_IDLE_MS = 150;
// Footer slide-up is 0.8s (_cases-archive.scss); fallback if transitionend is lost.
const FOOTER_FALLBACK_MS = 1000;

const KEYS_DOWN = ['ArrowDown', 'PageDown', 'End', ' '];
const KEYS_UP = ['ArrowUp', 'PageUp', 'Home'];

const isOverlayOpen = () =>
	document.body.classList.contains('is-drawer-open') ||
	document.querySelector('.nav.is-open') !== null;

const isTypingTarget = (el) =>
	el instanceof HTMLElement &&
	(el.isContentEditable || /^(INPUT|TEXTAREA|SELECT)$/.test(el.tagName));

const atTop = () => window.scrollY <= 1;

const pad = (n) => String(n).padStart(2, '0');

export function initCasesArchive() {
	const root = document.querySelector('[data-cases-archive]');
	const stage = root?.querySelector('[data-cases-stage]');

	if (!root || !stage) {
		logDebug('[casesArchive] skipped: no stage');
		return;
	}

	const viewport = stage.querySelector('[data-cases-viewport]');
	const covers = [...stage.querySelectorAll('[data-cases-cover]')];
	const panels = [...stage.querySelectorAll('[data-cases-panel]')];
	const dots = [...root.querySelectorAll('[data-cases-dot]')];
	const current = root.querySelector('[data-cases-current]');
	const prevBtn = stage.querySelector('[data-cases-prev]');
	const nextBtn = stage.querySelector('[data-cases-next]');
	const count = covers.length;

	const sliderMq = window.matchMedia(SLIDER_QUERY);
	const reducedMq = window.matchMedia(REDUCED_MOTION_QUERY);

	let swiper = null;
	let headerAuto = null;
	let mode = 'static';
	let index = 0;

	// One full pass before the footer (slider mode only).
	const visited = new Set([0]);
	let released = false;
	let lastWheelAt = 0;
	let lastChangeAt = 0;

	const render = () => {
		panels.forEach((panel) => {
			const active = Number(panel.dataset.casesPanel) === index;
			panel.classList.toggle('is-active', active);
			panel.inert = !active;
			panel.toggleAttribute('aria-hidden', !active);
		});

		covers.forEach((cover) => {
			const active = Number(cover.dataset.casesCover) === index;
			cover.classList.toggle('is-active', active);
			// Fragments are previews: keyboard focus goes to the active cover only.
			if (mode === 'slider' && !active) {
				cover.setAttribute('tabindex', '-1');
			} else {
				cover.removeAttribute('tabindex');
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
		index = instance.realIndex ?? 0;
		if (from === index) return;

		lastChangeAt = performance.now();
		visited.add(index);

		render();
		logDebug('[casesArchive] slide', { from, to: index, mode });
	};

	const options = (sliderMode) => {
		const loop = count >= LOOP_MIN;
		const base = {
			modules: [Keyboard, Mousewheel, Navigation],
			wrapperClass: 'cases-archive__track',
			slideClass: 'cases-archive__cover',
			slideActiveClass: 'is-current',
			initialSlide: index,
			loop,
			rewind: !loop,
			speed: reducedMq.matches ? 0 : SPEED,
			slidesPerView: 'auto',
			navigation: { prevEl: prevBtn, nextEl: nextBtn },
			keyboard: { enabled: true, onlyInViewport: true },
			on: { slideChange: onSlideChange },
		};

		if (!sliderMode) {
			return { ...base, direction: 'horizontal', spaceBetween: GAP_LANE };
		}

		return {
			...base,
			direction: 'vertical',
			centeredSlides: true,
			spaceBetween: GAP_SLIDER,
			// Mouse users switch with the wheel; dragging would fight link clicks.
			simulateTouch: false,
			// The whole page: the block fills the screen; casesArchive gates the
			// events (onWheelGate) so the page can scroll on to the footer.
			mousewheel: {
				eventsTarget: 'body',
				thresholdDelta: 30,
				releaseOnEdges: false,
			},
		};
	};

	// --- One pass, then the footer (slider mode) ------------------------------

	const footer = document.querySelector('.footer');
	let footerShown = false;

	const content = document.querySelector('.site-content');

	// The footer is fixed below the viewport while hidden. Slide it up and, in
	// step, shrink the archive by its height (padding-bottom on .site-content);
	// at the end put it back into the flow — same position, no jump.
	const showFooter = (reason) => {
		if (footerShown) return;
		footerShown = true;

		const { body } = document;
		const animate =
			mode === 'slider' && footer && content && !reducedMq.matches;

		if (!animate) {
			body.classList.remove('is-footer-hidden');
			logDebug('[casesArchive] footer shown', { reason, animate });
			return;
		}

		let timer = 0;
		const settle = () => {
			window.clearTimeout(timer);
			footer.removeEventListener('transitionend', onEnd);
			content.style.transition = 'none';
			body.classList.remove('is-footer-entering');
			content.style.paddingBottom = '';
			void content.offsetHeight; // apply without the transition
			content.style.transition = '';
			logDebug('[casesArchive] footer settled');
		};
		const onEnd = (e) => {
			if (e.target === footer && e.propertyName === 'transform') settle();
		};

		footer.addEventListener('transitionend', onEnd);
		timer = window.setTimeout(settle, FOOTER_FALLBACK_MS);
		body.classList.add('is-footer-entering');
		body.classList.remove('is-footer-hidden');
		content.style.paddingBottom = `${footer.offsetHeight}px`;
		logDebug('[casesArchive] footer shown', { reason, animate });
	};

	const release = (reason) => {
		released = true;
		showFooter(reason);
		logDebug('[casesArchive] release to page', {
			reason,
			visited: visited.size,
		});
	};

	// After the footer is in, a page that fits the screen has nowhere to go:
	// the slider simply keeps looping.
	const passDone = () =>
		visited.size >= count &&
		(!footerShown ||
			document.documentElement.scrollHeight > window.innerHeight + 1);

	const onPageScroll = () => {
		if (!atTop()) showFooter('page scroll');
	};
	const onFooterFocus = () => showFooter('focus');

	const rearm = (reason) => {
		released = false;
		visited.clear();
		visited.add(index);
		logDebug('[casesArchive] rearm slider', { reason, index });
	};

	// Runs before Swiper (window, capture). `stopPropagation` keeps the event
	// from Swiper and leaves the native scroll; `preventDefault` swallows it.
	const onWheelGate = (e) => {
		const now = performance.now();
		const newGesture = now - lastWheelAt > WHEEL_IDLE_MS;
		lastWheelAt = now;

		if (isOverlayOpen() || e.ctrlKey) {
			e.stopPropagation();
			return;
		}

		if (released || !atTop()) {
			if (released && atTop() && newGesture) {
				rearm('wheel at page top');
				e.preventDefault();
				return;
			}
			e.stopPropagation();
			return;
		}

		if (e.deltaY > 0 && passDone()) {
			e.stopPropagation();
			// Inertia of the gesture that brought the last case: swallow it.
			if (!newGesture || now - lastChangeAt < SPEED) {
				e.preventDefault();
				return;
			}
			release('wheel after a full pass');
			return;
		}

		// The slider takes it. Swiper listens on <body>, which Chrome makes
		// passive, so its own preventDefault is ignored: stop the page here.
		e.preventDefault();
	};

	const onKeyGate = (e) => {
		if (isOverlayOpen() || isTypingTarget(e.target)) return;
		if (e.altKey || e.ctrlKey || e.metaKey) return;

		const down = KEYS_DOWN.includes(e.key);
		const up = KEYS_UP.includes(e.key);
		if (!down && !up) return;

		if (released || !atTop()) {
			if (released && atTop() && up) {
				rearm('key at page top');
				return;
			}
			e.stopPropagation();
			return;
		}

		if (down && passDone()) {
			e.stopPropagation();
			release('key after a full pass');
		}
	};

	const bindGates = (on) => {
		const method = on ? 'addEventListener' : 'removeEventListener';
		window[method]('wheel', onWheelGate, { capture: true, passive: false });
		window[method]('keydown', onKeyGate, { capture: true });
		window[method]('scroll', onPageScroll, { passive: true });
		footer?.[method]('focusin', onFooterFocus);
		document.body.classList.toggle('is-footer-hidden', on && !footerShown);
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
		}
		mode = next;

		if (mode !== 'static') {
			swiper = new Swiper(viewport, options(mode === 'slider'));
		}
		if (mode === 'slider') {
			released = !atTop();
			bindGates(true);
			// The stage grows by the header height: re-measure the covers.
			headerAuto = initHeaderAutoHide({ onCollapse: () => swiper?.update() });
		}

		render();
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

	dots.forEach((dot, i) => {
		dot.addEventListener('click', () => swiper?.slideToLoop(i));
	});

	// A click on a PREV / NEXT fragment brings that case in instead of opening it.
	covers.forEach((cover) => {
		cover.addEventListener('click', (e) => {
			const i = Number(cover.dataset.casesCover);
			if (mode !== 'slider' || i === index || !swiper) return;

			e.preventDefault();
			swiper.slideToLoop(i);
			logDebug('[casesArchive] fragment click', { to: i });
		});
	});

	sliderMq.addEventListener('change', () => setMode(resolveMode()));
	reducedMq.addEventListener('change', () => {
		if (swiper) swiper.params.speed = reducedMq.matches ? 0 : SPEED;
	});

	setMode(resolveMode());
	render();

	logDebug('[casesArchive] init', {
		count,
		mode,
		lenis: Boolean(window.lenis),
	});
}
