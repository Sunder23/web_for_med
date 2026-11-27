import { logDebug } from '@js/utils/logDebug.js';

// Services page list (template-parts/blocks/section-service-list.php).
//
// Desktop (tablet and up): one service is always active — its image and
// description are shown in the preview column. Hover (real pointers only),
// keyboard focus or a click on a row makes it active.
// Phones: an accordion — a tap opens the row (description + image under it)
// and closes the others; a tap on the open row closes it.
//
// `is-active` is the desktop selection, `is-open` the phone accordion; when
// the layout switches, the state carries over (the open row becomes active
// and vice versa) and `aria-expanded` follows the current mode.

const DESKTOP_QUERY = '(min-width: 1024px)';
const HOVER_QUERY = '(hover: hover) and (pointer: fine)';

function initList(root) {
	const items = [...root.querySelectorAll('[data-service-item]')];
	const slides = [...root.querySelectorAll('[data-service-preview]')];
	if (!items.length) {
		logDebug('[service-list] no items', { root });
		return;
	}

	const desktop = window.matchMedia(DESKTOP_QUERY);
	const hover = window.matchMedia(HOVER_QUERY);
	const toggles = items.map((item) => item.querySelector('button'));

	let active = Math.max(
		0,
		items.findIndex((item) => item.classList.contains('is-active')),
	);
	let open = items.findIndex((item) => item.classList.contains('is-open'));

	const syncAria = () => {
		toggles.forEach((toggle, index) => {
			const expanded = desktop.matches ? index === active : index === open;
			toggle?.setAttribute('aria-expanded', expanded ? 'true' : 'false');
		});
	};

	const setActive = (index, source) => {
		if (index === active) return;
		active = index;
		items.forEach((item, i) => {
			item.classList.toggle('is-active', i === index);
		});
		slides.forEach((slide, i) => {
			slide.classList.toggle('is-active', i === index);
		});
		syncAria();
		logDebug('[service-list] active', { index, mode: 'desktop', source });
	};

	const setOpen = (index) => {
		open = index;
		items.forEach((item, i) => {
			item.classList.toggle('is-open', i === index);
		});
		syncAria();
		logDebug('[service-list] active', { index, mode: 'mobile' });
	};

	toggles.forEach((toggle, index) => {
		if (!toggle) return;

		toggle.addEventListener('click', () => {
			if (desktop.matches) {
				setActive(index, 'click');
				return;
			}
			setOpen(open === index ? -1 : index);
		});

		toggle.addEventListener('pointerenter', () => {
			if (desktop.matches && hover.matches) setActive(index, 'hover');
		});

		toggle.addEventListener('focus', () => {
			if (desktop.matches) setActive(index, 'focus');
		});
	});

	desktop.addEventListener('change', () => {
		if (desktop.matches) {
			if (open >= 0) setActive(open, 'layout');
		} else {
			setOpen(active);
		}
		syncAria();
		logDebug('[service-list] layout change', {
			desktop: desktop.matches,
			active,
			open,
		});
	});

	syncAria();
	logDebug('[service-list] init', {
		count: items.length,
		active,
		open,
		desktop: desktop.matches,
	});
}

export function initServiceList(selector = '[data-service-list]') {
	const lists = document.querySelectorAll(selector);
	if (!lists.length) {
		logDebug('[service-list] no lists', { selector });
		return;
	}
	lists.forEach(initList);
}
