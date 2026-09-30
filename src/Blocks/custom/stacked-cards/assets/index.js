import domReady from '@wordpress/dom-ready';
import manifest from './../manifest.json';

domReady(async () => {
	const sections = document.querySelectorAll(`.${manifest.blockJsClass}`);
	if (!sections.length) {
		return;
	}

	const { initStack } = await import('./stack');
	sections.forEach(initStack);
});
