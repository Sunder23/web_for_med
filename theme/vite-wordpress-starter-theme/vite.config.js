/**
 * View your website at your own local server.
 * Example: if you're using WP-CLI then the common URL is: http://localhost:8080.
 *
 * http://localhost:5173 is serving Vite on development. Access this URL will show empty page.
 *
 */

import { existsSync, readdirSync } from 'node:fs';
import { resolve } from 'node:path';
import pxtorem from 'postcss-pxtorem';
import { defineConfig } from 'vite';

// Flat top-level files in `dir` become entries (main.scss/main.js and any
// future top-level asset). `template-parts/blocks/` is scanned as one
// additional flat level, mirroring the PHP `template-parts/blocks/` layout
// one-for-one (e.g. `hero.php` <-> `template-parts/blocks/hero.scss`/`.js`)
// without making the whole tree recursive.
const collectFlatScssEntries = (dir, prefix, entries) => {
	readdirSync(dir, { withFileTypes: true })
		.filter(
			(entry) =>
				entry.isFile() &&
				entry.name.endsWith('.scss') &&
				!entry.name.startsWith('_'),
		)
		.forEach((entry) => {
			const name = prefix + entry.name.replace(/\.scss$/, '');
			entries[name] = resolve(dir, entry.name);
		});
};

const scssEntries = () => {
	const scssDir = resolve(__dirname, 'assets/src/scss');
	const entries = {};

	collectFlatScssEntries(scssDir, '', entries);

	const blocksDir = resolve(scssDir, 'template-parts/blocks');
	if (existsSync(blocksDir)) {
		collectFlatScssEntries(blocksDir, 'template-parts/blocks/', entries);
	}

	return entries;
};

const collectFlatJsEntries = (dir, prefix, entries) => {
	readdirSync(dir, { withFileTypes: true })
		.filter(
			(entry) =>
				entry.isFile() &&
				entry.name.endsWith('.js') &&
				!entry.name.startsWith('_'),
		)
		.forEach((entry) => {
			const name = prefix + entry.name.replace(/\.js$/, '');
			entries[`js/${name}`] = resolve(dir, entry.name);
		});
};

const jsEntries = () => {
	const jsDir = resolve(__dirname, 'assets/src/js');
	const entries = {};

	collectFlatJsEntries(jsDir, '', entries);

	const blocksDir = resolve(jsDir, 'template-parts/blocks');
	if (existsSync(blocksDir)) {
		collectFlatJsEntries(blocksDir, 'template-parts/blocks/', entries);
	}

	return entries;
};

export default defineConfig(({ command }) => ({
	base: './',

	plugins: [
		{
			handleHotUpdate({ file, server }) {
				if (file.endsWith('.php')) {
					server.ws.send({ type: 'full-reload', path: '*' });
				}
			},
		},
	],

	css: {
		devSourcemap: true,
		postcss: {
			plugins:
				command === 'build'
					? [
							pxtorem({
								rootValue: 16,
								propList: ['*'],
								unitPrecision: 5,
								minPixelValue: 2,
								exclude: /node_modules/i,
							}),
						]
					: [],
		},
	},

	build: {
		// emit manifest so PHP can find the hashed files
		manifest: true,

		outDir: resolve(__dirname, 'assets/dist/'),

		// don't base64 images
		assetsInlineLimit: 0,

		rollupOptions: {
			input: {
				...jsEntries(),
				...scssEntries(),
			},
			output: {
				entryFileNames: '[name]-[hash].js',
				chunkFileNames: '[name]-[hash].js',
				assetFileNames: (assetInfo) => {
					const assetName = assetInfo.name || assetInfo.names?.[0] || '';
					const extType = assetName.split('.');

					// group fonts in a folder
					if (
						extType[1] === 'woff' ||
						extType[1] === 'woff2' ||
						extType[1] === 'ttf'
					) {
						return 'fonts/[name]-[hash].[ext]';
					}

					// group images in a folder
					if (
						extType[1] === 'gif' ||
						extType[1] === 'jpg' ||
						extType[1] === 'jpeg' ||
						extType[1] === 'png'
					) {
						return 'img/[name]-[hash].[ext]';
					}

					return '[ext]/[name]-[hash].[ext]';
				},
			},
		},
	},

	server: {
		// required to load scripts from custom host
		cors: {
			origin: '*',
		},

		// We need a strict port to match on PHP side.
		strictPort: true,
		port: 5173,
	},

	resolve: {
		alias: {
			'@src': resolve(__dirname, 'assets/src'),
			'@js': resolve(__dirname, 'assets/src/js'),
			'@scss': resolve(__dirname, 'assets/src/scss'),
			'@': resolve(__dirname, 'static'),
		},
	},
}));
