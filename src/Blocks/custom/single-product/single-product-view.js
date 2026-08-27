/**
 * Single Product — Interactivity API store.
 *
 * State is seeded server-side by single-product.php via
 * wp_interactivity_state('delta9/singleProduct', ...). This module supplies
 * the derived getters, click actions, and the CSS-var sync callback.
 */

import { store, getContext } from '@wordpress/interactivity';

const { state } = store('delta9/singleProduct', {
	state: {
		get activeFlavor() {
			return state.flavors.find((f) => f.id === state.activeId);
		},
		get activePack() {
			const f = state.activeFlavor;
			if (!f || !f.packOptions || f.packOptions.length === 0) return null;
			return f.packOptions[state.packIndex] || f.packOptions[0] || null;
		},
		get displayPrice() {
			if (state.activePack) return state.activePack.priceHtml;
			return state.activeFlavor ? state.activeFlavor.priceHtml : '';
		},
		get panelTitle() {
			const f = state.activeFlavor;
			if (!f) return '';
			if (state.tab === 'description') {
				return (f.description || '').split('\n\n')[0] || '';
			}
			if (state.tab === 'cannafacts') return 'Nutritional facts';
			if (state.tab === 'ingredients') return 'Ingredients';
			return '';
		},
		get panelBody() {
			const f = state.activeFlavor;
			if (!f) return '';
			if (state.tab === 'description') {
				return (f.description || '').split('\n\n').slice(1).join('\n\n');
			}
			if (state.tab === 'cannafacts') return f.cannafacts || '';
			if (state.tab === 'ingredients') return f.ingredients || '';
			return '';
		},
	},
	actions: {
		selectFlavor() {
			// Navigate to the selected flavor's product page. All
			// per-flavor content (slot block, WC blocks, custom details)
			// re-renders server-side against the new product — much
			// simpler than maintaining iAPI bindings across the page.
			const { id } = getContext();
			const f = state.flavors.find((x) => x.id === id);
			if (f && f.permalink) {
				window.location.href = f.permalink;
			}
		},
		selectPack(event) {
			const idx = parseInt(event.target.value, 10) || 0;
			state.packIndex = idx;
			const f = state.activeFlavor;
			if (f && f.packOptions && f.packOptions[idx]) {
				state.qty = f.packOptions[idx].quantity;
			}
		},
		selectTab() {
			const { tab } = getContext();
			state.tab = tab;
		},
		incrementQty() {
			state.qty += 1;
			const f = state.activeFlavor;
			if (f && f.packOptions) {
				const idx = f.packOptions.findIndex((p) => p.quantity === state.qty);
				if (idx >= 0) state.packIndex = idx;
			}
		},
		decrementQty() {
			if (state.qty > 1) {
				state.qty -= 1;
				const f = state.activeFlavor;
				if (f && f.packOptions) {
					const idx = f.packOptions.findIndex((p) => p.quantity === state.qty);
					if (idx >= 0) state.packIndex = idx;
				}
			}
		},
		*addToCart() {
			const flavor = state.activeFlavor;
			if (!flavor) return;
			// Classic WC AJAX endpoint so PackPricing service's $_POST-based
			// hook picks up the pack price/label/quantity.
			const pack = state.activePack;
			const body = new URLSearchParams();
			body.append('product_id', String(flavor.id));
			body.append('quantity', String(state.qty));
			if (pack) {
				body.append('pack_price', String(pack.price));
				body.append('pack_label', pack.label);
				body.append('pack_quantity', String(pack.quantity));
			}
			const wcAjaxUrl = (window.woocommerce_params && window.woocommerce_params.wc_ajax_url)
				? window.woocommerce_params.wc_ajax_url.replace('%%endpoint%%', 'add_to_cart')
				: '/?wc-ajax=add_to_cart';
			try {
				const response = yield fetch(wcAjaxUrl, {
					method: 'POST',
					credentials: 'same-origin',
					headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
					body: body.toString(),
				});
				if (response.ok) {
					document.body.dispatchEvent(new Event('wc_fragment_refresh'));
					document.dispatchEvent(new Event('wc-blocks_added_to_cart'));
				}
			} catch (err) {
				// Swallow; the UI shows a generic error path in Phase 4 verification.
			}
		},
	},
	callbacks: {
		applyFlavorVars: () => {
			const f = state.activeFlavor;
			if (!f) return;
			const root = document.querySelector('.yb-single-product');
			if (!root) return;
			if (f.pageBg) root.style.setProperty('--page-bg', f.pageBg);
			if (f.pageContrast) root.style.setProperty('--page-contrast', f.pageContrast);
			if (f.nameColor) root.style.setProperty('--name-color', f.nameColor);
			// Active class on the matching flavor card.
			root.querySelectorAll('.yb-single-product__flavorCard').forEach((card) => {
				card.classList.remove('is-active');
			});
			// data-wp-context contains `{"id":N}` — match against activeId.
			root.querySelectorAll('.yb-single-product__flavorCard').forEach((card) => {
				const ctx = card.getAttribute('data-wp-context');
				if (ctx && JSON.parse(ctx).id === state.activeId) {
					card.classList.add('is-active');
				}
			});
		},
	},
});
