import domReady from '@wordpress/dom-ready';

domReady(() => {
	const selector = '.block-product-count-button__btn';

	$(selector).each(function () {
		const thisButton = $(this);
		
		$(thisButton).on('click', function () {
			let product_id = $(this).data('product_id');
			let product_quantity = $(this).attr('data-product_quantity');
			
			thisButton.removeClass( 'added' );
			thisButton.addClass( 'loading' );
			
			const params = {
				product_id: product_id,
				quantity: product_quantity
			};
			
			$.ajax({
				type: 'POST',
				url: woocommerce_params.wc_ajax_url.toString().replace('%%endpoint%%', 'add_to_cart'),
				data: params,
				dataType: 'json'
			}).success(function (response) {
				// Trigger event so themes can refresh other areas.
				$( document.body ).trigger('added_to_cart', [response.fragments, response.cart_hash, thisButton]);
			}).always(function (response) {
				thisButton.addClass( 'added' );
				thisButton.removeClass( 'loading' );
			});
		});
	});
});
