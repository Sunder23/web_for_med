export function initMobileNav() {
	const burger = document.getElementById('burger');
	const nav = document.getElementById('mainNav');

	if (!burger || !nav) {
		return;
	}

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
	};

	burger.addEventListener('click', () => {
		nav.classList.contains('is-open') ? closeNav() : openNav();
	});

	nav.querySelectorAll('.nav__link, .menu-item a').forEach((link) => {
		link.addEventListener('click', closeNav);
	});
}
