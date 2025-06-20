import domReady from '@wordpress/dom-ready';

domReady(() => {
	jQuery('.block-product-count').each(function (){
		let decreaseBtn = $(this).children('.product-count').children('.product-count-decrease');
		let increaseBtn = $(this).children('.product-count').children('.product-count-increase');
		let quantityElm = $(this).children('.product-count').children('.product-count-quantity');
		
		$(decreaseBtn).on('click', function () {
			let quantity = $(quantityElm).html();
			
			if (quantity > 1) {
				quantity--;
				$(quantityElm).html(quantity)
				
				updateAddToCart(quantity, this);
			}
		});
		
		$(increaseBtn).on('click', function () {
			let quantity = $(quantityElm).html();
			
			quantity++;
			$(quantityElm).html(quantity)
			
			updateAddToCart(quantity, this);
		});
		
		
		function updateAddToCart(quantity, thisElm) {
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
			
			let addToCartButtonContainer;
			let addToCartButton;
			let isProductPage = false;
			
			if($('.product-template-default.single.single-product').length != 0) {
				isProductPage = true;
			}
			
			if (isProductPage) {
				addToCartButton = $('.block-product-count-button').children('.block-product-count-button__btn');
			} else if(productBlockFound) {
				$(productBlock).children().each(function() {
					if($(this).hasClass('wrapper')) {
						let productCountBlock = $(this).children('.wrapper__inner').children('.block-product-count-button');
						
						if (typeof productCountBlock != 'undefined') {
							addToCartButtonContainer = productCountBlock;
						}
					}
				});
				
				addToCartButton = $(addToCartButtonContainer).children('.block-product-count-button__btn');
			}
			
			$(addToCartButton).attr('data-product_quantity', quantity);
		}
	});
});
