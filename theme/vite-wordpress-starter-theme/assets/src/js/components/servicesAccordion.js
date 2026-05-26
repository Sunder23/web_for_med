import { logDebug } from '../utils/logDebug.js';

export function initServicesAccordion() {
	const $ = window.jQuery;

	if (!$) {
		console.warn('[web_for_med] Services accordion skipped: jQuery is not available');
		return;
	}

	const $section = $('.s-services').first();
	const $list = $section.find('.services-list').first();
	const $items = $list.find('.services-list__item');
	const $images = $section.find('.services-img__item[data-service-image]');

	if (!$list.length || !$items.length) {
		logDebug('Services accordion skipped: list not found');
		return;
	}

	const setActiveItem = ($activeItem, animate = true) => {
		const activeTarget = String($activeItem.data('service-target') ?? '');
		const isAlreadyActive = $activeItem.hasClass('services-list__item--active');

		$items.each((_, item) => {
			const $item = $(item);
			const isActive = item === $activeItem[0];
			const $desc = $item.find('.services-list__desc').first();

			$item.attr('aria-expanded', String(isActive));
			$item.toggleClass('services-list__item--active', isActive);

			if ($desc.length) {
				$desc.attr('aria-hidden', String(!isActive));
				$desc.stop(true, true);
				$desc[animate ? (isActive ? 'slideDown' : 'slideUp') : isActive ? 'show' : 'hide'](250);
			}
		});

		if (isAlreadyActive) {
			return;
		}

		$images.each((_, image) => {
			const $image = $(image);
			const isActive = String($image.data('service-image') ?? '') === activeTarget;
			$image.stop(true, true);

			if (!animate) {
				$image.toggleClass('services-img__item--active', isActive);
				$image[isActive ? 'show' : 'hide']();
				return;
			}

			if (isActive) {
				$image
					.addClass('services-img__item--active')
					.hide()
					.fadeIn(500);
				return;
			}

			$image.fadeOut(500, () => {
				$image.removeClass('services-img__item--active');
			});
		});
	};

	const $initialActiveItem = $items.filter('.services-list__item--active').first().length
		? $items.filter('.services-list__item--active').first()
		: $items.first();

	$items.each((_, item) => {
		const $item = $(item);

		$item.attr({
			role: 'button',
			tabindex: '0',
		});

		$item.on('click', () => {
			setActiveItem($item);
		});

		$item.on('keydown', (event) => {
			if (event.key !== 'Enter' && event.key !== ' ') {
				return;
			}

			event.preventDefault();
			setActiveItem($item);
		});
	});

	setActiveItem($initialActiveItem, false);
	logDebug('Services accordion initialized', { items: $items.length });
}
