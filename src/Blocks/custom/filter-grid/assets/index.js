import domReady from '@wordpress/dom-ready';
import manifest from './../manifest.json';

domReady(async () => {
	const grids = document.querySelectorAll(`.${manifest.blockJsClass}`);

	if (!grids.length) {
		return;
	}

	const { FilterGrid } = await import('./filter');

	grids.forEach((element) => new FilterGrid({ element }).init());
});
