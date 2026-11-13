import { logDebug } from '@js/utils/logDebug.js';

/*
 * Header auto-hide for full-screen layouts (the /cases/ slider).
 *
 * A short while after load the header slides up and `.site` drops its
 * padding-top (`body.is-header-collapsed`, styles live with the page), so the
 * page takes the whole screen. The header comes back over the content — the
 * layout does not move — when the pointer reaches the top edge, keyboard
 * focus enters it or the page is scrolled up; it hides again once the pointer
 * leaves. While active, `body.is-header-auto` makes smartHeader.js stand by.
 *
 * Mouse devices only: without a pointer there is no way to call it back.
 */

const POINTER_QUERY = '(hover: hover) and (pointer: fine)';
const REDUCED_MOTION_QUERY = '(prefers-reduced-motion: reduce)';

// Distance (px) from the header's top edge (below the admin bar) that reveals it.
const REVEAL_ZONE = 24;
// Pause before hiding again once the pointer has left.
const HIDE_DELAY = 600;
// Minimal page scroll delta (px), as in smartHeader.js.
const SCROLL_THRESHOLD = 8;
// Header slide is 0.3s (_header.scss); fallback if transitionend is lost.
const COLLAPSE_FALLBACK_MS = 500;

const noop = { destroy() {} };

/**
 * @param {{ delay?: number, onCollapse?: () => void }} [options]
 * @returns {{ destroy: () => void }}
 */
export function initHeaderAutoHide({ delay = 2000, onCollapse } = {}) {
	const header = document.getElementById('header');

	if (!header) {
		logDebug('[headerAutoHide] skipped: #header not found');
		return noop;
	}

	if (!window.matchMedia(POINTER_QUERY).matches) {
		logDebug('[headerAutoHide] skipped: no fine pointer');
		return noop;
	}

	const { body } = document;
	let collapsed = false;
	let pointerY = null;
	let lastY = window.scrollY;
	let collapseTimer = 0;
	let concealTimer = 0;
	let settleTimer = 0;
	let moveFrame = 0;
	let scrollFrame = 0;

	const isHidden = () => header.classList.contains('is-hidden');

	const zoneBottom = () =>
		(Number.parseFloat(getComputedStyle(header).top) || 0) + REVEAL_ZONE;

	const pointerInZone = () => pointerY !== null && pointerY <= zoneBottom();

	const isLocked = () =>
		document.querySelector('.nav.is-open') !== null ||
		body.classList.contains('is-drawer-open');

	// Reasons to keep the header on screen right now.
	const blocker = () => {
		if (isLocked()) return 'overlay';
		if (header.matches(':hover')) return 'hover';
		// Keyboard focus only: a mouse click (e.g. the form button, which gets
		// focus back when the drawer closes) must not pin the header.
		if (header.querySelector(':focus-visible')) return 'focus';
		if (pointerInZone()) return 'zone';
		return '';
	};

	const setHidden = (value, reason) => {
		if (value === isHidden()) return;
		header.classList.toggle('is-hidden', value);
		logDebug(`[headerAutoHide] ${value ? 'conceal' : 'reveal'}`, {
			reason,
			y: window.scrollY,
		});
	};

	// --- First hide: header up, padding off -----------------------------------

	const settle = () => {
		window.clearTimeout(settleTimer);
		header.removeEventListener('transitionend', onCollapseEnd);
		onCollapse?.();
		logDebug('[headerAutoHide] collapse settled');
	};

	function onCollapseEnd(e) {
		if (e.target === header && e.propertyName === 'transform') settle();
	}

	const collapse = () => {
		collapseTimer = 0;
		const blockedBy = blocker();

		if (blockedBy) {
			collapseTimer = window.setTimeout(collapse, HIDE_DELAY);
			logDebug('[headerAutoHide] deferred', { reason: blockedBy });
			return;
		}

		collapsed = true;
		body.classList.add('is-header-collapsed');
		setHidden(true, 'collapse');
		logDebug('[headerAutoHide] collapse', { y: window.scrollY });

		if (window.matchMedia(REDUCED_MOTION_QUERY).matches) {
			settle();
			return;
		}

		header.addEventListener('transitionend', onCollapseEnd);
		settleTimer = window.setTimeout(settle, COLLAPSE_FALLBACK_MS);
	};

	// --- Reveal / conceal after the collapse ---------------------------------

	const cancelConceal = () => {
		window.clearTimeout(concealTimer);
		concealTimer = 0;
	};

	const tryConceal = () => {
		concealTimer = 0;
		if (!collapsed || isHidden()) return;

		const blockedBy = blocker();
		if (blockedBy) {
			// Retry: e.g. the contact drawer closes later.
			concealTimer = window.setTimeout(tryConceal, HIDE_DELAY);
			return;
		}

		setHidden(true, 'pointer left');
	};

	const scheduleConceal = () => {
		if (!collapsed || isHidden() || concealTimer) return;
		concealTimer = window.setTimeout(tryConceal, HIDE_DELAY);
	};

	const reveal = (reason) => {
		cancelConceal();
		setHidden(false, reason);
	};

	const onPointer = () => {
		moveFrame = 0;
		if (!collapsed) return;

		if (pointerInZone()) {
			reveal('pointer at top');
		} else if (!header.matches(':hover')) {
			scheduleConceal();
		} else {
			cancelConceal();
		}
	};

	const onMouseMove = (e) => {
		pointerY = e.clientY;
		if (!moveFrame) moveFrame = window.requestAnimationFrame(onPointer);
	};

	const onMouseOut = (e) => {
		// Pointer left the window.
		if (e.relatedTarget) return;
		pointerY = null;
		scheduleConceal();
	};

	const onFocusIn = (e) => {
		if (collapsed && e.target.matches(':focus-visible')) reveal('focus');
	};

	const onFocusOut = () => scheduleConceal();

	const onScrollFrame = () => {
		scrollFrame = 0;
		const y = window.scrollY;
		const delta = y - lastY;

		if (!collapsed || Math.abs(delta) < SCROLL_THRESHOLD) return;
		lastY = y;

		if (delta < 0) {
			reveal('scroll up');
		} else if (!blocker()) {
			cancelConceal();
			setHidden(true, 'scroll down');
		}
	};

	const onScroll = () => {
		if (!scrollFrame) scrollFrame = window.requestAnimationFrame(onScrollFrame);
	};

	document.addEventListener('mousemove', onMouseMove, { passive: true });
	document.addEventListener('mouseout', onMouseOut);
	window.addEventListener('scroll', onScroll, { passive: true });
	header.addEventListener('focusin', onFocusIn);
	header.addEventListener('focusout', onFocusOut);

	body.classList.add('is-header-auto');
	collapseTimer = window.setTimeout(collapse, delay);
	logDebug('[headerAutoHide] init', { delay });

	return {
		destroy() {
			window.clearTimeout(collapseTimer);
			window.clearTimeout(settleTimer);
			cancelConceal();
			window.cancelAnimationFrame(moveFrame);
			window.cancelAnimationFrame(scrollFrame);

			document.removeEventListener('mousemove', onMouseMove);
			document.removeEventListener('mouseout', onMouseOut);
			window.removeEventListener('scroll', onScroll);
			header.removeEventListener('focusin', onFocusIn);
			header.removeEventListener('focusout', onFocusOut);
			header.removeEventListener('transitionend', onCollapseEnd);

			body.classList.remove('is-header-auto', 'is-header-collapsed');
			header.classList.remove('is-hidden');
			collapsed = false;
			logDebug('[headerAutoHide] destroy');
		},
	};
}
