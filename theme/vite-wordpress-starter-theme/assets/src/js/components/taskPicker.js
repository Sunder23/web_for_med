import { logDebug } from '@js/utils/logDebug.js';

// "What is your task" picker of the services page
// (template-parts/blocks/section-capabilities.php).
//
// Chips are toggles (`aria-pressed`). The submit link opens the contact drawer
// (contactDrawer.js handles `[data-contact-open]`); before that, the selected
// tasks are written into the first line of the CF7 message as
// `Завдання: a; b`. Text the visitor has typed stays below it; a repeated
// click replaces only that line, and with nothing selected the line is removed.
//
// The click is caught on window in the capture phase: contactDrawer.js stops
// the event on document (capture), so a listener on the link itself would
// never run.

const MESSAGE_SELECTOR = 'textarea[name="your-message"]';

function writeTasks(picker, prefix) {
	const labels = [...picker.querySelectorAll('[data-task-chip]')]
		.filter((chip) => chip.getAttribute('aria-pressed') === 'true')
		.map((chip) => chip.textContent.trim());

	const field =
		document.querySelector(`.contact-drawer ${MESSAGE_SELECTOR}`) ??
		document.querySelector(MESSAGE_SELECTOR);
	if (!field) {
		logDebug('[task-picker] message field not found', { labels });
		return;
	}

	const lines = field.value.split('\n');
	if (lines[0].startsWith(prefix)) lines.shift();
	if (labels.length) lines.unshift(`${prefix} ${labels.join('; ')}`);

	field.value = lines.join('\n').replace(/\n+$/, '');
	field.dispatchEvent(new Event('input', { bubbles: true }));
	logDebug('[task-picker] message updated', { labels });
}

export function initTaskPicker(selector = '[data-task-picker]') {
	const pickers = [...document.querySelectorAll(selector)];
	if (!pickers.length) {
		logDebug('[task-picker] no pickers', { selector });
		return;
	}

	for (const picker of pickers) {
		picker.addEventListener('click', (e) => {
			const chip = e.target.closest('[data-task-chip]');
			if (!chip) return;
			const pressed = chip.getAttribute('aria-pressed') !== 'true';
			chip.setAttribute('aria-pressed', pressed ? 'true' : 'false');
			logDebug('[task-picker] toggle', {
				label: chip.textContent.trim(),
				pressed,
			});
		});
	}

	window.addEventListener(
		'click',
		(e) => {
			const submit = e.target.closest?.('[data-task-picker-submit]');
			const picker = submit?.closest(selector);
			if (!picker) return;
			writeTasks(picker, picker.dataset.taskPrefix || 'Завдання:');
		},
		true,
	);

	logDebug('[task-picker] init', { count: pickers.length });
}
