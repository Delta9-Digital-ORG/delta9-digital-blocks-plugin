import domReady from '@wordpress/dom-ready';
import manifest from './../manifest.json';

domReady(async () => {
	const { componentClass, componentJsToggleClass } = manifest;
	const body = document.querySelector('body');
	const ageVerificationSelector = `.${componentClass}`;
	const ageVerificationElements = document.querySelectorAll(ageVerificationSelector);

	if (!ageVerificationElements.length) {
		return;
	}

	// Append all modals at the bottom of body.
	[...ageVerificationElements].forEach((element) => {
		body.append(element);
	});

	// Instantiate and initialize Modal.
	const { AgeVerification } = await import('./age-verification');

	const ageVerification = new AgeVerification({
		openClass: 'is-open',
		componentClass: componentClass,
		jsToggleClass: componentJsToggleClass,
	});

	ageVerification.init();
});
