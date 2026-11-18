import { initHeaderAutoHide } from '@js/components/headerAutoHide.js';
import { logDebug } from '@js/utils/logDebug.js';
import Swiper from 'swiper';
import { Keyboard, Mousewheel } from 'swiper/modules';

/*
 * Cases archive (/cases/): case slider on Swiper (Figma 3025:141, mobile
 * 3166:3416).
 *
 * Modes (recreated on SLIDER_QUERY change):
 * - `slider`: vertical, the active cover centred with PREV / NEXT fragments.
 *   The block fills the screen under the header; the footer is out of the
 *   flow, below the viewport edge (`body.is-footer-hidden`). The wheel and
 *   the arrow keys switch cases, first to last without a loop (up at the
 *   first case does nothing). The last case is the end: a new downward
 *   gesture there slides the footer up while the block
 *   shrinks by its height, as in the mockup (on screens too low to fit, the
 *   page then scrolls on). An upward gesture at the page top slides it back
 *   down; the next one goes to the previous case. Any switch to another case
 *   (dots, fragments) hides it as well. Scrolling the page or tabbing into
 *   the footer reveals it. With a mouse, the header slides up ~2s after load
 *   and comes back over the bar from the top edge (headerAutoHide.js).
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
// The slider opens on the second case, so a PREV fragment sits above it
// (as in the mockup). Needs a case after it too, or it would be the end.
const SLIDER_START = 1;
const SLIDER_START_MIN = 3;
// A pause in the wheel stream longer than this starts a new gesture
// (trackpad inertia keeps firing events — they belong to the same gesture).
const WHEEL_IDLE_MS = 150;
// Footer slide is 0.8s (_cases-archive.scss); fallback if transitionend is lost.
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

// The page is taller than the screen (low screens with the footer in).
const pageScrolls = () =>
	document.documentElement.scrollHeight > window.innerHeight + 1;

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
	const count = covers.length;

	const sliderMq = window.matchMedia(SLIDER_QUERY);
	const reducedMq = window.matchMedia(REDUCED_MOTION_QUERY);

	const footer = document.querySelector('.footer');
	const content = document.querySelector('.site-content');

	let swiper = null;
	let headerAuto = null;
	let mode = 'static';
	let index = 0;

	// Footer state (slider mode) and wheel gesture tracking.
	let footerShown = false;
	let footerAnim = null;
	let lastWheelAt = 0;
	let lastChangeAt = 0;
	let gesture = 0;
	// The gesture that hid the footer: its inertia must not move the slider.
	let hideGesture = -1;
	// The gesture that scrolled the page back up: its inertia must not hide
	// the footer once the top is reached.
	let scrollGesture = -1;

	const atLastCase = () => index === count - 1;

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

	// --- Footer: slides up at the last case, back down on the way up ---------

	// Ends a running slide at once: footer at its final place, padding off.
	const settleFooter = () => {
		if (!footerAnim) return;

		window.clearTimeout(footerAnim.timer);
		footer.removeEventListener('transitionend', footerAnim.onEnd);
		footerAnim = null;

		content.style.transition = 'none';
		document.body.classList.remove('is-footer-moving');
		content.style.paddingBottom = '';
		void content.offsetHeight; // apply without the transition
		content.style.transition = '';
		logDebug('[casesArchive] footer settled', { shown: footerShown });
	};

	const cancelFooterAnim = () => {
		if (!footerAnim) return;
		settleFooter();
		logDebug('[casesArchive] footer anim cancelled');
	};

	// While hidden the footer is fixed below the viewport. It moves fixed
	// (`is-footer-moving`) while `.site-content` padding-bottom keeps the
	// archive in step: shrinking by the footer height as it comes up, growing
	// back as it goes down. Shown and settled, it is back in the flow.
	const setFooter = (shown, reason) => {
		if (shown === footerShown) return;

		cancelFooterAnim();
		footerShown = shown;

		const { body } = document;
		const animate =
			mode === 'slider' && footer && content && !reducedMq.matches;

		logDebug('[casesArchive] footer', { shown, reason, animate, index });

		if (!shown && !atTop()) {
			window.scrollTo({ top: 0, behavior: 'instant' });
		}

		if (!animate) {
			body.classList.toggle('is-footer-hidden', !shown);
			return;
		}

		const onEnd = (e) => {
			if (e.target === footer && e.propertyName === 'transform') {
				settleFooter();
			}
		};

		footerAnim = {
			onEnd,
			timer: window.setTimeout(settleFooter, FOOTER_FALLBACK_MS),
		};
		footer.addEventListener('transitionend', onEnd);

		const height = `${footer.offsetHeight}px`;

		if (shown) {
			body.classList.add('is-footer-moving');
			body.classList.remove('is-footer-hidden');
			content.style.paddingBottom = height;
			return;
		}

		// Take the footer out of the flow without a visible change, then
		// let it slide down while the padding goes back to zero.
		content.style.transition = 'none';
		body.classList.add('is-footer-moving');
		content.style.paddingBottom = height;
		void content.offsetHeight;
		content.style.transition = '';
		body.classList.add('is-footer-hidden');
		content.style.paddingBottom = '';
	};

	const onSlideChange = (instance) => {
		const from = index;
		index = instance.realIndex ?? 0;
		if (from === index) return;

		lastChangeAt = performance.now();
		render();
		logDebug('[casesArchive] slide', { from, to: index, mode });

		// Dots, fragment clicks: leaving the last case closes the footer.
		if (mode === 'slider' && footerShown && !atLastCase()) {
			setFooter(false, 'slide change');
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
			// A straight 01 → last run: the footer waits past the last case, so
			// wrapping around (up from the first case) would reach "the end" the
			// wrong way.
			loop: false,
			rewind: false,
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

	// --- Gates (slider mode) --------------------------------------------------

	const gateLog = (action, extra) =>
		logDebug('[casesArchive] gate', { action, index, ...extra });

	const onPageScroll = () => {
		if (!atTop()) setFooter(true, 'page scroll');
	};
	const onFooterFocus = () => setFooter(true, 'focus');

	// Runs before Swiper (window, capture). `stopPropagation` keeps the event
	// from Swiper and leaves the native scroll; `preventDefault` swallows it.
	const onWheelGate = (e) => {
		const now = performance.now();
		const newGesture = now - lastWheelAt > WHEEL_IDLE_MS;
		lastWheelAt = now;
		if (newGesture) gesture += 1;

		if (isOverlayOpen() || e.ctrlKey) {
			e.stopPropagation();
			return;
		}

		const down = e.deltaY > 0;
		const up = e.deltaY < 0;

		if (footerShown) {
			e.stopPropagation();
			// Scrolled (low screens): the page scrolls back to the top first.
			if (!atTop()) {
				scrollGesture = gesture;
				return;
			}

			if (down) {
				// Nothing below the footer: no loop to the first case.
				if (!pageScrolls()) e.preventDefault();
				return;
			}

			if (up) {
				e.preventDefault();
				if (gesture === scrollGesture) return;
				hideGesture = gesture;
				setFooter(false, 'wheel up');
				gateLog('hide footer', { deltaY: e.deltaY });
			}
			return;
		}

		if (!atTop()) {
			e.stopPropagation();
			return;
		}

		if (gesture === hideGesture) {
			// Inertia of the gesture that hid the footer.
			e.stopPropagation();
			e.preventDefault();
			return;
		}

		if (down && atLastCase()) {
			e.stopPropagation();
			e.preventDefault();
			// Inertia of the gesture that brought the last case: swallow it.
			if (!newGesture || now - lastChangeAt < SPEED) return;

			setFooter(true, 'wheel at last case');
			gateLog('show footer', { deltaY: e.deltaY });
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

		if (footerShown) {
			e.stopPropagation();
			if (!atTop()) return;

			if (up) {
				e.preventDefault();
				setFooter(false, 'key up');
				gateLog('hide footer', { key: e.key });
			} else if (!pageScrolls()) {
				e.preventDefault();
			}
			return;
		}

		if (!atTop()) {
			e.stopPropagation();
			return;
		}

		if (down && atLastCase()) {
			e.stopPropagation();
			e.preventDefault();
			setFooter(true, 'key at last case');
			gateLog('show footer', { key: e.key });
		}
	};

	const bindGates = (on) => {
		const method = on ? 'addEventListener' : 'removeEventListener';
		window[method]('wheel', onWheelGate, { capture: true, passive: false });
		window[method]('keydown', onKeyGate, { capture: true });
		window[method]('scroll', onPageScroll, { passive: true });
		footer?.[method]('focusin', onFooterFocus);
		if (!on) cancelFooterAnim();
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

		if (mode === 'slider' && from === 'static' && count >= SLIDER_START_MIN) {
			// First load on desktop only; a resize from the lane keeps the case.
			index = SLIDER_START;
		}

		if (mode !== 'static') {
			swiper = new Swiper(viewport, options(mode === 'slider'));
		}
		if (mode === 'slider') {
			// Loaded or resized while scrolled down: the footer is in view.
			footerShown = !atTop();
			hideGesture = -1;
			scrollGesture = -1;
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
			footerShown,
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

	logDebug('[casesArchive] init', {
		count,
		mode,
		index,
		lenis: Boolean(window.lenis),
	});
}
