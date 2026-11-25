import { logDebug } from '@js/utils/logDebug.js';

/*
 * Text reel of the cases archive (titles, descriptions): a window one item
 * high over a track with every item; the track slides to the active item in
 * step with the covers (casesArchive.js), the window takes its height.
 * Geometry goes to CSS as --reel-y / --reel-h (_cases-archive.scss).
 *
 * Loop edge: with more than one item the track gets two clones — the last
 * item before the first, the first after the last. Going 3 → 1 (dir > 0) the
 * track slides one step on to the clone of 1, 1 → 3 (dir < 0) one step back
 * to the clone of 3, so the text moves the same way as the covers; then it
 * jumps to the real item without a transition (`is-instant` on the reel).
 * A multi-step jump across the edge (a dot, 4+ cases) slides straight there.
 *
 * Markup: `[data-cases-reel]` > `[data-cases-reel-track]` >
 * `[data-cases-reel-item]` × count.
 */

// The jump waits for transitionend; this covers a lost event.
const JUMP_SLACK_MS = 50;

export function createReel(reel, { count, speed, name }) {
	const track = reel.querySelector('[data-cases-reel-track]');
	const items = track
		? [...track.querySelectorAll('[data-cases-reel-item]')]
		: [];

	if (!track || items.length !== count || count < 1) {
		logDebug('[casesReel] skipped', { name, count, items: items.length });
		return null;
	}

	let ms = speed;
	let index = 0;
	let target = items[0];
	// A slide to a clone waiting for its jump back: { to, timer }.
	let pending = null;

	const makeClone = (item) => {
		const clone = item.cloneNode(true);
		clone.removeAttribute('data-cases-reel-item');
		clone.setAttribute('data-reel-clone', '');
		clone.setAttribute('aria-hidden', 'true');
		clone.classList.remove('is-active');
		return clone;
	};

	const headClone = count > 1 ? makeClone(items[count - 1]) : null;
	const tailClone = count > 1 ? makeClone(items[0]) : null;
	if (headClone) track.prepend(headClone);
	if (tailClone) track.append(tailClone);

	// The track is `position: relative`, so offsetTop is measured inside it
	// and ignores its own transform.
	const place = (el) => {
		reel.classList.add('is-ready');
		reel.style.setProperty('--reel-y', `${-el.offsetTop}px`);
		reel.style.setProperty('--reel-h', `${el.offsetHeight}px`);
	};

	const setActive = (el) => {
		items.forEach((item) => {
			item.toggleAttribute('aria-hidden', item !== el);
		});
		[...track.children].forEach((child) => {
			child.classList.toggle('is-active', child === el);
		});
	};

	const instant = (fn) => {
		reel.classList.add('is-instant');
		fn();
		void reel.offsetHeight; // apply without the transition
		reel.classList.remove('is-instant');
	};

	const show = (el) => {
		target = el;
		setActive(el);
		place(el);
	};

	const onTrackEnd = (e) => {
		if (e.target === track && e.propertyName === 'transform') {
			finishJump('transitionend');
		}
	};

	// From the clone to the real item, at once (also before a new step).
	function finishJump(reason) {
		if (!pending) return;

		const { to } = pending;
		window.clearTimeout(pending.timer);
		track.removeEventListener('transitionend', onTrackEnd);
		pending = null;
		instant(() => show(items[to]));
		logDebug('[casesReel] jump', { name, to, reason });
	}

	const go = (to, dir = 0) => {
		finishJump('interrupted');

		const from = index;
		index = to;

		if (ms === 0 || dir === 0) {
			instant(() => show(items[to]));
			return;
		}
		if (from === to && target === items[to]) return;

		let el = items[to];
		if (tailClone && dir > 0 && from === count - 1 && to === 0) {
			el = tailClone;
		} else if (headClone && dir < 0 && from === 0 && to === count - 1) {
			el = headClone;
		}
		const viaClone = el !== items[to];

		show(el);
		logDebug('[casesReel] go', { name, from, to, dir, viaClone });

		if (viaClone) {
			pending = {
				to,
				timer: window.setTimeout(
					() => finishJump('timeout'),
					ms + JUMP_SLACK_MS,
				),
			};
			track.addEventListener('transitionend', onTrackEnd);
		}
	};

	// Re-measure without animating: only the geometry changed.
	const resync = (reason) => {
		instant(() => place(target));
		logDebug('[casesReel] resync', {
			name,
			reason,
			width: track.offsetWidth,
		});
	};

	// Wraps follow the column width and the clamp() font size.
	let width = track.offsetWidth;
	const observer = new ResizeObserver(() => {
		if (track.offsetWidth === width) return;
		width = track.offsetWidth;
		resync('resize');
	});
	observer.observe(track);
	document.fonts?.ready.then(() => resync('fonts'));

	const destroy = () => {
		finishJump('destroy');
		observer.disconnect();
		headClone?.remove();
		tailClone?.remove();
	};

	logDebug('[casesReel] init', {
		name,
		count,
		clones: headClone ? 2 : 0,
	});

	return {
		go,
		resync,
		setSpeed: (next) => {
			ms = next;
		},
		destroy,
	};
}
