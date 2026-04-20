import domReady from '@wordpress/dom-ready';

domReady(() => {
	const selectors = document.querySelectorAll('.bundle-picker');

	if (!selectors.length) {
		return;
	}

	selectors.forEach((picker) => {
		new BundlePicker(picker).init();
	});
});

class BundlePicker {
	constructor(element) {
		this.element = element;
		this.restUrl = element.dataset.restUrl;
		this.restNonce = element.dataset.restNonce;
		this.tiers = JSON.parse(element.dataset.tiers || '[]');
		this.products = JSON.parse(element.dataset.products || '[]');

		// DOM references.
		this.productCards = element.querySelectorAll('.bundle-picker__product');
		this.summaryCount = element.querySelector('.bundle-picker__summary-count');
		this.summarySubtotal = element.querySelector('.bundle-picker__summary-subtotal');
		this.summaryDiscount = element.querySelector('.bundle-picker__summary-discount');
		this.summaryDiscountRow = element.querySelector('.bundle-picker__summary-row--discount');
		this.summaryTotal = element.querySelector('.bundle-picker__summary-total');
		this.addToCartBtn = element.querySelector('.bundle-picker__add-to-cart');
		this.messageEl = element.querySelector('.bundle-picker__message');
		this.progressFill = element.querySelector('.bundle-picker__progress-fill');
		this.progressText = element.querySelector('.bundle-picker__progress-text');
		this.tierElements = element.querySelectorAll('.bundle-picker__tier');

		// State.
		this.isLoading = false;
	}

	init() {
		this.bindQuantityControls();
		this.bindAddToCart();
		this.updateSummary();
	}

	bindQuantityControls() {
		this.productCards.forEach((card) => {
			const minusBtn = card.querySelector('.bundle-picker__qty-minus');
			const plusBtn = card.querySelector('.bundle-picker__qty-plus');
			const input = card.querySelector('.bundle-picker__qty-input');

			minusBtn.addEventListener('click', () => {
				const current = parseInt(input.value, 10) || 0;
				if (current > 0) {
					input.value = current - 1;
					this.updateSummary();
				}
			});

			plusBtn.addEventListener('click', () => {
				const current = parseInt(input.value, 10) || 0;
				if (current < 99) {
					input.value = current + 1;
					this.updateSummary();
				}
			});

			input.addEventListener('change', () => {
				let val = parseInt(input.value, 10) || 0;
				val = Math.max(0, Math.min(99, val));
				input.value = val;
				this.updateSummary();
			});
		});
	}

	getSelections() {
		const items = [];

		this.productCards.forEach((card) => {
			const productId = parseInt(card.dataset.productId, 10);
			const price = parseFloat(card.dataset.price);
			const qty = parseInt(card.querySelector('.bundle-picker__qty-input').value, 10) || 0;

			if (qty > 0) {
				items.push({ product_id: productId, quantity: qty, price });
			}
		});

		return items;
	}

	getActiveTier(totalQty) {
		let activeTier = null;

		// Sort tiers by min descending, pick the highest qualifying tier.
		const sorted = [...this.tiers].sort((a, b) => b.min - a.min);

		for (const tier of sorted) {
			if (totalQty >= tier.min) {
				activeTier = tier;
				break;
			}
		}

		return activeTier;
	}

	getNextTier(totalQty) {
		// Sort tiers by min ascending, find the next tier above current qty.
		const sorted = [...this.tiers].sort((a, b) => a.min - b.min);

		for (const tier of sorted) {
			if (totalQty < tier.min) {
				return tier;
			}
		}

		return null;
	}

