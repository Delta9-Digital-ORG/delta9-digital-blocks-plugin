import domReady from '@wordpress/dom-ready';

domReady(() => {
	jQuery('.block-product-count').each(function (){
		const thisElm = this;
		let decreaseBtn = $(this).children('.product-count').children('.product-count-decrease');
		let increaseBtn = $(this).children('.product-count').children('.product-count-increase');
		let quantityElm = $(this).children('.product-count').children('.product-count-quantity');
		
		$(decreaseBtn).on('click', function () {
			let quantity = $(quantityElm).html();
			
			if (quantity > 1) {
				quantity--;
				$(quantityElm).html(quantity)
				
				updateAddToCart(quantity);
			}
		});
		
		$(increaseBtn).on('click', function () {
			let quantity = $(quantityElm).html();
			
			quantity++;
			$(quantityElm).html(quantity)
			
			updateAddToCart(quantity);
		});
		
		
		function updateAddToCart(quantity) {
			let productBlock = $(thisElm).parent();
			let productBlockFound = false;
			let count = 0;
			
			while(!productBlockFound) {
				if($(productBlock).hasClass('wc-block-product')) {
					productBlockFound = true;
				} else {
					productBlock = $(productBlock).parent();
				}
				
				count++;
				if(count > 100) {
					break;
				}
			}
			
			let addToCartButtonContainer = $(productBlock).children('.wc-block-components-product-button');
			let productInfo = $(addToCartButtonContainer).data('wc-context');
			let addToCartButton = $(addToCartButtonContainer).children('.wc-block-components-product-button__button');
			
			productInfo.quantityToAdd = quantity;
			//$(addToCartButton).attr('data-quantity', quantity);
			
			$(addToCartButtonContainer).data('wc-context', productInfo);
			
			//console.log($(addToCartButtonContainer).data('wc-context'));
			//console.log($(addToCartButton).data('quantity'));
		}
	});
});
