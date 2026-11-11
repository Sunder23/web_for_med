import { logDebug } from '@js/utils/logDebug.js';

// Contact form behaviour for the CF7 template in scripts/templates/cf7-contact-form.html
// (Figma 3155:2785): per-channel contact field, phone mask, client-side validation
// and the submit button label for loading / retry.

const DOMAIN_TYPOS = new Set([
	'gmal.com',
	'gmial.com',
	'gmali.com',
	'gmail.co',
	'gmail.cm',
	'gamil.com',
	'yaho.com',
	'yahooo.com',
	'outlok.com',
	'outllok.com',
	'hotmial.com',
	'hotmali.com',
	'uakr.net',
]);

const TEMP_DOMAINS = new Set([
	'mailinator.com',
	'tempmail.com',
	'throwam.com',
	'guerrillamail.com',
	'yopmail.com',
	'trashmail.com',
	'10minutemail.com',
	'fakeinbox.com',
	'maildrop.cc',
	'sharklasers.com',
	'spam4.me',
	'trashmail.me',
]);

const KBD_PATTERNS = [
	'qwert',
	'asdfg',
	'zxcvb',
	'йцуке',
	'фывап',
	'ячсми',
	'qazws',
	'wsxed',
];

const BUTTON_LABELS = {
	idle: 'Відправити',
	loading: 'Відправляємо…',
	retry: 'Спробувати ще раз',
};

