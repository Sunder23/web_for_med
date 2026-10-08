import AOS from 'aos';
import 'aos/dist/aos.css';

import { initTitleScramble } from '@js/components/titleScramble.js';

function fixAosBtnHover() {
	document.querySelectorAll('.btn[data-aos], .wpcf7-submit[data-aos]').forEach(el => {
		el.addEventListener('transitionend', () => {
			el.style.transition = 'background 0.3s ease-in-out';
			el.style.transitionDelay = '0ms';
		}, { once: true });
	});
}

export function initAos() {
	AOS.init({
		startEvent: 'DOMContentLoaded',
		// once: true
	});
	fixAosBtnHover();
}

export function initFooterFormAOS() {
	const form = document.querySelector('.contact-form');
	if (!form) return;

	const fields = form.querySelectorAll('.form-field');
	const btn = form.querySelector('.btn--submit');

	fields.forEach((field, i) => {
		field.setAttribute('data-aos', 'fade-up-sm');
		field.setAttribute('data-aos-duration', '400');
		field.setAttribute('data-aos-delay', String(300 + i * 100));
		field.setAttribute('data-aos-anchor', '#contacts');
	});

	if (btn) {
		const btnDelay = 300 + fields.length * 100;
		btn.setAttribute('data-aos', 'fade-up-scale');
		btn.setAttribute('data-aos-duration', '400');
		btn.setAttribute('data-aos-delay', String(btnDelay));
		btn.setAttribute('data-aos-anchor', '#contacts');
	}

	AOS.refreshHard();
}

export function initFooterCoverText() {
	const el = document.querySelector('.footer__cover_text');
	if (!el) return;

	const observer = new IntersectionObserver(
		(entries, obs) => {
			entries.forEach(entry => {
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
	const el = document.querySelector('.footer__cover-img');
	if (!el) return;

	const observer = new IntersectionObserver(
		(entries, obs) => {
			entries.forEach(entry => {
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
