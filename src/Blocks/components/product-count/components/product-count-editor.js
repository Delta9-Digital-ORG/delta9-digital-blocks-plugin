import React from 'react';

export const ProductCountEditor = (attributes) => {
	const {
		blockFullName
	} = attributes;
	
	return (
		<div class="product-count">
			<div class="product-count-button product-count-decrease">-</div>
			<span class="product-count-quantity">1</span>
			<div class="product-count-button product-count-increase">+</div>
		</div>
	);
};