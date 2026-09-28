import React from 'react';
import { __, sprintf } from '@wordpress/i18n';
import { InspectorControls } from '@wordpress/block-editor';
import { Button, PanelBody, SelectControl, TextControl, TextareaControl } from '@wordpress/components';
import { ServerSideRender, checkAttr, getAttrKey } from '@eightshift/frontend-libs/scripts';
import manifest from './manifest.json';

const TITLED = ['layers', 'band', 'table', 'grid'];
const LEADED = ['layers', 'band', 'grid'];
const PICKS = ['ribbon', 'band', 'grid'];

export const IntegrationsHub = ({ attributes, setAttributes }) => {
	const { blockFullName } = attributes;
	const setAttr = (key, value) => setAttributes({ [getAttrKey(key, attributes, manifest)]: value });

	const section = checkAttr('integrationsHubSection', attributes, manifest);
	const layers = checkAttr('integrationsHubLayers', attributes, manifest) || [];
	const platforms = checkAttr('integrationsHubPlatforms', attributes, manifest) || [];
	const setLayer = (i, patch) => setAttr('integrationsHubLayers', layers.map((row, n) => (n === i ? { ...row, ...patch } : row)));

	return (
		<>
			<InspectorControls>
				<PanelBody title={__('Integrations hub section', 'delta9-digital-blocks-plugin')}>
					<p>{__('Platforms come from the Integration posts (logo, accent, layer, "What it powers", status).', 'delta9-digital-blocks-plugin')}</p>
					<SelectControl
						label={__('Section', 'delta9-digital-blocks-plugin')}
						value={section}
						options={manifest.options.integrationsHubSection}
						onChange={(v) => setAttr('integrationsHubSection', v)}
					/>
					{TITLED.includes(section) && (
						<TextControl
							label={section === 'band' ? __('Card text override', 'delta9-digital-blocks-plugin') : __('Title override', 'delta9-digital-blocks-plugin')}
							help={__('Leave empty for the default.', 'delta9-digital-blocks-plugin')}
							value={checkAttr('integrationsHubTitle', attributes, manifest)}
							onChange={(v) => setAttr('integrationsHubTitle', v)}
						/>
					)}
					{LEADED.includes(section) && (
						<TextareaControl
							label={section === 'band' ? __('Card eyebrow override', 'delta9-digital-blocks-plugin') : __('Lead override', 'delta9-digital-blocks-plugin')}
							value={checkAttr('integrationsHubLead', attributes, manifest)}
							onChange={(v) => setAttr('integrationsHubLead', v)}
						/>
					)}
					{PICKS.includes(section) && (
						<TextControl
							label={__('Platforms (slugs, comma separated)', 'delta9-digital-blocks-plugin')}
							help={__('Leave empty for the default set, in hub order.', 'delta9-digital-blocks-plugin')}
							value={platforms.join(', ')}
							onChange={(v) => setAttr('integrationsHubPlatforms', v.split(',').map((s) => s.trim()).filter(Boolean))}
						/>
					)}
					{section === 'layers' && (
						<>
							<TextControl
								label={__('Button label', 'delta9-digital-blocks-plugin')}
								value={checkAttr('integrationsHubCtaLabel', attributes, manifest)}
								onChange={(v) => setAttr('integrationsHubCtaLabel', v)}
							/>
							<TextControl
								label={__('Button URL', 'delta9-digital-blocks-plugin')}
								value={checkAttr('integrationsHubCtaUrl', attributes, manifest)}
								onChange={(v) => setAttr('integrationsHubCtaUrl', v)}
							/>
						</>
					)}
				</PanelBody>
				{section === 'layers' && (
					<PanelBody title={__('Layer rows', 'delta9-digital-blocks-plugin')} initialOpen={false}>
						{layers.map((row, i) => (
							<div key={i} style={{ borderTop: '1px solid #ddd', paddingTop: 12, marginBottom: 12 }}>
								{/* translators: %d: row number. */}
								<strong>{sprintf(__('Row %d', 'delta9-digital-blocks-plugin'), i + 1)}</strong>
								<TextControl label={__('Title', 'delta9-digital-blocks-plugin')} value={row.title || ''} onChange={(v) => setLayer(i, { title: v })} />
								<TextareaControl label={__('Text', 'delta9-digital-blocks-plugin')} value={row.text || ''} onChange={(v) => setLayer(i, { text: v })} />
								<TextControl
									label={__('Chips (comma separated)', 'delta9-digital-blocks-plugin')}
									help={__('Chips matching an integration title link to its page.', 'delta9-digital-blocks-plugin')}
									value={row.chips || ''}
									onChange={(v) => setLayer(i, { chips: v })}
								/>
								<SelectControl
									label={__('Accent', 'delta9-digital-blocks-plugin')}
									value={row.accent || 'mint'}
									options={manifest.options.integrationsHubAccent}
									onChange={(v) => setLayer(i, { accent: v })}
								/>
								<Button isDestructive variant='link' onClick={() => setAttr('integrationsHubLayers', layers.filter((_, n) => n !== i))}>
									{__('Remove row', 'delta9-digital-blocks-plugin')}
								</Button>
							</div>
						))}
						<Button variant='secondary' onClick={() => setAttr('integrationsHubLayers', [...layers, { title: '', text: '', chips: '', accent: 'mint' }])}>
							{__('Add row', 'delta9-digital-blocks-plugin')}
						</Button>
					</PanelBody>
				)}
			</InspectorControls>
			<ServerSideRender block={blockFullName} attributes={{ ...attributes, integrationsHubServerSideRender: true }} />
		</>
	);
};
