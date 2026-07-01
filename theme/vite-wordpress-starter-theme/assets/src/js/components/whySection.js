import { logDebug } from '../utils/logDebug.js';

const MESSAGE_REVEAL_DELAY = 400;

export function initWhySection() {
	const section = document.querySelector('.s-why');

	if (!section) {
		logDebug('Why section skipped: section not found');
		return;
	}

	const triggers = Array.from(section.querySelectorAll('.why-reasons__trigger[data-reasons-target]'));
	const screens = Array.from(section.querySelectorAll('.why-chat__screen[data-chat-screen]'));

	if (!triggers.length || !screens.length) {
		logDebug('Why section skipped: triggers or screens missing', {
			triggers: triggers.length,
			screens: screens.length,
		});
		return;
	}

	const resetScreenMessages = (screen) => {
		const messages = Array.from(screen.querySelectorAll('[data-why-message]'));

		for (const message of messages) {
			message.classList.remove('why-msg--visible');
			message.style.transitionDelay = '0ms';
		}

		return messages;
	};

	const revealScreenMessages = (screen) => {
		const messages = resetScreenMessages(screen);

		requestAnimationFrame(() => {
			screen.classList.add('why-chat__screen--entered');

			messages.forEach((message, index) => {
				message.style.transitionDelay = `${(index + 1.5) * MESSAGE_REVEAL_DELAY}ms`;
				message.classList.add('why-msg--visible');
			});
		});
	};

	const setActiveScreen = (target, { animate = true } = {}) => {
		const nextTarget = String(target);
		const activeTrigger = section.querySelector('.why-reasons__trigger[aria-expanded="true"]');
		const currentTarget = activeTrigger?.dataset.reasonsTarget ?? null;

		if (currentTarget === nextTarget && animate) {
			logDebug('Why section switch skipped: target already active', { target: nextTarget });
			return;
		}

		for (const trigger of triggers) {
			const isActive = trigger.dataset.reasonsTarget === nextTarget;
			const item = trigger.closest('.why-reasons__item');

			trigger.setAttribute('aria-expanded', String(isActive));
			trigger.classList.toggle('why-reasons__trigger--active', isActive);
			item?.classList.toggle('why-reasons__item--active', isActive);
			item?.classList.toggle('why-reasons__item--hl', isActive);
		}

		for (const screen of screens) {
			const isActive = screen.dataset.chatScreen === nextTarget;

			screen.classList.toggle('why-chat__screen--active', isActive);
			screen.classList.remove('why-chat__screen--entered');
			screen.setAttribute('aria-hidden', String(!isActive));

			const messages = resetScreenMessages(screen);

			if (!isActive && !animate) {
				for (const message of messages) {
					message.classList.add('why-msg--visible');
				}
			}
		}

		const nextScreen = screens.find((screen) => screen.dataset.chatScreen === nextTarget);

		if (!nextScreen) {
			logDebug('Why section switch failed: target screen not found', { target: nextTarget });
			return;
		}

		if (!animate) {
			nextScreen.classList.add('why-chat__screen--entered');
			for (const message of nextScreen.querySelectorAll('[data-why-message]')) {
				message.classList.add('why-msg--visible');
			}

			logDebug('Why section initialized with default screen', { target: nextTarget });
			return;
		}

		revealScreenMessages(nextScreen);
		logDebug('Why section switched screen', {
			from: currentTarget,
			to: nextTarget,
			messages: nextScreen.querySelectorAll('[data-why-message]').length,
		});
	};

	for (const trigger of triggers) {
		trigger.addEventListener('click', () => {
			setActiveScreen(trigger.dataset.reasonsTarget);
		});
	}

	const initialTarget = triggers.find((trigger) => trigger.getAttribute('aria-expanded') === 'true')?.dataset.reasonsTarget
		?? triggers[0]?.dataset.reasonsTarget
		?? '0';

	setActiveScreen(initialTarget, { animate: false });
	logDebug('Why section component ready', {
		triggers: triggers.length,
		screens: screens.length,
	});
}
