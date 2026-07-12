import { Fancybox } from '@fancyapps/ui';
import '@fancyapps/ui/dist/fancybox/fancybox.css';
import { logDebug } from '../utils/logDebug.js';

const GROUP = 'post-content';
const IMAGE_FILE_RE = /\.(avif|gif|jpe?g|png|svg|webp)(\?.*)?$/i;

export function initLightbox() {
	const images = document.querySelectorAll('.entry-content .wp-block-image img');

	if (!images.length) {
		logDebug('Lightbox skipped: no .entry-content images found');
		return;
	}

	let count = 0;

	for (const img of images) {
		const link = img.closest('a');
		const href = link?.getAttribute('href') || '';

		let target;
		if (link) {
			// Only hijack "link to media file" anchors; leave links to
			// attachment pages or custom URLs working as regular links.
			if (!IMAGE_FILE_RE.test(href)) continue;
			target = link;
		} else {
			target = img;
			target.dataset.src = img.currentSrc || img.src;
		}

		target.dataset.fancybox = GROUP;

		const alt = (img.getAttribute('alt') || '').trim();
		if (alt) {
			target.dataset.caption = alt;
		}

		count++;
	}

	if (!count) {
		logDebug('Lightbox skipped: no eligible content images');
		return;
	}

	Fancybox.bind(`[data-fancybox="${GROUP}"]`, { Hash: false });

	logDebug('Lightbox initialized', { count });
}
