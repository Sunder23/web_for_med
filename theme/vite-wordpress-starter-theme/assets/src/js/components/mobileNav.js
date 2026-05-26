export function initMobileNav() {
	const burger = document.getElementById('burger');
	const nav = document.getElementById('mainNav');

	if (!burger || !nav) {
		return;
	}

	const openNav = () => {
		nav.classList.add('is-open');
		burger.classList.add('is-open');
		burger.setAttribute('aria-expanded', 'true');
		document.addEventListener('click', outsideClick, true);
	};

	const closeNav = () => {
		nav.classList.remove('is-open');
		burger.classList.remove('is-open');
		burger.setAttribute('aria-expanded', 'false');
		document.removeEventListener('click', outsideClick, true);
	};

	const outsideClick = (event) => {
		if (!nav.contains(event.target) && !burger.contains(event.target)) {
			closeNav();
		}
	};

	burger.addEventListener('click', () => {
		nav.classList.contains('is-open') ? closeNav() : openNav();
	});

	nav.querySelectorAll('.nav__link, .menu-item a').forEach((link) => {
		link.addEventListener('click', closeNav);
	});
}
