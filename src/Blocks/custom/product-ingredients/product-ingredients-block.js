import React from 'react';
import { InspectorControls } from '@wordpress/block-editor';
import { ProductCategoryEditor } from './components/product-ingredients-editor';
import { ProductCategoryOptions } from './components/product-ingredients-options';

export const ProductCategory = (props) => {
	return (
		<>
			<InspectorControls>
				<ProductCategoryOptions {...props} />
			</InspectorControls>
			<ProductCategoryEditor {...props} />
		</>
	);
};
