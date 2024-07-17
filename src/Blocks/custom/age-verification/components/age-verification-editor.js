import React from 'react';
import { BlockInserter, classnames } from '@eightshift/frontend-libs/scripts';
import { useBlockProps, useInnerBlocksProps } from '@wordpress/block-editor';
import { useSelect } from '@wordpress/data';

export const AgeVerificationEditor = ({ attributes, clientId }) => {
	const innerBlocks = useSelect((select) => select('core/block-editor').getBlock(clientId).innerBlocks);

	const {
		ageVerificationAllowedBlocks,
		blockClass,
	} = attributes;
	const blockProps = useBlockProps();

	const innerBlocksProps = useInnerBlocksProps(blockProps, {
		allowedBlocks: (typeof ageVerificationAllowedBlocks === 'undefined') || ageVerificationAllowedBlocks,
		renderAppender: innerBlocks.length === 0 ? () => <BlockInserter clientId={clientId} className='es-mb-4' hasLabel /> : false,
	});

	const modifiedInnerBlockProps = {
		...innerBlocksProps,
		className: classnames(innerBlocksProps.className, blockClass),
	};
	
	return (
		<div {...modifiedInnerBlockProps}>
		</div>
	);
};
