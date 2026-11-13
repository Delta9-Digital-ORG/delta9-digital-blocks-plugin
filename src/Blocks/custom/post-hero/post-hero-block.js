import React from 'react';
import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody, ToggleControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { ServerSideRender, checkAttr, getAttrKey } from '@eightshift/frontend-libs/scripts';
import manifest from './manifest.json';

export const PostHero = ({ attributes, setAttributes }) => {
	const { blockFullName } = attributes;

	return (
		<>
			<InspectorControls>
				<PanelBody title={__('Post hero', 'delta9-digital-blocks-plugin')}>
					<p>{__('Title, category, tags, date, author and featured image come from the post.', 'delta9-digital-blocks-plugin')}</p>
					<ToggleControl
						label={__('Share menu', 'delta9-digital-blocks-plugin')}
						checked={checkAttr('postHeroShowShare', attributes, manifest)}
						onChange={(v) => setAttributes({ [getAttrKey('postHeroShowShare', attributes, manifest)]: v })}
					/>
				</PanelBody>
			</InspectorControls>
			<ServerSideRender block={blockFullName} attributes={{ ...attributes, postHeroServerSideRender: true }} />
		</>
	);
};
