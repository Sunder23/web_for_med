import Swiper from 'swiper';
import { Navigation, Pagination } from 'swiper/modules';
import 'swiper/css';
import 'swiper/css/navigation';
import 'swiper/css/pagination';

// `root` scopes the lookup so the editor canvas can init each block preview
// on its own (see editor-section-blocks.js). Returns the Swiper instance.
export function initCasesSlider(root = document) {
	const sliderEl = root.querySelector('#casesSlider');

	if (!sliderEl || sliderEl.swiper) {
		return sliderEl?.swiper ?? null;
	}

	return new Swiper(sliderEl, {
		modules: [Navigation, Pagination],
		slidesPerView: 1,
		spaceBetween: 0,
		loop: true,
		navigation: {
			prevEl: root.querySelector('#casePrev'),
			nextEl: root.querySelector('#caseNext'),
		},
		pagination: {
			el: root.querySelector('#casesDots'),
			clickable: true,
			bulletClass: 'cases__dot',
			bulletActiveClass: 'is-active',
			renderBullet(index, className) {
				return `<button class="${className}" type="button" aria-label="Go to slide ${index + 1}"></button>`;
			},
		},
		breakpoints: {
			766: {
				slidesPerView: 2,
			},
			1024: {
				slidesPerView: 3,
			},
		},
	});
}
