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

		// Build a map of quantity → option index for fast lookup, plus a
		// normalised pack-tier list for cumulative step pricing.
		this.quantityMap = {};
		this.packOptions = [];
		Array.from(this.select.options).forEach((option, index) => {
			const qty = parseInt(option.dataset.quantity, 10) || 1;
			this.quantityMap[qty] = index;
			this.packOptions.push({
				quantity: qty,
				price: parseFloat(option.dataset.price) || 0,
				label: option.dataset.label || '',
			});
		});
	}

	/**
	 * Base per-unit price for units beyond the active tier: the single-unit
	 * (quantity === 1) tier price, else the smallest tier's implied per-unit.
	 */
	computeBaseUnit() {
		const one = this.packOptions.find((o) => o.quantity === 1);
		if (one) {
			return one.price;
		}
		const first = this.packOptions[0];
		return first && first.quantity > 0 ? first.price / first.quantity : this.regularPrice;
	}

	/**
	 * Largest tier whose quantity is <= qty (order-independent).
	 */
	computeActiveTier(qty) {
		let active = null;
		this.packOptions.forEach((o) => {
			if (o.quantity <= qty && (!active || o.quantity > active.quantity)) {
				active = o;
			}
		});
		return active;
	}

	/**
	 * Cumulative "step" total via greedy bundle-stacking: repeatedly take the
	 * largest tier that fits the remaining quantity, so the discount keeps
	 * applying past the top tier (e.g. 15 = 48-pack + 12-pack). Any leftover
	 * below the smallest tier bills at base. Mirrors PackPricing.php.
	 */
	computePackTotal(qty) {
		if (!this.packOptions.length || qty < 1) {
			return 0;
		}
		const base = this.computeBaseUnit();
		let total = 0;
		let remaining = qty;
		while (remaining > 0) {
			const tier = this.computeActiveTier(remaining);
			if (!tier) {
				total += remaining * base;
				break;
			}
			total += tier.price;
			remaining -= tier.quantity;
		}
		return total;
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
		const label = selectedOption.dataset.label || '';

		// Sync the product-count quantity display.
		if (this.quantityDisplay) {
			this.quantityDisplay.textContent = quantity;
		}

		// Selecting an exact pack tier — its total is that tier's flat price.
		this.applyPackData(quantity, this.computePackTotal(quantity), label);
	}

	/**
	 * Product-count +/- changed — recompute the cumulative step total for the
	 * new quantity and, when it matches a pack tier exactly, sync the dropdown.
	 */
	onQuantityChange(quantity) {
		const optionIndex = this.quantityMap[quantity];

		if (optionIndex !== undefined) {
			// Exact match — reflect it in the dropdown selection.
			this.select.selectedIndex = optionIndex;
		}

		const tier = this.computeActiveTier(quantity);
		this.applyPackData(quantity, this.computePackTotal(quantity), tier ? tier.label : '');
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
