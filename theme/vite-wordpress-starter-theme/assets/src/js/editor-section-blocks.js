// Editor-only: runs inside the page editor canvas iframe.
// Enqueued by starter_vite_enqueue_editor_canvas_assets() in configure/js-css.php.
//
// Section previews are server-rendered by ACF and replaced on every field
// change, so carousels are (re)initialised whenever a section block shows
// up with a slider node that has no instance yet. Other section scripts
// (reveal animations, accordions) intentionally stay front-end only.
import { initCasesSlider } from '@js/components/casesSlider.js';

const CAROUSELS = [
	{ block: '[data-type="acf/section-cases"]', init: initCasesSlider },
];

// Swiper instances created here; ones whose element left the DOM (preview
// re-rendered or block removed) are destroyed so their resize listeners and
// observers do not pile up over a long editing session.
const instances = new Set();

let scheduled = false;

function destroyDetachedCarousels() {
	instances.forEach((swiper) => {
		if (swiper.el?.isConnected) {
			return;
		}
		if (!swiper.destroyed) {
			swiper.destroy(true, false);
		}
		instances.delete(swiper);
	});
}

function initPendingCarousels() {
	scheduled = false;

	destroyDetachedCarousels();

	CAROUSELS.forEach(({ block, init }) => {
		document.querySelectorAll(block).forEach((blockEl) => {
			const swiper = init(blockEl);
			if (swiper) {
				instances.add(swiper);
			}
		});
	});
}

function schedule() {
	if (scheduled) {
		return;
	}
	scheduled = true;
	// setTimeout, not requestAnimationFrame: rAF never fires while the editor
	// tab is hidden, which would leave carousels uninitialised.
	window.setTimeout(initPendingCarousels, 0);
}

// The module runs from <head> before the canvas <body> exists.
new MutationObserver(schedule).observe(document.documentElement, {
	childList: true,
	subtree: true,
});

schedule();
