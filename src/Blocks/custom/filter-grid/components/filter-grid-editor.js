import React from 'react';
import { BlockInserter, classnames } from '@eightshift/frontend-libs/scripts';
import { useBlockProps, useInnerBlocksProps } from '@wordpress/block-editor';

// In the editor every item shows; filtering runs on the front end only.
export const FilterGridEditor = ({ attributes, clientId }) => {
	const { blockClass } = attributes;

	const blockProps = useBlockProps();
	const innerBlocksProps = useInnerBlocksProps(blockProps, {
		template: [['core/query']],
		renderAppender: () => <BlockInserter clientId={clientId} className='es-mb-4' hasLabel />,
	});

	return (
		<div {...innerBlocksProps} className={classnames(innerBlocksProps.className, blockClass)} />
	);
};
