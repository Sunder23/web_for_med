const CLEAR_ICON = `<svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
  <circle cx="9" cy="9" r="8" stroke="currentColor" stroke-width="1.5"/>
  <path d="M11.5 6.5L6.5 11.5M6.5 6.5L11.5 11.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
</svg>`;

const RESERVED_NAMES = new Set([
    'test', 'admin', 'user', 'administrator', 'moderator', 'guest',
    'name', 'username', 'null', 'none', 'нікнейм', 'ім\'я',
]);

const DOMAIN_TYPOS = new Set([
    'gmal.com', 'gmial.com', 'gmali.com', 'gmail.co', 'gmail.cm', 'gamil.com',
    'yaho.com', 'yahooo.com', 'outlok.com', 'outllok.com',
    'hotmial.com', 'hotmali.com', 'uakr.net',
]);

const TEMP_DOMAINS = new Set([
    'mailinator.com', 'tempmail.com', 'throwam.com', 'guerrillamail.com',
    'yopmail.com', 'trashmail.com', '10minutemail.com', 'fakeinbox.com',
    'maildrop.cc', 'sharklasers.com', 'spam4.me', 'trashmail.me',
]);

const KBD_PATTERNS = ['qwert', 'asdfg', 'zxcvb', 'йцуке', 'фывап', 'ячсми', 'qazws', 'wsxed'];

function looksRandom(s) {
    const c = s.toLowerCase().replace(/['\-\s]/g, '');
    for (const p of KBD_PATTERNS) {
        if (c.includes(p)) return true;
    }
    if (/(.)\1{3,}/.test(c)) return true;
    if (/[bcdfghjklmnpqrstvwxyzбвгджзклмнпрстфхцчшщ]{5,}/i.test(c)) return true;
    return false;
}

function validateName(v) {
    if (v.includes('@') || /https?:\/\//.test(v)) return "У цьому полі потрібно вказати саме ім'я";
    if (!v) return "Вкажіть, будь ласка, ваше ім'я";
    if (!v.trim()) return 'Поле не може складатися лише з пробілів';
    if (/\s{2,}/.test(v)) return 'Приберіть зайві пробіли в імені';
    if (v.trim().length === 1) return "Ім'я виглядає занадто коротким";
    if (/\d/.test(v)) return "Ім'я не повинно містити цифр";
    if (/[^a-zA-ZА-ЯҐЄІЇа-яґєії'\-\s]/.test(v)) return 'Використовуйте лише літери, дефіс або апостроф';
    if (v.trim().length > 50) return "Ім'я занадто довге, перевірте введення";
    if (looksRandom(v.trim())) return "Перевірте, чи правильно вказане ім'я";
    return null;
}

function validateEmail(v) {
    if (!v) return 'Вкажіть, будь ласка, email';
    if (/\s/.test(v)) return 'Email не повинен містити пробілів';
    if (/[а-яА-ЯґҐєЄіІїЇ]/.test(v)) return 'Використовуйте латинські літери без зайвих символів';
    if (!v.includes('@')) return 'Email має містити символ @';
    if (/[.,;:!?]$/.test(v)) return 'Приберіть зайвий символ у кінці email';
    const domain = v.slice(v.indexOf('@') + 1);
    if (!domain) return 'Після @ потрібно вказати домен';
    if (!domain.includes('.')) return 'Перевірте домен email, наприклад gmail.com';
    if (DOMAIN_TYPOS.has(domain.toLowerCase())) return 'Перевірте написання домену email';
    if (TEMP_DOMAINS.has(domain.toLowerCase())) return 'Перевірте, чи правильно вказаний ваш email';
    if (!/^[^\s@]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test(v)) return 'Вкажіть email у форматі name@example.com';
    return null;
}

function showError(field, input, errorEl, msg) {
    field.classList.add('is-error');
    input.setAttribute('aria-invalid', 'true');
    errorEl.textContent = msg;
    errorEl.hidden = false;
}

function clearError(field, input, errorEl) {
    field.classList.remove('is-error');
    input.removeAttribute('aria-invalid');
    errorEl.textContent = '';
    errorEl.hidden = true;
}

export function initContactForm() {
    const cfWrap = document.querySelector('.wpcf7');
    const form = cfWrap?.querySelector('.wpcf7-form');

    document.querySelectorAll('.form-field').forEach(field => {
        const input = field.querySelector('.form-field__input');
        if (!input) return;

        if (input.tagName.toLowerCase() === 'textarea') {
            field.classList.add('form-field--textarea');
        }

        const clearBtn = document.createElement('button');
        clearBtn.type = 'button';
        clearBtn.className = 'form-field__clear';
        clearBtn.setAttribute('aria-label', 'Очистити поле');
        clearBtn.innerHTML = CLEAR_ICON;
        field.appendChild(clearBtn);

        const errorEl = document.createElement('span');
        errorEl.className = 'form-field__error';
        errorEl.setAttribute('role', 'alert');
        errorEl.hidden = true;
        field.appendChild(errorEl);

        const update = () => field.classList.toggle('is-filled', input.value.trim() !== '');

        input.addEventListener('focus', () => field.classList.add('is-focused'));
        input.addEventListener('blur', () => field.classList.remove('is-focused'));
        input.addEventListener('input', () => {
            update();
            if (field.classList.contains('is-error')) clearError(field, input, errorEl);
        });

        clearBtn.addEventListener('click', () => {
            input.value = '';
            field.classList.remove('is-filled');
            clearError(field, input, errorEl);
            input.dispatchEvent(new Event('input', { bubbles: true }));
            input.focus();
        });

        update();
    });

    if (!form) return;

    const nameInput = form.querySelector('[name="your-name"]');
    const emailInput = form.querySelector('[name="your-email"]');

    const validators = new Map([
        [nameInput, validateName],
        [emailInput, validateEmail],
    ]);

    function runValidation(input) {
        const validator = validators.get(input);
        if (!validator) return true;
        const field = input.closest('.form-field');
        const errorEl = field?.querySelector('.form-field__error');
        if (!field || !errorEl) return true;
        const msg = validator(input.value);
        if (msg) { showError(field, input, errorEl, msg); return false; }
        clearError(field, input, errorEl);
        return true;
    }

    form.addEventListener('submit', (e) => {
        let valid = true;
        validators.forEach((_, input) => { if (!runValidation(input)) valid = false; });
        if (!valid) {
            e.preventDefault();
            e.stopImmediatePropagation();
            form.querySelector('.form-field.is-error .form-field__input')?.focus();
        }
    }, true);

    cfWrap.addEventListener('wpcf7:invalid', (e) => {
        form.querySelectorAll('.wpcf7-not-valid-tip').forEach(el => el.remove());

        const invalid = e.detail?.apiResponse?.invalid_fields ?? [];
        invalid.forEach(({ field: name }) => {
            const input = form.querySelector(`[name="${name}"]`);
            if (!input) return;
            // Re-run our own validator; if it passes, show a generic fallback
            if (!runValidation(input)) return;
            const field = input.closest('.form-field');
            const errorEl = field?.querySelector('.form-field__error');
            if (field && errorEl) showError(field, input, errorEl, 'Перевірте правильність заповнення поля');
        });
    });
}
