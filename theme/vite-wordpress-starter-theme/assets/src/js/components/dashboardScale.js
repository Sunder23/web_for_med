import { logDebug } from '@js/utils/logDebug.js';

// The clinic dashboard (partials/parts/clinic-dashboard.php) is drawn at its
// design size; this keeps `--dashboard-scale` = wrapper width / design width
// (never above 1), so the whole picture shrinks proportionally on narrow
// screens. Without JS the stage keeps scale 1 and the wrapper clips it.

const DESIGN_WIDTH = 1350;

export function initDashboardScale(selector = '[data-clinic-dashboard]') {
	const dashboards = [...document.querySelectorAll(selector)];
	if (!dashboards.length) {
		logDebug('[dashboardScale] no dashboards', { selector });
		return;
	}

	const apply = (dashboard, width) => {
		const scale = Math.min(1, width / DESIGN_WIDTH);
		dashboard.style.setProperty('--dashboard-scale', scale.toFixed(4));
		logDebug('[dashboardScale] scale', { width, scale });
	};

	if (!('ResizeObserver' in window)) {
		const update = () => {
			for (const dashboard of dashboards) {
				apply(dashboard, dashboard.clientWidth);
			}
		};
		update();
		window.addEventListener('resize', update);
		logDebug('[dashboardScale] init (resize fallback)', {
			count: dashboards.length,
		});
		return;
	}

	const observer = new ResizeObserver((entries) => {
		for (const entry of entries) {
			apply(entry.target, entry.contentRect.width);
		}
	});

	for (const dashboard of dashboards) {
		observer.observe(dashboard);
	}

	logDebug('[dashboardScale] init', { count: dashboards.length });
}
