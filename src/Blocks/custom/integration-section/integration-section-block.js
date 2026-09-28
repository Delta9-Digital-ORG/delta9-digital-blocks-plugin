import React from 'react';
import { __ } from '@wordpress/i18n';
import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody, SelectControl, TextControl, TextareaControl } from '@wordpress/components';
import { ServerSideRender, checkAttr, getAttrKey } from '@eightshift/frontend-libs/scripts';
import manifest from './manifest.json';

export const IntegrationSection = ({ attributes, setAttributes }) => {
	const { blockFullName } = attributes;
	const setAttr = (key, value) => setAttributes({ [getAttrKey(key, attributes, manifest)]: value });

	return (
		<>
			<InspectorControls>
				<PanelBody title={__('Integration section', 'delta9-digital-blocks-plugin')}>
					<p>{__('Content comes from the integration post (Integration details panel, excerpt, layer).', 'delta9-digital-blocks-plugin')}</p>
					<SelectControl
						label={__('Section', 'delta9-digital-blocks-plugin')}
						value={checkAttr('integrationSectionSection', attributes, manifest)}
						options={manifest.options.integrationSectionSection}
						onChange={(v) => setAttr('integrationSectionSection', v)}
					/>
					<TextControl
						label={__('Title override', 'delta9-digital-blocks-plugin')}
						help={__('Leave empty for the default. Not used by the hero.', 'delta9-digital-blocks-plugin')}
						value={checkAttr('integrationSectionTitle', attributes, manifest)}
						onChange={(v) => setAttr('integrationSectionTitle', v)}
					/>
					<TextareaControl
						label={__('Lead override', 'delta9-digital-blocks-plugin')}
						value={checkAttr('integrationSectionLead', attributes, manifest)}
						onChange={(v) => setAttr('integrationSectionLead', v)}
					/>
				</PanelBody>
			</InspectorControls>
			<ServerSideRender block={blockFullName} attributes={{ ...attributes, integrationSectionServerSideRender: true }} />
		</>
	);
};