function looksRandom(s) {
	const c = s.toLowerCase().replace(/['’\-\s]/g, '');
	for (const p of KBD_PATTERNS) {
		if (c.includes(p)) return true;
	}
	if (/(.)\1{3,}/.test(c)) return true;
	if (/[bcdfghjklmnpqrstvwxyzбвгджзклмнпрстфхцчшщ]{5,}/i.test(c)) return true;
	return false;
}

function validateName(v) {
	if (!v.trim()) return 'Вкажіть ваше ім’я.';
	if (v.includes('@') || /https?:\/\//.test(v))
		return "У цьому полі потрібно вказати саме ім'я";
	if (/\s{2,}/.test(v)) return 'Приберіть зайві пробіли в імені';
	if (v.trim().length === 1) return "Ім'я виглядає занадто коротким";
	if (/\d/.test(v)) return "Ім'я не повинно містити цифр";
	if (/[^a-zA-ZА-ЯҐЄІЇа-яґєії'’\-\s]/.test(v))
		return 'Використовуйте лише літери, дефіс або апостроф';
	if (v.trim().length > 50) return "Ім'я занадто довге, перевірте введення";
	if (looksRandom(v.trim())) return "Перевірте, чи правильно вказане ім'я";
	return null;
}

function validateEmail(v) {
	if (!v) return 'Вкажіть email-адресу';
	if (/\s/.test(v)) return 'Email не повинен містити пробілів';
	if (/[а-яА-ЯґҐєЄіІїЇ]/.test(v))
		return 'Використовуйте латинські літери без зайвих символів';
	if (!v.includes('@')) return 'Email має містити символ @';
	if (/[.,;:!?]$/.test(v)) return 'Приберіть зайвий символ у кінці email';
	const domain = v.slice(v.indexOf('@') + 1);
	if (!domain || !domain.includes('.')) return 'Вкажіть коректну email-адресу.';
	if (DOMAIN_TYPOS.has(domain.toLowerCase()))
		return 'Перевірте написання домену email';
	if (TEMP_DOMAINS.has(domain.toLowerCase()))
		return 'Перевірте, чи правильно вказаний ваш email';
	if (!/^[^\s@]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test(v))
		return 'Вкажіть коректну email-адресу.';
	return null;
}

function phoneDigits(v) {
	return v.replace(/\D/g, '');
}

function validatePhone(emptyMessage) {
	return (v) => {
		const digits = phoneDigits(v);
		if (!digits) return emptyMessage;
		const valid = digits.startsWith('380')
			? digits.length === 12
			: digits.length >= 10 && digits.length <= 15;
		return valid ? null : 'Вкажіть коректний номер із кодом країни.';
	};
}

function validateTelegram(v) {
	if (!v || v === '@') return 'Вкажіть ім’я користувача Telegram';
	if (!/^@[A-Za-z0-9_]{5,32}$/.test(v))
		return 'Вкажіть @ та щонайменше 5 символів.';
	return null;
}

/**
 * Formats a phone as `+380 (67) 123 45 67`; a leading 0 is read as a Ukrainian
 * number. Other country codes are kept as `+<digits>` (max 15 digits, E.164).
 */
function formatPhone(value) {
	let digits = phoneDigits(value);
	if (!digits) return '';
	if (digits.startsWith('0')) digits = `38${digits}`;
	if (!digits.startsWith('380')) return `+${digits.slice(0, 15)}`;

	const d = digits.slice(3, 12);
	let out = '+380';
	if (d.length) out += ` (${d.slice(0, 2)}`;
	if (d.length >= 2) out += ')';
	if (d.length > 2) out += ` ${d.slice(2, 5)}`;
	if (d.length > 5) out += ` ${d.slice(5, 7)}`;
	if (d.length > 7) out += ` ${d.slice(7, 9)}`;
	return out;
}

function formatTelegram(value) {
	const v = value.replace(/\s/g, '');
	if (!v) return '';
	return v.startsWith('@') ? v : `@${v}`;
}

// Figma 3155:2808 — one channel, one contact field.
const CHANNELS = {
	Телефон: {
		label: 'Номер телефону',
		placeholder: '+380 (__) ___ __ __',
		hint: 'Міжнародний формат, наприклад +380 67 123 45 67.',
		type: 'tel',
		inputmode: 'tel',
		autocomplete: 'tel',
		format: formatPhone,
		validate: validatePhone('Вкажіть номер телефону'),
	},
	Email: {
		label: 'Email',
		placeholder: 'name@example.com',
		hint: 'Формат адреси: name@example.com.',
		type: 'email',
		inputmode: 'email',
		autocomplete: 'email',
		format: null,
		validate: validateEmail,
	},
	Telegram: {
		label: 'Ім’я користувача Telegram',
		placeholder: '@username',
		hint: 'Від 5 до 32 латинських літер, цифр або _ після @.',
		type: 'text',
		inputmode: 'text',
		autocomplete: 'username',
		format: formatTelegram,
		validate: validateTelegram,
	},
	WhatsApp: {
		label: 'Номер WhatsApp',
		placeholder: '+380 (__) ___ __ __',
		hint: 'Номер із кодом країни, зареєстрований у WhatsApp.',
		type: 'tel',
		inputmode: 'tel',
		autocomplete: 'tel',
		format: formatPhone,
		validate: validatePhone('Вкажіть номер WhatsApp'),
	},
};

function getErrorEl(field) {
	let errorEl = field.querySelector('.form-field__error');
	if (!errorEl) {
		errorEl = document.createElement('span');
		errorEl.className = 'form-field__error';
		errorEl.hidden = true;
		field.appendChild(errorEl);
	}
	return errorEl;
}

function showError(input, msg) {
	const field = input.closest('.form-field');
	if (!field) return;
	const errorEl = getErrorEl(field);
	if (!errorEl.id) errorEl.id = `${input.id || input.name}-error`;
	field.classList.add('is-error');
	input.setAttribute('aria-invalid', 'true');
	input.setAttribute('aria-describedby', errorEl.id);
	errorEl.textContent = msg;
	errorEl.hidden = false;
}

function clearError(input) {
	const field = input.closest('.form-field');
	if (!field) return;
	const errorEl = getErrorEl(field);
	field.classList.remove('is-error');
	input.removeAttribute('aria-invalid');
	input.removeAttribute('aria-describedby');
	errorEl.textContent = '';
	errorEl.hidden = true;
}

/**
 * Sets the submit button label: `idle`, `loading` or `retry`.
 *
 * @param {HTMLFormElement} form CF7 form.
 * @param {'idle'|'loading'|'retry'} state Button state.
 */
export function setSubmitState(form, state) {
	const button = form.querySelector('.contact-form__button');
	const label = button?.querySelector('.contact-form__button-label');
	if (!button || !label) return;
	label.textContent = BUTTON_LABELS[state] ?? BUTTON_LABELS.idle;
	button.disabled = state === 'loading';
}

function initForm(form) {
	const nameInput = form.querySelector('[name="your-name"]');
	const contactInput = form.querySelector('[name="your-contact"]');
	const contactField = contactInput?.closest('.form-field');
	const contactLabel = contactField?.querySelector('.form-field__label');
	const contactHint = contactField?.querySelector('.form-field__hint');
	const methodInputs = form.querySelectorAll('[name="contact-method"]');
	const values = new Map();

	let channel = CHANNELS.Телефон;

	const applyChannel = (key, { restore = true } = {}) => {
		const next = CHANNELS[key];
		if (!next || !contactInput) return;

		values.set(channel, contactInput.value);
		channel = next;

		if (contactLabel) {
			contactLabel.innerHTML = `${next.label} <span class="form-field__req">*</span>`;
		}
		if (contactHint) contactHint.textContent = next.hint;
		contactInput.type = next.type;
		contactInput.placeholder = next.placeholder;
		contactInput.setAttribute('inputmode', next.inputmode);
		contactInput.setAttribute('autocomplete', next.autocomplete);
		contactInput.value = restore ? (values.get(next) ?? '') : '';
		clearError(contactInput);

		logDebug('[contactForm] method change', { method: key });
	};

	const validators = new Map([
		[nameInput, validateName],
		[contactInput, (v) => channel.validate(v)],
	]);

	const runValidation = (input) => {
		const validator = validators.get(input);
		if (!input || !validator) return true;
		const msg = validator(input.value.trim());
		if (msg) {
			showError(input, msg);
			return false;
		}
		clearError(input);
		return true;
	};

	methodInputs.forEach((radio) => {
		radio.addEventListener('change', () => {
			if (radio.checked) applyChannel(radio.value);
		});
	});

	const checked = form.querySelector('[name="contact-method"]:checked');
	if (checked && checked.value !== 'Телефон') {
		applyChannel(checked.value, { restore: false });
	}

	contactInput?.addEventListener('input', () => {
		if (channel.format) {
			const formatted = channel.format(contactInput.value);
			if (formatted !== contactInput.value) contactInput.value = formatted;
		}
	});

	validators.forEach((_, input) => {
		if (!input) return;
		input.addEventListener('input', () => {
			if (input.closest('.form-field')?.classList.contains('is-error'))
				clearError(input);
		});
		input.addEventListener('blur', () => {
			if (input.value.trim()) runValidation(input);
		});
	});

	form.addEventListener(
		'submit',
		(e) => {
			let valid = true;
			validators.forEach((_, input) => {
				if (input && !runValidation(input)) valid = false;
			});
			if (!valid) {
				e.preventDefault();
				e.stopImmediatePropagation();
				form.querySelector('.form-field.is-error .form-field__input')?.focus();
				logDebug('[contactForm] client validation failed');
			}
		},
		true,
	);

	const wrap = form.closest('.wpcf7');

	wrap?.addEventListener('wpcf7beforesubmit', () =>
		setSubmitState(form, 'loading'),
	);

	wrap?.addEventListener('wpcf7invalid', (e) => {
		setSubmitState(form, 'idle');
		const invalid = e.detail?.apiResponse?.invalid_fields ?? [];
		invalid.forEach(({ field: name }) => {
			const input = form.querySelector(`[name="${name}"]`);
			if (!input || !runValidation(input)) return;
			// Our validator passed but the server did not — show a generic message.
			showError(input, 'Перевірте правильність заповнення поля');
		});
	});

	wrap?.addEventListener('wpcf7mailsent', () => {
		setSubmitState(form, 'idle');
		values.clear();
		// CF7 resets the form; bring the contact field back to the default channel.
		window.setTimeout(() => {
			const current = form.querySelector('[name="contact-method"]:checked');
			applyChannel(current?.value ?? 'Телефон', { restore: false });
		});
	});

	['wpcf7mailfailed', 'wpcf7spam'].forEach((type) => {
		wrap?.addEventListener(type, () => setSubmitState(form, 'retry'));
	});

	logDebug('[contactForm] init', { channels: methodInputs.length });
}

export function initContactForm() {
	document.querySelectorAll('.wpcf7-form').forEach((form) => {
		if (form.querySelector('.contact-form')) initForm(form);
	});
}
