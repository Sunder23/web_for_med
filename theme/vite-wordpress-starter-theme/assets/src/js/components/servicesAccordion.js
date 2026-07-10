import { logDebug } from '../utils/logDebug.js';

export function initServicesAccordion() {
	const $ = window.jQuery;

	if (!$) {
		console.warn('[web_for_med] Services accordion skipped: jQuery is not available');
		return;
	}

	const $list = $('.s-services').first().find('.services-list').first();
	const $items = $list.find('.services-list__item');

	if (!$list.length || !$items.length) {
		logDebug('Services accordion skipped: list not found');
		return;
	}

	const setActiveItem = ($activeItem, animate = true) => {
		$items.each((_, item) => {
			const $item = $(item);
			const isActive = item === $activeItem[0];
			const $desc = $item.find('.services-list__desc').first();

			$item.attr('aria-expanded', String(isActive));
			$item.toggleClass('services-list__item--active', isActive);

			if ($desc.length) {
				$desc.attr('aria-hidden', String(!isActive));
				$desc.stop(true, true);

				if (animate) {
					$desc[isActive ? 'slideDown' : 'slideUp'](250);
				} else {
					// [FIX] instant, non-animated initial state — an animated
					// collapse here shifts page height ~250ms after DOMContentLoaded,
					// which is after AOS has already cached scroll-trigger offsets
					// for elements below (e.g. .s-process .section-title), making
					// their fade-in animations trigger far too late on mobile.
					$desc[isActive ? 'show' : 'hide']();
				}
			}
		});
	};

	const closeItem = ($item) => {
		$item.attr('aria-expanded', 'false');
		$item.removeClass('services-list__item--active');
		const $desc = $item.find('.services-list__desc').first();
		if ($desc.length) {
			$desc.attr('aria-hidden', 'true');
			$desc.stop(true, true).slideUp(250);
		}
	};

	const $glitchImage = $('.s-services').first().find('.glitch-image').first();
	let glitchTimeout = null;

	const triggerGlitch = () => {
		if (!$glitchImage.length) return;
		clearTimeout(glitchTimeout);
		$glitchImage.addClass('glitch-image--glitching');
		glitchTimeout = setTimeout(() => $glitchImage.removeClass('glitch-image--glitching'), 1800);
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
			const isMobile = window.matchMedia('(max-width: 767px)').matches;
			const isAlreadyActive = $item.hasClass('services-list__item--active');

			if (isMobile && isAlreadyActive) {
				closeItem($item);
			} else {
				setActiveItem($item);
				triggerGlitch();
			}
		});

		$item.on('keydown', (event) => {
			if (event.key !== 'Enter' && event.key !== ' ') {
				return;
			}

			event.preventDefault();
			setActiveItem($item);
			triggerGlitch();
		});
	});

	setActiveItem($initialActiveItem, false);
	logDebug('Services accordion initialized', { items: $items.length });
}
