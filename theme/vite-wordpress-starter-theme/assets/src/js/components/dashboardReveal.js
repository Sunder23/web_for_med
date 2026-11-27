import { logDebug } from '@js/utils/logDebug.js';

// Clinic dashboard entrance, once, when 30% of it is in view: the chart bars
// grow from the baseline in a cascade, the source bars fill from the left
// (CSS transitions, see _clinic-dashboard.scss) and the KPI numbers count up.
//
// The start state exists only while `.is-reveal-pending` is set here, so
// without JS, with reduced motion and in the block editor the final picture
// is shown as is.

const PENDING = 'is-reveal-pending';
const REVEALED = 'is-revealed';
const COUNT_MS = 900;

const easeOut = (t) => 1 - (1 - t) ** 3;

function countUp(node) {
	const target = Number(node.dataset.countTo);
	const prefix = node.dataset.prefix ?? '';
	if (!Number.isFinite(target)) {
		logDebug('[dashboardReveal] bad count target', { node });
		return;
	}

	const start = performance.now();
	const step = (now) => {
		const progress = Math.min(1, (now - start) / COUNT_MS);
		node.textContent = `${prefix}${Math.round(target * easeOut(progress))}`;
		if (progress < 1) requestAnimationFrame(step);
	};
	requestAnimationFrame(step);
}

function setZero(node) {
	node.textContent = `${node.dataset.prefix ?? ''}0`;
}

export function initDashboardReveal(selector = '[data-clinic-dashboard]') {
	const dashboards = [...document.querySelectorAll(selector)];
	if (!dashboards.length) {
		logDebug('[dashboardReveal] no dashboards', { selector });
		return;
	}

	if (
		!('IntersectionObserver' in window) ||
		window.matchMedia('(prefers-reduced-motion: reduce)').matches
	) {
		logDebug('[dashboardReveal] skipped (reduced motion or no observer)');
		return;
	}

	const observer = new IntersectionObserver(
		(entries) => {
			for (const entry of entries) {
				if (!entry.isIntersecting) continue;

				const dashboard = entry.target;
				observer.unobserve(dashboard);
				dashboard.classList.remove(PENDING);
				dashboard.classList.add(REVEALED);
				dashboard.querySelectorAll('[data-count-to]').forEach(countUp);
				logDebug('[services-hero] dashboard revealed', { dashboard });
			}
		},
		{ threshold: 0.3 },
	);

	for (const dashboard of dashboards) {
		dashboard.classList.add(PENDING);
		dashboard.querySelectorAll('[data-count-to]').forEach(setZero);
		observer.observe(dashboard);
	}

	logDebug('[dashboardReveal] init', { count: dashboards.length });
}
