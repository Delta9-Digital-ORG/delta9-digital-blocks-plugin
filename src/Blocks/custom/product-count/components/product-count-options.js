import React from 'react';
import { __ } from '@wordpress/i18n';
import { PanelBody } from '@wordpress/components';
import { checkAttr, getAttrKey, getOption, props, OptionSelector, icons } from '@eightshift/frontend-libs/scripts';
import { ProductCountOptions as ProductCountOptionsComponent } from '../../../components/product-count/components/product-count-options';
import manifest from '../manifest.json';

export const ProductCountOptions = ({ attributes, setAttributes }) => {
	const productCountAlign = checkAttr('productCountAlign', attributes, manifest);

	return (
		<PanelBody title={__('Product Count', 'delta9-digital-blocks-plugin')}>
			<ProductCountOptionsComponent />
		</PanelBody>
	);
};
