import React from 'react';
import { __ } from '@wordpress/i18n';
import { PanelBody, TextControl } from '@wordpress/components';
import { checkAttr, getAttrKey } from '@eightshift/frontend-libs/scripts';
import manifest from '../manifest.json';

export const TabsOptions = ({ attributes, setAttributes }) => {
	const tabsAriaLabel = checkAttr('tabsAriaLabel', attributes, manifest);

	return (
		<PanelBody title={__('Tabs', 'delta9-digital-blocks-plugin')}>
			<TextControl
				label={__('Tab list label (screen readers)', 'delta9-digital-blocks-plugin')}
				help={__('Describes the set of tabs, e.g. "Compliance".', 'delta9-digital-blocks-plugin')}
				value={tabsAriaLabel}
				onChange={(value) => setAttributes({ [getAttrKey('tabsAriaLabel', attributes, manifest)]: value })}
			/>
		</PanelBody>
	);
};
