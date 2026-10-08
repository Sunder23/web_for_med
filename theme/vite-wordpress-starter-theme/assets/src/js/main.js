import { initActiveNav } from '@js/components/activeNav.js';
import { initContactForm } from '@js/components/contactForm.js';
import { initLightbox } from '@js/components/lightbox.js';
import { initMobileNav } from '@js/components/mobileNav.js';
import { initSmoothScroll } from '@js/components/smoothScroll.js';
import { initAos, initFooterCoverText, initFooterFormAOS, initFooterCoverImageGlitch } from '@js/components/siteAnimations.js';

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
