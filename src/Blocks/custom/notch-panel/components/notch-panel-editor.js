import React from 'react';
import { __ } from '@wordpress/i18n';
import { RichText, useInnerBlocksProps } from '@wordpress/block-editor';
import { BlockInserter, checkAttr, getAttrKey, classnames } from '@eightshift/frontend-libs/scripts';
import manifest from '../manifest.json';

export const NotchPanelEditor = ({ attributes, setAttributes, clientId }) => {
	const { blockClass } = attributes;

	const pillText = checkAttr('notchPanelPillText', attributes, manifest);
	const pillSide = checkAttr('notchPanelPillSide', attributes, manifest);
	const tone = checkAttr('notchPanelTone', attributes, manifest);
	const glow = checkAttr('notchPanelGlow', attributes, manifest);
	const allowedBlocks = checkAttr('notchPanelAllowedBlocks', attributes, manifest);

	const innerBlocksProps = useInnerBlocksProps(
		{ className: 'd9-notch__inner' },
		{
			allowedBlocks,
			renderAppender: () => <BlockInserter clientId={clientId} className='es-mb-4' hasLabel />,
		},
	);

	const panelClass = classnames(
		blockClass,
		'd9-notch',
		`d9-notch--${pillSide}`,
		`d9-notch--${tone}`,
		glow && 'd9-notch--glow',
	);

	return (
		<section className={panelClass}>
			<span className='d9-notch__cut' aria-hidden='true' />
			<span className='d9-notch__fillet d9-notch__fillet--x' aria-hidden='true' />
			<span className='d9-notch__fillet d9-notch__fillet--y' aria-hidden='true' />
			<RichText
				tagName='span'
				className='d9-notch__pill'
				placeholder={__('Pill text', 'delta9-digital-blocks-plugin')}
				value={pillText}
				onChange={(v) => setAttributes({ [getAttrKey('notchPanelPillText', attributes, manifest)]: v })}
				allowedFormats={[]}
			/>
			<div {...innerBlocksProps} />
		</section>
	);
};
