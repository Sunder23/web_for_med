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
	let skipLogged = false;

	const isLocked = () =>
		document.querySelector('.nav.is-open') !== null ||
		document.body.classList.contains('is-drawer-open');

	// headerAutoHide.js owns the header while `body.is-header-auto` is set.
	const isYielded = () => {
		const yielded = document.body.classList.contains('is-header-auto');

		if (yielded && !skipLogged) {
			skipLogged = true;
			logDebug('[smartHeader] skipped: header-auto');
		} else if (!yielded) {
			skipLogged = false;
		}

		return yielded;
	};

	const setHidden = (value, y) => {
		// Compare with the class, not a local flag: headerAutoHide.js may have
		// changed it while it owned the header.
		if (value === header.classList.contains('is-hidden')) {
			return;
		}

		header.classList.toggle('is-hidden', value);
		logDebug('[smartHeader] state', { hidden: value, y });
	};

	const update = (y) => {
		if (isYielded()) {
			lastY = y;
			return;
		}

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

	header.addEventListener('focusin', () => {
		if (!isYielded()) setHidden(false, window.scrollY);
	});

	logDebug('[smartHeader] init', { lenis: Boolean(window.lenis) });
}
