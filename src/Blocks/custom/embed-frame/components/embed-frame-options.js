import React from 'react';
import { __ } from '@wordpress/i18n';
import { PanelBody, TextControl, ToggleControl } from '@wordpress/components';
import { checkAttr, getAttrKey, getOption, NumberPicker, Control, icons } from '@eightshift/frontend-libs/scripts';
import manifest from '../manifest.json';

export const EmbedFrameOptions = ({ attributes, setAttributes }) => {
	const attr = (name) => checkAttr(name, attributes, manifest);
	const set = (name) => (value) => setAttributes({ [getAttrKey(name, attributes, manifest)]: value });

	return (
		<>
			<PanelBody title={__('Embed frame', 'delta9-digital-blocks-plugin')}>
				<TextControl label={__('URL (https)', 'delta9-digital-blocks-plugin')} type='url' value={attr('embedFrameUrl')} onChange={set('embedFrameUrl')} />
				<TextControl label={__('Title (screen readers)', 'delta9-digital-blocks-plugin')} value={attr('embedFrameTitle')} onChange={set('embedFrameTitle')} />
				<Control icon={icons.height} label={__('Minimum height (px)', 'delta9-digital-blocks-plugin')} inlineLabel>
					<NumberPicker {...getOption('embedFrameMinHeight', attributes, manifest)} value={attr('embedFrameMinHeight')} onChange={set('embedFrameMinHeight')} noBottomSpacing fixedWidth={4} />
				</Control>
				<ToggleControl label={__('Lazy load', 'delta9-digital-blocks-plugin')} checked={attr('embedFrameLazy')} onChange={set('embedFrameLazy')} />
			</PanelBody>
			<PanelBody title={__('Messages from the app', 'delta9-digital-blocks-plugin')} initialOpen={false}>
				<TextControl
					label={__('Resize message type', 'delta9-digital-blocks-plugin')}
					help={__('The app posts { type, height } when its content changes size.', 'delta9-digital-blocks-plugin')}
					value={attr('embedFrameResizeMessage')}
					onChange={set('embedFrameResizeMessage')}
				/>
				<TextControl
					label={__('Analytics event message type (optional)', 'delta9-digital-blocks-plugin')}
					help={__('Messages { type, event, params } are pushed to window.dataLayer.', 'delta9-digital-blocks-plugin')}
					value={attr('embedFrameEventMessage')}
					onChange={set('embedFrameEventMessage')}
				/>
				<TextControl label={__('Event name prefix', 'delta9-digital-blocks-plugin')} value={attr('embedFrameEventPrefix')} onChange={set('embedFrameEventPrefix')} />
				<TextControl label={__('dataLayer key for params', 'delta9-digital-blocks-plugin')} value={attr('embedFrameEventKey')} onChange={set('embedFrameEventKey')} />
			</PanelBody>
		</>
	);
};
