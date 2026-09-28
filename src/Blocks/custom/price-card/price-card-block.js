import React from 'react';
import { __ } from '@wordpress/i18n';
import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody, SelectControl, TextControl, TextareaControl, ToggleControl } from '@wordpress/components';
import { ServerSideRender, checkAttr, getAttrKey } from '@eightshift/frontend-libs/scripts';
import manifest from './manifest.json';

export const PriceCard = ({ attributes, setAttributes }) => {
	const { blockFullName } = attributes;
	const attr = (key) => checkAttr(key, attributes, manifest);
	const setAttr = (key, value) => setAttributes({ [getAttrKey(key, attributes, manifest)]: value });
	const text = (key, label, help) => <TextControl label={label} help={help} value={attr(key)} onChange={(v) => setAttr(key, v)} />;

	return (
		<>
			<InspectorControls>
				<PanelBody title={__('Price card', 'delta9-digital-blocks-plugin')}>
					{text('priceCardEyebrow', __('Eyebrow (packages)', 'delta9-digital-blocks-plugin'), __('e.g. Launch', 'delta9-digital-blocks-plugin'))}
					{text('priceCardPrice', __('Price', 'delta9-digital-blocks-plugin'), '$6,500')}
					{text('priceCardSuffix', __('Price suffix', 'delta9-digital-blocks-plugin'), __('from, one-time, /mo', 'delta9-digital-blocks-plugin'))}
					<ToggleControl
						label={__('Price in the accent colour', 'delta9-digital-blocks-plugin')}
						checked={attr('priceCardHighlight')}
						onChange={(v) => setAttr('priceCardHighlight', v)}
					/>
					{text('priceCardName', __('Name (retainers)', 'delta9-digital-blocks-plugin'), __('Shown under the price in the accent colour.', 'delta9-digital-blocks-plugin'))}
					<TextareaControl label={__('Description (retainers)', 'delta9-digital-blocks-plugin')} value={attr('priceCardBody')} onChange={(v) => setAttr('priceCardBody', v)} />
					<TextareaControl
						label={__('Included (packages, one per line)', 'delta9-digital-blocks-plugin')}
						value={attr('priceCardFeatures')}
						onChange={(v) => setAttr('priceCardFeatures', v)}
						rows={6}
					/>
					{text('priceCardLinkLabel', __('Button label', 'delta9-digital-blocks-plugin'))}
					{text('priceCardLinkUrl', __('Button URL', 'delta9-digital-blocks-plugin'))}
					<SelectControl
						label={__('Button style', 'delta9-digital-blocks-plugin')}
						value={attr('priceCardLinkStyle')}
						options={manifest.options.priceCardLinkStyle}
						onChange={(v) => setAttr('priceCardLinkStyle', v)}
					/>
					<SelectControl
						label={__('Accent', 'delta9-digital-blocks-plugin')}
						value={attr('priceCardAccent')}
						options={manifest.options.priceCardAccent}
						onChange={(v) => setAttr('priceCardAccent', v)}
					/>
				</PanelBody>
			</InspectorControls>
			<ServerSideRender block={blockFullName} attributes={{ ...attributes, priceCardServerSideRender: true }} />
		</>
	);
};
