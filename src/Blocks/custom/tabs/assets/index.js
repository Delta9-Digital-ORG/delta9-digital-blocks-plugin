import domReady from '@wordpress/dom-ready';
import manifest from './../manifest.json';

domReady(async () => {
	const selector = `.${manifest.blockJsClass}`;
	const elements = document.querySelectorAll(selector);

	if (!elements.length) {
		return;
	}

	const { Tabs } = await import('./tabs');

	elements.forEach((element) => {
		new Tabs({
			element,
			listSelector: `${selector}-list`,
			panelSelector: '.js-block-tab',
		}).init();
	});
});
