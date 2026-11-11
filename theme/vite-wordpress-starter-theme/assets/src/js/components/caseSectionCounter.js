import { logDebug } from '../utils/logDebug.js';

// "Section: NN/MM" in the single case utility bar (partials/parts/case-single.php).
// Section 01 is the intro, then every h2 of the content. PHP prints the total;
// the DOM count wins if they differ (e.g. a heading added by a filter).

const pad = (n) => String(n).padStart(2, '0');

export function initCaseSectionCounter() {
	const counter = document.querySelector('[data-case-section-counter]');
	const current = counter?.querySelector('[data-case-section-current]');
	const total = counter?.querySelector('[data-case-section-total]');
	const intro = document.querySelector('.case-single__intro');

	if (!current || !intro) {
		logDebug('case counter: no sections, skipped');
		return;
	}

	const sections = [
		intro,
		...document.querySelectorAll('.case-single__content > h2'),
	];

	const phpTotal = Number.parseInt(total?.textContent || '0', 10);
	if (total && phpTotal !== sections.length) {
		logDebug('case counter: total mismatch', {
			php: phpTotal,
			dom: sections.length,
		});
		total.textContent = pad(sections.length);
	}

	logDebug('case counter: init', { total: sections.length });

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
		logDebug('case counter: section', { n: index + 1 });
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
