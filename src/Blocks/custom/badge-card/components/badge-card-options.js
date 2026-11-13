import React from 'react';
import { __ } from '@wordpress/i18n';
import { PanelBody, SelectControl, TextControl } from '@wordpress/components';
import { checkAttr, getAttrKey } from '@eightshift/frontend-libs/scripts';
import manifest from '../manifest.json';
import { useParentVariant } from './use-parent-variant';

export const BadgeCardOptions = ({ attributes, setAttributes, clientId }) => {
	const variant = useParentVariant(clientId);
	const setAttr = (key, value) => setAttributes({ [getAttrKey(key, attributes, manifest)]: value });

	return (
		<PanelBody title={__('Badge card', 'delta9-digital-blocks-plugin')}>
			<SelectControl
				label={__('Accent', 'delta9-digital-blocks-plugin')}
				help={__('"By position" follows the grid\'s accent cycle.', 'delta9-digital-blocks-plugin')}
				value={checkAttr('badgeCardAccent', attributes, manifest)}
				options={manifest.options.badgeCardAccent}
				onChange={(v) => setAttr('badgeCardAccent', v)}
			/>
			{variant !== 'steps' && (
				<TextControl
					label={__('Link URL', 'delta9-digital-blocks-plugin')}
					type='url'
					value={checkAttr('badgeCardLinkUrl', attributes, manifest)}
					onChange={(v) => setAttr('badgeCardLinkUrl', v)}
				/>
			)}
		</PanelBody>
	);
};
