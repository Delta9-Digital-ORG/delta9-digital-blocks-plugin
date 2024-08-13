import React from 'react';
import { InspectorControls } from '@wordpress/block-editor';
import { ProductCountEditor } from './components/product-count-editor';
import { ProductCountOptions } from './components/product-count-options';

export const ProductCount = (props) => {
	return (
		<>
			<InspectorControls>
				<ProductCountOptions {...props} />
			</InspectorControls>
			<ProductCountEditor {...props} />
		</>
	);
};
