import { logDebug } from '@js/utils/logDebug.js';

// "Section: NN/MM" in a utility bar: single case (partials/parts/case-single.php)
// and single blog post (partials/parts/post-single.php).
// Section 01 is the intro, then every h2 of the content. PHP prints the total;
// the DOM count wins if they differ (e.g. a heading added by a filter).
//
// Markup: [data-section-counter] > [data-section-current] + [data-section-total].

const pad = (n) => String(n).padStart(2, '0');

/**
 * @param {Object} options
 * @param {string} options.intro    Selector of the intro block (section 01).
 * @param {string} options.headings Selector of the content headings (sections 02…).
 * @param {string} [options.counter] Selector of the counter element.
 */
export function initSectionCounter({
	intro: introSelector,
	headings: headingsSelector,
	counter: counterSelector = '[data-section-counter]',
}) {
	const counter = document.querySelector(counterSelector);
	const current = counter?.querySelector('[data-section-current]');
	const total = counter?.querySelector('[data-section-total]');
	const intro = document.querySelector(introSelector);

	if (!current || !intro) {
		logDebug('[section-counter] no sections, skipped', {
			counter: Boolean(counter),
			intro: Boolean(intro),
		});
		return;
	}

	const sections = [intro, ...document.querySelectorAll(headingsSelector)];

	const phpTotal = Number.parseInt(total?.textContent || '0', 10);
	if (total && phpTotal !== sections.length) {
		logDebug('[section-counter] total mismatch', {
			php: phpTotal,
			dom: sections.length,
		});
		total.textContent = pad(sections.length);
	}

	logDebug('[section-counter] init', { total: sections.length });

	// A section is current once its top passes the upper third of the
	// viewport; the last one passed wins. At the very bottom of the page the
	// last section wins, since short closing sections never reach that line.
	// Measured on scroll (rAF-throttled): an IntersectionObserver misses
	// headings that jump over its zone on fast scrolls and anchor jumps.
	let shown = 0;
	let queued = false;

	const update = () => {
		queued = false;

		const line = window.innerHeight / 3;
		const atBottom =
			window.innerHeight + window.scrollY >=
			document.documentElement.scrollHeight - 2;
		let index = 0;

		if (atBottom) {
			index = sections.length - 1;
		} else {
			for (let i = 1; i < sections.length; i++) {
				if (sections[i].getBoundingClientRect().top > line) break;
				index = i;
			}
		}

		if (index === shown) return;
		shown = index;
		current.textContent = pad(index + 1);
		logDebug('[section-counter] section', { n: index + 1 });
	};

	const schedule = () => {
		if (queued) return;
		queued = true;
		window.requestAnimationFrame(update);
	};

	window.addEventListener('scroll', schedule, { passive: true });
	window.addEventListener('resize', schedule, { passive: true });
	update();
}
