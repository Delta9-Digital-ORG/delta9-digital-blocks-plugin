import React from 'react';
import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, ToggleControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { ServerSideRender, checkAttr, getAttrKey } from '@eightshift/frontend-libs/scripts';
import manifest from './manifest.json';

export const InsightArchive = ({ attributes, setAttributes }) => {
	const { blockFullName } = attributes;
	const setAttr = (key, value) => setAttributes({ [getAttrKey(key, attributes, manifest)]: value });

	return (
		<>
			<InspectorControls>
				<PanelBody title={__('Insight archive', 'delta9-digital-blocks-plugin')}>
					<p>{__('Lists the posts of the page it is on (blog index, category, tag). The editor preview shows the latest four.', 'delta9-digital-blocks-plugin')}</p>
					<TextControl
						label={__('Panel label', 'delta9-digital-blocks-plugin')}
						value={checkAttr('insightArchiveLabel', attributes, manifest)}
						onChange={(v) => setAttr('insightArchiveLabel', v)}
					/>
					<ToggleControl
						label={__('Category filters', 'delta9-digital-blocks-plugin')}
						checked={checkAttr('insightArchiveShowFilters', attributes, manifest)}
						onChange={(v) => setAttr('insightArchiveShowFilters', v)}
					/>
				</PanelBody>
			</InspectorControls>
			<ServerSideRender block={blockFullName} attributes={{ ...attributes, insightArchiveServerSideRender: true }} />
		</>
	);
};
