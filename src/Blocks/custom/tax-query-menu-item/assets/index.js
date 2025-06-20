import domReady from '@wordpress/dom-ready';
import manifest from './../manifest.json';

domReady(async () => {
	const taxQueryMenuItemSelector = `.${manifest.blockJsClass}`;
	const taxQueryMenuItemElements = document.querySelectorAll(taxQueryMenuItemSelector);

	if (!taxQueryMenuItemElements.length) {
		return;
	}

	const { TaxQueryMenuItem  } = await import('./tax-query-menu-item.js');

	taxQueryMenuItemElements.forEach((item) => {
		const taxQueryMenuItem = new TaxQueryMenuItem({
			containerElement: item,
			itemSelector: `${accordionSelector}-item`,
			triggerSelector: `${accordionSelector}-item-trigger`,
		});

		taxQueryMenuItem.init();
	});
});