	updateSummary() {
		const items = this.getSelections();
		const totalQty = items.reduce((sum, item) => sum + item.quantity, 0);
		const subtotal = items.reduce((sum, item) => sum + (item.price * item.quantity), 0);

		const activeTier = this.getActiveTier(totalQty);
		const discountPct = activeTier ? activeTier.pct : 0;
		const discountAmount = subtotal * (discountPct / 100);
		const total = subtotal - discountAmount;

		// Update count.
		this.summaryCount.textContent = totalQty;

		// Update subtotal.
		this.summarySubtotal.textContent = this.formatPrice(subtotal);

		// Update discount row.
		if (discountPct > 0) {
			this.summaryDiscountRow.style.display = '';
			this.summaryDiscount.textContent = `-${this.formatPrice(discountAmount)} (${discountPct}%)`;
		} else {
			this.summaryDiscountRow.style.display = 'none';
		}

		// Update total.
		this.summaryTotal.textContent = this.formatPrice(total);

		// Update add to cart button.
		this.addToCartBtn.disabled = totalQty === 0;

		// Update per-product discounted prices.
		this.productCards.forEach((card) => {
			const price = parseFloat(card.dataset.price);
			const currentPriceEl = card.querySelector('.bundle-picker__price-current');
			const discountedPriceEl = card.querySelector('.bundle-picker__price-discounted');

			if (discountPct > 0) {
				const discountedPrice = price * (1 - discountPct / 100);
				currentPriceEl.style.textDecoration = 'line-through';
				currentPriceEl.style.opacity = '0.6';
				discountedPriceEl.style.display = '';
				discountedPriceEl.textContent = this.formatPrice(discountedPrice);
			} else {
				currentPriceEl.style.textDecoration = '';
				currentPriceEl.style.opacity = '';
				discountedPriceEl.style.display = 'none';
			}
		});

		// Update tier highlights.
		this.tierElements.forEach((tierEl) => {
			const tierMin = parseInt(tierEl.dataset.min, 10);
			tierEl.classList.toggle('bundle-picker__tier--active', activeTier && tierMin === activeTier.min);
		});

		// Update progress bar.
		this.updateProgress(totalQty);
	}

	updateProgress(totalQty) {
		if (!this.progressFill || !this.progressText) {
			return;
		}

		const nextTier = this.getNextTier(totalQty);
		const activeTier = this.getActiveTier(totalQty);

		if (!nextTier && activeTier) {
			// At max tier.
			this.progressFill.style.width = '100%';
			this.progressText.textContent = `${activeTier.pct}% discount applied!`;
		} else if (nextTier) {
			const prevMin = activeTier ? activeTier.min : 0;
			const range = nextTier.min - prevMin;
			const progress = totalQty - prevMin;
			const percentage = Math.min(100, (progress / range) * 100);

			this.progressFill.style.width = `${percentage}%`;

			const remaining = nextTier.min - totalQty;
			this.progressText.textContent = `Add ${remaining} more to unlock ${nextTier.pct}% off!`;
		} else {
			this.progressFill.style.width = '0%';
			this.progressText.textContent = this.tiers.length > 0
				? `Add ${this.tiers[0].min} items to start saving!`
				: '';
		}
	}

	bindAddToCart() {
		this.addToCartBtn.addEventListener('click', () => {
			if (this.isLoading) {
				return;
			}

			this.addToCart();
		});
	}

	async addToCart() {
		const items = this.getSelections();

		if (items.length === 0) {
			return;
		}

		this.isLoading = true;
		this.addToCartBtn.disabled = true;
		this.addToCartBtn.textContent = 'Adding...';
		this.hideMessage();

		const payload = {
			items: items.map(({ product_id, quantity }) => ({ product_id, quantity })),
			tiers: this.tiers,
		};

		try {
			const response = await fetch(this.restUrl, {
				method: 'POST',
				mode: 'same-origin',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': this.restNonce,
				},
				credentials: 'same-origin',
				redirect: 'follow',
				referrer: 'no-referrer',
				body: JSON.stringify(payload),
			});

			const data = await response.json();

			if (response.ok && data.success) {
				this.showMessage('Bundle added to cart!', 'success');
				this.resetQuantities();
				this.updateSummary();

				// Refresh WooCommerce mini-cart fragments.
				this.refreshCartFragments(data.fragments);
			} else {
				this.showMessage(data.message || 'Failed to add bundle to cart.', 'error');
			}
		} catch {
			this.showMessage('Network error. Please try again.', 'error');
		} finally {
			this.isLoading = false;
			this.addToCartBtn.disabled = false;
			this.addToCartBtn.textContent = 'Add Bundle to Cart';
		}
	}

	refreshCartFragments(fragments) {
		if (!fragments) {
			return;
		}

		// Update WooCommerce cart fragments in the DOM (mini-cart, cart count, etc.).
		Object.entries(fragments).forEach(([selector, html]) => {
			const el = document.querySelector(selector);
			if (el) {
				el.outerHTML = html;
			}
		});

		// Trigger WooCommerce cart fragment refresh event.
		document.body.dispatchEvent(new Event('wc_fragment_refresh'));
	}

	resetQuantities() {
		this.productCards.forEach((card) => {
			card.querySelector('.bundle-picker__qty-input').value = 0;
		});
	}

	showMessage(text, type) {
		this.messageEl.textContent = text;
		this.messageEl.className = `bundle-picker__message bundle-picker__message--${type}`;
		this.messageEl.style.display = '';
	}

	hideMessage() {
		this.messageEl.style.display = 'none';
	}

	formatPrice(amount) {
		return `$${amount.toFixed(2)}`;
	}
}
