import React from 'react';
import { __ } from '@wordpress/i18n';
import { checkAttr, selector } from '@eightshift/frontend-libs/scripts';
import manifest from '../manifest.json';

// The editor shows a placeholder at the minimum height instead of loading the app.
export const EmbedFrameEditor = ({ attributes }) => {
	const { blockClass } = attributes;

	const embedFrameUrl = checkAttr('embedFrameUrl', attributes, manifest);
	const embedFrameTitle = checkAttr('embedFrameTitle', attributes, manifest);
	const embedFrameMinHeight = checkAttr('embedFrameMinHeight', attributes, manifest);

	return (
		<div className={blockClass}>
			<div
				className={selector(blockClass, blockClass, 'placeholder')}
				style={{ minHeight: Math.min(embedFrameMinHeight, 480), display: 'flex', flexDirection: 'column', alignItems: 'center', justifyContent: 'center', gap: 8, border: '1px dashed currentColor', padding: 24, textAlign: 'center' }}
			>
				<strong>{embedFrameTitle || __('Embed frame', 'delta9-digital-blocks-plugin')}</strong>
				<code>{embedFrameUrl || __('Set the URL in the block settings.', 'delta9-digital-blocks-plugin')}</code>
			</div>
		</div>
	);
};
