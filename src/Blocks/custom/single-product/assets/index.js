// The iAPI view code runs as an inline ES module emitted by single-product.php
// so it can `import { store } from '@wordpress/interactivity'` against
// WordPress's real script module — webpack's bundle would have externalized
// it to `wp.interactivity`, which is undefined under the new module-based iAPI.
//
// What lives here is the 3D can that replaces the hero image on drink pages.
// It doesn't touch the store: the PHP template opts the block in with
// `data-can-3d` (the label data for each flavor), and the store's
// selectFlavor action finds the scene on `root.ybCan3d` to spin it before
// navigating. three.js is a separate chunk, so pages without a can never
// download it.

import domReady from '@wordpress/dom-ready';

domReady(() => {
	document.querySelectorAll('.yb-single-product[data-can-3d]').forEach(bootCan3d);
});

// If three.js hasn't drawn a can by now (slow connection, cold cache), put
// the product photo back rather than leaving the hero empty. The can still
// takes over when it arrives.
const POSTER_GRACE_MS = 2500;

function bootCan3d(root) {
	const stage = root.querySelector('.yb-single-product__heroImage');
	if (!stage) {
		return;
	}

	let config;
	try {
		// Not `dataset`: it only camel-cases a dash before a letter, so this
		// attribute would surface as dataset['can-3d'].
		config = JSON.parse(root.getAttribute('data-can-3d'));
	} catch {
		root.classList.add('is-can3d-poster');
		return;
	}

	const posterTimer = setTimeout(() => root.classList.add('is-can3d-poster'), POSTER_GRACE_MS);
	const fail = (err) => {
		clearTimeout(posterTimer);
		root.classList.add('is-can3d-poster');
		if (err) {
			// eslint-disable-next-line no-console
			console.warn('[single-product] 3D can unavailable, showing product photo.', err);
		}
	};

	import(/* webpackChunkName: "single-product-can-3d" */ './can-3d/can-scene')
		.then(({ createCanScene, supportsWebGL }) => {
			if (!supportsWebGL()) {
				fail();
				return null;
			}
			return createCanScene(stage, config);
		})
		.then((scene) => {
			if (!scene) {
				return;
			}
			clearTimeout(posterTimer);
			root.ybCan3d = scene;
			root.classList.remove('is-can3d-poster');
			root.classList.add('is-can3d-ready');
		})
		.catch(fail);
}
