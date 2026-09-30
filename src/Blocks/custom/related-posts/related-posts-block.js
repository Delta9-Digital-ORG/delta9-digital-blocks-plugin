import React from 'react';
import { __ } from '@wordpress/i18n';
import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody, RangeControl } from '@wordpress/components';
import { ServerSideRender, checkAttr, getAttrKey } from '@eightshift/frontend-libs/scripts';
import manifest from './manifest.json';

export const RelatedPosts = ({ attributes, setAttributes }) => {
	const { blockFullName } = attributes;

	return (
		<>
			<InspectorControls>
				<PanelBody title={__('Related posts', 'delta9-digital-blocks-plugin')}>
					<RangeControl
						label={__('Number of posts', 'delta9-digital-blocks-plugin')}
						value={checkAttr('relatedPostsCount', attributes, manifest)}
						onChange={(v) => setAttributes({ [getAttrKey('relatedPostsCount', attributes, manifest)]: v })}
						min={manifest.options.relatedPostsCount.min}
						max={manifest.options.relatedPostsCount.max}
					/>
				</PanelBody>
			</InspectorControls>
			<ServerSideRender block={blockFullName} attributes={{ ...attributes, relatedPostsServerSideRender: true }} />
		</>
	);
};
