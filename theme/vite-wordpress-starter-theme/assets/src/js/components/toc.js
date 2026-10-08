import { logDebug } from '../utils/logDebug.js';

// Heading counts as "current" once its top passes this share of the viewport.
const ACTIVE_OFFSET_RATIO = 0.3;
// Breathing room between the sticky header and a heading scrolled to via the TOC.
const SCROLL_GAP = 24;

const getHeaderOffset = () =>
	(document.querySelector('.header')?.offsetHeight ?? 0) + SCROLL_GAP;

const getTarget = (link) => {
	const id = decodeURIComponent((link.getAttribute('href') || '').slice(1));
	return id ? document.getElementById(id) : null;
};

export function initToc() {
	const toc = document.querySelector('[data-toc]');

	if (!toc) {
		logDebug('TOC skipped: no [data-toc] found');
		return;
	}

	const sections = Array.from(toc.querySelectorAll('.toc__link'))
		.map((link) => {
			const target = getTarget(link);

			if (!target) {
				logDebug('TOC target heading not found', {
					href: link.getAttribute('href'),
				});
			}

			return target ? { link, target } : null;
		})
		.filter(Boolean);

	if (!sections.length) {
		logDebug('TOC skipped: no matching heading targets');
		return;
	}

	const nav = toc.querySelector('.toc__nav');
	const indicator = toc.querySelector('.toc__indicator');
	const list = toc.querySelector('.toc__list');

	let activeLink = null;
	let ticking = false;

	// One shared frame slides to the active link instead of each link
	// drawing its own border. Measured after the active class is applied:
	// the bolder weight can rewrap the text and change the link height.
	const positionIndicator = (link) => {
		if (!indicator || !link) return;
		indicator.style.transform = `translateY(${link.offsetTop}px)`;
		indicator.style.height = `${link.offsetHeight}px`;
	};

	// On short viewports the sticky sidebar caps the list height and
	// .toc__nav scrolls on its own — keep the active link visible there
	// (scrollIntoView would move the window as well).
	const revealInNav = (link) => {
		if (!nav || !link || nav.scrollHeight <= nav.clientHeight) return;
		const top = link.offsetTop;
		const bottom = top + link.offsetHeight;
		let next = null;

		if (top < nav.scrollTop) next = top;
		else if (bottom > nav.scrollTop + nav.clientHeight)
			next = bottom - nav.clientHeight;
		if (next === null) return;

		nav.scrollTo({ top: next, behavior: 'smooth' });
	};

	// Exactly one link stays active: the last heading scrolled past the
	// offset line, so the highlight never vanishes inside a long section.
	const update = () => {
		ticking = false;
		const offset = window.innerHeight * ACTIVE_OFFSET_RATIO;
		let current = sections[0].link;

		for (const { link, target } of sections) {
			if (target.getBoundingClientRect().top - offset > 0) break;
			current = link;
		}

		if (current === activeLink) return;
		activeLink?.classList.remove('is-active');
		current.classList.add('is-active');
		activeLink = current;
		positionIndicator(current);
		revealInNav(current);
	};

	const requestUpdate = () => {
		if (ticking) return;
		ticking = true;
		requestAnimationFrame(update);
	};

	window.addEventListener('scroll', requestUpdate, { passive: true });
	window.addEventListener('resize', requestUpdate, { passive: true });
	update();

	// Click: scroll via Lenis when present (handles Cyrillic anchors that
	// break querySelector-based handlers), fall back to native smooth scroll.
	toc.addEventListener('click', (event) => {
		const link = event.target.closest('.toc__link');
		if (!link) return;

		const target = getTarget(link);
		if (!target) return;

		event.preventDefault();

		if (window.lenis) {
			window.lenis.scrollTo(target, { offset: -getHeaderOffset() });
		} else {
			target.scrollIntoView({ behavior: 'smooth' });
		}

		window.history.pushState(null, '', link.getAttribute('href'));
	});

	if (nav && indicator) {
		// Re-measure when link geometry changes without a new active link
		// (text rewrap on resize, web fonts loading, breakpoint switch).
		if ('ResizeObserver' in window && list) {
			new ResizeObserver(() => positionIndicator(activeLink)).observe(list);
		}

		// Show the frame at its initial spot, then enable transitions two
		// frames later so it does not slide in from the top on load.
		nav.classList.add('toc__nav--ready');
		requestAnimationFrame(() =>
			requestAnimationFrame(() => nav.classList.add('toc__nav--animated')),
		);
	}

	logDebug('TOC initialized', { headings: sections.length });
}
