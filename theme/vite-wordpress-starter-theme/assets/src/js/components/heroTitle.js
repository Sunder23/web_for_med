import { initTitleScramble } from '@js/components/titleScramble.js';

function animateSubtitleAndButtons() {
	const subtitle = document.querySelector('.hero__subtitle');
	const btnPrimary = document.querySelector('.hero__actions .btn--primary');
	const btnSecondary = document.querySelector('.hero__actions .btn--secondary');

	[subtitle, btnPrimary, btnSecondary].forEach(el => {
		if (el) el.classList.add('is-visible');
	});
}

export async function initHeroTitleAnimation() {
	await initTitleScramble(document.querySelector('.hero__title'));
	animateSubtitleAndButtons();
}
