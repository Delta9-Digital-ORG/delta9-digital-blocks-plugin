import React from 'react';
import { useBlockProps, InnerBlocks } from '@wordpress/block-editor';

const ALLOWED_BLOCKS = ['core/paragraph', 'core/heading'];

const TEMPLATE = [
	['core/heading', {
		level: 2,
		placeholder: 'Category Label',
		content: 'Category Label',
	}],
];

export const CategoryLabelEditor = ({ attributes }) => {
	const blockProps = useBlockProps();

	return (
		<div {...blockProps} className="category-label">
			<InnerBlocks
				allowedBlocks={ALLOWED_BLOCKS}
				template={TEMPLATE}
				templateLock={false}
			/>
		</div>
	);
};
