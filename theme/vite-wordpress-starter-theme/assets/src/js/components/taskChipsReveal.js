import { logDebug } from '@js/utils/logDebug.js';

// Entrance of the "what is your task" panel
// (template-parts/blocks/section-capabilities.php): when the panel enters the
// viewport the chips appear one by one (fade + 18px rise, as `.why-message`
// on the front page), then the submit button after a short pause. Runs once
// per panel.
//
// The hidden state exists only while `is-reveal-pending` is set by this
// script, so without JS, with reduced motion and in the block editor
// everything is just shown. The delay is passed as `--chip-delay` and used
// only for opacity / transform (see _task-chip.scss and
// section-capabilities.scss), so hover and `aria-pressed` transitions stay
// instant afterwards.

const PENDING = 'is-reveal-pending';
const REVEALED = 'is-revealed';
const STEP_MS = 150;
const BUTTON_PAUSE_MS = 300;

function getTargets(picker) {
	const chips = [...picker.querySelectorAll('[data-task-chip]')];
	const button = picker.querySelector('[data-task-picker-submit]');
	return { chips, button };
}

function reveal(picker) {
	const { chips, button } = getTargets(picker);
	for (const el of [...chips, button]) {
		if (!el) continue;
		el.classList.add(REVEALED);
		el.classList.remove(PENDING);
	}
	logDebug('[task-chips-reveal] revealed', {
		chips: chips.length,
		buttonDelay: button?.style.getPropertyValue('--chip-delay') ?? null,
	});
}

export function initTaskChipsReveal(selector = '[data-task-picker]') {
	const pickers = [...document.querySelectorAll(selector)];
	if (!pickers.length) {
		logDebug('[task-chips-reveal] no pickers', { selector });
		return;
	}

	const reducedMotion = window.matchMedia(
		'(prefers-reduced-motion: reduce)',
	).matches;
	if (!('IntersectionObserver' in window) || reducedMotion) {
		logDebug('[task-chips-reveal] skipped', {
			observer: 'IntersectionObserver' in window,
			reducedMotion,
		});
		return;
	}

	const observer = new IntersectionObserver(
		(entries) => {
			for (const entry of entries) {
				if (!entry.isIntersecting) continue;
				observer.unobserve(entry.target);
				reveal(entry.target);
			}
		},
		{ threshold: 0.35 },
	);

	for (const picker of pickers) {
		const { chips, button } = getTargets(picker);
		chips.forEach((chip, index) => {
			chip.style.setProperty('--chip-delay', `${index * STEP_MS}ms`);
			chip.classList.add(PENDING);
		});
		if (button) {
			button.style.setProperty(
				'--chip-delay',
				`${chips.length * STEP_MS + BUTTON_PAUSE_MS}ms`,
			);
			button.classList.add(PENDING);
		}
		observer.observe(picker);
	}

	logDebug('[task-chips-reveal] init', { count: pickers.length });
}
