import { initActiveNav } from '@js/components/activeNav.js';
import { initCasesSlider } from '@js/components/casesSlider.js';
import { initContactForm } from '@js/components/contactForm.js';
import { initFaqAccordion } from '@js/components/faqAccordion.js';
import { initGlitchImage } from '@js/components/glitchImage.js';
import { initHeroAnimations, initFooterCoverText, initFooterFormAOS, initFooterCoverImageGlitch } from '@js/components/heroAnimations.js';
import { initMobileNav } from '@js/components/mobileNav.js';
import { initServicesAccordion } from '@js/components/servicesAccordion.js';
import { initSmoothScroll } from '@js/components/smoothScroll.js';
import { initWhySection } from '@js/components/whySection.js';

document.addEventListener('DOMContentLoaded', () => {
	initHeroAnimations();
	initSmoothScroll();
	initServicesAccordion();
	initFaqAccordion();
	initMobileNav();
	initCasesSlider();
	initActiveNav();
	initWhySection();
	initGlitchImage();
	initFooterCoverText();
	initFooterFormAOS();
	initContactForm();
	initFooterCoverImageGlitch();
});
