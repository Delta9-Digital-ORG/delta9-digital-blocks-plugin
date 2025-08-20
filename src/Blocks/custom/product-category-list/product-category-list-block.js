import React from 'react';
import { InspectorControls } from '@wordpress/block-editor';
import { ProductCategoryListEditor } from './components/product-category-list-editor';
import { ProductCategoryListOptions } from './components/product-category-list-options';

export const ProductCategoryList = (props) => {
	return (
		<>
			<InspectorControls>
				<ProductCategoryListOptions {...props} />
			</InspectorControls>
			<ProductCategoryListEditor {...props} />
		</>
	);
};
