import { logDebug } from '@js/utils/logDebug.js';
import Swiper from 'swiper';
import { Keyboard, Mousewheel, Navigation } from 'swiper/modules';

/*
 * Cases archive (/cases/): looped case slider on Swiper (Figma 3025:141,
 * mobile 3166:3416). The page fits one screen (footer visible), so the
 * slider never has to hand the scroll back to the page.
 *
 * Modes (recreated on SLIDER_QUERY change):
 * - `slider`: vertical, the active cover centred with PREV / NEXT fragments;
 *   wheel over the stage, arrow keys, arrows and dots switch cases.
 * - `lane` (phones, low screens): horizontal, the next cover peeks in.
 * - `static`: a single case, no Swiper.
 *
 * Swiper moves the covers; text panels, dots and the counter follow
 * `realIndex`. Sizes live in CSS (_cases-archive.scss).
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
	let mode = 'static';
	let index = 0;

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
			mousewheel: {
				eventsTarget: '[data-cases-stage]',
				thresholdDelta: 30,
				releaseOnEdges: false,
			},
		};
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
		mode = next;

		// The page scrolls only on low screens; the wheel over the stage belongs
		// to the slider there, not to Lenis.
		stage.toggleAttribute('data-lenis-prevent-wheel', mode === 'slider');

		if (mode !== 'static') {
			swiper = new Swiper(viewport, options(mode === 'slider'));
		}

		render();
		logDebug('[casesArchive] mode', {
			from,
			to: mode,
			loop: swiper?.params.loop,
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
