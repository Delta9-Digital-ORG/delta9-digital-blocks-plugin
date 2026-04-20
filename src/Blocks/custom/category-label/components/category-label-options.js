import React from 'react';
import { __ } from '@wordpress/i18n';
import {
	icons,
	Section,
	checkAttr,
	getAttrKey,
	Select,
} from '@eightshift/frontend-libs/scripts';
import { TextControl } from '@wordpress/components';
import manifest from './../manifest.json';

export const CategoryLabelOptions = ({ attributes, setAttributes }) => {

	const categoryLabelSource = checkAttr('categoryLabelSource', attributes, manifest);
	const categoryLabelParentSlug = checkAttr('categoryLabelParentSlug', attributes, manifest);
	const categoryLabelFormat = checkAttr('categoryLabelFormat', attributes, manifest);
	const categoryLabelSeparator = checkAttr('categoryLabelSeparator', attributes, manifest);
	const categoryLabelTransform = checkAttr('categoryLabelTransform', attributes, manifest);

	return (
		<>
			<Section icon={icons.tools} label={__('Source', 'delta9-digital-blocks-plugin')}>
				<Select
					label={__('Data Source', 'delta9-digital-blocks-plugin')}
					value={categoryLabelSource}
					options={manifest.allowed.sources}
					onChange={(value) => setAttributes({ [getAttrKey('categoryLabelSource', attributes, manifest)]: value })}
				/>
			</Section>

			{categoryLabelSource?.value === 'product' && (
				<Section icon={icons.tools} label={__('Parent Category', 'delta9-digital-blocks-plugin')}>
					<Select
						label={__('Filter by Parent', 'delta9-digital-blocks-plugin')}
						value={categoryLabelParentSlug}
						options={manifest.allowed.parentSlugs}
						onChange={(value) => setAttributes({ [getAttrKey('categoryLabelParentSlug', attributes, manifest)]: value })}
					/>
				</Section>
			)}

			<Section icon={icons.tools} label={__('Display Format', 'delta9-digital-blocks-plugin')}>
				<Select
					label={__('Name Order', 'delta9-digital-blocks-plugin')}
					value={categoryLabelFormat}
					options={manifest.allowed.formats}
					onChange={(value) => setAttributes({ [getAttrKey('categoryLabelFormat', attributes, manifest)]: value })}
				/>

				<TextControl
					label={__('Separator', 'delta9-digital-blocks-plugin')}
					value={categoryLabelSeparator}
					onChange={(value) => setAttributes({ [getAttrKey('categoryLabelSeparator', attributes, manifest)]: value })}
					help={__('Character(s) between parent and child names (default: space)', 'delta9-digital-blocks-plugin')}
				/>

				<Select
					label={__('Text Transform', 'delta9-digital-blocks-plugin')}
					value={categoryLabelTransform}
					options={manifest.allowed.transforms}
					onChange={(value) => setAttributes({ [getAttrKey('categoryLabelTransform', attributes, manifest)]: value })}
				/>
			</Section>
		</>
	);
};
