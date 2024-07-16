import domReady from '@wordpress/dom-ready';
import manifest from './../manifest.json';
import { Cookies } from './cookies';

domReady(async () => {
	// if age verification cookie is set then return
	let cookieName = 'site-visitor-over-21';
	let cookies = new Cookies();
	let over21 =  cookies.getCookie(cookieName);
	
	if(over21 == 'true') {
		return;
	}
	
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
		cookieName: cookieName,
	});

	ageVerification.init();
});
