import React from 'react';
import { __ } from '@wordpress/i18n';
import { PanelBody, TextControl } from '@wordpress/components';
import { checkAttr, getAttrKey } from '@eightshift/frontend-libs/scripts';
import manifest from '../manifest.json';

export const FilterSearchOptions = ({ attributes, setAttributes }) => {
	const text = (key, label, help) => (
		<TextControl
			label={label}
			help={help}
			value={checkAttr(key, attributes, manifest)}
			onChange={(value) => setAttributes({ [getAttrKey(key, attributes, manifest)]: value })}
		/>
	);

	return (
		<PanelBody title={__('Filter search', 'delta9-digital-blocks-plugin')}>
			{text('filterSearchGroup', __('Filter group', 'delta9-digital-blocks-plugin'), __('Must match the Filter grid it controls.', 'delta9-digital-blocks-plugin'))}
			{text('filterSearchLabel', __('Label (screen readers)', 'delta9-digital-blocks-plugin'))}
			{text('filterSearchPlaceholder', __('Placeholder', 'delta9-digital-blocks-plugin'))}
			{text(
				'filterSearchUrlParam',
				__('URL parameter (optional)', 'delta9-digital-blocks-plugin'),
				__('Keeps the search in the URL, e.g. "q".', 'delta9-digital-blocks-plugin'),
			)}
		</PanelBody>
	);
};
