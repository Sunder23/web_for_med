import Swiper from 'swiper';
import { A11y, Keyboard, Navigation } from 'swiper/modules';
import 'swiper/css';

import { logDebug } from '@js/utils/logDebug.js';

/**
 * «Актуальні теми» under a blog post (partials/parts/post-related.php).
 * Cards keep their own width (361px from `tablet`, the full row on phones) through
 * `slidesPerView: 'auto'`; the last visible card is cut by the frame as in the design.
 * Arrows are hidden on phones (swipe) and disabled at the ends; with nothing to
 * scroll Swiper locks itself (`watchOverflow`).
 */
export function initPostRelatedSlider() {
	const slider = document.querySelector('[data-related-slider]');

	if (!slider) {
		logDebug('[post-related] skipped: no slider');
		return;
	}

	const section = slider.closest('.post-related');
	const slides = slider.querySelectorAll('.swiper-slide').length;

	const swiper = new Swiper(slider, {
		modules: [Navigation, Keyboard, A11y],
		slidesPerView: 'auto',
		spaceBetween: 16,
		watchOverflow: true,
		grabCursor: true,
		keyboard: { enabled: true, onlyInViewport: true },
		navigation: {
			prevEl: section?.querySelector('[data-related-prev]'),
			nextEl: section?.querySelector('[data-related-next]'),
			disabledClass: 'is-disabled',
			lockClass: 'is-locked',
		},
		a11y: {
			prevSlideMessage: 'Попередні статті',
			nextSlideMessage: 'Наступні статті',
		},
		breakpoints: {
			1024: { spaceBetween: 24 },
		},
		on: {
			slideChange: (instance) => {
				logDebug('[post-related] slide', { index: instance.activeIndex });
			},
		},
	});

	logDebug('[post-related] init', { slides, locked: swiper.isLocked });
}
