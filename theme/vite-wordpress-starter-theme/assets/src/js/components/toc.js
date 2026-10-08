import { logDebug } from '../utils/logDebug.js';

const HEADER_OFFSET = 90; // sticky header height + breathing room

export function initToc() {
	const toc = document.querySelector('[data-toc]');

	if (!toc) {
		logDebug('TOC skipped: no [data-toc] found');
		return;
	}

	const targets = Array.from(toc.querySelectorAll('.toc__link'))
		.map((link) => {
			const id = decodeURIComponent((link.getAttribute('href') || '').slice(1));
			const el = id ? document.getElementById(id) : null;

			if (!el) {
				console.warn('[web_for_med] TOC target heading not found:', id);
			}

			return el ? { link, el } : null;
		})
		.filter(Boolean);

	if (!targets.length) {
		console.warn('[web_for_med] TOC skipped: no matching heading targets');
		return;
	}

	const linkByEl = new Map(targets.map(({ link, el }) => [el, link]));
	let activeLink = null;

	const setActive = (link) => {
		if (link === activeLink) return;
		activeLink?.classList.remove('is-active');
		link?.classList.add('is-active');
		activeLink = link;
	};

	// Scrollspy: headings inside the activation band (below the sticky header,
	// above the lower 60% of the viewport) win; when the band is empty, the
	// nearest heading above it stays active.
	const visible = new Set();

	const updateActive = () => {
		let current = targets.find(({ el }) => visible.has(el));

		if (!current) {
			const line = window.innerHeight * 0.4;
			for (const target of targets) {
				if (target.el.getBoundingClientRect().top <= line) {
					current = target;
				} else {
					break;
				}
			}
		}

		setActive(current ? linkByEl.get(current.el) : null);
	};

	const observer = new IntersectionObserver(
		(entries) => {
			for (const entry of entries) {
				if (entry.isIntersecting) {
					visible.add(entry.target);
				} else {
					visible.delete(entry.target);
				}
			}
			updateActive();
		},
		{ rootMargin: `-${HEADER_OFFSET}px 0px -60% 0px`, threshold: 0 },
	);

	targets.forEach(({ el }) => observer.observe(el));

	// Click: scroll via Lenis when present (handles Cyrillic anchors that
	// break querySelector-based handlers), fall back to native smooth scroll.
	toc.addEventListener('click', (event) => {
		const link = event.target.closest('.toc__link');
		if (!link) return;

		const id = decodeURIComponent((link.getAttribute('href') || '').slice(1));
		const el = document.getElementById(id);
		if (!el) return;

		event.preventDefault();

		if (window.lenis) {
			window.lenis.scrollTo(el, { offset: -HEADER_OFFSET });
		} else {
			el.scrollIntoView({ behavior: 'smooth' });
		}

		window.history.pushState(null, '', link.getAttribute('href'));
	});

	logDebug('TOC scrollspy initialized', { headings: targets.length });
}
