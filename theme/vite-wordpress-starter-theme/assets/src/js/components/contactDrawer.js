import { logDebug } from '@js/utils/logDebug.js';

// Off-canvas contact drawer (partials/parts/contact-drawer.php).
// Opens on `[data-contact-open]` and `a[href$="#contact-form"]`; closes on the
// close buttons, the overlay and Esc. Traps focus, stops Lenis, and swaps the
// form for the success screen after CF7 `wpcf7mailsent`.

const TRIGGER_SELECTOR = '[data-contact-open], a[href$="#contact-form"]';
const FOCUSABLE =
	'a[href], button:not([disabled]), input:not([disabled]):not([type="hidden"]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])';

export function initContactDrawer() {
	const drawer = document.getElementById('contact-drawer');

	if (!drawer) {
		logDebug('[contactDrawer] skipped: #contact-drawer not found');
		return;
	}

	const panel = drawer.querySelector('.contact-drawer__panel');
	const body = drawer.querySelector('.contact-drawer__body');
	const alert = drawer.querySelector('.contact-drawer__alert');
	const formWrap = drawer.querySelector('.contact-drawer__form');
	const success = drawer.querySelector('.contact-drawer__success');
	const cf7 = drawer.querySelector('.wpcf7');

	let lastTrigger = null;

	const isOpen = () => drawer.classList.contains('is-open');

	const resetSuccess = () => {
		drawer.classList.remove('is-success');
		if (success) success.hidden = true;
		if (formWrap) formWrap.hidden = false;
	};

	const focusables = () =>
		[...panel.querySelectorAll(FOCUSABLE)].filter(
			(el) => el.offsetParent !== null || el === document.activeElement,
		);

	const open = (trigger = null) => {
		if (isOpen()) return;

		lastTrigger = trigger;
		resetSuccess();
		drawer.removeAttribute('inert');
		drawer.classList.add('is-open');
		document.body.classList.add('is-drawer-open');
		window.lenis?.stop();

		// Wait for the panel to become visible before moving focus.
		window.requestAnimationFrame(() => {
			const first = drawer.querySelector(
				'.contact-drawer__form .form-field__input',
			);
			(first ?? drawer.querySelector('.contact-drawer__close'))?.focus({
				preventScroll: true,
			});
		});

		logDebug('[contactDrawer] open', {
			trigger: trigger?.className || trigger?.getAttribute?.('href') || null,
		});
	};

	const close = () => {
		if (!isOpen()) return;

		drawer.classList.remove('is-open');
		drawer.setAttribute('inert', '');
		document.body.classList.remove('is-drawer-open');
		window.lenis?.start();

		if (drawer.classList.contains('is-success')) {
			// Next opening starts with a fresh form.
			window.setTimeout(resetSuccess, 400);
		}

		lastTrigger?.focus?.({ preventScroll: true });
		lastTrigger = null;

		logDebug('[contactDrawer] close');
	};

	// Capture phase: runs before Lenis' anchor handler on window.
	document.addEventListener(
		'click',
		(e) => {
			const trigger = e.target.closest(TRIGGER_SELECTOR);
			if (!trigger || drawer.contains(trigger)) return;

			e.preventDefault();
			e.stopPropagation();

			// The mobile menu overlay covers the page — close it first.
			if (document.querySelector('.nav.is-open')) {
				document.getElementById('burger')?.click();
			}

			open(trigger);
		},
		true,
	);

	drawer.addEventListener('click', (e) => {
		if (e.target.closest('[data-contact-close]')) close();
	});

	document.addEventListener('keydown', (e) => {
		if (!isOpen()) return;

		if (e.key === 'Escape') {
			e.preventDefault();
			close();
			return;
		}

		if (e.key !== 'Tab') return;

		const items = focusables();
		if (!items.length) return;

		const first = items[0];
		const last = items[items.length - 1];

		if (e.shiftKey && document.activeElement === first) {
			e.preventDefault();
			last.focus();
		} else if (!e.shiftKey && document.activeElement === last) {
			e.preventDefault();
			first.focus();
		} else if (!panel.contains(document.activeElement)) {
			e.preventDefault();
			first.focus();
		}
	});

	if (cf7) {
		cf7.addEventListener('wpcf7beforesubmit', () => {
			if (alert) alert.hidden = true;
		});

		cf7.addEventListener('wpcf7mailsent', () => {
			drawer.classList.add('is-success');
			if (formWrap) formWrap.hidden = true;
			if (success) success.hidden = false;
			body?.scrollTo(0, 0);
			success?.querySelector('button')?.focus({ preventScroll: true });
			logDebug('[contactDrawer] cf7 event', { type: 'wpcf7mailsent' });
		});

		['wpcf7mailfailed', 'wpcf7spam'].forEach((type) => {
			cf7.addEventListener(type, () => {
				if (alert) alert.hidden = false;
				body?.scrollTo(0, 0);
				logDebug('[contactDrawer] cf7 event', { type });
			});
		});

		cf7.addEventListener('wpcf7invalid', () => {
			logDebug('[contactDrawer] cf7 event', { type: 'wpcf7invalid' });
		});
	}

	// Direct link to …#contact-form opens the drawer on load.
	if (window.location.hash === '#contact-form') {
		open();
	}

	logDebug('[contactDrawer] init', { form: Boolean(cf7) });
}
