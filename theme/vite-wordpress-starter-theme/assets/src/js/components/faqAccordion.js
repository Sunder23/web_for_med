import { logDebug } from '../utils/logDebug.js';

export function initFaqAccordion() {
	const $ = window.jQuery;

	if (!$) {
		console.warn('[web_for_med] FAQ accordion skipped: jQuery is not available');
		return;
	}

	const $faqs = $('[data-faq]');

	if (!$faqs.length) {
		logDebug('FAQ accordion skipped: no [data-faq] blocks found');
		return;
	}

	$faqs.each((_, faq) => {
		const $items = $(faq).find('.faq__item');

		$items.each((_, item) => {
			const $item = $(item);
			const $question = $item.find('.faq__question').first();
			const $answer = $item.find('.faq__answer').first();

			// Answers start collapsed via CSS (display: none), so no
			// JS-driven layout shift happens after AOS caches its offsets.
			$question.attr('aria-expanded', 'false');
			$answer.attr('aria-hidden', 'true');

			$question.on('click', () => {
				const isOpen = $item.hasClass('faq__item--open');

				$item.toggleClass('faq__item--open', !isOpen);
				$question.attr('aria-expanded', String(!isOpen));
				$answer.attr('aria-hidden', String(isOpen));
				$answer.stop(true, true)[isOpen ? 'slideUp' : 'slideDown'](250);
			});
		});
	});

	logDebug('FAQ accordion initialized', { blocks: $faqs.length });
}
