import { logDebug } from '@js/utils/logDebug.js';

// Label offset from the cursor tip (Figma 3241:4277).
const OFFSET_X = 12;
const OFFSET_Y = 26;
const EDGE_GAP = 8;
// Matches the `tablet` breakpoint in mixins/_breakpoint.scss.
const MEDIA = '(hover: hover) and (pointer: fine) and (min-width: 1024px)';

export function initTeamHoverLabels(root = document) {
	const members = [...root.querySelectorAll('[data-team-member]')].filter(
		(member) => member.querySelector('[data-team-label]'),
	);
	const query = window.matchMedia(MEDIA);

	logDebug('[teamHoverLabel] init', {
		count: members.length,
		enabled: query.matches,
	});

	if (!members.length) {
		return;
	}

	let active = null;
	let lastX = 0;
	let lastY = 0;
	let frame = 0;

	const place = () => {
		frame = 0;
		if (!active) {
			return;
		}
		const label = active.querySelector('[data-team-label]');
		let x = lastX + OFFSET_X;
		const overflow = x + label.offsetWidth + EDGE_GAP - window.innerWidth;
		if (overflow > 0) {
			x -= overflow;
		}
		label.style.setProperty('--x', `${x}px`);
		label.style.setProperty('--y', `${lastY + OFFSET_Y}px`);
	};

	const schedule = () => {
		if (!frame) {
			frame = window.requestAnimationFrame(place);
		}
	};

	const leave = () => {
		active?.classList.remove('is-hovered');
		active = null;
	};

	members.forEach((member) => {
		member.addEventListener('pointerenter', (event) => {
			if (!query.matches || event.pointerType !== 'mouse') {
				return;
			}
			active = member;
			lastX = event.clientX;
			lastY = event.clientY;
			place();
			member.classList.add('is-hovered');
		});
		member.addEventListener('pointermove', (event) => {
			if (active !== member) {
				return;
			}
			lastX = event.clientX;
			lastY = event.clientY;
			schedule();
		});
		member.addEventListener('pointerleave', leave);
	});

	// Scrolling moves the card away without a pointer event.
	window.addEventListener(
		'scroll',
		() => {
			if (!active) {
				return;
			}
			const under = document.elementFromPoint(lastX, lastY);
			if (!under || !active.contains(under)) {
				leave();
			}
		},
		{ passive: true },
	);

	query.addEventListener('change', (event) => {
		if (!event.matches) {
			logDebug('[teamHoverLabel] skip: no fine pointer');
			leave();
		}
	});
}
