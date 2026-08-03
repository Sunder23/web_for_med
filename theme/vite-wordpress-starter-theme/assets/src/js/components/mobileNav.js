import gsap from 'gsap';

export function initMobileNav() {
	const burger = document.getElementById('burger');
	const nav = document.getElementById('mainNav');

	if (!burger || !nav) {
		return;
	}

	let scanTimeline = null;
	let scanLineEl = null;

	const getScanLine = () => {
		if (!scanLineEl) {
			scanLineEl = document.createElement('span');
			scanLineEl.className = 'nav__scan-line';
			scanLineEl.setAttribute('aria-hidden', 'true');
			nav.appendChild(scanLineEl);
		}
		return scanLineEl;
	};

	const resetScanEffect = () => {
		scanTimeline?.kill();
		scanTimeline = null;

		if (scanLineEl) {
			gsap.set(scanLineEl, { y: -3 });
		}

		nav.querySelectorAll('.grid-flash').forEach((item) => {
			item.classList.remove('grid-flash');
			delete item.dataset.gridFlashed;
		});
	};

	const getScanTargets = () => nav.querySelectorAll('.nav__links .menu-item, .nav__email, .nav__social-icons');

	const playScanEffect = () => {
		resetScanEffect();

		const line = getScanLine();
		const navRect = nav.getBoundingClientRect();
		const travel = navRect.height + 6;

		const items = Array.from(getScanTargets())
			.map((el) => {
				const rect = el.getBoundingClientRect();
				const top = rect.top - navRect.top;
				return { el, top, bottom: top + rect.height };
			})
			.filter((item) => item.bottom > item.top);

		gsap.set(line, { y: -3 });
		// Delay must match the panel's opening transition duration (_header.scss:.is-open)
		// so the scan line starts right as the panel finishes sliding in.
		scanTimeline = gsap.timeline({ delay: 1.2 }).to(line, {
			y: travel - 3,
			duration: 4.3,
			ease: 'none',
			onUpdate: () => {
				const currentY = gsap.getProperty(line, 'y');
				items.forEach((item) => {
					if (currentY >= item.top && currentY <= item.bottom) {
						item.el.classList.add('grid-flash');
						item.el.addEventListener(
							'animationend',
							() => item.el.classList.remove('grid-flash'),
						);
					}
				});
			},
		});
	};

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
		playScanEffect();
	};

	const closeNav = () => {
		nav.classList.remove('is-open');
		nav.setAttribute('aria-hidden', 'true');
		burger.classList.remove('is-open');
		burger.setAttribute('aria-expanded', 'false');
		document.body.style.overflow = '';
		closeSubmenus();
		resetScanEffect();
	};

	burger.addEventListener('click', () => {
		nav.classList.contains('is-open') ? closeNav() : openNav();
	});

	nav.querySelectorAll('.nav__link, .menu-item a').forEach((link) => {
		link.addEventListener('click', closeNav);
	});

	initSubmenuToggles(nav);
}

function initSubmenuToggles(nav) {
	const parents = nav.querySelectorAll('.menu-item-has-children');

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
		});
	});
}
