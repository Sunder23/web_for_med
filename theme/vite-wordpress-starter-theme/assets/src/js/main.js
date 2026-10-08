import { initActiveNav } from '@js/components/activeNav.js';
import { initContactForm } from '@js/components/contactForm.js';
import { initLightbox } from '@js/components/lightbox.js';
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
	initLightbox();
	initMobileNav();
	initActiveNav();
	initFooterCoverText();
	initFooterFormAOS();
	initContactForm();
	initFooterCoverImageGlitch();
});
