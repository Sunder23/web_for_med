import { logDebug } from '@js/utils/logDebug.js';

// Clinic dashboard entrance, once, when 30% of it is in view. The timeline
// (start delays) lives in partials/parts/clinic-dashboard.php as `--d` /
// `data-delay`; the CSS transitions in _clinic-dashboard.scss run it: texts
// float up, menu items appear one by one, chart rules draw from the left,
// bars rise one after another, source bars fill. This script only flips the
// state and counts the numbers (KPI values, source percentages) up.
//
// When the last bar is up the dashboard gets `is-live` and the bars keep
// moving gently; the animation pauses while the dashboard is off screen.
//
// The start state exists only while `.is-reveal-pending` is set here, so
// without JS, with reduced motion and in the block editor the final picture
// is shown as is.

const PENDING = 'is-reveal-pending';
const REVEALED = 'is-revealed';
const LIVE = 'is-live';
const OFFSCREEN = 'is-offscreen';
const COUNT_MS = 900;
const BAR_MS = 450;
const REVEAL_RATIO = 0.3;

const easeOut = (t) => 1 - (1 - t) ** 3;

const format = (node, value) =>
	`${node.dataset.prefix ?? ''}${value}${node.dataset.suffix ?? ''}`;

function countUp(node) {
	const target = Number(node.dataset.countTo);
	if (!Number.isFinite(target)) {
		logDebug('[dashboardReveal] bad count target', { node });
		return;
	}

	const duration = Number(node.dataset.duration) || COUNT_MS;
	const delay = Number(node.dataset.delay) || 0;

	window.setTimeout(() => {
		const start = performance.now();
		const step = (now) => {
			const progress = Math.min(1, (now - start) / duration);
			node.textContent = format(node, Math.round(target * easeOut(progress)));
			if (progress < 1) requestAnimationFrame(step);
		};
		requestAnimationFrame(step);
	}, delay);
}

// Last bar start + its rise: the moment the chart is complete.
function liveDelay(dashboard) {
	const bars = dashboard.querySelectorAll('.clinic-dashboard__bar');
	const last = bars[bars.length - 1];
	const delay = last
		? Number.parseFloat(getComputedStyle(last).getPropertyValue('--d')) || 0
		: 0;
	return delay + BAR_MS + 100;
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
				const dashboard = entry.target;
				dashboard.classList.toggle(OFFSCREEN, !entry.isIntersecting);

				if (
					entry.intersectionRatio < REVEAL_RATIO ||
					!dashboard.classList.contains(PENDING)
				) {
					continue;
				}

				dashboard.classList.remove(PENDING);
				dashboard.classList.add(REVEALED);
				dashboard.querySelectorAll('[data-count-to]').forEach(countUp);

				const live = liveDelay(dashboard);
				window.setTimeout(() => {
					dashboard.classList.add(LIVE);
					logDebug('[services-hero] dashboard live');
				}, live);

				logDebug('[services-hero] dashboard revealed', { dashboard, live });
			}
		},
		{ threshold: [0, REVEAL_RATIO] },
	);

	for (const dashboard of dashboards) {
		dashboard.classList.add(PENDING);
		dashboard.querySelectorAll('[data-count-to]').forEach((node) => {
			node.textContent = format(node, 0);
		});
		// Commit the start state before the observer can fire: for a dashboard
		// that is in view on load the callback comes before the first paint,
		// and without a style flush the transitions would have nothing to start from.
		dashboard.getBoundingClientRect();
		// Kept observed after the reveal: it pauses the live bars off screen.
		observer.observe(dashboard);
	}

	logDebug('[dashboardReveal] init', { count: dashboards.length });
}
