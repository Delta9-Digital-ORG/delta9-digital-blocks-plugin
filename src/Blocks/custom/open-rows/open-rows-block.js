import React from 'react';
import { __ } from '@wordpress/i18n';
import { InspectorControls, useInnerBlocksProps } from '@wordpress/block-editor';
import { PanelBody, TextControl } from '@wordpress/components';
import { BlockInserter, checkAttr, getAttrKey, classnames } from '@eightshift/frontend-libs/scripts';
import manifest from './manifest.json';

export const OpenRows = ({ attributes, setAttributes, clientId }) => {
	const { blockClass } = attributes;
	const label = checkAttr('openRowsLabel', attributes, manifest);

	const innerBlocksProps = useInnerBlocksProps({ className: 'd9-open-rows__list' }, {
		allowedBlocks: checkAttr('openRowsAllowedBlocks', attributes, manifest),
		template: [['eightshift-boilerplate/open-row'], ['eightshift-boilerplate/open-row']],
		renderAppender: () => <BlockInserter clientId={clientId} hasLabel />,
	});

	return (
		<>
			<InspectorControls>
				<PanelBody title={__('Open rows', 'delta9-digital-blocks-plugin')}>
					<TextControl
						label={__('Pill label', 'delta9-digital-blocks-plugin')}
						value={label}
						onChange={(v) => setAttributes({ [getAttrKey('openRowsLabel', attributes, manifest)]: v })}
					/>
				</PanelBody>
			</InspectorControls>
			<div className={classnames(blockClass, 'd9-open-rows')}>
				<span className='d9-open-rows__cut' aria-hidden='true' />
				<span className='d9-open-rows__fillet d9-open-rows__fillet--x' aria-hidden='true' />
				<span className='d9-open-rows__fillet d9-open-rows__fillet--y' aria-hidden='true' />
				{label && <span className='d9-open-rows__pill'>{label}</span>}
				<div {...innerBlocksProps} />
			</div>
		</>
	);
};
