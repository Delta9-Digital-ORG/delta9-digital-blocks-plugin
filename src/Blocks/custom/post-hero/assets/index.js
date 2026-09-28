import domReady from '@wordpress/dom-ready';
import manifest from './../manifest.json';

domReady(async () => {
	if (!document.querySelector(`.${manifest.blockJsClass} .d9-share`)) {
		return;
	}

	const { initShareMenus } = await import('./share');
	initShareMenus(`.${manifest.blockJsClass} .d9-share`);
});
