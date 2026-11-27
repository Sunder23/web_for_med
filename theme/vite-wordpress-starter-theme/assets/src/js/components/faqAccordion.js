import { logDebug } from '../utils/logDebug.js';

// FAQ accordion (`.faq[data-faq]`, partials/parts/faq-list.php).
//
// Only the state is toggled here: `faq__item--open` on the item,
// `aria-expanded` on the question, `aria-hidden` + `inert` on the answer. The
// height animates in CSS (grid-template-rows 0fr → 1fr, components/_faq.scss),
// so a closed answer takes no space and nothing shifts after AOS caches its
// offsets. An item rendered with `faq__item--open` (`open_first`) starts open.

function setAnswerState(answer, open) {
	if (!answer) return;
	answer.setAttribute('aria-hidden', String(!open));
	answer.inert = !open;
}

export function initFaqAccordion() {
	const faqs = [...document.querySelectorAll('[data-faq]')];

	if (!faqs.length) {
		logDebug('FAQ accordion skipped: no [data-faq] blocks found');
		return;
	}

	for (const faq of faqs) {
		for (const item of faq.querySelectorAll('.faq__item')) {
			const question = item.querySelector('.faq__question');
			const answer = item.querySelector('.faq__answer');
			if (!question) continue;

			const startsOpen = item.classList.contains('faq__item--open');
			question.setAttribute('aria-expanded', String(startsOpen));
			setAnswerState(answer, startsOpen);

			question.addEventListener('click', () => {
				const open = !item.classList.contains('faq__item--open');
				item.classList.toggle('faq__item--open', open);
				question.setAttribute('aria-expanded', String(open));
				setAnswerState(answer, open);
				logDebug('FAQ item toggled', { open });
			});
		}
	}

	logDebug('FAQ accordion initialized', { blocks: faqs.length });
}
