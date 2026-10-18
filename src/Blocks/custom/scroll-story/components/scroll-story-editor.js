import React from 'react';
import { BlockInserter, classnames } from '@eightshift/frontend-libs/scripts';
import { useBlockProps, useInnerBlocksProps } from '@wordpress/block-editor';

const NS = 'eightshift-boilerplate';

// In the editor the panel shows once at the top and the steps stack below it.
export const ScrollStoryEditor = ({ attributes, clientId }) => {
	const { blockClass } = attributes;

	const blockProps = useBlockProps();
	const innerBlocksProps = useInnerBlocksProps(blockProps, {
		allowedBlocks: [`${NS}/scroll-story-panel`, `${NS}/scroll-story-step`],
		template: [[`${NS}/scroll-story-panel`], [`${NS}/scroll-story-step`], [`${NS}/scroll-story-step`]],
		renderAppender: () => <BlockInserter clientId={clientId} className='es-mb-4' hasLabel />,
	});

	return (
		<div {...innerBlocksProps} className={classnames(innerBlocksProps.className, blockClass, `${blockClass}--editor`)} />
	);
};
