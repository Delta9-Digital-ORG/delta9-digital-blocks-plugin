import domReady from '@wordpress/dom-ready';
import manifest from './../manifest.json';

domReady(async () => {
	const sections = document.querySelectorAll(`.${manifest.blockJsClass}`);
	if (!sections.length) {
		return;
	}

	const { initRibbon, initTable } = await import('./hub');
	sections.forEach((section) => {
		if (section.dataset.section === 'ribbon') {
			initRibbon(section);
		}
		if (section.dataset.section === 'table') {
			initTable(section);
		}
	});
});
