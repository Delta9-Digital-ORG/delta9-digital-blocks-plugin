import React from 'react';
import { __ } from '@wordpress/i18n';
import { RangeControl } from '@wordpress/components';
import {
	icons,
	IconToggle,
	Section,
	checkAttr,
	getAttrKey,
	Select,
} from '@eightshift/frontend-libs/scripts';
import manifest from './../manifest.json';

export const ProductTopBenefitsOptions = ({ attributes, setAttributes }) => {

	const productTopBenefitsBorder = checkAttr('productTopBenefitsBorder', attributes, manifest);
	const productTopBenefitsBorderThick = checkAttr('productTopBenefitsBorderThick', attributes, manifest);
	const productTopBenefitsMax = checkAttr('productTopBenefitsMax', attributes, manifest);
	const productTopBenefitsFormat = checkAttr('productTopBenefitsFormat', attributes, manifest);

	return (
		<>
			<Section icon={icons.tools} label={__('Product Benefits', 'delta9-digital-blocks-plugin')} >
				<IconToggle
					icon={icons.width}
					label={__('Show Border', 'delta9-digital-blocks-plugin')}
					checked={productTopBenefitsBorder}
					onChange={(value) => setAttributes({ [getAttrKey('productTopBenefitsBorder', attributes, manifest)]: value })}
				/>

				{productTopBenefitsBorder && (
					<IconToggle
						icon={icons.width}
						label={__('Show Thick Border', 'delta9-digital-blocks-plugin')}
						checked={productTopBenefitsBorderThick}
						onChange={(value) => setAttributes({ [getAttrKey('productTopBenefitsBorderThick', attributes, manifest)]: value })}
					/>
				)}

				<RangeControl
					label={__('Max Shown', 'delta9-digital-blocks-plugin')}
					value={productTopBenefitsMax}
					onChange={(value) => setAttributes({ [getAttrKey('productTopBenefitsMax', attributes, manifest)]: value })}
					min={1}
					max={8}
					allowReset
					resetFallbackValue={6}
				/>
			</Section>

			<Section icon={icons.tools} label={__('Display Format', 'delta9-digital-blocks-plugin')} >
				<Select
					label={__('Display Format', 'delta9-digital-blocks-plugin')}
					value={productTopBenefitsFormat}
					options={manifest.allowed.displayFormat}
					onChange={(value) => setAttributes({ [getAttrKey('productTopBenefitsFormat', attributes, manifest)]: value })}
				/>
			</Section>
		</>
	);
};
