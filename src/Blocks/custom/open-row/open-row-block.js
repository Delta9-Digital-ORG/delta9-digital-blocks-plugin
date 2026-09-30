import React from 'react';
import { __ } from '@wordpress/i18n';
import { RichText } from '@wordpress/block-editor';
import { checkAttr, getAttrKey } from '@eightshift/frontend-libs/scripts';
import manifest from './manifest.json';

export const OpenRow = ({ attributes, setAttributes }) => {
	const set = (key) => (value) => setAttributes({ [getAttrKey(key, attributes, manifest)]: value });

	return (
		<div className='d9-open-row'>
			<RichText
				tagName='div'
				className='d9-open-row__question'
				placeholder={__('Question', 'delta9-digital-blocks-plugin')}
				value={checkAttr('openRowQuestion', attributes, manifest)}
				onChange={set('openRowQuestion')}
				allowedFormats={[]}
			/>
			<RichText
				tagName='div'
				className='d9-open-row__answer'
				placeholder={__('Answer', 'delta9-digital-blocks-plugin')}
				value={checkAttr('openRowAnswer', attributes, manifest)}
				onChange={set('openRowAnswer')}
				allowedFormats={['core/bold', 'core/italic', 'core/link']}
			/>
		</div>
	);
};
