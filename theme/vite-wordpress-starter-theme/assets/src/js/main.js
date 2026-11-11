import { initActiveNav } from '@js/components/activeNav.js';
import { initContactDrawer } from '@js/components/contactDrawer.js';
import { initContactForm } from '@js/components/contactForm.js';
import { initMobileNav } from '@js/components/mobileNav.js';
import {
	initAos,
	initFooterCoverImageGlitch,
	initFooterCoverText,
} from '@js/components/siteAnimations.js';
import { initSmartHeader } from '@js/components/smartHeader.js';
import { initSmoothScroll } from '@js/components/smoothScroll.js';

document.addEventListener('DOMContentLoaded', () => {
	initAos();
	initSmoothScroll();
	initSmartHeader();
	initMobileNav();
	initActiveNav();
	initFooterCoverText();
	initContactForm();
	initContactDrawer();
	initFooterCoverImageGlitch();
});
