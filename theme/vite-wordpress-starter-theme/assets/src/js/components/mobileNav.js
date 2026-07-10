import { logDebug } from '@js/utils/logDebug.js';

export function initMobileNav() {
	const burger = document.getElementById('burger');
	const nav = document.getElementById('mainNav');

	if (!burger || !nav) {
		return;
	}

	const closeSubmenus = () => {
		nav.querySelectorAll('.menu-item-has-children.is-sub-open').forEach((item) => {
			item.classList.remove('is-sub-open');
			item.querySelector('.sub-toggle')?.setAttribute('aria-expanded', 'false');
		});
	};

	const openNav = () => {
		nav.classList.add('is-open');
		nav.setAttribute('aria-hidden', 'false');
		burger.classList.add('is-open');
		burger.setAttribute('aria-expanded', 'true');
		document.body.style.overflow = 'hidden';
	};

	const closeNav = () => {
		nav.classList.remove('is-open');
		nav.setAttribute('aria-hidden', 'true');
		burger.classList.remove('is-open');
		burger.setAttribute('aria-expanded', 'false');
		document.body.style.overflow = '';
		closeSubmenus();
	};

	burger.addEventListener('click', () => {
		nav.classList.contains('is-open') ? closeNav() : openNav();
	});

	nav.querySelectorAll('.nav__link, .menu-item a').forEach((link) => {
		link.addEventListener('click', closeNav);
	});

	initSubmenuToggles(nav);
}

// Parent items link to real pages, so navigation stays on the <a>
// and expand/collapse lives on a separate injected button.
function initSubmenuToggles(nav) {
	const parents = nav.querySelectorAll('.menu-item-has-children');
	logDebug('[FIX] mobile submenu toggles init', { count: parents.length });

	parents.forEach((item) => {
		const link = item.querySelector(':scope > a');
		const label = link?.textContent.trim() ?? '';

		const toggle = document.createElement('button');
		toggle.type = 'button';
		toggle.className = 'sub-toggle';
		toggle.setAttribute('aria-expanded', 'false');
		toggle.setAttribute('aria-label', `Показати підменю: ${label}`);

		if (link) {
			link.after(toggle);
		} else {
			item.prepend(toggle);
		}

		toggle.addEventListener('click', () => {
			const isOpen = item.classList.toggle('is-sub-open');
			toggle.setAttribute('aria-expanded', String(isOpen));
			logDebug('[FIX] mobile submenu toggled', { label, isOpen });
		});
	});
}
