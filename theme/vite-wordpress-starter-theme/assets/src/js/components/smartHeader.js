import { logDebug } from '@js/utils/logDebug.js';

// Minimal scroll delta (px) before the header changes state — ignores jitter.
const SCROLL_THRESHOLD = 8;

/**
 * Smart header: hides `.header` while scrolling down and shows it again on
 * scroll up. Always visible near the top of the page, while the mobile menu
 * (`.nav.is-open`) or the contact drawer (`body.is-drawer-open`) is open,
 * and when keyboard focus moves into it. Listens to Lenis when available.
 */
export function initSmartHeader() {
	const header = document.getElementById('header');

	if (!header) {
		logDebug('[smartHeader] skipped: #header not found');
		return;
	}

	let lastY = window.scrollY;
	let hidden = false;

	const isLocked = () =>
		document.querySelector('.nav.is-open') !== null ||
		document.body.classList.contains('is-drawer-open');

	const setHidden = (value, y) => {
		if (value === hidden) {
			return;
		}

		hidden = value;
		header.classList.toggle('is-hidden', value);
		logDebug('[smartHeader] state', { hidden, y });
	};

	const update = (y) => {
		if (y <= header.offsetHeight || isLocked()) {
			setHidden(false, y);
			lastY = y;
			return;
		}

		const delta = y - lastY;

		if (Math.abs(delta) < SCROLL_THRESHOLD) {
			return;
		}

		setHidden(delta > 0, y);
		lastY = y;
	};

	if (window.lenis) {
		window.lenis.on('scroll', ({ scroll }) => update(scroll));
	} else {
		let ticking = false;

		window.addEventListener(
			'scroll',
			() => {
				if (ticking) {
					return;
				}

				ticking = true;
				requestAnimationFrame(() => {
					update(window.scrollY);
					ticking = false;
				});
			},
			{ passive: true },
		);
	}

	header.addEventListener('focusin', () => setHidden(false, window.scrollY));

	logDebug('[smartHeader] init', { lenis: Boolean(window.lenis) });
}
