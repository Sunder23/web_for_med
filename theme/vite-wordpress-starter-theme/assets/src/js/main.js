import { initActiveNav } from '@js/components/activeNav.js';
import { initContactForm } from '@js/components/contactForm.js';
import { initMobileNav } from '@js/components/mobileNav.js';
import {
	initAos,
	initFooterCoverImageGlitch,
	initFooterCoverText,
	initFooterFormAOS,
} from '@js/components/siteAnimations.js';
import { initSmoothScroll } from '@js/components/smoothScroll.js';

document.addEventListener('DOMContentLoaded', () => {
	initAos();
	initSmoothScroll();
	initMobileNav();
	initActiveNav();
	initFooterCoverText();
	initFooterFormAOS();
	initContactForm();
	initFooterCoverImageGlitch();
});
