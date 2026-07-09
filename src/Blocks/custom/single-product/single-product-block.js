import React from 'react';
import { InspectorControls } from '@wordpress/block-editor';
import { SingleProductEditor } from './components/single-product-editor';
import { SingleProductOptions } from './components/single-product-options';

export const SingleProduct = (props) => {
	return (
		<>
			<InspectorControls>
				<SingleProductOptions {...props} />
			</InspectorControls>
			<SingleProductEditor {...props} />
		</>
	);
};
