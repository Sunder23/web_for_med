import Lenis from 'lenis';
import 'lenis/dist/lenis.css';
import { logDebug } from '@js/utils/logDebug.js';

// Pages that keep native scrolling: the cases archive is a one-screen page
// whose wheel belongs to the Swiper slider (casesArchive.js).
const NATIVE_SCROLL_BODY_CLASSES = ['is-cases-archive'];

export function initSmoothScroll() {
	const native = NATIVE_SCROLL_BODY_CLASSES.find((cls) =>
		document.body.classList.contains(cls),
	);

	if (native) {
		logDebug('[smoothScroll] skipped: native scroll page', {
			bodyClass: native,
		});
		return;
	}

	const lenis = new Lenis({
		autoRaf: true,
		anchors: true,
	});

	window.lenis = lenis;
}
