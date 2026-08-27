import domReady from '@wordpress/dom-ready';

domReady(() => {
	const selector = '.block-product-count-button__btn';

	$(selector).each(function () {
		const thisButton = $(this);

		$(thisButton).on('click', function () {
			let product_id = $(this).attr('data-product_id');
			let product_quantity = $(this).attr('data-product_quantity');

			thisButton.removeClass( 'added' );
			thisButton.addClass( 'loading' );

			const params = {
				product_id: product_id,
				quantity: product_quantity
			};

			// Include pack pricing data if set by the dropdown picker.
			const packPrice = $(this).attr('data-pack_price');
			const packLabel = $(this).attr('data-pack_label');
			const packQuantity = $(this).attr('data-pack_quantity');

			if (packPrice) {
				params.pack_price = packPrice;
			}
			if (packLabel) {
				params.pack_label = packLabel;
			}
			if (packQuantity) {
				params.pack_quantity = packQuantity;
			}

			$.ajax({
				type: 'POST',
				url: woocommerce_params.wc_ajax_url.toString().replace('%%endpoint%%', 'add_to_cart'),
				data: params,
				dataType: 'json'
			}).done(function (response) {
				// Trigger jQuery event for classic WC fragments.
				$( document.body ).trigger('added_to_cart', [response.fragments, response.cart_hash, thisButton]);
				// Trigger native event for WooCommerce block-based mini-cart (must be on document, not body).
				document.dispatchEvent(new Event('wc-blocks_added_to_cart'));
			}).always(function (response) {
				thisButton.addClass( 'added' );
				thisButton.removeClass( 'loading' );
			});
		});
	});
});
