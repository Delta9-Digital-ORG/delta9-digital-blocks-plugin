/**
 * Front-end entry for the Work Showcase block.
 *
 * Stays tiny: server-rendered <img> tiles are the baseline (SEO, alt text,
 * no-JS, reduced-motion, editor preview). Once the block scrolls near the
 * viewport we lazy-import the three.js scene, mount it over the tiles, and
 * fade the tiles out (they stay in the DOM). We bail entirely on
 * prefers-reduced-motion or Save-Data.
 *
 * three.js + gsap live behind the dynamic import('./scene/Scene') so they are
 * code-split out of the initial front-end bundle.
 */

import domReady from '@wordpress/dom-ready';
import manifest from '../manifest.json';

function readTiles(root) {
	return [...root.querySelectorAll('.d9-showcase__tile')].map((li) => ({
		src: li.dataset.full,
		mediumSrc: li.dataset.medium,
		aspect: parseFloat(li.dataset.aspect) || 0,
		blurred: li.dataset.blurred === '1',
		type: li.dataset.type || 'can',
		link: li.dataset.link || '',
		caption: li.dataset.caption || '',
		alt: li.querySelector('img')?.alt || '',
	}));
}

function mountOne(root) {
	const win = root.ownerDocument.defaultView;

	const reduced = win.matchMedia('(prefers-reduced-motion: reduce)').matches;
	const saveData = win.navigator.connection?.saveData === true;
	if (reduced || saveData) {
		return;
	}

	const stage = root.querySelector('.d9-showcase__stage');
	if (!stage) {
		return;
	}

	const tiles = readTiles(root);
	if (!tiles.length) {
		return;
	}

	const scrollExit = root.dataset.scrollExit !== '0';
	const radius = parseFloat(root.dataset.radius) || 0.08;

	let scene = null;
	let mounted = false;

	const mount = async () => {
		if (mounted) {
			return;
		}
		mounted = true;
		const { Scene } = await import('./scene/Scene.js');
		scene = new Scene({ container: stage, tiles, scrollExit, radius });
		await scene.mount();
		root.classList.add('is-3d');

		const replay = root.querySelector('.d9-showcase__replay');
		if (replay) {
			replay.hidden = false;
			replay.addEventListener('click', () => scene?.intro?.restart());
		}
	};

	// Mount when near the viewport; pause the RAF loop when fully offscreen.
	const io = new win.IntersectionObserver(
		(entries) => {
			entries.forEach((entry) => {
				if (entry.isIntersecting) {
					mount();
					scene?.resume();
				} else if (scene && mounted) {
					scene.pause();
				}
			});
		},
		{ rootMargin: '200px 0px' },
	);
	io.observe(stage);
}

domReady(() => {
	const selector = `.${manifest.blockJsClass}`;
	document.querySelectorAll(selector).forEach((root) => mountOne(root));
});
