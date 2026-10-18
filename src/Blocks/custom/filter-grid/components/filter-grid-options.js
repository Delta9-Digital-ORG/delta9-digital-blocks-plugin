import React from 'react';
import { __ } from '@wordpress/i18n';
import { PanelBody, TextControl } from '@wordpress/components';
import { checkAttr, getAttrKey } from '@eightshift/frontend-libs/scripts';
import manifest from '../manifest.json';

export const FilterGridOptions = ({ attributes, setAttributes }) => {
	const text = (key, label, help) => (
		<TextControl
			label={label}
			help={help}
			value={checkAttr(key, attributes, manifest)}
			onChange={(value) => setAttributes({ [getAttrKey(key, attributes, manifest)]: value })}
		/>
	);

	return (
		<PanelBody title={__('Filter grid', 'delta9-digital-blocks-plugin')}>
			{text(
				'filterGridGroup',
				__('Filter group', 'delta9-digital-blocks-plugin'),
				__('Filter chips and Filter search blocks with the same group control this grid.', 'delta9-digital-blocks-plugin'),
			)}
			{text(
				'filterGridItemSelector',
				__('Item selector', 'delta9-digital-blocks-plugin'),
				__('CSS selector of the items to filter. Query Loop posts: .wp-block-post', 'delta9-digital-blocks-plugin'),
			)}
			{text('filterGridEmptyMessage', __('Message when nothing matches', 'delta9-digital-blocks-plugin'))}
			{text(
				'filterGridCountTemplate',
				__('Result count (screen readers)', 'delta9-digital-blocks-plugin'),
				__('%d is replaced with the number of visible items.', 'delta9-digital-blocks-plugin'),
			)}
		</PanelBody>
	);
};
