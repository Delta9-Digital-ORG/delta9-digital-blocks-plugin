import domReady from '@wordpress/dom-ready';
import manifest from './../manifest.json';

domReady(async () => {
	const frames = document.querySelectorAll(`.${manifest.blockJsClass}`);

	if (!frames.length) {
		return;
	}

	const { EmbedFrame } = await import('./embed-frame');

	new EmbedFrame([...frames]).init();
});
