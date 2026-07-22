import React from 'react';
import { InspectorControls } from '@wordpress/block-editor';
import { ProductTopBenefitsEditor } from './components/product-top-benefits-editor';
import { ProductTopBenefitsOptions } from './components/product-top-benefits-options';

export const ProductTopBenefits = (props) => {
	return (
		<>
			<InspectorControls>
				<ProductTopBenefitsOptions {...props} />
			</InspectorControls>
			<ProductTopBenefitsEditor {...props} />
		</>
	);
};
