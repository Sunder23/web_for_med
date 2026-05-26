import Swiper from 'swiper';
import { Navigation, Pagination } from 'swiper/modules';
import 'swiper/css';
import 'swiper/css/navigation';
import 'swiper/css/pagination';

export function initCasesSlider() {
	const sliderEl = document.getElementById('casesSlider');

	if (!sliderEl) {
		return;
	}

	new Swiper(sliderEl, {
		modules: [Navigation, Pagination],
		slidesPerView: 1,
		spaceBetween: 0,
		loop: true,
		navigation: {
			prevEl: '#casePrev',
			nextEl: '#caseNext',
		},
		pagination: {
			el: '#casesDots',
			clickable: true,
			bulletClass: 'cases-dot',
			bulletActiveClass: 'is-active',
			renderBullet(index, className) {
				return `<button class="${className}" type="button" aria-label="Go to slide ${index + 1}"></button>`;
			},
		},
		breakpoints: {
			769: {
				slidesPerView: 2,
			},
			1025: {
				slidesPerView: 3,
			},
		},
	});
}
