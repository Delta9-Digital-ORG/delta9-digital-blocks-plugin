import React from 'react';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { PanelBody, TextControl, Button } from '@wordpress/components';
import {
	icons,
	getAttrKey,
	IconToggle,
	checkAttr,
	getOption,
	unescapeHTML,
	getFetchWpApi,
	Section,
	Select,
	AsyncMultiSelect,
	OptionSelector,
} from '@eightshift/frontend-libs/scripts';
import manifest from '../manifest.json';

export const BundlePickerOptions = ({ attributes, setAttributes }) => {
	const bundlePickerCategory = checkAttr('bundlePickerCategory', attributes, manifest);
	const bundlePickerProductIds = checkAttr('bundlePickerProductIds', attributes, manifest) ?? [];
	const bundlePickerTiers = checkAttr('bundlePickerTiers', attributes, manifest) ?? [];
	const bundlePickerHeading = checkAttr('bundlePickerHeading', attributes, manifest);
	const bundlePickerSubheading = checkAttr('bundlePickerSubheading', attributes, manifest);
	const bundlePickerColumns = checkAttr('bundlePickerColumns', attributes, manifest);
	const bundlePickerShowProgress = checkAttr('bundlePickerShowProgress', attributes, manifest);

	const [useSpecificProducts, setUseSpecificProducts] = useState(bundlePickerProductIds?.length > 0);

	const columnOptions = getOption('bundlePickerColumns', attributes, manifest);

	return (
		<PanelBody title={__('Bundle Picker', 'delta9-digital-blocks-plugin')}>

			<Section icon={icons.textH} label={__('Content', 'delta9-digital-blocks-plugin')}>
				<TextControl
					label={__('Heading', 'delta9-digital-blocks-plugin')}
					value={bundlePickerHeading}
					onChange={(value) => setAttributes({ [getAttrKey('bundlePickerHeading', attributes, manifest)]: value })}
				/>
				<TextControl
					label={__('Subheading', 'delta9-digital-blocks-plugin')}
					value={bundlePickerSubheading}
					onChange={(value) => setAttributes({ [getAttrKey('bundlePickerSubheading', attributes, manifest)]: value })}
				/>
			</Section>

			<Section icon={icons.filter} label={__('Product Source', 'delta9-digital-blocks-plugin')}>
				<AsyncMultiSelect
					label={__('Category', 'delta9-digital-blocks-plugin')}
					help={__('Select a product category to display.', 'delta9-digital-blocks-plugin')}
					value={bundlePickerCategory ? [bundlePickerCategory] : []}
					loadOptions={getFetchWpApi('products/categories', {
						fields: 'id,name',
						processId: ({ id }) => id,
						processLabel: ({ name }) => unescapeHTML(name),
						noCache: true,
					})}
					onChange={(value) => setAttributes({
						[getAttrKey('bundlePickerCategory', attributes, manifest)]: value?.length > 0 ? value[0] : undefined,
					})}
					reFetchOnSearch
				/>

				<IconToggle
					icon={icons.itemSelect}
					label={__('Select specific products', 'delta9-digital-blocks-plugin')}
					checked={useSpecificProducts}
					onChange={() => {
						setUseSpecificProducts(!useSpecificProducts);
						setAttributes({
							[getAttrKey('bundlePickerProductIds', attributes, manifest)]: undefined,
						});
					}}
					reducedBottomSpacing={useSpecificProducts}
				/>

				{useSpecificProducts &&
					<AsyncMultiSelect
						help={__('Search and select products to include in the bundle.', 'delta9-digital-blocks-plugin')}
						value={bundlePickerProductIds}
						loadOptions={getFetchWpApi('products', {
							fields: 'id,name',
							processId: ({ id }) => id,
							processLabel: ({ name }) => unescapeHTML(name),
							noCache: true,
						})}
						onChange={(value) => setAttributes({ [getAttrKey('bundlePickerProductIds', attributes, manifest)]: value })}
						reFetchOnSearch
					/>
				}
			</Section>

			<Section icon={icons.layoutAlt3} label={__('Layout', 'delta9-digital-blocks-plugin')}>
				<OptionSelector
					label={__('Columns', 'delta9-digital-blocks-plugin')}
					value={bundlePickerColumns}
					options={columnOptions}
					onChange={(value) => setAttributes({ [getAttrKey('bundlePickerColumns', attributes, manifest)]: value })}
				/>
			</Section>

			<Section icon={icons.percent} label={__('Discount Tiers', 'delta9-digital-blocks-plugin')}>
				{bundlePickerTiers.map((tier, index) => (
					<div key={index} style={{ display: 'flex', gap: '8px', alignItems: 'flex-end', marginBottom: '8px' }}>
						<TextControl
							label={index === 0 ? __('Min Qty', 'delta9-digital-blocks-plugin') : ''}
							type="number"
							value={tier.min}
							onChange={(value) => {
								const newTiers = [...bundlePickerTiers];
								newTiers[index] = { ...newTiers[index], min: parseInt(value, 10) };
								setAttributes({ [getAttrKey('bundlePickerTiers', attributes, manifest)]: newTiers });
							}}
							style={{ width: '70px' }}
						/>
						<TextControl
							label={index === 0 ? __('Discount %', 'delta9-digital-blocks-plugin') : ''}
							type="number"
							value={tier.pct}
							onChange={(value) => {
								const newTiers = [...bundlePickerTiers];
								newTiers[index] = { ...newTiers[index], pct: parseInt(value, 10) };
								setAttributes({ [getAttrKey('bundlePickerTiers', attributes, manifest)]: newTiers });
							}}
							style={{ width: '80px' }}
						/>
						<TextControl
							label={index === 0 ? __('Label', 'delta9-digital-blocks-plugin') : ''}
							value={tier.label}
							onChange={(value) => {
								const newTiers = [...bundlePickerTiers];
								newTiers[index] = { ...newTiers[index], label: value };
								setAttributes({ [getAttrKey('bundlePickerTiers', attributes, manifest)]: newTiers });
							}}
						/>
						<Button
							isDestructive
							isSmall
							icon="no-alt"
							onClick={() => {
								const newTiers = bundlePickerTiers.filter((_, i) => i !== index);
								setAttributes({ [getAttrKey('bundlePickerTiers', attributes, manifest)]: newTiers });
							}}
						/>
					</div>
				))}
				<Button
					isSecondary
					isSmall
					onClick={() => {
						const newTiers = [...bundlePickerTiers, { min: 1, pct: 5, label: '' }];
						setAttributes({ [getAttrKey('bundlePickerTiers', attributes, manifest)]: newTiers });
					}}
				>
					{__('Add Tier', 'delta9-digital-blocks-plugin')}
				</Button>
			</Section>

			<Section icon={icons.moreH} label={__('Other', 'delta9-digital-blocks-plugin')} noBottomSpacing>
				<IconToggle
					icon={icons.checkCircle}
					label={__('Show progress bar', 'delta9-digital-blocks-plugin')}
					help={__('Shows how close the customer is to the next discount tier.', 'delta9-digital-blocks-plugin')}
					checked={bundlePickerShowProgress}
					onChange={(value) => setAttributes({ [getAttrKey('bundlePickerShowProgress', attributes, manifest)]: value })}
					inlineHelp
				/>
			</Section>
		</PanelBody>
	);
};
