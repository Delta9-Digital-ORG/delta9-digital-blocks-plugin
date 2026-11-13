import domReady from '@wordpress/dom-ready';
import manifest from './../manifest.json';

domReady(async () => {
	if (!document.querySelector(`.${manifest.blockJsClass}`)) {
		return;
	}

	const { attachMegaPanels } = await import('./attach');
	attachMegaPanels(`.${manifest.blockJsClass}`);
});
