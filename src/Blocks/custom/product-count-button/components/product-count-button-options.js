import React from 'react';
import { __ } from '@wordpress/i18n';
import { PanelBody } from '@wordpress/components';
import { checkAttr, getAttrKey, getOption, props, OptionSelector, icons } from '@eightshift/frontend-libs/scripts';
import { ProductCountButtonOptions as ProductCountButtonOptionsComponent } from '../../../components/product-count-button/components/product-count-button-options';
import manifest from '../manifest.json';

export const ProductCountButtonOptions = ({ attributes, setAttributes }) => {
	const productCountButtonAlign = checkAttr('productCountButtonAlign', attributes, manifest);

	return (
		<PanelBody title={__('Product Count Button', 'delta9-digital-blocks-plugin')}>
			<ProductCountButtonOptionsComponent
				{...props('product-count-button', attributes, { setAttributes })}

				additionalControls={
					<OptionSelector
						icon={icons.position3x3Empty}
						label={__('Alignment', 'delta9-digital-blocks-plugin')}
						value={productCountButtonAlign}
						options={getOption('productCountButtonAlign', attributes, manifest)}
						onChange={(value) => setAttributes({ [getAttrKey('productCountButtonAlign', attributes, manifest)]: value })}
						inlineLabel
						iconOnly
					/>
				}
				noLabel
				noUseToggle
				noExpandButton
			/>
		</PanelBody>
	);
};
