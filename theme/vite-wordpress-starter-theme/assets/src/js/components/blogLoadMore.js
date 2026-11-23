import { logDebug } from '@js/utils/logDebug.js';

/**
 * «Показати ще» on the blog listing (partials/parts/blog-archive.php).
 *
 * The button is a plain link to the next page (`/blog/page/2/`), so without JS it
 * just navigates. With JS the next page is fetched, its `.blog-archive__item` cards
 * are appended to the grid and the button takes the next page's link (or is removed
 * on the last page). On any error the click falls back to normal navigation.
 * The address bar is not changed.
 */
export function initBlogLoadMore() {
	const grid = document.querySelector('[data-blog-grid]');
	const button = document.querySelector('[data-blog-more]');

	if (!grid || !button) {
		logDebug('[blog] load more skipped: no grid or button', {
			grid: Boolean(grid),
			button: Boolean(button),
		});
		return;
	}

	let loading = false;

	const setLoading = (state) => {
		loading = state;
		button.classList.toggle('is-loading', state);
		button.setAttribute('aria-busy', state ? 'true' : 'false');
	};

	button.addEventListener('click', async (event) => {
		event.preventDefault();

		if (loading) {
			return;
		}

		const url = button.href;
		logDebug('[blog] load more: start', { url });
		setLoading(true);

		try {
			const response = await fetch(url, {
				credentials: 'same-origin',
				headers: { 'X-Requested-With': 'fetch' },
			});

			if (!response.ok) {
				throw new Error(`HTTP ${response.status}`);
			}

			const doc = new DOMParser().parseFromString(
				await response.text(),
				'text/html',
			);
			const items = Array.from(doc.querySelectorAll('.blog-archive__item'));

			if (!items.length) {
				throw new Error('no cards on the next page');
			}

			const first = document.importNode(items[0], true);
			items.forEach((item, index) => {
				const node = index === 0 ? first : document.importNode(item, true);
				node.classList.add('is-appended');
				node.style.setProperty('--appear-delay', `${index * 60}ms`);
				grid.append(node);
			});

			// Keyboard users continue from the first new card.
			first.querySelector('.blog-card__link')?.focus({ preventScroll: true });

			const next = doc.querySelector('[data-blog-more]');
			if (next?.href) {
				button.href = next.href;
			} else {
				button.closest('.blog-archive__more-row')?.remove();
			}

			logDebug('[blog] load more: appended', {
				count: items.length,
				next: next?.href ?? null,
			});
			setLoading(false);
		} catch (error) {
			logDebug('[blog] load more: failed, fallback to navigation', {
				url,
				error: String(error),
			});
			window.location.assign(url);
		}
	});

	logDebug('[blog] load more: ready', { next: button.href });
}
