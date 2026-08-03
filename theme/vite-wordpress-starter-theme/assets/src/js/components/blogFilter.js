import { logDebug } from '../utils/logDebug.js';

export function initBlogFilter() {
	const $grid = document.querySelector('[data-blog-grid]');
	const $buttons = document.querySelectorAll('[data-blog-filter]');

	if (!$grid || !$buttons.length) {
		logDebug('Blog filter skipped: no [data-blog-grid]/[data-blog-filter] elements found');
		return;
	}

	const $cards = $grid.querySelectorAll('.archive-card');

	$buttons.forEach(($button) => {
		$button.addEventListener('click', () => {
			const category = $button.dataset.blogFilter;

			$buttons.forEach(($btn) => $btn.classList.toggle('is-active', $btn === $button));

			let visibleCount = 0;

			$cards.forEach(($card) => {
				const cardCategories = ($card.dataset.category || '').split(' ').filter(Boolean);
				const isVisible = category === 'all' || cardCategories.includes(category);

				$card.hidden = !isVisible;

				if (isVisible) {
					visibleCount += 1;
				}
			});

			console.debug(`[W4M blogFilter] filter=${category} visible=${visibleCount}`);
		});
	});

	logDebug('Blog filter initialized', { buttons: $buttons.length, cards: $cards.length });
}
