import gsap from 'gsap';
import { SplitText } from 'gsap/SplitText';
import { ScrambleTextPlugin } from 'gsap/ScrambleTextPlugin';

gsap.registerPlugin(SplitText, ScrambleTextPlugin);

/**
 * Splits an element into chars and reveals them with a scramble effect.
 *
 * @param {HTMLElement|null} el Element to animate.
 * @param {number} delay Timeline delay in seconds.
 * @returns {Promise<void>} Resolves when the animation completes.
 */
export function initTitleScramble(el, delay = 0.2) {
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

		const tl = gsap.timeline({ delay: delay, onComplete: resolve });

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
