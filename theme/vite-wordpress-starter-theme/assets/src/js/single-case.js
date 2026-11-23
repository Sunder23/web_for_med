import { initCaseGalleries } from '@js/components/caseGalleries.js';
import { initSectionCounter } from '@js/components/sectionCounter.js';

document.addEventListener('DOMContentLoaded', () => {
	initSectionCounter({
		intro: '.case-single__intro',
		headings: '.case-single__content > h2',
	});
	initCaseGalleries();
});
