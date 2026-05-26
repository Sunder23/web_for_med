import { initActiveNav } from '@js/components/activeNav.js';
import { initCasesSlider } from '@js/components/casesSlider.js';
import { initMobileNav } from '@js/components/mobileNav.js';
import { initServicesAccordion } from '@js/components/servicesAccordion.js';
import { initSmoothScroll } from '@js/components/smoothScroll.js';
import { initWhySection } from '@js/components/whySection.js';

document.addEventListener('DOMContentLoaded', () => {
	initSmoothScroll();
	initServicesAccordion();
	initMobileNav();
	initCasesSlider();
	initActiveNav();
	initWhySection();
});
