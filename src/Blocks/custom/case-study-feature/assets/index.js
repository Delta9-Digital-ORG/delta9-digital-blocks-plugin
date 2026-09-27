import domReady from '@wordpress/dom-ready';
import manifest from './../manifest.json';

domReady(async () => {
	if (!document.querySelector(`.${manifest.blockJsClass}`)) {
		return;
	}

	const { initCaseStudyPan } = await import('./pan');
	initCaseStudyPan(`.${manifest.blockJsClass}`);
});
