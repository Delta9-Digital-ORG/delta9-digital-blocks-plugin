import React from 'react';
import { __ } from '@wordpress/i18n';
import { checkAttr, selector } from '@eightshift/frontend-libs/scripts';
import manifest from '../manifest.json';

// The widget never loads in the editor — it only injects its floating launcher on the front end.
// Here we show a labelled placeholder so editors can see the block is present and configured.
export const ChatWidgetEditor = ({ attributes }) => {
	const { blockClass } = attributes;

	const chatWidgetScriptUrl = checkAttr('chatWidgetScriptUrl', attributes, manifest);

	const placeholderStyle = {
		display: 'flex',
		flexDirection: 'column',
		alignItems: 'center',
		justifyContent: 'center',
		gap: 8,
		border: '1px dashed currentColor',
		borderRadius: 8,
		padding: 24,
		textAlign: 'center',
	};

	return (
		<div className={blockClass}>
			<div
				className={selector(blockClass, blockClass, 'placeholder')}
				style={placeholderStyle}
			>
				<strong>{__('💬 Chat widget', 'delta9-digital-blocks-plugin')}</strong>
				<code>{chatWidgetScriptUrl || __('Set the widget script URL in the block settings.', 'delta9-digital-blocks-plugin')}</code>
				<small>{__('The floating chat launcher appears on the live site, not here.', 'delta9-digital-blocks-plugin')}</small>
			</div>
		</div>
	);
};
