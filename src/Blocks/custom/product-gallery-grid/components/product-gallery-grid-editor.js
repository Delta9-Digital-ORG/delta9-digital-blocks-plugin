import React from 'react';
import { __ } from '@wordpress/i18n';

// Editor preview — placeholder tiles in the same grid the frontend uses.
// The real images resolve server-side from the current product's WC
// gallery, and this block's main editing surface (the shared description/
// nutrition pattern) has no product context to fetch from.
export const ProductGalleryGridEditor = ({ attributes }) => {
	const count = Math.max(1, attributes.productGalleryGridMaxImages ?? 4);

	return (
		<div className='yb-product-gallery-grid' data-count={count}>
			{Array.from({ length: count }, (_, index) => (
				<figure key={index} className='yb-product-gallery-grid__item yb-product-gallery-grid__item--placeholder'>
					{index === 0 &&
						<span className='yb-product-gallery-grid__placeholderLabel'>
							{__('Product gallery photos', 'delta9-digital-blocks-plugin')}
						</span>
					}
				</figure>
			))}
		</div>
	);
};
