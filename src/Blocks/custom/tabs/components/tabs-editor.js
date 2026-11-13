import React from 'react';
import { BlockInserter, classnames } from '@eightshift/frontend-libs/scripts';
import { useBlockProps, useInnerBlocksProps } from '@wordpress/block-editor';

// In the editor every tab is shown, stacked, so each panel can be edited in place.
export const TabsEditor = ({ attributes, clientId }) => {
	const {
		tabsAllowedBlocks,
		blockClass,
	} = attributes;

	const blockProps = useBlockProps();
	const innerBlocksProps = useInnerBlocksProps(blockProps, {
		allowedBlocks: (typeof tabsAllowedBlocks === 'undefined') || tabsAllowedBlocks,
		template: [['eightshift-boilerplate/tab'], ['eightshift-boilerplate/tab']],
		renderAppender: () => <BlockInserter clientId={clientId} className='es-mb-4' hasLabel />,
	});

	return (
		<div {...innerBlocksProps} className={classnames(innerBlocksProps.className, blockClass)} />
	);
};
