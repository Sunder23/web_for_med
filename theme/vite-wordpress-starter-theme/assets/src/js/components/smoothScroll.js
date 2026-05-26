import Lenis from 'lenis';
import 'lenis/dist/lenis.css';

export function initSmoothScroll() {
	const lenis = new Lenis({
		autoRaf: true,
		anchors: true,
	});

	window.lenis = lenis;
}
