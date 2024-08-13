import React from 'react';
import { InspectorControls } from '@wordpress/block-editor';
import { ProductCountButtonEditor } from './components/product-count-button-editor';
import { ProductCountButtonOptions } from './components/product-count-button-options';

export const ProductCountButton = (props) => {
	return (
		<>
			<InspectorControls>
				<ProductCountButtonOptions {...props} />
			</InspectorControls>
			<ProductCountButtonEditor {...props} />
		</>
	);
};
