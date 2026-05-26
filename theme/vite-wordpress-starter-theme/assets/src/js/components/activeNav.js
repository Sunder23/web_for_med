export function initActiveNav() {
	const sections = Array.from(document.querySelectorAll('section[id], footer[id]'));
	const links = Array.from(document.querySelectorAll('.nav__link, .menu-item a'));

	if (!sections.length || !links.length) {
		return;
	}

	const observer = new IntersectionObserver(
		(entries) => {
			entries.forEach((entry) => {
				if (entry.isIntersecting) {
					const id = entry.target.getAttribute('id');

					links.forEach((link) => {
						link.classList.toggle('is-active', link.getAttribute('href') === `#${id}`);
					});
				}
			});
		},
		{ rootMargin: '-30% 0px -65% 0px' },
	);

	sections.forEach((section) => {
		observer.observe(section);
	});
}
