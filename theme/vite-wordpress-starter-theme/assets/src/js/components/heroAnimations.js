import gsap from 'gsap';
import { SplitText } from 'gsap/SplitText';
import { ScrambleTextPlugin } from 'gsap/ScrambleTextPlugin';
import AOS from 'aos';
import 'aos/dist/aos.css';

gsap.registerPlugin(SplitText, ScrambleTextPlugin);

// function initHeroImageRevealCallback() {
// 	const heroImg = document.querySelector('.hero__image');
// 	if (!heroImg) return;

// 	heroImg.addEventListener(
// 		'animationend',
// 		(e) => {
// 			if (e.animationName === 'hero-glitch-reveal') {
// 				heroImg.classList.add('hero__image--revealed');
// 			}
// 		},
// 		{ once: false },
// 	);
// }

function animateSubtitleAndButtons() {
	const subtitle = document.querySelector('.hero__subtitle');
	const btnPrimary = document.querySelector('.hero__actions .btn--primary');
	const btnSecondary = document.querySelector('.hero__actions .btn--secondary');

	[subtitle, btnPrimary, btnSecondary].forEach(el => {
		if (el) el.classList.add('is-visible');
	});
}

function initTitleScramble(el) {
	return new Promise(resolve => {
		if (!el) { resolve(); return; }

		const split = new SplitText(el, {
			type: 'lines,words,chars',
			linesClass: 'split-line',
			wordsClass: 'split-word',
			charsClass: 'split-char',
		});

		split.lines.forEach((line, i) => line.style.setProperty('--line-index', i));
		split.words.forEach((word, i) => word.style.setProperty('--word-index', i));
		split.chars.forEach((char, i) => char.style.setProperty('--char-index', i));

		// container visible, all chars hidden — no FOUC
		gsap.set(el, { opacity: 1 });
		gsap.set(split.chars, { opacity: 0 });

		const tl = gsap.timeline({ delay: 0.2, onComplete: resolve });

		split.chars.forEach((char, i) => {
			const original = char.textContent;
			const pos = i * 0.03;
			// reveal char at its stagger position, already showing random scramble
			tl.set(char, { opacity: 1 }, pos);
			tl.to(char, {
				duration: 0.65,
				scrambleText: {
					text: original,
					chars: '!#*()-_+=/[]{};:,0123456789',
					speed: 0.35,
					revealDelay: 0.45,
				},
				ease: 'none',
			}, pos);
		});
	});
}

function fixAosBtnHover() {
	document.querySelectorAll('.btn[data-aos], .wpcf7-submit[data-aos]').forEach(el => {
		el.addEventListener('transitionend', () => {
			el.style.transition = 'background 0.3s ease-in-out';
			el.style.transitionDelay = '0ms';
		}, { once: true });
	});
}

export async function initHeroAnimations() {
	AOS.init({
		startEvent: 'DOMContentLoaded',
		// once: true
	});
	fixAosBtnHover();
	await initTitleScramble(document.querySelector('.hero__title'));
	animateSubtitleAndButtons();
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
					initTitleScramble(el);
				}
			});
		},
		{ threshold: 0.5 },
	);

	observer.observe(el);
}
