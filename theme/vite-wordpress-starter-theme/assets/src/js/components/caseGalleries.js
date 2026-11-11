import { logDebug } from '../utils/logDebug.js';

// Single case galleries: images in a row share one height (Figma "Media
// placeholder" with two screens). Each figure grows by its image's
// width / height, see `--case-ratio` in components/_case-single.scss.

export function initCaseGalleries() {
	const figures = document.querySelectorAll(
		'.case-single__content > .wp-block-gallery > .wp-block-image',
	);

	if (!figures.length) {
		logDebug('case galleries: none, skipped');
		return;
	}

	for (const figure of figures) {
		const img = figure.querySelector('img');
		const width = Number(img?.getAttribute('width'));
		const height = Number(img?.getAttribute('height'));

		if (width > 0 && height > 0) {
			figure.style.setProperty('--case-ratio', (width / height).toFixed(4));
		}
	}

	logDebug('case galleries: init', { images: figures.length });
}
