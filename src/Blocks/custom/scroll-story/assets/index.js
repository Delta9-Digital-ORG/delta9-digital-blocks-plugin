import domReady from '@wordpress/dom-ready';
import manifest from './../manifest.json';

domReady(async () => {
	const elements = document.querySelectorAll(`.${manifest.blockJsClass}`);

	if (!elements.length) {
		return;
	}

	const { ScrollStory } = await import('./scroll-story');

	elements.forEach((element) => new ScrollStory({ element }).init());
});
