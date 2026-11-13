import React from 'react';
import { __ } from '@wordpress/i18n';
import { InnerBlocks, RichText } from '@wordpress/block-editor';
import { checkAttr, getAttrKey, selector } from '@eightshift/frontend-libs/scripts';
import manifest from '../manifest.json';

export const TabEditor = ({ attributes, setAttributes }) => {
	const { blockClass } = attributes;

	const tabLabel = checkAttr('tabLabel', attributes, manifest);
	const tabIconUrl = checkAttr('tabIconUrl', attributes, manifest);

	return (
		<div className={blockClass}>
			<div className={selector(blockClass, blockClass, 'editor-label')}>
				{tabIconUrl && <img src={tabIconUrl} alt='' width={24} height={24} />}
				<RichText
					tagName='strong'
					placeholder={__('Tab label', 'delta9-digital-blocks-plugin')}
					value={tabLabel}
					onChange={(value) => setAttributes({ [getAttrKey('tabLabel', attributes, manifest)]: value })}
					allowedFormats={[]}
					withoutInteractiveFormatting
				/>
			</div>

			<InnerBlocks />
		</div>
	);
};
