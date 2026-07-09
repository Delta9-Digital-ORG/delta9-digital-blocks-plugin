import React from 'react';
import { __ } from '@wordpress/i18n';
import { PanelBody, TextControl, ToggleControl } from '@wordpress/components';

export const SingleProductOptions = ({ attributes, setAttributes }) => {
	const {
		singleProductBadgeUrl,
		singleProductBadgeAlt,
		singleProductServerSideRender,
	} = attributes;

	return (
		<PanelBody title={__('Single Product', 'delta9-digital-blocks-plugin')}>
			<TextControl
				label={__('Award badge image URL', 'delta9-digital-blocks-plugin')}
				value={singleProductBadgeUrl}
				onChange={(value) => setAttributes({ singleProductBadgeUrl: value })}
			/>
			<TextControl
				label={__('Award badge alt text', 'delta9-digital-blocks-plugin')}
				value={singleProductBadgeAlt}
				onChange={(value) => setAttributes({ singleProductBadgeAlt: value })}
			/>
			<ToggleControl
				label={__('Server-side render', 'delta9-digital-blocks-plugin')}
				checked={singleProductServerSideRender}
				onChange={(value) => setAttributes({ singleProductServerSideRender: value })}
				help={__('Uses PHP render and seeds the Interactivity API state on the server.', 'delta9-digital-blocks-plugin')}
			/>
		</PanelBody>
	);
};
