// Editor-only: runs inside the block editor canvas iframe.
// Enqueued by starter_vite_enqueue_editor_canvas_assets() in configure/js-css.php.
//
// ACF block previews are server-rendered front-end markup, so a click on a
// link (or a CF7 submit) inside a preview navigates the canvas iframe away
// and breaks the editor. Default actions are cancelled here, but events still
// propagate, so clicking a link keeps selecting its block as usual.
const BLOCK_SELECTOR = '.wp-block[data-type^="acf/"]';

// ACF field forms (block edit mode) and RichText stay fully interactive.
const EXEMPT_SELECTOR =
	'.acf-block-fields, .acf-fields, [contenteditable="true"]';

const LINK_SELECTOR = 'a[href], area[href]';
const SUBMIT_SELECTOR =
	'button[type="submit"], input[type="submit"], input[type="image"]';

function isInsideBlockPreview(element) {
	return (
		Boolean(element?.closest(BLOCK_SELECTOR)) &&
		!element.closest(EXEMPT_SELECTOR)
	);
}

function onClick(event) {
	if (!(event.target instanceof Element)) {
		return;
	}

	const target =
		event.target.closest(LINK_SELECTOR) ||
		event.target.closest(SUBMIT_SELECTOR);

	if (target && isInsideBlockPreview(target)) {
		event.preventDefault();
	}
}

function onSubmit(event) {
	if (!isInsideBlockPreview(event.target)) {
		return;
	}

	event.preventDefault();
	// Capture phase on document: stops CF7's own submit listener from firing
	// an AJAX request from the editor.
	event.stopPropagation();
}

if (typeof Element.prototype.closest !== 'function') {
	console.warn(
		'[editor-link-guard] Element.closest is unavailable — block preview links stay clickable',
	);
} else {
	// The module runs from <head> before the canvas <body> exists; delegated
	// listeners on document also cover previews ACF re-renders on field change.
	document.addEventListener('click', onClick, true);
	document.addEventListener('auxclick', onClick, true);
	document.addEventListener('submit', onSubmit, true);
}
