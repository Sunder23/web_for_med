export function initGlitchImage() {
	document.querySelectorAll('.glitch-image').forEach(container => {
		const img = container.querySelector('img');
		if (!img) return;
		container.appendChild(img.cloneNode(true));
		container.appendChild(img.cloneNode(true));
	});
}
