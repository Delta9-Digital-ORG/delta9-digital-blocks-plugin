import React from 'react';
import { __ } from '@wordpress/i18n';
import { PanelBody, TextControl, ToggleControl } from '@wordpress/components';
import { checkAttr, getAttrKey } from '@eightshift/frontend-libs/scripts';
import manifest from '../manifest.json';

export const ChatWidgetOptions = ({ attributes, setAttributes }) => {
	const attr = (name) => checkAttr(name, attributes, manifest);
	const set = (name) => (value) => setAttributes({ [getAttrKey(name, attributes, manifest)]: value });

	return (
		<PanelBody title={__('Chat widget', 'delta9-digital-blocks-plugin')}>
			<TextControl
				label={__('Widget script URL (https)', 'delta9-digital-blocks-plugin')}
				help={__('The self-contained chat widget script, e.g. a concierge bot or tawk.to/Crisp embed.', 'delta9-digital-blocks-plugin')}
				type='url'
				value={attr('chatWidgetScriptUrl')}
				onChange={set('chatWidgetScriptUrl')}
			/>
			<ToggleControl
				label={__('Load asynchronously', 'delta9-digital-blocks-plugin')}
				help={__('Recommended. Turn off only if the widget requires synchronous loading.', 'delta9-digital-blocks-plugin')}
				checked={attr('chatWidgetAsync')}
				onChange={set('chatWidgetAsync')}
			/>
		</PanelBody>
	);
};
