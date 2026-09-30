import React from 'react';
import { __ } from '@wordpress/i18n';
import { PanelBody, TextControl, SelectControl, ToggleControl } from '@wordpress/components';
import { checkAttr, getAttrKey } from '@eightshift/frontend-libs/scripts';
import manifest from '../manifest.json';

export const NotchPanelOptions = ({ attributes, setAttributes }) => {
	const pillText = checkAttr('notchPanelPillText', attributes, manifest);
	const pillSide = checkAttr('notchPanelPillSide', attributes, manifest);
	const tone = checkAttr('notchPanelTone', attributes, manifest);
	const glow = checkAttr('notchPanelGlow', attributes, manifest);

	const setAttr = (key, value) => setAttributes({ [getAttrKey(key, attributes, manifest)]: value });

	return (
		<PanelBody title={__('Notch panel', 'delta9-digital-blocks-plugin')}>
			<TextControl
				label={__('Pill text', 'delta9-digital-blocks-plugin')}
				value={pillText}
				onChange={(v) => setAttr('notchPanelPillText', v)}
			/>
			<TextControl
				label={__('Pill link', 'delta9-digital-blocks-plugin')}
				help={__('Optional. Turns the pill into a link, e.g. /services/.', 'delta9-digital-blocks-plugin')}
				value={checkAttr('notchPanelPillHref', attributes, manifest)}
				onChange={(v) => setAttr('notchPanelPillHref', v)}
			/>
			<SelectControl
				label={__('Notch corner', 'delta9-digital-blocks-plugin')}
				value={pillSide}
				options={manifest.options.notchPanelPillSide}
				onChange={(v) => setAttr('notchPanelPillSide', v)}
			/>
			<SelectControl
				label={__('Tone', 'delta9-digital-blocks-plugin')}
				value={tone}
				options={manifest.options.notchPanelTone}
				onChange={(v) => setAttr('notchPanelTone', v)}
			/>
			<ToggleControl
				label={__('Glow', 'delta9-digital-blocks-plugin')}
				checked={glow}
				onChange={(v) => setAttr('notchPanelGlow', v)}
			/>
		</PanelBody>
	);
};
