import { initPostRelatedSlider } from '@js/components/postRelatedSlider.js';
import { initSectionCounter } from '@js/components/sectionCounter.js';

document.addEventListener('DOMContentLoaded', () => {
	initSectionCounter({
		intro: '.post-single__intro',
		headings: '.post-single__content > h2',
	});
	initPostRelatedSlider();
});
