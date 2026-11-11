import AOS from 'aos';
import 'aos/dist/aos.css';

import { initTitleScramble } from '@js/components/titleScramble.js';

function fixAosBtnHover() {
	document
		.querySelectorAll('.button[data-aos], .wpcf7-submit[data-aos]')
		.forEach((el) => {
			el.addEventListener(
				'transitionend',
				() => {
					el.style.transition = 'background 0.3s ease-in-out';
					el.style.transitionDelay = '0ms';
				},
				{ once: true },
			);
		});
}

export function initAos() {
	AOS.init({
		startEvent: 'DOMContentLoaded',
		// once: true
	});
	fixAosBtnHover();
}

export function initFooterCoverText() {
	const el = document.querySelector('.footer__cover-text');
	if (!el) return;

	const observer = new IntersectionObserver(
		(entries, obs) => {
			entries.forEach((entry) => {
				if (entry.isIntersecting) {
					obs.disconnect();
					initTitleScramble(el, 0.7);
				}
			});
		},
		{ threshold: 0.5 },
	);

	observer.observe(el);
}
export function initFooterCoverImageGlitch() {
	const el = document.querySelector('.footer__cover-image');
	if (!el) return;

	const observer = new IntersectionObserver(
		(entries) => {
			entries.forEach((entry) => {
				if (entry.isIntersecting) {
					observer.disconnect();

					setTimeout(() => {
						el.classList.remove('glitch');

						requestAnimationFrame(() => {
							el.classList.add('glitch');
						});
					}, 2500);
				}
			});
		},
		{ threshold: 0.7 },
	);

	observer.observe(el);
}
