import domReady from '@wordpress/dom-ready';

domReady(() => {
	const selectors = document.querySelectorAll('.dropdown-picker__select');

	if (!selectors.length) {
		return;
	}

	selectors.forEach((select) => {
		new DropdownPickerHandler(select).init();
	});
});

class DropdownPickerHandler {
	constructor(selectElement) {
		this.select = selectElement;
		this.productId = selectElement.dataset.productId;
		this.regularPrice = parseFloat(selectElement.dataset.regularPrice) || 0;

		// Find the nearest product card container.
		this.productCard = selectElement.closest('.wc-block-grid__product, .wp-block-post, .product, .wc-block-product, [class*="product"]');

		// Find the product-count-button (add to cart) in the same product card.
		this.addToCartBtn = this.productCard
			? this.productCard.querySelector('.block-product-count-button__btn')
			: null;

		// Find the product-count quantity display in the same product card.
		this.quantityDisplay = this.productCard
			? this.productCard.querySelector('.product-count-quantity')
			: null;

		// Find price display element in the same product card.
		this.priceDisplay = this.productCard
			? this.productCard.querySelector('.wc-block-components-product-price, .price, [class*="price"]')
			: null;

		// Build a map of quantity → option index for fast lookup.
		this.quantityMap = {};
		Array.from(this.select.options).forEach((option, index) => {
			const qty = parseInt(option.dataset.quantity, 10) || 1;
			this.quantityMap[qty] = index;
		});
	}

	init() {
		// Listen for dropdown changes.
		this.select.addEventListener('change', () => this.onSelectChange());

		// Listen for product-count +/- changes (custom event dispatched on document by product-count JS).
		document.addEventListener('product-count:change', (e) => {
			if (String(e.detail.productId) === String(this.productId)) {
				this.onQuantityChange(e.detail.quantity);
			}
		});

		// Apply default (first option) pack data on page load.
		this.onSelectChange();
	}

	/**
	 * Dropdown changed — sync product-count display and apply pack data.
	 */
	onSelectChange() {
		const selectedOption = this.select.options[this.select.selectedIndex];
		const quantity = parseInt(selectedOption.dataset.quantity, 10) || 1;
		const price = parseFloat(selectedOption.dataset.price) || this.regularPrice;
		const label = selectedOption.dataset.label || '';

		// Sync the product-count quantity display.
		if (this.quantityDisplay) {
			this.quantityDisplay.textContent = quantity;
		}

		this.applyPackData(quantity, price, label);
	}

	/**
	 * Product-count +/- changed — find matching pack tier and select it.
	 */
	onQuantityChange(quantity) {
		const optionIndex = this.quantityMap[quantity];

		if (optionIndex !== undefined) {
			// Exact match — select the matching pack option.
			this.select.selectedIndex = optionIndex;
			const option = this.select.options[optionIndex];
			const price = parseFloat(option.dataset.price) || this.regularPrice;
			const label = option.dataset.label || '';

			this.applyPackData(quantity, price, label);
		} else {
			// No exact pack tier match — keep current dropdown selection.
		}
	}

	/**
	 * Update the add-to-cart button and price display with pack data.
	 */
	applyPackData(quantity, price, label) {
		if (this.addToCartBtn) {
			this.addToCartBtn.setAttribute('data-product_quantity', quantity);

			if (label) {
				this.addToCartBtn.setAttribute('data-pack_price', price);
				this.addToCartBtn.setAttribute('data-pack_label', label);
				this.addToCartBtn.setAttribute('data-pack_quantity', quantity);
			} else {
				// "Single" or non-pack quantity — remove pack data.
				this.addToCartBtn.removeAttribute('data-pack_price');
				this.addToCartBtn.removeAttribute('data-pack_label');
				this.addToCartBtn.removeAttribute('data-pack_quantity');
			}
		}

		// Update the displayed price.
		if (this.priceDisplay) {
			this.priceDisplay.textContent = '$' + price.toFixed(2);
		}
	}
}
